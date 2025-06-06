<?php
/**
 * Tarih kolonu
 *
 * @var GPOS_Transaction $transaction
 *
 * @package Gurmehub
 */

$timestamp = strtotime( $transaction->get_date() );

if ( ! $timestamp ) {
	echo '&ndash;';
	return;
}

if ( $timestamp > strtotime( '-1 day', time() ) && $timestamp <= time() ) {
	$show_date = sprintf(
	/* translators: %s: Saat farkı için kullanılan saat örn. 10 Dakika Önce */
		_x( '%s ago', '%s = human-readable time difference', 'gurmepos' ),
		human_time_diff( $timestamp, time() )
	);
} else {
	$show_date = date_i18n( __( 'j F Y', 'gurmepos' ), $timestamp );
}
?>
<time 
	datetime="<?php echo esc_attr( $transaction->get_date() ); ?>" 
	title="<?php echo esc_attr( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) ); ?>">
	<?php echo esc_html( $show_date ); ?>
</time>
