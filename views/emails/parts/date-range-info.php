<?php
/**
 * E-Postalarda bildirilen işlemlerin tarih aralığı
 *
 * @package Gurmehub
 *
 * @var string $period
 */

?>
<table style="margin-top: 16px; margin-bottom: 16px" cellpadding="0" cellspacing="0" role="none">
	<tbody>
		<tr>
			<th>
			<div style="font-size: 14px; color: #9ca3af">
				<?php
				echo wp_kses_post(
					sprintf(
						// translators: %1$s Start date, %2$s End date.
						__( 'This data represents payment transactions between <strong>%1$s</strong> and <strong>%2$s</strong>', 'gurmepos' ),
						date_i18n( 'd F D', strtotime( 'daily' === $period ? '-24 hour' : '-1 week' ) ),
						date_i18n( 'd F D' ),
					)
				);
				?>
			</div>
		</tr>
	</tbody>
</table>
