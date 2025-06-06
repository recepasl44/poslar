<?php
/**
 * Bu dosya "gpos_" prefixli yardımcı fonksiyonları barındırır.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS için görünüm dosyasını getirir.
 *
 * @param string $view_name Dahil edilecek görünüm.
 * @param array  $args Görünüm içerisinde kullanılacak veriler.
 * @param string $view_path Görünüm klasör yolu.
 *
 * @return void
 */
function gpos_get_view( $view_name, $args = array(), $view_path = GPOS_PLUGIN_DIR_PATH ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args ); //phpcs:ignore  WordPress.PHP.DontExtract.extract_extract
	}
	$view = $view_path . '/views/' . $view_name;
	include $view;
}

/**
 * WooCommerce'in kurulu ve aktif olup olmadığını kontrol eder.
 *
 * @return bool
 */
function gpos_is_woocommerce_enabled() {
	return class_exists( 'WooCommerce' );
}

/**
 * WooCommerce'in kurulu ve aktif olup olmadığını kontrol eder.
 *
 * @return bool
 */
function gpos_is_givewp_v3_enabled() {
	return class_exists( 'Give' ) && defined( 'GIVE_VERSION' ) && version_compare( GIVE_VERSION, '3.0.0', '>=' );
}

/**
 * GurmePOS Pro eklentisinin kurulu ve aktif olup olmadığını kontrol eder.
 *
 * @return bool
 */
function gpos_is_pro_active() {
	return class_exists( 'GurmePOS_PRO' );
}

/**
 * GurmePOS Form eklentisinin kurulu ve aktif olup olmadığını kontrol eder.
 *
 * @return bool
 */
function gpos_is_form_active() {
	return class_exists( 'GurmePOS_Form' );
}

/**
 * Ajaxın anlık aktiflik durumunu kontrol eder..
 *
 * @return bool
 */
function gpos_is_ajax() {
	return apply_filters( 'gpos_is_ajax', function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : DOING_AJAX ); // @phpstan-ignore-line
}

/**
 * GurmePOS'un test modunda olma durumunu döndürür.
 *
 * @return bool
 */
function gpos_is_test_mode() {
	return gpos_settings()->is_test_mode();
}

/**
 * GurmePOS'un ödeme dilini döndürür.
 *
 * @return string
 */
function gpos_get_payment_locale() {
	$lang = gpos_form_settings()->get_setting_by_key( 'payment_lang' );
	return 'auto' === $lang ? apply_filters( 'gpos_payment_language', 'tr' ) : $lang;
}
/**
 * Veri temizleme işlemi. sanitize_text_field fonksiyonunu kullanır.
 * Gönderilen parametre dizi ise (array) her elemanını için tekrar kendini çağırır.
 *
 * @param mixed $variable Temizlenecek veri.
 *
 * @return mixed
 */
function gpos_clean( $variable ) {
	if ( is_array( $variable ) ) {
		return array_map( 'gpos_clean', $variable );
	}
	return is_scalar( $variable ) ? sanitize_text_field( wp_unslash( $variable ) ) : $variable;
}

/**
 * Geri dönüş verilerindeki nonce bilgilerini temizler.
 *
 * @param array $variable Temizlenecek dizi.
 *
 * @return void
 */
function gpos_unset_nonces( &$variable ) {
	unset( $variable['_wpnonce'] );
	unset( $variable['woocommerce-edit-address-nonce'] );
	unset( $variable['woocommerce-login-nonce'] );
	unset( $variable['woocommerce-reset-password-nonce'] );
	unset( $variable['save-account-details-nonce'] );
}


/**
 * GurmePOS tarafından desteklenen ödeme kuruluşlarını döndürür.
 *
 * @return array Desteklenen ödeme kuruluşları.
 */
function gpos_get_payment_gateways() {
	return gpos_payment_gateways()->get_payment_gateways();
}


/**
 * Sipariş onayı, siparişin geçeceği durum gibi ayarlarda
 * kullanılmak için WooCommerce sipariş durumlarını döndürür.
 * İptal Edildi, İade Edildi gibi durumları diziden çıkartır.
 *
 * @return array $gpos_statuses
 */
