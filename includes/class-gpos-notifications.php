<?php
/**
 * GurmePOS için bildirimleri organize eden sınıf GPOS_Notifications'ı barındıran dosya.
 *
 * @package Gurmehub
 */

/**
 * Bildirim sınıfı
 */
class GPOS_Notifications {

	/**
	 * Ayar sınıfı.
	 *
	 * @var array
	 */
	public $settings;

	/**
	 * Kurucu method.
	 */
	public function __construct() {
		$this->settings = gpos_notification_settings()->get_settings();
	}

	/**
	 * Bildirimleri kayıt eder.
	 */
	public function register() {
		if ( $this->settings['daily']['active'] && ! wp_next_scheduled( 'gpos_daily_transaction_notification' ) ) {
			$datetime = date_create( $this->settings['daily']['notify_hour'], wp_timezone() );
			wp_schedule_event( $datetime->getTimestamp(), 'daily', 'gpos_daily_transaction_notification' );
		}

		if ( $this->settings['weekly']['active'] && ! wp_next_scheduled( 'gpos_weekly_transaction_notification' ) ) {
			$datetime = date_create( "{$this->settings['weekly']['notify_day']} {$this->settings['weekly']['notify_hour']}", wp_timezone() );
			wp_schedule_event( $datetime->getTimestamp(), 'weekly', 'gpos_weekly_transaction_notification' );
		}
	}

	/**
	 * Günlük mail gönderilmeli mi ?
	 *
	 * @return bool
	 */
	public function daily_is_avaiable() {
		return in_array( strtolower( gmdate( 'l' ) ), is_array( $this->settings['daily']['notify_days'] ) ? $this->settings['daily']['notify_days'] : array(), true );
	}

	/**
	 * Dizi içerisindeki e-postaları kontrol etmeye yarayan fonksiyon
	 *
	 * @param array $addresses girilen mail adreslerinin dizisi.
	 *
	 * @return array|bool
	 */
	public function check_emails( $addresses ) {
		return array_filter( $addresses, fn( $address ) => is_email( $address ) );
	}

	/**
	 * Hatalı işlemleri bildirir.
	 *
	 * @param string     $error_message Hata mesajı.
	 * @param int|string $transaction_id İşlem numarası.
	 */
	public function error_transaction_notification( $error_message, $transaction_id ) {
		$this->send_transaction_notification(
			explode( PHP_EOL, $this->settings['errors']['emails'] ),
			'POS Entegratör ' . __( 'Error in payment process', 'gurmepos' ),
			'emails/error.php',
			array(
				'error_message' => $error_message,
				'transaction'   => gpos_transaction( $transaction_id ),
			)
		);
	}

	/**
	 * Başarılı işlemleri bildirir.
	 *
	 * @param int|string $transaction_id İşlem numarası.
	 */
	public function success_transaction_notification( $transaction_id ) {
		$this->send_transaction_notification(
			explode( PHP_EOL, $this->settings['success']['emails'] ),
			sprintf( '#%s %s', $transaction_id, __( 'New Payment Transaction', 'gurmepos' ) ),
			'emails/success.php',
			array(
				'transaction' => gpos_transaction( $transaction_id ),
			)
		);
	}

	/**
	 * İşlem bildirimlerini gönderir
	 *
	 * @param string[] $addresses Mesajın gönderileceği adresler
	 * @param string   $title Mesajın başlığı
	 * @param string   $template Kullanılacak template.
	 * @param array    $args Şablonda kullanıcak argumanlar.
	 */
	private function send_transaction_notification( $addresses, $title, $template, $args ) {
		$addresses = $this->check_emails( $addresses );
		if ( ! empty( $addresses ) ) {
			ob_start();
			gpos_get_view(
				$template,
				$args
			);
			$html = ob_get_clean();
			$this->mail_to( $addresses, $title, $html );
		}
	}
	/**
	 * Ödeme işlemlerinin günlük bildirimi.
	 *
	 * @return void
	 */
	public function daily_transaction_notification() {
		if ( $this->daily_is_avaiable() ) {
			$addresses = $this->check_emails( explode( PHP_EOL, $this->settings['daily']['emails'] ) );
			if ( ! empty( $addresses ) ) {
				$start_date = gmdate( 'Y-m-d H:i:s', strtotime( '-24 hours', strtotime( current_time( 'mysql' ) ) ) );
				$failed     = $this->get_posts( $start_date, GPOS_Transaction_Utils::FAILED );
				$successful = $this->get_posts( $start_date, GPOS_Transaction_Utils::COMPLETED );

				if ( 0 !== (int) $successful->found_posts || 0 !== (int) $failed->found_posts ) {
					ob_start();
					gpos_get_view(
						'emails/transaction-info.php',
						array(
							'success_total' => $successful->found_posts,
							'failed_total'  => $failed->found_posts,
							'start_date'    => $start_date,
							'period'        => 'daily',
						)
					);
					$this->mail_to( $addresses, 'POS Entegratör ' . __( 'Daily Transaction Notification', 'gurmepos' ), ob_get_clean() );
				}
			}
		}
	}

	/**
	 * Ödeme işlemlerinin haftalık bildirimi.
	 *
	 * @return void
	 */
	public function weekly_transaction_notification() {
		$addresses = $this->check_emails( explode( PHP_EOL, $this->settings['weekly']['emails'] ) );
		if ( ! empty( $addresses ) ) {
			$start_date = gmdate( 'Y-m-d H:i:s', strtotime( '-1 week', strtotime( current_time( 'mysql' ) ) ) );
			$failed     = $this->get_posts( $start_date, GPOS_Transaction_Utils::FAILED );
			$successful = $this->get_posts( $start_date, GPOS_Transaction_Utils::COMPLETED );

			ob_start();
			gpos_get_view(
				'emails/transaction-info.php',
				array(
					'success_total' => $successful->found_posts,
					'failed_total'  => $failed->found_posts,
					'start_date'    => $start_date,
					'period'        => 'weekly',
				)
			);

			$this->mail_to( $addresses, 'POS Entegratör ' . __( 'Weekly Transaction Notification', 'gurmepos' ), ob_get_clean() );
		}
	}

	/**
	 * Verilen gün ile şimdi arasındaki işlem sayılarını statüse göre getirme.
	 *
	 * @param string $start_date İşlemin başlangıç tarihi.
	 * @param string $status İşlemin durumu.
	 *
	 * @return WP_Query
	 */
	protected function get_posts( $start_date, $status ) {
		$args = array(
			'post_status'    => $status,
			'post_type'      => 'gpos_transaction',
			'date_query'     => array(
				array(
					'after'     => $start_date,
					'inclusive' => true,
				),
			),
			'tax_query'      => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'gpos_transaction_process_type',
					'field'    => 'slug',
					'terms'    => GPOS_Transaction_Utils::PAYMENT,
				),
			),
			'posts_per_page' => -1, //phpcs:ignore WordPress.WP.PostsPerPageNoUnlimited.posts_per_page_posts_per_page
		);

		return new WP_Query( $args );
	}

	/**
	 * Mail gönderimini sağlar.
	 *
	 * @param array  $addresses  Gönderilecek adresler.
	 * @param string $subject Gönderilecek başlık.
	 * @param string $html Gönderilecek HTML kodu.
	 *
	 * @return void
	 */
	protected function mail_to( $addresses, $subject, $html ) {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		wp_mail( $addresses, $subject, $html, $headers );
	}
}
