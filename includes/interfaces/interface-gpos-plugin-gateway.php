<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * GurmePOS'un entegre olacağı tüm ödeme eklentilerinin ödeme sınıfı yazılırken dikkat edilmesi gereken methodlar için interface sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS GPOS_Plugin_Gateway interface sınıfı
 *
 * @method void set_fee_line( $total_amount )
 */
interface GPOS_Plugin_Gateway {

	/**
	 * Ödeme başlangıcıdır, GPOS_Plugin_Payment_Gateway tratinde bulunur.
	 *
	 * @param array      $post_data Ödeme verileri
	 * @param int|string $plugin_transaction_id Ödeme eklentisindeki benzersiz kimlik numarası.
	 * @param string     $plugin Ödeme eklentisi.
	 *
	 * @return void
	 */
	public function create_new_payment_process( $post_data, $plugin_transaction_id, $plugin );

	/**
	 * Ödeme işlemi için kart bilgileri hariç tüm verilerin tanımlandığı methodtur.
	 */
	public function set_properties();

	/**
	 * Ödeme işleminin başarıya ulaşması sonucunda yapılacak işlemlerin hepsini barındırır.
	 *
	 * @param GPOS_Gateway_Response $response Ödeme geçidi cevabı
	 * @param bool                  $on_checkout Ödeme sayfasında mı ?
	 *
	 * @return array|void
	 */
	public function success_process( GPOS_Gateway_Response $response, $on_checkout );

	/**
	 * Ödeme işleminin bildirim tarafından gelen cevaba istinaden yapılacak aksiyonları organzie eder.
	 *
	 * @param GPOS_Gateway_Response $response Ödeme geçidi cevabı
	 *
	 * @return void
	 */
	public function notify_process( GPOS_Gateway_Response $response );

	/**
	 * Ödeme işleminin hatayla karşılaşması sonucunda yapılacak işlemlerin hepsini barındırır.
	 *
	 * @param GPOS_Gateway_Response $response Ödeme geçidi cevabı
	 * @param bool                  $on_checkout Ödeme sayfasında mı ?

	 * @return array|void
	 *
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public function error_process( GPOS_Gateway_Response $response, $on_checkout );

	/**
	 * Geri dönüş fonksiyonu ödeme geçitlerinden gelen veriler bu fonksiyonda karşılanır.
	 *
	 * @param int|string $transaction_id İşlem numarası.
	 */
	public function process_callback( $transaction_id );

	/**
	 * İşlem için başarılı olma durumunda yapılacaklar.
	 *
	 * @param GPOS_Gateway_Response $response GPOS_Gateway_Response objesi.
	 */
	public function transaction_success_process( $response );

	/**
	 * İşlem için başarısız olma durumunda yapılacaklar.
	 *
	 * @param GPOS_Gateway_Response $response GPOS_Gateway_Response objesi.
	 */
	public function transaction_error_process( $response );
}
