<?php
/**
 * E-Postaların nasıl kapatılacağını bildiren link
 *
 * @package Gurmehub
 */

?>

<div style="font-size: 14px; color: #6b7280; padding: 30px 0 30px 0;">
	<?php
		echo wp_kses_post(
			sprintf(
				// translators: %1$s Site name and link, %2$s Help link
				__( 'This email was prepared and forwarded by %1$s. To learn how to disable %2$s.', 'gurmepos' ),
				'<a href="' . home_url() . '" style="color: #6b7280;">' . get_bloginfo( 'name' ) . '</a>',
				'<a href="https://yardim.gurmehub.com/docs/pos-entegrator/bildirimler" style="color: #6b7280;">' . __( 'click here', 'gurmepos' ) . '</a>'
			)
		);
		?>
</div>
