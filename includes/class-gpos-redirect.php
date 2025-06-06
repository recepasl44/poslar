<?php
/**
 * GurmePOS için 3D yönlendirme yapmayı sağlayan sınıf olan GPOS_Redirect sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS yönlendirme sınıfı
 */
class GPOS_Redirect extends GPOS_Model {

	/**
	 * Verilerinin tutulduğu tablo.
	 *
	 * @var string
	 */
	public $table_name = 'gpos_redirect';

	/**
	 * 3D yönlendirme verilerini veri tabanından getirir.
	 *
	 * @return string|false
	 */
	public function get_html_content() {
		$html_content = '';
		$hex          = $this->connection->get_var(
			$this->connection->prepare( "SELECT `html_content` FROM {$this->get_table_name()} WHERE `transaction_id` = %s", $this->transaction_id )
		);

		$this->delete_html_content();

		if ( isset( $_GET['gpos_redirect_nonce'] ) && isset( $_GET['gpos_redirect_key'] ) ) {                                                         // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$html_content = gpos_forge()->db_decrypt( $hex, gpos_clean( $_GET['gpos_redirect_nonce'] ), gpos_clean( $_GET['gpos_redirect_key'] ) );   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return $html_content;
	}

	/**
	 * 3D yönlendirme verilerini veri tabanında yazar.
	 *
	 * @param string $html_content Yönlendirme verileri.
	 *
	 * @return GPOS_Redirect
	 */
	public function set_html_content( $html_content ) {
		$forged_data       = gpos_forge()->db_crypt( $html_content, $this->forge_key );
		$this->init_vector = $forged_data['iv'];
		$this->connection->insert(
			$this->get_table_name(),
			array(
				'transaction_id' => $this->transaction_id,
				'html_content'   => $forged_data['hex'],
			)
		);
		return $this;
	}

	/**
	 * 3D verilerini ekrana yazar.
	 */
	public function get_redirect_url() {
		return add_query_arg(
			array(
				'transaction_id'      => $this->transaction_id,
				'gpos_redirect_nonce' => $this->init_vector,
				'gpos_redirect_key'   => $this->forge_key,
			),
			home_url() . '/gpos-redirect/'
		);
	}

	/**
	 * 3D yönlendirme verilerini veri tabanından siler.
	 *
	 * @return void
	 */
	public function delete_html_content() {
		$this->connection->delete(
			$this->get_table_name(),
			array( 'transaction_id' => $this->transaction_id )
		);
	}


	/**
	 * 3D verilerini ekrana yazar.
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	public function render() {
		add_filter( 'trp_stop_translating_page', '__return_true' );

		$transaction = gpos_transaction( $this->transaction_id );
		$transaction->set_status( GPOS_Transaction_Utils::REDIRECTED );
		$is_iframe = $transaction->get_payment_method_type() === GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_IFRAME;

		gpos_get_template(
			'redirect-render',
			array(
				'content'     => $this->get_html_content(),
				'transaction' => $transaction,
				'is_iframe'   => $is_iframe,
			)
		);
	}
}
