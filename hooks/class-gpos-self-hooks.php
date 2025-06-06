<?php
/**
 * GPOS_Self_Hooks sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * Bu sınıf GurmePOS un kendi içerisinde attığı kancaları barındırır.
 */
class GPOS_Self_Hooks {

	/**
	 * GPOS_Self_Hooks kurucu fonksiyonu
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'gpos_success_transaction', array( $this, 'success_transaction' ) );
		add_action( 'gpos_failed_transaction', array( $this, 'failed_transaction' ), 10, 2 );
		add_filter( 'gpos_payment_language', array( $this, 'payment_language' ) );
	}

	/**
	 * Başarıyla tamamlanmış işlemlerin sonuna tanımlanmış aksiyon.
	 *
	 * @param GPOS_Transaction $transaction İşlem.
	 *
	 * @return void
	 */
	public function success_transaction( GPOS_Transaction $transaction ) {

		gpos_tracker()->schedule_event(
			'transaction',
			array(
				'site'            => home_url(),
				'payment_gateway' => str_replace( [ 'GPOS', 'PRO', '_', 'Gateway' ], '', $transaction->get_payment_gateway_class() ),
				'payment_plugin'  => $transaction->get_plugin(),
				'total'           => $transaction->get_total(),
				'currency'        => $transaction->get_currency(),
				'installment'     => (int) $transaction->get_installment(),
				'use_saved_card'  => (int) $transaction->need_use_saved_card(),
				'save_card'       => (int) $transaction->get_save_card(),
				'gate_used'       => (int) $transaction->get_gate_affected(),
				'security_type'   => $transaction->get_security_type() ? $transaction->get_security_type() : '',
				'is_test'         => gpos_is_test_mode(),
				'version'         => GPOS_VERSION,
			)
		);
		if ( true === gpos_notification_settings()->get_setting_by_key( 'success' )['active'] ) {
			wp_schedule_single_event( time(), 'gpos_success_transaction_notification', array( $transaction->id ) );
		}
	}

	/**
	 * Başarısız işlemlerin sonuna tanımlanmış aksiyon.
	 *
	 * @param GPOS_Gateway_Response $response İşlem.
	 * @param GPOS_Transaction      $transaction İşlem.
	 *
	 * @return void
	 */
	public function failed_transaction( GPOS_Gateway_Response $response, GPOS_Transaction $transaction ) {
		gpos_tracker()->schedule_event(
			'error',
			array(
				'site'            => home_url(),
				'error_code'      => $response->get_error_code(),
				'error_message'   => $response->get_error_message(),
				'payment_gateway' => str_replace( [ 'GPOS', 'PRO', '_', 'Gateway' ], '', $response->get_gateway() ),
				'payment_plugin'  => $transaction->get_plugin(),
				'is_test'         => gpos_is_test_mode(),
				'version'         => GPOS_VERSION,

			)
		);

		if ( true === gpos_notification_settings()->get_setting_by_key( 'errors' )['active'] ) {
			wp_schedule_single_event( time(), 'gpos_error_transaction_notification', array( $response->get_error_message(), $transaction->id ) );
		}
	}


	/**
	 * İşlem dilini tespit eder.
	 *
	 * @param string $lang Dil.
	 *
	 * @return string
	 */
	public function payment_language( $lang ) {
		if ( function_exists( 'trp_get_locale' ) ) {                                    // TranslatePress https://translatepress.com/
			$lang = substr( trp_get_locale(), 0, 2 );
		} elseif ( function_exists( 'PLL' ) ) {                                         // Polylang https://polylang.pro/
			$lang = substr( PLL()->curlang->locale, 0, 2 );
		} elseif ( function_exists( 'weglot_get_current_language' ) ) {                 // Weglot https://www.weglot.com/
			$lang = substr( weglot_get_current_language(), 0, 2 );
		}

		return $lang;
	}
}
