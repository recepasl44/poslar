<?php
/**
 * Kredi kartı yardımcısını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GPOS_Credit_Card sınıfı.
 */
trait GPOS_Credit_Card {

	/**
	 * Gizli kredi kartı numarası
	 *
	 * @var string $masked_card_bin
	 */
	protected $masked_card_bin;

	/**
	 * Kredi kartı numarası
	 *
	 * @var int|string $card_bin
	 */
	protected $card_bin;

	/**
	 * Kredi kartı güvenlik numarası
	 *
	 * @var int|string $card_cvv
	 */
	protected $card_cvv;

	/**
	 * Kredi kartı son kullanım yıl
	 *
	 * @var int|string $card_expiry_year
	 */
	protected $card_expiry_year;

	/**
	 * Kredi kartı son kullanım ay
	 *
	 * @var int|string $card_exp_month
	 */
	protected $card_expiry_month;

	/**
	 * Kart ailesi
	 *
	 * @var string $card_family
	 * */
	protected $card_family;

	/**
	 * Kart markası
	 *
	 * @var string $card_brand
	 * */
	protected $card_brand;

	/**
	 * Kart ülkesi
	 *
	 * @var string $card_country
	 * */
	protected $card_country;

	/**
	 * Kart tipi
	 *
	 * @var string $card_type
	 * */
	protected $card_type;

	/**
	 * Kart üzerindeki isim
	 *
	 * @var string $card_holder_name
	 * */
	protected $card_holder_name;

	/**
	 * Kartın takma ismi
	 *
	 * @var string $card_name
	 * */
	protected $card_name;

	/**
	 * Kartın banka ismi
	 *
	 * @var string $card_bank_name
	 * */
	protected $card_bank_name;

	/**
	 * Kartın banka ismi
	 *
	 * @var string $card_bank_code
	 * */
	protected $card_bank_code;

	/**
	 * Kredi kartı numara bilgisini ayarlar
	 *
	 * @param int|string $value Kredi kartı numara bilgisi.
	 * @return $this
	 */
	public function set_card_bin( $value ) {
		$this->card_bin = preg_replace( '/[^0-9]/', '', $value );
		$this->set_masked_card_bin( $this->card_bin );
		return $this;
	}

	/**
	 * Kredi kartı numara bilgisini döndürür
	 *
	 * @return int|string
	 */
	public function get_card_bin() {
		return $this->card_bin;
	}

	/**
	 * Kredi kartı güvenlik numarası bilgisini ayarlar
	 *
	 * @param int|string $value Kredi kartı güvenlik numarası bilgisi.
	 * @return $this
	 */
	public function set_card_cvv( $value ) {
		$this->card_cvv = preg_replace( '/[^0-9]/', '', $value );
		return $this;
	}

	/**
	 * Kredi kartı güvenlik numarası bilgisini döndürür
	 *
	 * @return int|string
	 */
	public function get_card_cvv() {
		return $this->card_cvv;
	}

	/**
	 * Kredi kartı son kullanım tarihi yıl bilgisini ayarlar
	 *
	 * @param int|string $value Kredi kartı son kullanım tarihi yıl bilgisi.
	 * @return $this
	 */
	public function set_card_expiry_year( $value ) {
		$this->set_prop( __FUNCTION__, preg_replace( '/[^0-9]/', '', $value ) );
		return $this;
	}

	/**
	 * Kredi kartı son kullanım tarihi yıl bilgisini döndürür
	 *
	 * @return int|string
	 */
	public function get_card_expiry_year() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kredi kartı son kullanım tarihi ay bilgisini ayarlar
	 *
	 * @param int|string $value Kredi kartı son kullanım tarihi ay bilgisi.
	 * @return $this
	 */
	public function set_card_expiry_month( $value ) {
		$this->set_prop( __FUNCTION__, preg_replace( '/[^0-9]/', '', $value ) );
		return $this;
	}

