<?php
/**
 * GurmePOS için pro, form gibi modüllerin güncellemelerini kontrol edip GurmePOS un güncellemelerini organize eder.
 * Aktif edilme durumlarını hook aksiyonlarını organize eder.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS güncelleme engelleme sınıfı
 */
class GPOS_Module_Manager {

	/**
	 * Modüllerin entegre olduğu kanca..
	 */
	public function gpos_loaded() {
		if ( defined( 'GPOSPRO_VERSION' ) ) {
			$pro_version = defined( 'GPOS_PRODUCTION' ) && true === GPOS_PRODUCTION ? GPOSPRO_VERSION : '100';
			if ( version_compare( $pro_version, '2.6.64', '>=' ) ) {
				do_action( 'gpos_loaded_for_pro' );
			}
		}

		if ( defined( 'GPOSFORM_VERSION' ) ) {
			$form_version = defined( 'GPOS_PRODUCTION' ) && true === GPOS_PRODUCTION ? GPOSFORM_VERSION : '100';
			if ( version_compare( $form_version, '1.0.32', '>=' ) ) {
				do_action( 'gpos_loaded_for_form' );
			}
		}
	}

	/**
	 * GurmePOS update edilebilir mi ? kontrollerini gerçekleştirme.
	 *
	 * @param stdClass $plugin_updates Güncellemeler.
	 *
	 * @return stdClass
	 */
	public function transient_update_plugins( $plugin_updates ) {
		$show_alert   = false;
		$pro_basename = defined( 'GPOSPRO_PLUGIN_BASENAME' ) ? GPOSPRO_PLUGIN_BASENAME : 'gurmepos-pro/gurmepos-pro.php';
		if ( isset( $plugin_updates->response[ $pro_basename ] ) ) {
			unset( $plugin_updates->response[ GPOS_PLUGIN_BASENAME ] );
			$show_alert = true;
		}

		$form_basename = defined( 'GPOSFORM_PLUGIN_BASENAME' ) ? GPOSFORM_PLUGIN_BASENAME : 'gurmepos-form/gurmepos-form.php';
		if ( isset( $plugin_updates->response[ $form_basename ] ) ) {
			unset( $plugin_updates->response[ GPOS_PLUGIN_BASENAME ] );
			$show_alert = true;
		}

		if ( $show_alert ) {
			add_action( 'after_plugin_row_' . GPOS_PLUGIN_BASENAME, array( $this, 'after_plugin_row' ) );
		}

		return $plugin_updates;
	}

	/**
	 * GurmePOS modüller güncellenmeden güncellenemiyor notu.
	 *
	 * @return void
	 */
	public function after_plugin_row() {
		gpos_get_view( 'cannot-update-notice.php' );
	}
}
