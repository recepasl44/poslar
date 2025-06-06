<?php // phpcs:ignore
namespace GurmeHub;

/**
 * Uygulama sınıfı
 */
class Frontend {
	/**
	 * Eklenti.
	 *
	 * @var GurmeHub\Plugin
	 */
	protected $plugin;


	/**
	 * Frontend kurucu method.
	 *
	 * @param GurmeHub\Plugin $plugin Eklenti
	 *
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Deaktif etme nedeni alma arayüzü.
	 *
	 * @return void
	 */
	public function reason_of_deactivate() {
		$plugin_name = $this->plugin->get_plugin_slug();
		?>
			<div class="gph-modal-area" id="<?php echo esc_attr( "{$plugin_name}-deactivate-reason-modal-area" ); ?>">
				<div class="gph-modal">
					<div class="gph-modal-title">
						<?php echo esc_html( sprintf( $this->texts( 'main_title' ), $this->plugin->get_name() ) ); ?>
						<p>
							<?php echo esc_html( $this->texts( 'main_description' ) ); ?>
						</p>
					</div>
					<div class="gph-modal-content">
						<p><?php echo esc_html( $this->texts( 'reasons_title' ) ); ?></p>
						<?php foreach ( $this->get_reasons() as $reason ) : ?>
						<label for="<?php echo esc_attr( "{$plugin_name}-{$reason['value']}" ); ?>">
							<input 
								type="checkbox"
								id="<?php echo esc_attr( "{$plugin_name}-{$reason['value']}" ); ?>"
								name="<?php echo esc_attr( "{$plugin_name}-deactivate-reason" ); ?>"
								value="<?php echo esc_attr( $reason['value'] ); ?>"
								class="<?php echo esc_attr( "{$plugin_name}-deactivate-reason-checkbox" ); ?>"
							>
							<?php echo esc_html( $reason['label'] ); ?>
						</label>
						<?php endforeach; ?>
						<label for="<?php echo esc_attr( "{$plugin_name}-reason-comment" ); ?>">
						<p><?php echo esc_html( $this->texts( 'comment_title' ) ); ?></p>
							<textarea 
								cols="30"
								id="<?php echo esc_attr( "{$plugin_name}-reason-comment" ); ?>"
							></textarea>
						</label>
					</div>
					<div class="gph-modal-footer">
						<div>
							<a href="#" id="<?php echo esc_attr( "{$plugin_name}-skip-reason" ); ?>"><?php echo esc_html( $this->texts( 'skip_button' ) ); ?></a>
						</div>
						<div>
							<button class="button" id="<?php echo esc_attr( "{$plugin_name}-cancel-modal" ); ?>"><?php echo esc_html( $this->texts( 'cancel_button' ) ); ?></button>
							<button class="button button-primary" id="<?php echo esc_attr( "{$plugin_name}-submit-reason" ); ?>"><?php echo esc_html( $this->texts( 'submit_button' ) ); ?></button>
						</div>
					</div>
				</div>
			</div>
		<?php
		$this->reason_of_deactivate_style();
		$this->reason_of_deactivate_script();
	}

	/**
	 * Deaktif etme nedenleri.
	 *
	 * @return array
	 */
	private function get_reasons() {
		return apply_filters( "gph_{$this->plugin->get_basename()}_deactive_reasons", array() );
	}

	/**
	 * Deaktif etme nedenleri.
	 *
	 * @param string $button Button text.
	 *
	 * @return string
	 */
	private function texts( $button ) {
		$texts = apply_filters(
			"gph_{$this->plugin->get_basename()}_texts",
			array(
				'skip_button'      => 'Skip & Deactive',
				'submit_button'    => 'Deactive',
				'cancel_button'    => 'Cancel',
				'reasons_title'    => 'Why you are leaving us ?',
				'comment_title'    => 'Comments (Optional)',
				'main_title'       => 'No thanks, i don\'t want the %s',
				'main_description' => 'After a step, the plugin will be deactivated. Could you please take a moment and support us to make your app better?',
			)
		);

		return $texts[ $button ];
	}

