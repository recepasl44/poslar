<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * GurmePOS ödeme geçidi veri tabanı işlemlerin için base sınıfı barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS GPOS_Model abstract sınıfı
 */
abstract class GPOS_Model {

	/**
	 * Verilerinin tutulduğu tablo.
	 *
	 * @var string
	 */
	public $table_name;

	/**
	 * İşlem için tanımlanmış benzersiz işlem numarası.
	 *
	 * @var string $transaction_id Benzersiz işlem numarası.
	 */
	public $transaction_id;

	/**
	 * Veri tabanı bağlantı sınıfı.
	 *
	 * @var wpdb $db Veri tabanı bağlantısı
	 */
	public $connection;

	/**
	 * Forged veriler için anahtar.
	 *
	 * @var string
	 */
	public $forge_key;

	/**
	 * Forged veriler için başlangıç vektörü.
	 *
	 * @var string
	 */
	public $init_vector;

	/**
	 * GPOS_Model sınıfı kurucu fonksiyonu
	 *
	 * @param string|int $transaction_id İşlem numarası.
	 *
	 * @return void
	 */
	public function __construct( $transaction_id = 0 ) {
		global $wpdb;
		$this->transaction_id = $transaction_id;
		$this->connection     = $wpdb;
		$this->forge_key      = uniqid();
	}

	/**
	 * Veri tabanı ön eki ekleyerek tablo ismini döndürür.
	 *
	 * @return string
	 */
	public function get_table_name() {
		return $this->connection->prefix . $this->table_name;
	}

	/**
	 * Tabloyu temizler.
	 *
	 * @return void
	 */
	public function clear_table() {
		$this->connection->query( "TRUNCATE TABLE `{$this->get_table_name()}`" );
	}
}
