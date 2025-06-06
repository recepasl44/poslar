<?php
/**
 * GurmePOS desteklenen ödeme geçitlerinin organize edildiği sınıfı barındırır.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS desteklenen ödeme geçitleri sınıfı
 */
class GPOS_Payment_Gateways {


	/**
	 * Varsayılan ödeme geçidini türetip döndürür.
	 *
	 * @param GPOS_Transaction|bool $transaction Ödeme işlemi verileri.
	 *
	 * @return GPOS_Payment_Gateway
	 */
	public function get_default_gateway( $transaction ) {
		$account = gpos_gateway_accounts()->get_default_account();
		if ( $transaction instanceof GPOS_Transaction ) {
			return $this->prepare_gateway( $account, $transaction );
		}
		return $this->get_gateway_without_transaction( $account );
	}

	/**
	 * Ödeme hesabının numrasına göre ödeme geçidini türetip döndürür.
	 *
	 * @param int|string            $account_id Hesap no.
	 * @param GPOS_Transaction|bool $transaction Ödeme işlemi verileri.
	 *
	 * @return GPOS_Payment_Gateway
	 */
	public function get_gateway_by_account_id( $account_id, $transaction ) {
		if ( $transaction instanceof GPOS_Transaction ) {
			return $this->prepare_gateway( gpos_gateway_accounts()->get_account( $account_id ), $transaction );
		}
		return $this->get_gateway_without_transaction( gpos_gateway_accounts()->get_account( $account_id ) );
	}


	/**
	 * Hesabının ödeme geçidini türetip döndürür.
	 *
	 * @param GPOS_Gateway_Account|false $account Ödeme geçidi hesabı.
	 * @param GPOS_Transaction           $transaction Ödeme işlemi verileri.
	 *
	 * @return GPOS_Payment_Gateway|false
	 *
	 * @throws Exception Hatalı Hesap yada Ödeme geçidi.
	 */
	private function prepare_gateway( $account, $transaction ) {
		$gateway = false;
		if ( $account && property_exists( $account, 'gateway_class' ) && $account->gateway_class ) {
			$gateway = $account->gateway_class;
			$transaction->set_payment_gateway_id( $account->gateway_id );
			$transaction->set_payment_gateway_class( get_class( $gateway ) );
			$transaction->set_account_id( $account->id );
			$gateway->set_transaction( $transaction );
		}

		if ( gpos_is_ajax() && false === $gateway ) {
			// translators: %s = POS Entegratör Pro.
			throw new Exception( sprintf( esc_html__( 'Invalid gateway, gateway removed or %s disabled.', 'gurmepos' ), 'POS Entegratör Pro' ) ); // phpstan-ignore-line
		}

		return $gateway;
	}

	/**
	 * Hesabının ödeme geçidini türetip döndürür.
	 *
	 * @param GPOS_Gateway_Account|false $account Ödeme geçidi hesabı.
	 *
	 * @return GPOS_Payment_Gateway|false
	 *
	 * @throws Exception Hatalı Hesap yada Ödeme geçidi.
	 */
	private function get_gateway_without_transaction( $account ) {
		$gateway = false;
		if ( $account && property_exists( $account, 'gateway_class' ) && $account->gateway_class ) {
			$gateway = $account->gateway_class;
			return $gateway;
		}

		if ( gpos_is_ajax() && false === $gateway ) {
			// translators: %s = POS Entegratör Pro.
			throw new Exception( sprintf( esc_html__( 'Invalid gateway, gateway removed or %s disabled.', 'gurmepos' ), 'POS Entegratör Pro' ) ); // phpstan-ignore-line
		}

		return $gateway;
	}

	/**
	 * Anahtarı gönderilen ödeme geçidini döndürür.
	 *
	 * @param string $gateway_id Ödeme kuruluşunun idsi.
	 *
	 * @return false|GPOS_Gateway
	 */
	public function get_base_gateway_by_gateway_id( string $gateway_id ) {
		$gateway = array_filter( $this->get_payment_gateways(), fn ( $gateway ) => $gateway_id === $gateway->id );
		return $gateway ? $gateway[ array_key_first( $gateway ) ] : false;
	}

