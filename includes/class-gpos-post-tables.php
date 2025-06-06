<?php
/**
 * GurmePOS için gerekli WordPress post tiplerinin (post_type) listeleme tablolarını organize eden sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * Eklenti için gerekli post tiplerinin listeleme tablolarını organize eder.
 */
class GPOS_Post_Tables {


	/**
	 * WordPress post düzenleme işlemini devre dışı bırakma.
	 *
	 * @param array $actions Toplu işlemler.
	 *
	 * @return array
	 */
	public function bulk_actions_edit( $actions ) {
		unset( $actions['edit'] );
		$actions['export'] = __( 'Export', 'gurmepos' );
		return $actions;
	}

	/**
	 *  İşlemlerin csv olarak dışarı çıkartılmasını sağlar
	 *
	 * @param string $redirect Yönlendirel url
	 * @param string $doaction Bulk action id si
	 * @param array  $transaction_ids Bulk action için seçilen id ler
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	public function export_bulk_actions( $redirect, $doaction, $transaction_ids ) {
		if ( 'export' === $doaction ) {
			$transactions = gpos_transactions()->get_transactions(
				array(
					'numberposts'    => count( $transaction_ids ),  //phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
					'post__in'       => $transaction_ids,
					'posts_per_page' => -1,                         //phpcs:ignore WordPress.WP.PostsPerPageNoUnlimited.posts_per_page_posts_per_page
				)
			);
			return gpos_export()->export_transaction_excel( $transactions );
		}
	}

	/**
	 * WordPress 'manage_gpos_transaction_posts_custom_column' kancası
	 *
	 * @param string $column Kolon.
	 *
	 * @return void
	 */
	public function transaction_custom_column( $column ) {
		global $post;
		gpos_get_view( 'transaction-columns/' . str_replace( '_', '-', $column ) . '.php', array( 'transaction' => gpos_transaction( $post->ID ) ) );
	}

	/**
	 * WordPress 'manage_edit-gpos_transaction_columns' kancası
	 *
	 * @param array $columns Kolonlar.
	 *
	 * @return array
	 */
	public function transaction_columns( $columns ) {

		return array(
			'cb'              => $columns['cb'],
			'transaction'     => __( 'Transaction', 'gurmepos' ),
			'payment_gateway' => __( 'Payment Gateway', 'gurmepos' ),
			'payment_plugin'  => __( 'Plugin', 'gurmepos' ),
			'total'           => __( 'Total', 'gurmepos' ),
			'installment'     => __( 'Installment', 'gurmepos' ),
			'process_type'    => __( 'Process Type', 'gurmepos' ),
			'status'          => __( 'Status', 'gurmepos' ),
			'refund_status'   => __( 'Refund Status', 'gurmepos' ),
			'security_type'   => __( 'Security Type', 'gurmepos' ),
			'create_date'     => __( 'Date', 'gurmepos' ),
		);
	}
}
