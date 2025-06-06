<?php
/**
 * GPOS_Gph (Gurmehub Plugin Helper) sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * Bu sınıf GurmePOS un Gurmehub Plugin Helper'a attığı kancaları taşır.
 */
class GPOS_Gph {

	/**
	 * GPOS_Gph kurucu fonksiyonu
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'gph_' . GPOS_PLUGIN_BASENAME . '_deactive_reasons', array( $this, 'deactive_reasons' ) );
		add_filter( 'gph_' . GPOS_PLUGIN_BASENAME . '_texts', array( $this, 'texts' ) );
	}

	/**
	 * GurmePOS kaldırılma nedenleri kancası.
	 *
	 * @param array $reasons Nedenler.
	 *
	 * @return array
	 */
	public function deactive_reasons( $reasons ) {
		return array_merge(
			$reasons,
			array(
				array(
					'value' => 'integration',
					'label' => __( 'I couldn\'t find the payment gateway or integration I was looking for (Please share below)', 'gurmepos' ),
				),
				array(
					'value' => 'satisfied',
					'label' => __( 'I\'m already satisfied with my payment settings', 'gurmepos' ),
				),
				array(
					'value' => 'about',
					// translators: %s is the name of the plugin.
					'label' => sprintf( __( 'I need more information about %s', 'gurmepos' ), 'POS Entegratör' ),
				),
				array(
					'value' => 'temporarily',
					'label' => __( 'Temporarily deactivating', 'gurmepos' ),
				),
				array(
					'value' => 'didnt_work',
					'label' => __( 'Didn\'t work as expected', 'gurmepos' ),
				),
				array(
					'value' => 'other',
					'label' => __( 'Other (Please share below)', 'gurmepos' ),
				),
			)
		);
	}

	/**
	 * GurmePOS kaldırılma nedenleri buton yazıları kancası.
	 *
	 * @param array $texts Buton yazıları.
	 *
	 * @return array
	 */
	public function texts( $texts ) {
		$texts = array(
			'skip_button'      => __( 'Skip & Deactive', 'gurmepos' ),
			'submit_button'    => __( 'Deactive', 'gurmepos' ),
			'cancel_button'    => __( 'Cancel', 'gurmepos' ),
			'reasons_title'    => __( 'Why you are leaving us ?', 'gurmepos' ),
			'comment_title'    => __( 'Comments (Optional)', 'gurmepos' ),
			// translators: %s is the name of the plugin.
			'main_title'       => __( 'No thanks, i don\'t want the %s', 'gurmepos' ),
			'main_description' => __( 'After a step, the plugin will be deactivated. Could you please take a moment and support us to make your app better?', 'gurmepos' ),
		);
		return $texts;
	}
}
