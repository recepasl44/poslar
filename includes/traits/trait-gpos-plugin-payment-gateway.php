<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * GurmePOS ödeme eklentilerinin sınıflarını oluştururken kullanılacak temel sınıf.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS GPOS_Plugin_Gateway abstract sınıfı
 */
trait GPOS_Plugin_Payment_Gateway {


	/**
	 * Form ayarları
	 *
	 * @var GPOS_Form_Settings $form_settings
	 */
	public $form_settings;

	/**
	 * Ödeme geçidi tekil kimliği
	 *
	 * @var string $gpos_prefix
	 */
	public $gpos_prefix = GPOS_PREFIX;

	/**
	 * Ödeme verisi.
	 *
	 * @var array $post_data
	 */
	public $post_data;

	/**
	 * Ödeme geçidi.
	 *
	 * @var GPOS_Payment_Gateway|false $gateway
	 */
	public $gateway;

	/**
	 * Hesap ataması yapılmamış ödeme geçidi.
	 *
	 * @var GPOS_Gateway $gateway
	 */
	public $base_gateway;

	/**
	 * Ödeme geçidi hesabının benzersiz kimlik numarası.
	 *
	 * @var int|string $account_id
	 */
	public $account_id;

	/**
	 * İşlemin ödeme eklentisi.
	 *
	 * @var int|string $plugin_transaction_id
	 */
	public $plugin;

	/**
	 * İşlemin ödeme eklentisindeki kimlik numarası.
	 *
	 * @var int|string $plugin_transaction_id
	 */
	public $plugin_transaction_id;

	/**
	 * Ödeme geçidi hesabı.
	 *
	 * @var GPOS_Gateway_Account $account
	 */
	public $account;

	/**
	 * Ödeme bilgisi.
	 *
	 * @var GPOS_Transaction $transaction
	 */
	public $transaction;

	/**
	 * Ödemede 3D güvenlik kullanılacak mı bilgisi.
	 *
	 * @var bool $threed
	 */
	public $threed = false;

	/**
	 * Ödemede kart kayıt edilsin mi?
	 *
	 * @var bool $save_card
	 */
	public $save_card = false;

	/**
	 * Ödemede kullanılan kayıtlı kart.
	 *
	 * @var bool|int|string $saved_card
	 */
	public $saved_card = false;

	/**
	 * Taksit sayısı.
	 *
	 * @var int|string $installment
	 */
	public $installment;

	/**
	 * Kayıtlı kart kullanılacak mı?
	 *
	 * @var bool $use_saved_card
	 */
	public $use_saved_card;

	// Protected Methods

	/**
	 * Ödemenin basit ödeme formu ile mi yapıldığını kontrol eder, kullanılacak post verilerini belirler.
	 *
	 * @param array $post_data Ödeme verisi.
	 *
	 * @return void
	 */
	protected function create_post_data( $post_data ) {
		$this->post_data = isset( $post_data['gpos-sample-form'] ) &&
			'on' === $post_data['gpos-sample-form'] ? $post_data :
			gpos_forge()->checkout_decrypt( $post_data['_wp_refreshed_fragments'], $post_data['_wp_fragment'], $post_data['_gpos_nonce'] );
	}

	/**
	 * İşlem objesini hazırlar.
	 *
	 * @return void
	 */
	protected function create_transaction() {
		$this->transaction = gpos_transaction()
			->set_plugin_transaction_id( $this->plugin_transaction_id )
			->set_plugin( $this->plugin )
			->set_type( GPOS_Transaction_Utils::PAYMENT );
	}