function gpos_get_wc_order_statuses() {
	$gpos_statuses     = array();
	$disabled_statuses = array(
		'wc-cancelled',
		'wc-refunded',
		'wc-pending',
		'wc-on-hold',
		'wc-failed',
		'wc-checkout-draft',
	);

	if ( function_exists( 'wc_get_order_statuses' ) ) {
		foreach ( wc_get_order_statuses() as $status_key => $status_text ) {
			if ( ! in_array( $status_key, $disabled_statuses, true ) ) {
				$status = array(
					'value' => str_replace( 'wc-', '', $status_key ),
					'text'  => $status_text,
				);
				array_push( $gpos_statuses, $status );
			}
		}
	}

	return $gpos_statuses;
}


/**
 * Gönderilen mesajı WooCommerce hata mesajına çevirerek html döndürür.
 *
 * @param  string $message Mesaj.
 * @param  string $notice_type Mesaj tipleri 'error', 'info', 'success'
 *
 * @return string $message
 */
function gpos_woocommerce_notice( string $message, string $notice_type = 'error' ) {
	if ( function_exists( 'wc_print_notice' ) ) {
		ob_start();
		wc_print_notice( $message, $notice_type );
		$message = ob_get_contents();
		ob_clean();
	}
	return $message;
}

/**
 * Frontend için gerekli kelime, cümle çevirilerini döndürür.
 *
 * @param bool $checkout Ödeme ekranı mı ?
 *
 * @return array
 *
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
function gpos_get_i18n_texts( $checkout = false ) {
	$gpos_texts          = include GPOS_PLUGIN_DIR_PATH . '/languages/gpos-settings-texts.php';
	$gpos_bank_texts     = include GPOS_PLUGIN_DIR_PATH . '/languages/gpos-bank-texts.php';
	$gpos_checkout_texts = include GPOS_PLUGIN_DIR_PATH . '/languages/gpos-checkout-texts.php';
	return array( 'en' => $checkout ? $gpos_checkout_texts : array_merge( $gpos_texts, $gpos_bank_texts ) );
}


/**
 * Desteklenen taksit adetleri.
 *
 * @return array
 */
function gpos_supported_installment_counts() {
	return apply_filters(
		/**
		 * Desteklenen taksit adetlerini düzenleme kancası.
		 *
		 * @param array
		 */
		'gpos_supported_installment_counts',
		array(
			'2',
			'3',
			'4',
			'5',
			'6',
			'7',
			'8',
			'9',
			'10',
			'11',
			'12',
		)
	);
}

/**
 * Desteklenen taksit firmaları.
 *
 * @return array
 */
function gpos_supported_installment_companies() {
	return apply_filters(
		/**
		 * Desteklenen taksit firmalarını düzenleme kancası.
		 *
		 * @param array
		 */
		'gpos_supported_installment_companies',
		array(
			'bonus',
			'world',
			'axess',
			'maximum',
			'cardfinans',
			'bankkartcombo',
			'paraf',
			'saglamkart',
			'advantage',
			'denizbankcc',
			'ingbankcc',
		)
	);
}


/**
 * Yönlendirme linkleri için utm eklemeleri yapar.
 *
 * @param string $utm_camping Parametre : utm_campaign.
 * @param string $link Url.
 *
 * @return string
 */
function gpos_create_utm_link( $utm_camping, $link = 'https://posentegrator.com' ) {
	return add_query_arg(
		array(
			'utm_source'   => 'wp_plugin',
			'utm_medium'   => 'referal',
			'utm_campaign' => $utm_camping,
		),
		$link
	);
}

/**
 * GurmePOS dil ve etki alanı tanımlamaları.
 *
 * @return void
 */
function gpos_load_plugin_text_domain() {
	$locale = determine_locale();
	unload_textdomain( 'gurmepos' );
	load_textdomain( 'gurmepos', GPOS_PLUGIN_DIR_PATH . 'languages/gurmepos-' . $locale . '.mo' );
	load_plugin_textdomain( 'gurmepos', false, plugin_basename( dirname( GPOS_PLUGIN_BASEFILE ) ) . '/languages' );
}

/**
 * GurmePOS standart fiyat yazım formatı.
 *
 * @param string|int|float $value Fiyat.
 *
 * @return float Fiyat.
 */
function gpos_number_format( $value ) {
	return (float) number_format( (float) $value, 2, '.', '' );
}

