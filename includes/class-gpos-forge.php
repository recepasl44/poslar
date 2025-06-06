<?php
/**
 * GPOS_Forge sınıfını barındırır.
 *
 * @package GurmeHub
 */

/**
 * GPOS_Forge sınıfı.
 */
class GPOS_Forge {

	/**
	 * Şifreli verileri okuma.
	 *
	 * @param string $hex Okunacak veri.
	 * @param string $iv Başlangıç vektörü.
	 * @param string $key Anahtar.
	 *
	 * @return array
	 *
	 * @SuppressWarnings(PHPMD.ShortVariable)
	 */
	public function checkout_decrypt( $hex, $iv, $key ) {

		$text = openssl_decrypt( hex2bin( $hex ), 'aes-256-cbc', hex2bin( hash( 'sha256', $key ) ), OPENSSL_RAW_DATA, hex2bin( $iv ) );

		return json_decode( $text, true );
	}

	/**
	 * Veri şifreleme.
	 *
	 * @param string $data Şifrelenecek veri.
	 * @param string $key Anahtar.
	 *
	 * @return array
	 *
	 * @SuppressWarnings(PHPMD.ShortVariable)
	 */
	public function db_crypt( $data, $key ) {
		$iv             = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
		$encrypted_text = openssl_encrypt( $data, 'aes-256-cbc', $key, 0, $iv );

		return array(
			'hex' => $encrypted_text,
			'iv'  => bin2hex( $iv ),
		);
	}

	/**
	 * Şifreli verileri okuma.
	 *
	 * @param string $hex Okunacak veri.
	 * @param string $iv Başlangıç vektörü.
	 * @param string $key Anahtar.
	 *
	 * @return string|false
	 *
	 * @SuppressWarnings(PHPMD.ShortVariable)
	 */
	public function db_decrypt( $hex, $iv, $key ) {
		return openssl_decrypt( $hex, 'aes-256-cbc', $key, 0, hex2bin( $iv ) );
	}
}