	/**
	 * Ödeme verilerisi içeriğine göre sınıf atamalarını gerçekleştir.
	 *
	 * @return void
	 */
	protected function set_isset_results() {
		$this->account_id     = isset( $this->post_data[ "{$this->gpos_prefix}-account-id" ] ) ? $this->post_data[ "{$this->gpos_prefix}-account-id" ] : $this->account_id;
		$this->threed         = isset( $this->post_data[ "{$this->gpos_prefix}-threed" ] ) && 'on' === $this->post_data[ "{$this->gpos_prefix}-threed" ];
		$this->use_saved_card = isset( $this->post_data[ "{$this->gpos_prefix}-use-saved-card" ] ) && 'on' === $this->post_data[ "{$this->gpos_prefix}-use-saved-card" ];
		$this->save_card      = isset( $this->post_data[ "{$this->gpos_prefix}-save-card" ] ) && 'on' === $this->post_data[ "{$this->gpos_prefix}-save-card" ];
		$this->saved_card     = isset( $this->post_data[ "{$this->gpos_prefix}-saved-card" ] ) ? $this->post_data[ "{$this->gpos_prefix}-saved-card" ] : false;
		$this->installment    = isset( $this->post_data[ "{$this->gpos_prefix}-installment" ] ) ? $this->post_data[ "{$this->gpos_prefix}-installment" ] : 0;
	}

	/**
	 * Ödemeyi yapacak araç bilgisini oluştur.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	protected function set_payment_instrument() {
		if ( $this->use_saved_card ) {
			$this->transaction->set_use_saved_card( true );
			$this->transaction->set_saved_card_id( $this->saved_card );
			do_action( 'gpos_transaction_use_saved_card', $this->transaction, $this->saved_card );
		} else {
			$this->transaction->set_save_card(
				( class_exists( 'WC_Subscriptions_Cart' ) && WC_Subscriptions_Cart::cart_contains_subscription() ) || // Sepette abonelik ürünü varsa kaydetmeye zorlamak için eklendi.
					$this->save_card
			);
			$this->card_setter( $this->transaction );
		}
	}

	/**
	 * Taksiti belirler.
	 *
	 * @return void
	 */
	protected function set_installment() {
		if ( (int) $this->installment > 1 ) {
			$this->transaction->set_installment( $this->installment );
			$this->transaction->set_installment_rate( $this->post_data[ "{$this->gpos_prefix}-installment-rate" ] );
		}
	}

	/**
	 * Ödeme hesabı, ödeme geçidini ve geçidin ana özelliklerini taşıyan sınıfları tespit eder.
	 *
	 * @return void
	 */
	protected function prepare_payment() {

		/**
		 * $account_id doğrudan iletilmişse belirtilen account ve gateway kullanılır.
		 *
		 * Bu senaryo common, iframe, alternatif ödemeler gibi ön tanımlı id ile ödemeye gelen geçitlerde kullanılır.
		 */
		if ( $this->account_id ) {
			$this->account = gpos_gateway_account( (int) $this->account_id );
			$this->gateway = gpos_payment_gateways()->get_gateway_by_account_id( (int) $this->account_id, $this->transaction );
		} else {
			/**
			 * $account_id yoksa ve pro aktifse işlem gate e uygun mu kontolü için hook tetiklenir.
			 *
			 * Bu senaryo da gate aktifse ve bir ödeme kuralına takılmışsa $account_id dolacaktır.
			 */
			if ( gpos_is_pro_active() ) {
				$this->account_id = apply_filters( 'gpos_get_payment_account_id', $this->account_id, $this->transaction );
			}
			/**
			 * $account_id dolmuşsa belirtilen aksi halde varsayılan account ve gateway kullanılır.
			 */
			if ( $this->account_id ) {
				$this->account = gpos_gateway_account( (int) $this->account_id );
				$this->gateway = gpos_payment_gateways()->get_gateway_by_account_id( (int) $this->account_id, $this->transaction );
			} else {
				$this->account = gpos_gateway_accounts()->get_default_account();
				$this->gateway = gpos_payment_gateways()->get_default_gateway( $this->transaction );
			}
		}
		$this->base_gateway = gpos_payment_gateways()->get_base_gateway_by_gateway_id( $this->account->gateway_id );
	}