/**
 * GurmePOS için default callback error mesajı döndürür.
 *
 * @return string
 */
function gpos_get_default_callback_error_message() {
	return __( 'Error in 3D progress. The password was entered incorrectly or the 3D page was abandoned.', 'gurmepos' );
}

/**
 * GurmePOS için default callback error mesajı döndürür.
 *
 * @return string
 */
function gpos_get_default_status_check_error_message() {
	return __( 'The status of the transaction remaining in the 3D routing state was marked as failed after rechecking. There may have been many failure scenarios in this transaction depending on the internet cloud.', 'gurmepos' );
}

/**
 * GPOS_Transaction işlemine göre hangi ödeme eklentisi kullanıldığını tespit edip ödeme geçidini döndürür.
 *
 * @param GPOS_Transaction $transaction GPOS_Transaction objesi.
 *
 * @return GPOS_Plugin_Gateway $plugin_payment_gateway
 *
 * @throws Exception Ödeme geçidi methodu tanımlanmamış.
 */
function gpos_get_plugin_gateway_by_transaction( GPOS_Transaction $transaction ) {
	$functions = apply_filters(
		'gpos_plugin_gateway_functions',
		array(
			GPOS_Transaction_Utils::WOOCOMMERCE => 'gpos_woocommerce_payment_gateway',
			GPOS_Transaction_Utils::GIVEWP_V3   => 'gpos_givewp_v3_payment_gateway',
		)
	);

	if ( array_key_exists( $transaction->get_plugin(), $functions ) ) {
		$function                            = $functions[ $transaction->get_plugin() ];
		$plugin_payment_gateway              = call_user_func( $function );
		$plugin_payment_gateway->transaction = $transaction;
		return $plugin_payment_gateway;
	}

	throw new Exception( 'Undefined plugin gateway function, please add your {myplugin}_payment_gateway function to \'gpos_plugin_gateway_functions\' filter.' );
}

/**
 * GPOS_Frontend için alarm mesajları.
 *
 * @return array
 */
function gpos_get_alert_texts() {
	return array(
		'ok'                     => __( 'OK', 'gurmepos' ),
		'yes'                    => __( 'Yes', 'gurmepos' ),
		'cancel'                 => __( 'Cancel', 'gurmepos' ),
		'remove_gateway'         => __( 'Are you sure you want to remove payment gateway?', 'gurmepos' ),
		'please_wait'            => __( 'Please wait...', 'gurmepos' ),
		'setting_saved'          => __( 'The settings have been saved.', 'gurmepos' ),
		'installments_applied'   => __( 'Installments were applied.', 'gurmepos' ),
		'installments_get_error' => __( 'Error when bringing in installments.', 'gurmepos' ),
		'process_success'        => __( 'Process completed successfully !', 'gurmepos' ),
		'bulk_refund_error'      => __( 'Error in refund process. Please review unsuccessful refunds for error details and try again.', 'gurmepos' ),
		'card_bin'               => __( 'Card number field cannot be left blank.', 'gurmepos' ),
		'card_expiry_month'      => __( 'Card expiration month cannot be left blank.', 'gurmepos' ),
		'card_expiry_year'       => __( 'Card expiration year cannot be left blank.', 'gurmepos' ),
		'card_cvv'               => __( 'Card cvc field cannot be left blank.', 'gurmepos' ),
		'card_holder_name'       => __( 'Name on the card field cannot be left blank.', 'gurmepos' ),
		'ok_test_success'        => __( 'Test operation successful and saved settings', 'gurmepos' ),
	);
}

/**
 * GurmePOS için ortam bilgisi.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @return array
 */
