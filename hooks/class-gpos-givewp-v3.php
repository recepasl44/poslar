<?php
/**
 * GPOSPRO_GiveWP sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * Bu sınıf eklenti aktif olur olmaz çalışmaya başlar ve
 * kurucu fonksiyonu içerisindeki GiveWP kancalarına tutunur.
 */
class GPOS_GiveWP_V3 {

	/**
	 * Ödeme geçidi tekil kimliği
	 *
	 * @var string
	 */
	public $prefix = GPOS_PREFIX;

	/**
	 * GPOSPRO_GiveWP kurucu fonksiyonu
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'givewp_register_payment_gateway', array( $this, 'register_payment_gateway' ) );
	}

	/**
	 * Ödeme geçidi kayıt
	 *
	 * @param Give\Framework\PaymentGateways\PaymentGatewayRegister $registrar Kayıt sınıfı
	 */
	public function register_payment_gateway( Give\Framework\PaymentGateways\PaymentGatewayRegister $registrar ) {
		$registrar->registerGateway( GPOS_GiveWP_V3_Payment_Gateway::class );
	}
}
