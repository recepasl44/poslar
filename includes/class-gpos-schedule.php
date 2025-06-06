<?php
/**
 * GPOS_Schedule sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * Bu sınıf WordPress üzerinde zamanlanmış görevlere istinaden uygun fonksiyonları, methodları çalıştırır.
 */
class GPOS_Schedule {

	/**
	 * GPOS_Schedule kurucu fonksiyonu
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'gpos_clear_redirect_table', array( gpos_redirect(), 'clear_table' ) );
		add_action( 'gpos_clear_export_dir', array( gpos_export(), 'clear_export_dir' ) );
		add_action( 'gpos_add_transaction', array( gpos_tracker(), 'add_transaction' ) );
		add_action( 'gpos_add_error_message', array( gpos_tracker(), 'add_error_message' ) );
		add_action( 'gpos_delete_session_meta', array( gpos_session(), 'delete_session_meta' ), 10, 2 );
		add_action( 'gpos_transaction_check_status', array( gpos_transaction_status_checker(), 'scheduled_check' ) );

		add_action( 'gpos_garbage_collector', array( gpos_garbage_collector(), 'schedule_transactions' ) );

		$notify_class = gpos_is_pro_active() && function_exists( 'gpospro_notifications' ) ? gpospro_notifications() : gpos_notifications();
		add_action( 'gpos_daily_transaction_notification', array( $notify_class, 'daily_transaction_notification' ) );
		add_action( 'gpos_weekly_transaction_notification', array( $notify_class, 'weekly_transaction_notification' ) );
		add_action( 'gpos_error_transaction_notification', array( $notify_class, 'error_transaction_notification' ), 10, 2 );
		add_action( 'gpos_success_transaction_notification', array( $notify_class, 'success_transaction_notification' ) );
	}

	/**
	 * Paytr webhook response kontrolü.
	 *
	 * @param array $post_data Post data.
	 * @return void
	 */
	public function paytr_check_webhook_response( array $post_data ) {
		$transaction_id = $post_data['merchant_oid'];
		$settings       = gpos_other_settings()->get_setting_by_key( 'payment_id_settings' );
		if ( $settings && $settings->active ) {
			$transaction_id = explode( $settings->separator, $post_data['merchant_oid'] )[1];
		}
		$transaction = gpos_transaction( $transaction_id );
		$gateway     = gpos_payment_gateways()->get_gateway_by_account_id( $transaction->get_account_id(), $transaction );
		if ( $gateway && ( $gateway instanceof GPOSPRO_PayTR_Gateway || $gateway instanceof GPOS_PayTR_IFrame_Gateway ) && $gateway->check_hash( $post_data ) ) { // @phpstan-ignore-line
			$gateway->check_notify( $post_data ); // @phpstan-ignore-line
		}
	}
	/**
	 * Zamanlanmış görevleri kayıt etme.
	 */
	public function register() {
		if ( ! wp_next_scheduled( 'gpos_clear_redirect_table' ) ) {
			wp_schedule_event( time(), 'hourly', 'gpos_clear_redirect_table' );
		}

		if ( ! wp_next_scheduled( 'gpos_clear_export_dir' ) ) {
			wp_schedule_event( time(), 'hourly', 'gpos_clear_export_dir' );
		}

		if ( ! wp_next_scheduled( 'gpos_garbage_collector' ) ) {
			wp_schedule_event( time(), 'hourly', 'gpos_garbage_collector' );
		}
	}
}