function gpos_get_env_info() {
	$theme = wp_get_theme( get_stylesheet() );

	$response   = wp_remote_get( 'https://icanhazip.com/' );
	$ip_address = __( 'Not available', 'gurmepos' );
	if ( ! is_wp_error( $response ) ) {
		$ip_address = trim( wp_remote_retrieve_body( $response ) );
		$ip_address = filter_var( $ip_address, FILTER_VALIDATE_IP ) ? $ip_address : __( 'Not available', 'gurmepos' );
	}

	return array(
		'working_requirements' => gpos_status_check()->check_total_errors(),
		'wordpress'            => array(
			array(
				'label' => __( 'Theme Name', 'gurmepos' ),
				'value' => $theme->get( 'Name' ),
			),
			array(
				'label' => __( 'Theme Version', 'gurmepos' ),
				'value' => $theme->get( 'Version' ),
			),
			array(
				'label' => __( 'WordPress Version', 'gurmepos' ),
				'value' => get_bloginfo( 'version' ),
			),
			array(
				'label' => __( 'Plugin DB Version', 'gurmepos' ),
				'value' => get_option( 'gpos_db_version' ) . '/' . GPOS_DB_VERSION,
			),
			array(
				'label' => __( 'Multisite', 'gurmepos' ),
				'value' => is_multisite() ? __( 'Yes', 'gurmepos' ) : __( 'No', 'gurmepos' ),
			),
			array(
				'label' => __( 'Debug Mode', 'gurmepos' ),
				'value' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? __( 'Activated', 'gurmepos' ) : __( 'Disabled', 'gurmepos' ),
			),
		),
		'server'               => array(
			array(
				'label' => 'PHP Version',
				'value' => function_exists( 'phpversion' ) && phpversion() ? phpversion() : __( 'Not available', 'gurmepos' ),
			),
			array(
				'label' => 'PHP cURL',
				'value' => function_exists( 'curl_init' ) ? __( 'Yes', 'gurmepos' ) : __( 'No', 'gurmepos' ),
			),
			array(
				'label' => 'PHP SoapClient',
				'value' => class_exists( 'SoapClient' ) ? __( 'Yes', 'gurmepos' ) : __( 'No', 'gurmepos' ),
			),
			array(
				'label' => 'PHP Memory Limit',
				'value' => ini_get( 'memory_limit' ),
			),
			array(
				'label' => 'PHP Max Execution Time',
				'value' => ini_get( 'max_execution_time' ),
			),
			array(
				'label' => __( 'IP Address', 'gurmepos' ),
				'value' => $ip_address,
			),
		),
	);
}
/**
 * GurmePOS için işlem yapan kullanıcının ip adresini döndürür
 *
 * @return string
 */
