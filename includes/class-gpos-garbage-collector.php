<?php
/**
 * GurmePOS 3D kalmış ödemelerin durumunu tekrar sorgulan ve işlemi neticelendirir
 *
 * @package GurmeHub
 */

/**
 * GurmePOS İşlem durum toplayıcısı
 */
class GPOS_Garbage_Collector {

	/**
	 * Eklenti ön eki
	 *
	 * @var string $prefix
	 */
	private $prefix = GPOS_PREFIX;

	/**
	 *  3D de kalan işlemleri getirir
	 */
	public function schedule_transactions() {
		$transactions = gpos_transactions()->get_transactions(
			array(
				'post_status'    => "{$this->prefix}_redirected",
				'date_query'     => array(
					array(
						'before'    => gmdate( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
						'inclusive' => true,
					),
				),
				'posts_per_page' => -1, //phpcs:ignore
			)
		);
		foreach ( $transactions as $transaction ) {
			gpos_transaction_status_checker()->add_schedule_check( $transaction->id );

		}
	}
}
