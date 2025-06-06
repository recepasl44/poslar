<?php
/**
 * Veri dışarı aktarmak için kullanılan GPOS_Export sınıfını barındıran dosya..
 *
 * @package Gurmehub
 */

/**
 * XLSX vb. dosya çıktıları almak için kullanılan sınıf.
 */
class GPOS_Export {

	/**
	 * WordPress dosya yöneticisi
	 *
	 * @var WP_Filesystem_Base $wp_filesystem;
	 */
	protected $wp_filesystem;

	/**
	 * GPOS çıktı klasörü
	 *
	 * @var string $export_dir;
	 */
	protected $export_dir;

	/**
	 * Kurucu method
	 */
	public function __construct() {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}

		WP_Filesystem();

		$this->wp_filesystem = $wp_filesystem;
		$this->export_dir    = sprintf( '%s/pos-entegrator', wp_upload_dir()['basedir'] );
	}

	/**
	 * XLSX formatında çıkartılan dosyları temizler.
	 */
	public function clear_export_dir() {
		try {
			WP_Filesystem();
			$this->wp_filesystem->delete( $this->export_dir, true );
		} catch ( TypeError $e ) {
			update_option( 'gpos_clear_export_dir_failed', true );
		}
	}
	/**
	 *  İşlemlerin xlsx formatında çıkartır
	 *
	 *  @param array $transactions Kayıtların listesi
	 *
	 *  @return mixed
	 *
	 *  @SuppressWarnings(PHPMD.ExitExpression)
	 *  @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public function export_transaction_excel( $transactions ) {
		$all_statuses = gpos_post_operations()->get_post_statuses();
		$headers      = array(
			__( 'ID' ),
			__( 'Date', 'gurmepos' ),
			__( 'Payment ID', 'gurmepos' ),
			__( 'Plugin Transaction ID', 'gurmepos' ),
			__( 'Customer Name', 'gurmepos' ),
			__( 'Installment', 'gurmepos' ),
			__( 'Status', 'gurmepos' ),
			__( 'Total', 'gurmepos' ),
			__( 'Currency', 'gurmepos' ),
			__( 'Refund Status', 'gurmepos' ),
			__( 'Payment Gateway', 'gurmepos' ),
		);

		$rows[] = apply_filters( 'gpos_transaction_export_header', array_map( fn( $header ) => sprintf( '<b>%s</b>', $header ), $headers ) );

		foreach ( $transactions as $transaction ) {
			$status = $transaction->get_status();
			$row    = array(
				'id'                    => sprintf( '<left>%s</left>', $transaction->id ),
				'date'                  => $transaction->get_date(),
				'payment_id'            => $transaction->get_payment_id(),
				'plugin_transaction_id' => $transaction->get_plugin_transaction_id(),
				'name'                  => "{$transaction->get_customer_first_name()} {$transaction->get_customer_last_name()}",
				'installment'           => $transaction->get_installment(),
				'status'                => array_key_exists( $status, $all_statuses ) ? $all_statuses[ $status ]['label'] : $status,
				'total'                 => $transaction->get_total(),
				'currency'              => $transaction->get_currency(),
				'refund_status'         => gpos_get_i18n_texts()['default'][ $transaction->get_refund_status() ],
				'payment_gateway'       => $transaction->get_payment_gateway_id(),
			);
			$rows[] = apply_filters( 'gpos_transaction_export_row', $row );
		}

		$this->wp_filesystem->mkdir( $this->export_dir );
		$filename = sprintf( 'export-transaction-%s.xlsx', current_time( 'Y-m-d-h-i-s' ) );
		Shuchkin\SimpleXLSXGen::fromArray( $rows )
		->setDefaultFont( 'Calibri' )
		->setDefaultFontSize( 12 )
		->setColWidth( 1, 35 )
		->saveAs( sprintf( '%s/%s', $this->export_dir, $filename ) );
		wp_safe_redirect( sprintf( '%s/pos-entegrator/%s', wp_upload_dir()['baseurl'], $filename ) );
		exit;
	}
}