function gpos_get_user_ip() {

	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {  // İlk olarak HTTP_X_FORWARDED_FOR kontrol edilir (proxy kullanılıyorsa)
		$ip_array   = explode( ',', gpos_clean( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		$ip_address = trim( end( $ip_array ) );
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) { // Daha sonra REMOTE_ADDR kontrol edilir (proxy kullanılmıyorsa veya gizlenmemişse)
		$ip_address = trim( gpos_clean( $_SERVER['REMOTE_ADDR'] ) );
	} else {
		$ip_address = '127.0.0.1';
	}

	return filter_var( $ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ? $ip_address : '127.0.0.1';
}

/**
 * Türkçe karakterleri düzenler, boşlukları ve non-alfa karakterleri siler.
 * Sağlam Kart gibi kelimeleri saglamkart olarak düzenler.
 *
 * @param string $text Temizlenecek kelime
 *
 * @return string
 */
function gpos_clear_non_alfa( $text ) {
	return preg_replace(
		'/[^a-zA-Z]/',
		'',
		strtr(
			strtolower( $text ),
			array(
				'ğ' => 'g',
				'ç' => 'c',
				'ş' => 's',
				'ı' => 'i',
				'İ' => 'i',
				'ö' => 'o',
				'ü' => 'u',
			)
		)
	);
}


/**
 * Yönlendirme olmadan 3D yi iframe içerisinde kullanmayı sağlar.
 *
 * @param string  $iframe_url Sayfa linki.
 * @param boolean $render Yadır.
 *
 * @return string
 *
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
function gpos_iframe_content( $iframe_url, $render = false ) {
	ob_start();
	gpos_get_view( 'threeds-iframe.php', array( 'iframe_url' => $iframe_url ) );
	$content = ob_get_clean();
	if ( $render ) {
		echo $content; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	return $content;
}

/**
 * Desteklene bilen para birimlerini döndürür
 *
 * @return array
 */
function gpos_get_supported_currencies() {
	$currencies = array( 'TRY', 'EUR', 'USD', 'CHF', 'MXN', 'ARS', 'SAR', 'ZAR', 'INR', 'CNY', 'AUD', 'ILS', 'JPY', 'PLN', 'GBP', 'BOB', 'IDR', 'HUF', 'KWD', 'RUB', 'AED', 'RSD', 'DKK', 'COP', 'CAD', 'BGN', 'NOK', 'RON', 'CZK', 'SEK', 'NZD', 'BRL', 'BHD' );
	return apply_filters( 'gpos_supported_currency', $currencies );
}

/**
 * Ödeme için form,givewp gibi yerlerde kullanılan öntanımlı bilgi dizisini döndürür
 *
 * @return array
 */
function gpos_get_default_customer_data() {
	$default_customer_data = array(
		'first_name' => __( 'Name', 'gurmepos' ),
		'last_name'  => __( 'Surname', 'gurmepos' ),
		'phone'      => __( 'Phone', 'gurmepos' ),
		'email'      => __( 'E-Mail', 'gurmepos' ),
		'address'    => __( 'Address', 'gurmepos' ),
		'city'       => __( 'City', 'gurmepos' ),
		'state'      => __( 'State', 'gurmepos' ),
		'country'    => __( 'Country', 'gurmepos' ),
	);
	return apply_filters( 'gpos_default_customer_data', $default_customer_data );
}

/**
 * Kullanıcıya göre yetki tanımı.
 *
 * @return string $role;
 */
function gpos_capability() {
	return apply_filters( 'gpos_menu_capability', 'manage_options' );
}

/**
 * Para birimi ISO kodu
 *
 * @param string $currency_text Para birimi kodu (TRY, USD vs.)
 *
 * @return int|string
 */
function gpos_get_currency_iso_code( $currency_text ) {
	$codes = array(
		'USD' => 840,
		'EUR' => 978,
		'RUB' => 643,
		'GBP' => 826,
		'TRY' => 949,
		'SEK' => 752,
		'PLN' => 985,
		'CZK' => 203,
		'NOK' => 578,
		'AUD' => 036,
		'SGD' => 702,
		'PHP' => 608,
		'THB' => 764,
		'NZD' => 554,
		'MKD' => 807,
	);

	return array_key_exists( $currency_text, $codes ) ? $codes[ $currency_text ] : $currency_text;
}


/**
 * Platforma istinaden ödenecek tutar verisini döndüren fonksiyon.
 *
 * @param string $platform Platform (WooCommerce, GiveWP vb.)
 *
 * @return array
 */
function gpos_get_platform_data_to_be_paid( $platform ) {
	$data = array(
		'amount'   => 0,
		'currency' => 'TRY',
	);

	if ( in_array( $platform, array( GPOS_Transaction_Utils::WOOCOMMERCE, GPOS_Transaction_Utils::WOOCOMMERCE_SUBS, GPOS_Transaction_Utils::WOOCOMMERCE_BLOCKS ), true ) ) {
		$data = array(
			'amount'   => WC()->cart->get_total( 'float' ),
			'currency' => get_woocommerce_currency(),
		);
	}

	return apply_filters( 'gpos_platform_data_to_be_paid', $data, $platform );
}

/**
 * Para birimlerine göre simgeleri döndürür.
 *
 * @param string $currency_code Para birimi iso kodu TRY, USD vs.
 *
 * @return string
 */
function gpos_get_currency_symbol( $currency_code ) {
	$symbols = array(
		'USD' => '&#36;',
		'EUR' => '&#8364;',
		'GBP' => '&#163;',
		'JPY' => '&#165;',
		'CNY' => '&#165;',
		'AUD' => '&#36;',
		'CAD' => '&#36;',
		'CHF' => '&#67;&#72;&#70;',
		'RUB' => '&#8381;',
		'INR' => '&#8377;',
		'BRL' => '&#82;&#36;',
		'MXN' => '&#36;',
		'KRW' => '&#8361;',
		'SGD' => '&#36;',
		'HKD' => '&#36;',
		'SEK' => '&#107;&#114;',
		'NOK' => '&#107;&#114;',
		'NZD' => '&#36;',
		'ZAR' => '&#82; ',
		'TRY' => '&#8378;',
	);

	return array_key_exists( $currency_code, $symbols ) ? $symbols[ $currency_code ] : $currency_code;
}


/**
 * Taksit şablonu
 *
 * @return array
 */
function gpos_default_installments_template() {
	$template  = array();
	$companies = gpos_supported_installment_companies();
	$counts    = gpos_supported_installment_counts();

	if ( false === empty( $companies ) && false === empty( $counts ) ) {
		foreach ( $companies as $company ) {
			foreach ( $counts as $count ) {
				$template[ $company ][ $count ] = array(
					'enabled' => false,
					'rate'    => 0,
					'number'  => $count,
					'extra'   => '',
				);
			}
		}
	}

	return $template;
}

/**
 * stdClass gibi objeleri diziye döndürür.
 *
 * @param mixed $variable Obje.
 *
 * @return array
 */
function gpos_object_to_array( $variable ) {
	return json_decode( wp_json_encode( $variable ), true );
}

/**
 * Kart ailelerinin marka renklerini döndürür
 *
 * @return array
 */
function gpos_get_card_family_color() {
	$familiy_color_data = array(
		'maximum'       => '#EC018C',
		'cardfinans'    => '#294AA4',
		'paraf'         => '#03DCFF',
		'world'         => '#9D69A7',
		'axess'         => '#FFC20D',
		'bonus'         => '#64C25A',
		'bankkartcombo' => '#EC0C10',
		'advantage'     => '#EB724F',
		'saglamkart'    => '#006748',
		'denizbankcc'   => '#004c91',
		'ingbankcc'     => '#ff6801',
	);
	return apply_filters( 'gpos_default_card_family_color', $familiy_color_data );
}


/**
 * Ülke kodlarını ve isimlerini döndürür.
 *
 * @return array
 */
function gpos_get_countries() {
	return include GPOS_PLUGIN_DIR_PATH . '/languages/gpos-countries.php';
}

/**
 * Sipariş, bağış vs gibi arayüzlü uygulamalarda not eklerken pos entegratör için kullanılacak not.
 *
 * @param GPOS_Gateway_Response $response Ödeme geçidi cevabı
 *
 * @return string
 */
function gpos_transaction_note( GPOS_Gateway_Response $response ) {

	if ( $response->is_success() ) {
		$message = sprintf(
			// translators: %s => Ödeme geçidi benzersiz numarası.
			__( 'Payment completed successfully. Payment number: %s.', 'gurmepos' ),
			$response->get_payment_id()
		);
	} else {
		// translators: %s => Ödeme geçidi hatası.
		$message = sprintf( '<strong>POS Entegratör</strong><br>' . __( 'Error in payment process: %s.', 'gurmepos' ), $response->get_error_message() );
	}

	return sprintf( '<strong style="color:#2563eb">%1$s</strong><br>%2$s<br>', 'POS Entegratör', $message );
}

/**
 * Hash doğrulama hatası.
 *
 * @return string
 */
function gpos_get_hash_error_message() {
	return __( '3D Hash verification error, the reason for this error is that your key (3D Key, ENC Key, Store Key) that encrypts the communication between the bank and the system is incorrect. Please verify this key.', 'gurmepos' );
}

/**
 * GurmePOS için görünümü getir.
 *
 * @param string $template_name Dahil edilecek görünüm.
 * @param array  $args Görünüm içerisinde kullanılacak veriler.
 *
 * @return void
 */
function gpos_get_template( $template_name, $args = array() ) {

	$default_path = GPOS_PLUGIN_DIR_PATH . 'views/';

	$template_name = $template_name . '.php';

	$template = locate_template(
		array(
			trailingslashit( $default_path ) . $template_name,
			'gurmepos/' . $template_name,
			$template_name,
		)
	);

	if ( empty( $template ) ) {
		$template = trailingslashit( $default_path ) . $template_name;
	}

	load_template( $template, true, $args );
}

/**
 * GurmePOS için gruplu ürünlerin, ürün detay sayfasında ki adetlerine göre ücret toplamını yapan fonksiyon.
 *
 * @param array $products ürünlerin id ve adetlerini taşır.
 *
 * @return array
 */
function gpos_calculate_group_product_price( $products ) {
	$total = 0;
	foreach ( $products as $product ) {
		$total += wc_get_price_including_tax( wc_get_product( $product->product_id ) ) * $product->qty;
	}

	return [
		'total'    => $total,
		'currency' => get_woocommerce_currency_symbol(),
	];
}
