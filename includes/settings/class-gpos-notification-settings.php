<?php
/**
 * GurmePOS bildirim ayarları sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS bildirim ayarları sınıfı
 */
class GPOS_Notification_Settings extends GPOS_Options {

	/**
	 * Anahtar; wp_options tablosunda ayarın tutulacağı option_name.
	 *
	 * @var string $options_table_key
	 */
	public $options_table_key = 'gpos_notification_settings';

	/**
	 * Ayarlar kayıt edildikten sonra çalışan tetik.
	 */
	protected function settings_updated() {
		wp_clear_scheduled_hook( 'gpos_daily_transaction_notification' );
		wp_clear_scheduled_hook( 'gpos_weekly_transaction_notification' );
	}
}