	/**
	 * Hesap tiplerine istinaden aktif geçitleri döndürür.

	 * PAYMENT_METHOD_TYPE_COMMON          = 'common_form_payment';
	 * PAYMENT_METHOD_TYPE_ALTERNATIVE     = 'alternative_payment';
	 * PAYMENT_METHOD_TYPE_BANK_TRANSFER   = 'bank_transfer';
	 * PAYMENT_METHOD_TYPE_SHOPPING_CREDIT = 'shopping_credit';
	 * PAYMENT_METHOD_TYPE_IFRAME          = 'iframe_payment';
	 *
	 * @param string $type Hesap tipi.
	 *
	 * @return array
	 */
	public function get_gateways_by_method_type( $type ) {
		$accounts = gpos_gateway_accounts()->get_accounts( $type );
		if ( false === empty( $accounts ) ) {
			$accounts = array_map(
				function ( $account ) {
					$base_gateway             = gpos_payment_gateways()->get_base_gateway_by_gateway_id( $account->gateway_id );
					$base_gateway->account_id = $account->get_id();
					return $base_gateway;
				},
				$accounts
			);
		}
		return $accounts;
	}

	/**
	 * Desteklenen ödeme kuruluşlarını döndürür.
	 *
	 * @return array
	 */
	public function get_payment_gateways() {
		$payment_gateways = array(
			'GPOS_Paratika',
			'GPOS_Iyzico',
			'GPOS_Iyzico_IFrame',
			'GPOS_Pay_With_Iyzico',
			'GPOS_PayTR_IFrame',
			'GPOS_Dummy_Payment',
			'GPOS_Akode',
			'GPOS_Akbank',
			'GPOS_Akbank_Json',
			'GPOS_Albaraka',
			'GPOS_Craftgate',
			'GPOS_Denizbank',
			'GPOS_Esnekpos',
			'GPOS_Finansbank',
			'GPOS_Finansbank_Payfor',
			'GPOS_Finansbank_Payfor_V2',
			'GPOS_Garanti',
			// 'GPOS_Garanti_Pay',
			'GPOS_Halkbank',
			'GPOS_Halkbank_Mkd',
			'GPOS_Is_Bankasi',
			'GPOS_Is_Bankasi_GiroGate',
			'GPOS_Kuveyt_Turk',
			'GPOS_Lidio',
			'GPOS_Param',
			'GPOS_PayNKolay',
			'GPOS_PayTR',
			'GPOS_QNBpay',
			'GPOS_Sekerbank',
			'GPOS_Sipay',
			'GPOS_Teb',
			'GPOS_Paidora',
			'GPOS_Vakifbank',
			'GPOS_Wyld',
			'GPOS_Yapi_Kredi',
			'GPOS_Ziraat',
			'GPOS_PayBull',
			'GPOS_United_Payment',
			'GPOS_Weepay',
			'GPOS_WorldPAY',
			'GPOS_Papara_Checkout',
			'GPOS_Ziraat_Katilim',
			'GPOS_ZiraatPay',
			'GPOS_Vepara',
			'GPOS_Shopier',
			'GPOS_Mollie',
			'GPOS_IsyerimPOS',
			'GPOS_Ozan',
			'GPOS_Paycell',
			'GPOS_Papara',
			'GPOS_Rubikpara',
			'GPOS_Erpapay',
			'GPOS_Setcard',
			'GPOS_Vallet',
			'GPOS_Moka',
			'GPOS_Hepsipay',
			'GPOS_Vakif_Katilim',
			'GPOS_Tami',
		);

		return apply_filters(
			/**
			 * Desteklenen ödeme kuruluşlarını düzenleme kancasıdır.
			 *
			 * @param array Ödeme geçitleri
			 */
			'gpos_payment_gateways',
			array_map( fn ( $gateway ) => new $gateway(), $payment_gateways )
		);
	}
}