	/**
	 * Kredi kartı son kullanım tarihi ay bilgisini döndürür
	 *
	 * @return int|string
	 */
	public function get_card_expiry_month() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kredi kartı üzerindeki isim bilgisi bilgisini ayarlar.
	 *
	 * @param int|string $value Kredi kartı üzerindeki isim bilgisi bilgisi.
	 * @return $this
	 */
	public function set_card_holder_name( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Kredi kartı üzerindeki isim bilgisi bilgisini döndürür.
	 *
	 * @return string
	 */
	public function get_card_holder_name() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Gizli kredi kartı numara bilgisini ayarlar
	 *
	 * @param int|string $value Kredi kartı numara bilgisi.
	 * @param bool       $already_masked Numaranın zaten gizli olduğu belirtir.
	 * @return $this
	 *
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public function set_masked_card_bin( $value, $already_masked = false ) {
		if ( false === $already_masked ) {
			$value = substr( $value, 0, 4 ) . ' ' . substr( $value, 4, 4 ) . ' **** ' . substr( $value, -4 );
		}
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Gizli kredi kartı numara bilgisini döndürür.
	 *
	 * @return int|string
	 */
	public function get_masked_card_bin() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kredi yada banka kartı kayıt edilirken girilen isimi ayarlar.
	 *
	 * @param string $value Kart ismi bilgisi.
	 * @return $this
	 */
	public function set_card_name( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Kredi yada banka kartı kayıt edilirken girilen isimi döndürür.
	 *
	 * @return string
	 */
	public function get_card_name() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kredi yada banka kartı bilgisini ayarlar.
	 *
	 * @param string $value Kartı tipi bilgisi.
	 * @return $this
	 */
	public function set_card_type( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Kredi yada banka kartı bilgisini döndürür.
	 *
	 * @return string
	 */
	public function get_card_type() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kart firma bilgisini ayarlar. Master, Visa, Troy vs.
	 *
	 * @param string $value Kart firma bilgisi.
	 * @return $this
	 */
	public function set_card_brand( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Kart firma bilgisini döndürür.
	 *
	 * @return string
	 */
	public function get_card_brand() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kart aile bilgisini ayarlar. Axess, Bonus, Word vs.
	 *
	 * @param string|int $value Kartı aile bilgisi.
	 * @return $this
	 */
	public function set_card_family( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Kart aile bilgisini döndürür.
	 *
	 * @return string
	 */
	public function get_card_family() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kart banka bilgisini ayarlar. Akbank, Finansbank vs.
	 *
	 * @param string $value Kart banka bilgisi.
	 *
	 * @return $this
	 */
	public function set_card_bank_name( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Kart banka bilgisini döndürür.
	 *
	 * @return string
	 */
	public function get_card_bank_name() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kart banka kodu bilgisini ayarlar. 10:Ziraat Bankası, 32:Teb vs.
	 *
	 * @param string $value Kart banka kodu bilgisi.
	 *
	 * @return $this
	 */
	public function set_card_bank_code( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Kart banka kodu bilgisini döndürür.
	 *
	 * @return string
	 */
	public function get_card_bank_code() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kart ülke bilgisini ayarlar. Türkiye vs.
	 *
	 * @param string $value Kart ülke bilgisi.
	 * @return $this
	 */
	public function set_card_country( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Kart ülke bilgisini döndürür.
	 *
	 * @return string
	 */
	public function get_card_country() {
		return $this->get_prop( __FUNCTION__ );
	}

	/**
	 * Kart ülke kodu bilgisini ayarlar. TR, USA vs.
	 *
	 * @param string $value Kart ülke kodu bilgisi.
	 * @return $this
	 */
	public function set_card_country_code( $value ) {
		$this->set_prop( __FUNCTION__, $value );
		return $this;
	}

	/**
	 * Kart ülke kodu bilgisini döndürür.
	 *
	 * @return string
	 */
	public function get_card_country_code() {
		return $this->get_prop( __FUNCTION__ );
	}
}