	/**
	 * Script
	 */
	public function reason_of_deactivate_script() {
		$plugin_name = $this->plugin->get_plugin_slug();
		?>
		<script>
			jQuery(document).ready(function($){
				const pluginName = "<?php echo esc_html( $plugin_name ); ?>";
				const $deactivateLink = $(`a.${pluginName}-deactivate-reason`);
				const $licenseError = $(`span.gph-license-error-${pluginName}`);
				const deactivateLinkHref = $deactivateLink.attr('href');

				if($licenseError){
					$notice = $licenseError.parent('p').parent('div');
					$notice.removeClass('notice-warning');
					$notice.addClass('notice-error');
				}

				$deactivateLink.click((e) => {
					e.preventDefault();
					openModal()
				});
				$(`a#${pluginName}-skip-reason`).click((e) => {
					e.preventDefault();
					const reasons = [
						{
							value: "skipped",
							label : 'skipped'
						}
					];
					submitReasons({
						reasons
					})
				});
				$(`button#${pluginName}-cancel-modal`).click(() => {
					closeModal()
				});
				$(`button#${pluginName}-submit-reason`).click(() => {
					const reasons = [];
					const comment =  $('#<?php echo esc_attr( "{$plugin_name}-reason-comment" ); ?>').val();
					if(comment){
						reasons.push(		{
							value: "comment",
							label : comment
						})
					}
					$('input[name="<?php echo esc_attr( "{$plugin_name}-deactivate-reason" ); ?>"]:checked').each(function(){
						const baseReasons = JSON.parse(`<?php echo wp_json_encode( $this->get_reasons() ); ?>`);
						reasons.push( baseReasons.find((base) => $(this).val() === base.value));
					});

					if(0 === reasons.length){
						$(".<?php echo esc_attr( "{$plugin_name}-deactivate-reason-checkbox" ); ?>").css('border', '1px solid red');
					}else{
					submitReasons({
						reasons,
					})
					}

				});

				const deactivate = () => {
					closeModal();
					window.location.href = deactivateLinkHref;
				}

				const openModal = () => {
					$(`#${pluginName}-deactivate-reason-modal-area`).css('display','flex');
				}

				const closeModal = () => {
					$(`#${pluginName}-deactivate-reason-modal-area`).css('display','none');
				}

				const submitReasons = (data) => {
					const url = '/wp-admin/admin-ajax.php?action=<?php echo esc_attr( "{$plugin_name}_deactivate_reasons" ); ?>&_wpnonce=<?php echo esc_attr( wp_create_nonce( "{$plugin_name}_deactivate_reasons" ) ); ?>';
					$.ajax({
						url,
						type: 'POST',
						data,
						success: deactivate(),
						error: deactivate()
					})
				}

				$('.gph-modal-area').click(function(e){
					if (e.target !== this){
						return;
					}
					$('.gph-modal').css('transform', 'scale(0.9)');
					setTimeout(() => {
						$('.gph-modal').css('transform', 'scale(1)');
					},50);
				});
			});
		</script>
		<?php
	}

	/**
	 * Stil
	 *
	 * @return void
	 */
	private function reason_of_deactivate_style() {
		?>
		<style>
			.gph-modal-area{
				display: none;
				position: absolute;
				justify-content: center;
				align-items: center;
				top: 0;
				left: 0;
				width: 100%;
				height: 100vh;
				z-index: 98;
			}
			.gph-modal{
				padding: 20px;
				min-width: 20%;
				max-width: 20%;
				position: fixed;
				background-color: #FFFFFF;
				box-shadow: 3px 8px 15px #888888;
				border-radius: 5px;
				display: flex;
				flex-direction: column;
				z-index: 99;
			}
			.gph-modal-title{
				font-weight: 600;
				font-size: large;
			}
			.gph-modal-title p{
				font-weight: 400;
				margin: 5px 0 !important;
			}
			.gph-modal-content{
				display: flex;
				flex-direction: column;
				gap:10px;
				padding: 10px 0;
			}

			.gph-modal-content textarea{
				width: 100%;
			}

			.gph-modal-content p{
				width: 100%;
				font-weight: 500;
				font-size: 14px;
				margin: 5px 0 !important;
			}

			.gph-modal-footer{
				display: flex;
				justify-content: space-between;
				align-items: center;
			}

			.gph-modal-footer a{
				font-size: 10px;
			}
		</style>
		<?php
	}
}