	/**
	 * Taksit komisyonu belirler.
	 *
	 * @return void
	 */
	protected function set_installment_fee() {
		if ( (int) $this->installment > 1 && true === $this->base_gateway->add_fee_for_installment ) {
			$total_with_fee = $this->account->installment_rate_calculate( (float) $this->transaction->get_installment_rate(), $this->transaction->get_total() );
			$this->set_fee_line( $total_with_fee );
		}
	}

	/**
	 * Transaction fee değelerinie ekler
	 *
	 * @param float $total_with_fee Vade farklı toplam tutar.
	 *
	 * @return void
	 */
	public function set_fee_line( $total_with_fee ) {
		if ( (int) $this->transaction->get_installment() > 1 ) {
			$transaction_total = $this->transaction->get_total();
			$fee               = $total_with_fee - $transaction_total;

			if ( 0 < $fee ) {
				$id = null;

				if ( ! empty( $this->transaction->get_lines( array( 'fee' ) ) ) ) {
					$id = $this->transaction->get_lines( array( 'fee' ) )[0]->get_id();
				}

				$fee_line = gpos_transaction_line( $id )
					// translators: vade farkı
					->set_name( sprintf( __( 'Installment Fee (%s Mounth)', 'gurmepos' ), $this->transaction->get_installment() ) )
					->set_quantity( 1 )
					->set_type( 'fee' )
					->set_total( $fee );

				$this->transaction->add_line( $fee_line );
				$this->transaction->set_total( $total_with_fee );
			}
		}
	}
	/**
	 * Taksit komisyonu döndürür.
	 *
	 * @return void|GPOS_Transaction_Line
	 */
	protected function get_installment_fee() {
		$fees = $this->transaction->get_lines( array( 'fee' ) );
		if ( 0 < $this->transaction->get_installment_rate() && ! empty( $fees ) ) {
			return array_shift( $fees );
		}
	}

	/**
	 * Ödemenin belirleyici özelliklerini ayarlar.
	 *
	 * @return void
	 */
	protected function set_payment_properties() {
		$this->transaction->set_payment_method_type( $this->base_gateway->payment_method_type );
		$this->transaction->set_payment_form_type( $this->base_gateway->payment_form_type );
		switch ( $this->base_gateway->payment_form_type ) {
			case GPOS_Transaction_Utils::FORM_TYPE_CARD:
				$this->transaction->set_security_type( false === $this->threed && in_array( GPOS_Transaction_Utils::REGULAR, $this->base_gateway->supports, true ) ? GPOS_Transaction_Utils::REGULAR : GPOS_Transaction_Utils::THREED );
				break;
			case GPOS_Transaction_Utils::FORM_TYPE_EMPTY:
				break;
		}
	}

	/**
	 * Ödemenin extra özelliklerini ayarlar.
	 *
	 * @return void
	 */
	protected function set_extra_properties() {
		foreach ( array( 'isbank_girogate_gateway', 'isbank_girogate_country' ) as $key ) {
			$input_key = str_replace( '_', '-', $key );
			$input     = "{$this->gpos_prefix}-{$input_key}";
			if ( isset( $this->post_data[ $input ] ) ) {
				$this->transaction->add_meta( $key, $this->post_data[ $input ] );
			}
		}
		if ( GPOS_Transaction_Utils::THREED === $this->transaction->get_security_type() && ( property_exists( $this->account->gateway_settings, 'merchant_threed_type' ) || property_exists( $this->account->gateway_settings, 'test_merchant_threed_type' ) ) ) {
			$this->transaction->set_threed_type( gpos_is_test_mode() ? $this->account->gateway_settings->test_merchant_threed_type : $this->account->gateway_settings->merchant_threed_type );
		}
	}

	// Public Methods

