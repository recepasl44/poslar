<?php
/**
 * GurmePOS 3D Yönlendirme ve iFrame ödemeler için sayfayı render etmeye yarayan kod parçacağı. Temanıza göre özelleştirmek için lütfen bu dosyayı kopyalayın ve temanızın
 * altında gurmepos klasörü oluşturarak yapıştırın. Ardından dosyada özelleştirmeler yapabilirsiniz.
 *
 * @package GurmeHub
 * @var array $args Redirect fonksiyonundan gelen parametreler.
 */

if ( $args['content'] ) {
	if ( $args['is_iframe'] ) {
		get_header();
		echo $args['content']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		get_footer();
	} else {
		echo $args['content']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
} else {
	?>
	<center style="font-family:Roboto;">
		<div style="font-size:36px; margin:20px 0;">
			<?php esc_html_e( 'Incorrect transaction, please refresh the payment page and try again.', 'gurmepos' ); ?>
		</div>
		<button style="background-color:#1c64f2; color:#fff; border-color:#1c64f2; border-radius:999px; padding:10px 20px;" onclick="window.history.back()">
		<?php esc_html_e( 'Back to payment page', 'gurmepos' ); ?>
		</button>
	</center>
	<?php
}
exit;
