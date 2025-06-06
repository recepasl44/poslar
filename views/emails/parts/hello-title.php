<?php
/**
 * Veri E-Postalarının giriş başlığı
 *
 * @package Gurmehub
 *
 * @var string $period
 */

?>
<table style="width: 100%; border-style: none; padding: 0" cellpadding="0" cellspacing="0" role="none">
	<tbody>
		<tr>
			<td align="left">
				<div style="font-size: 24px; font-weight: 700"><?php esc_html_e( 'Hello', 'gurmepos' ); ?> 👋</div>
					<div style="margin-top: 8px">
						<?php
							echo esc_html(
								sprintf(
									// translators: %1$s Plugin name,  %2$s period (today or this week)
									__( 'We thought you might be interested, let\'s see how many transactions you received with %1$s %2$s.', 'gurmepos' ),
									'POS Entegratör',
									'daily' === $period ? __( 'today', 'gurmepos' ) : __( 'this week', 'gurmepos' )
								),
							);
							?>
					</div>
			</td>
		</tr>
	</tbody>
</table>