	/**
	 * Ödeme işlemini organize eden temel method.
	 *
	 * @param array      $post_data Ön yüzden alınmış form verileri.
	 * @param int|string $plugin_transaction_id Ödeme eklentisindeki benzersiz kimlik numarası.
	 * @param string     $plugin Ödeme eklentisi.
	 * @param int|string $account_id Ödemenin yapılacağı geçit.
	 *
	 * @return GPOS_Gateway_Response
	 */
	public function create_new_payment_process( $post_data, $plugin_transaction_id, $plugin, $account_id = 0 ) {
		$this->form_settings         = gpos_form_settings();
		$this->plugin_transaction_id = $plugin_transaction_id;
		$this->account_id            = $account_id;
		$this->plugin                = $plugin;

		/**
		 * Method sıralaması ve bağımlılıklara dikkat ederek düzenleme yapınız.
		 */
		$this->create_post_data( $post_data );      // Adım 1 : Basit yada hashli formdan gelen verileri sınıfa tanımla.
		$this->create_transaction();                // Adım 2 : GPOS_Transaction objesini oluştur.
		$this->set_isset_results();                 // Adım 3 : isset() fonksiyonu ile yapılması gereken tanımlamaları yap.
		$this->set_payment_instrument();            // Adım 4 : Ödeme aracını belirleme.
		$this->set_installment();                   // Adım 5 : Taksit miktarını belirleme.
		$this->set_properties();                    // Adım 6 : GPOS_Transaction objesine ödeme için gerekli atamaları yap. (Üst katmanda)
		$this->prepare_payment();                   // Adım 7 : Ödemenin geçeceği hesabı ve geçidi ayarlar.  Dept: set_payment_instrument, set_properties.
		$this->set_payment_properties();            // Adım 8 : Ödemenin form tipini ve ödeme türünü belirler. Dept: prepare_payment
		$this->set_installment_fee();               // Adım 9 : Ödeme geçidine istinaden varsa taksit için komisyon atamalarını yapar. Dept: prepare_payment
		$this->set_extra_properties();              // Adım 10 : Ödeme için gerekli özel alanlar.

		return $this->gateway->before_process_payment()->process_payment();
	}

	/**
	 * Geri dönüş fonksiyonu ödeme geçitlerinden gelen veriler bu fonksiyonda karşılanır.
	 *
	 * @param int|string $transaction_id İşlem numarası.
	 */
	public function process_callback( $transaction_id ) {
		$this->form_settings = gpos_form_settings();
		$post_data           = gpos_clean( $_REQUEST ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		gpos_unset_nonces( $post_data );
		try {
			$this->transaction = gpos_transaction( $transaction_id );
			$this->transaction->add_log( GPOS_Transaction_Utils::LOG_PROCESS_CALLBACK, [], $post_data );
			$this->gateway = gpos_payment_gateways()->get_gateway_by_account_id( $this->transaction->get_account_id(), $this->transaction );
			$this->gateway->gateway_response->set_error_message( gpos_get_default_callback_error_message() );
			$response = $this->gateway->process_callback( $post_data );
			if ( $response->is_success() ) {
				$this->transaction_success_process( $response );
				$this->success_process( $response, false );
			} else {
				$this->transaction_error_process( $response );
				$this->error_process( $response, false );
			}
		} catch ( Exception $e ) {
			return $this->exception_handler( $e, false );
		}
	}

	/**
	 * Ödeme geçidinin yönlendirmeye ihtiyacı varsa gerekli yönlendirmeyi ayarlar ve linki döndürür.
	 *
	 * @param GPOS_Gateway_Response $response Ödeme geçidi yanıtı
	 *
	 * @return false|string Yönlendirme gerekiyorsa link, gerekmiyorsa false döner.
	 */
	public function get_redirect_url( $response ) {
		$link = false;
		switch ( $this->base_gateway->payment_method_type ) {
			case GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_COMMON:
				$link = $response->get_common_form_url();
				break;
			case GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_ALTERNATIVE:
				$link = $response->get_alternative_payment_url();
				break;
			case GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_IFRAME:
				$link = gpos_redirect( $this->transaction->id )->set_html_content( $response->get_html_content() )->get_redirect_url();
				break;
			case GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_VIRTUAL_POS:
				if ( GPOS_Transaction_Utils::THREED === $this->transaction->get_security_type() || $response->get_html_content() ) {
					$link = gpos_redirect( $this->transaction->id )->set_html_content( $response->get_html_content() )->get_redirect_url();
				}
				break;
			default:
				$link = false;
		}
		return $link;
	}

	/**
	 * İşlem iframe içerisinde yapılmış ise yönlendirme yapar.
	 *
	 * @param string $redirect_url Yönlendirme linki.
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	public function iframe_redirect( $redirect_url ) {
		?>
		<script>
			window.parent.location.href = '<?php echo esc_url_raw( $redirect_url ); ?>';
		</script>
		<?php
		exit;
	}

	/**
	 * GPOS_Frontend tarafından yaratılan ödeme formundaki kart bilgi alanlarını işleme yansıtır.
	 *
	 * @param mixed $card Kart verilerinin setleneceği obje.
	 *
	 * @return void
	 */
	public function card_setter( &$card ) {
		foreach (
			array(
				'card_bin',
				'card_cvv',
				'card_expiry_month',
				'card_expiry_year',
				'card_type',
				'card_brand',
				'card_family',
				'card_bank_name',
				'card_bank_code',
				'card_country',
				'card_country_code',
				'card_name',
				'card_holder_name',
			) as $property
		) {
			$fnc = "set_{$property}";
			if ( method_exists( $card, $fnc ) ) {
				$property = str_replace( '_', '-', $property );
				$key      = "{$this->gpos_prefix}-{$property}";
				$param    = isset( $this->post_data[ $key ] ) && false === empty( $this->post_data[ $key ] ) ? $this->post_data[ $key ] : '';
				call_user_func_array( array( $card, $fnc ), array( $param ) );
			}
		}
	}

	/**
	 * İşlem için başarılı olma durumunda yapılacaklar.
	 *
	 * @param GPOS_Gateway_Response $response GPOS_Gateway_Response objesi.
	 */
	public function transaction_success_process( $response ) {
		if ( $response->is_pending_payment() ) {
			// translators: %s => Ödeme geçidindeki tekil kimlik.
			$message = sprintf( __( 'Pending payment. Payment number: %s', 'gurmepos' ), $response->get_payment_id() );
			$status  = GPOS_Transaction_Utils::PENDING;
		} else {
			// translators: %s => Ödeme geçidindeki tekil kimlik.
			$message = $response->get_payment_id() ? sprintf( __( 'Payment completed successfully. Payment number: %s', 'gurmepos' ), $response->get_payment_id() ) : __( 'Payment completed successfully.', 'gurmepos' );
			$status  = GPOS_Transaction_Utils::COMPLETED;
		}

		$this->transaction->add_note( $message, 'complete' );
		if ( $response->get_payment_id() ) {
			$this->transaction->set_payment_id( $response->get_payment_id() );
		}
		$this->transaction->set_status( $status );
		do_action( 'gpos_success_transaction', $this->transaction );
	}

	/**
	 * İşlem için başarısız olma durumunda yapılacaklar.
	 *
	 * @param GPOS_Gateway_Response $response GPOS_Gateway_Response objesi.
	 */
	public function transaction_error_process( $response ) {
		$this->transaction->set_status( GPOS_Transaction_Utils::FAILED );
		$this->transaction->set_error_message( $response->get_error_message() );
		$this->transaction->add_note( $response->get_error_message(), 'failed' );
		do_action( 'gpos_failed_transaction', $response, $this->transaction );
	}

	/**
	 * Ödeme esnasında alınan hataları işleme yansıtır.
	 *
	 * @param Exception $exception Hata.
	 * @param boolean   $on_checkout Ödeme sayfasında mı ?
	 */
	public function exception_handler( Exception $exception, $on_checkout ) {
		$error_exception = new GPOS_Gateway_Response( is_object( $this->gateway ) ? get_class( $this->gateway ) : 'undefined' );
		$error_exception->set_error_message( $exception->getMessage() );
		$this->transaction_error_process( $error_exception );
		return $this->error_process( $error_exception, $on_checkout );
	}
}
