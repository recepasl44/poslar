<?php
/**
 * GurmePOS için admin menülerini olşturan sınıfı olan GPOS_Admin_Menu sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * GurmePOS admin menü ve bar sınıfı.
 */
class GPOS_Admin {

	/**
	 * Eklenti prefix.
	 *
	 * @var string $prefix
	 */
	protected $prefix = GPOS_PREFIX;

	/**
	 * Eklenti simgesi.
	 *
	 * @var string $icon
	 */
	protected $icon = '';

	/**
	 * Eklenti menü ismi
	 *
	 * @var string $parent_title
	 */
	public $parent_title = 'POS Entegratör';

	/**
	 * Eklenti menü urlini oluşturacak slug.
	 *
	 * @var string $parent_slug
	 */
	public $parent_slug = 'gurmepos';

	/**
	 * Admin menüye eklenecek menüleri ekler ve callback fonksiyonlarını organize eder
	 *
	 * @return void
	 *   */
	public function admin_menu() {
		global $submenu;

		$menu_pages = array(
			array(
				'menu_title' => __( 'Dashboard', 'gurmepos' ),
				'menu_slug'  => $this->parent_slug,
			),
			array(
				'menu_title' => false,
				'menu_slug'  => "{$this->prefix}-payment-method",
				'hidden'     => true,
			),
			array(
				'menu_title' => false,
				'menu_slug'  => "{$this->prefix}-add-payment-method",
				'hidden'     => true,
			),
			array(
				'menu_title' => false,
				'menu_slug'  => "{$this->prefix}-transaction",
				'hidden'     => true,
			),
			array(
				'menu_title' => __( 'Payment Methods', 'gurmepos' ),
				'menu_slug'  => "{$this->prefix}-payment-methods",
			),

			array(
				'menu_title' => __( 'Settings', 'gurmepos' ) . $this->status_badge(),
				'menu_slug'  => "{$this->prefix}-settings",
			),
		);
		add_menu_page(
			$this->parent_title,
			$this->parent_title,
			gpos_capability(),
			$this->parent_slug,
			'__return_false',
			$this->get_icon(),
			59
		);

		foreach ( $menu_pages as $sub_menu_page ) {
			add_submenu_page(
				isset( $sub_menu_page['hidden'] ) && true === $sub_menu_page['hidden'] ? '' : $this->parent_slug,
				$sub_menu_page['menu_title'],
				$sub_menu_page['menu_title'],
				gpos_capability(),
				$sub_menu_page['menu_slug'],
				array( $this, 'view' ),
			);
		}

		$submenu_order = apply_filters(
			'gpos_admin_submenu_order',
			array(
				'gurmepos'                            => 10,
				'edit.php?post_type=gpos_transaction' => 20,
				'gpos-payment-methods'                => 90,
				'gpos-settings'                       => 100,
			)
		);

		if ( isset( $submenu[ $this->parent_slug ] ) && false === empty( $submenu[ $this->parent_slug ] ) ) {
			$submenu[ $this->parent_slug ] = array_map(
				function ( $menu ) use ( $submenu_order ) {
					$menu['priority'] = isset( $submenu_order[ $menu[2] ] ) ? $submenu_order[ $menu[2] ] : 80;
					return $menu;
				},
				$submenu[ $this->parent_slug ]
			);

			usort( $submenu[ $this->parent_slug ], fn ( $a_elem, $b_elem ) => $a_elem['priority'] - $b_elem['priority'] );

			if ( ! gpos_is_pro_active() ) {

				$submenu[ $this->parent_slug ][] = array(
					sprintf(
						'%2$s <img src="%1$s/images/new-tab.svg" class="new-tab">',
						GPOS_ASSETS_DIR_URL,
						__( 'Upgrade Pro', 'gurmepos' ),
					),
					'manage_woocommerce',
					gpos_create_utm_link( 'sol_menu' ),
					false,
					'gpos-target-blank gpos-upgrade-pro',
				);
			}
		}
	}

	/**
	 * Eklenti alt menüleri açıldığında ilgili vue sayfasını render eder
	 *
	 * @return void
	 */
	public function view() {

		$page = isset( $_GET['page'] ) ? str_replace( "{$this->prefix}-", '', gpos_clean( $_GET['page'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $page ) {
			gpos_vue()
			->set_vue_page( $page )
			->set_localize( $this->get_localize_data( $page ) )
			->require_script()
			->require_style()
			->create_app_div();
		}
	}


	/**
	 * Display admin bar when active.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 *
	 * @return void
	 */
	public function admin_bar_menu( WP_Admin_Bar $wp_admin_bar ) {
		if ( current_user_can( gpos_capability() ) && gpos_other_settings()->get_setting_by_key( 'admin_bar_menu' ) ) {
			$admin_bar_args = array(
				'id'    => "{$this->parent_slug}-admin-bar",
				'title' => sprintf(
					'<span style="display:flex; align-items:center; gap:4px;"><img style="width:20px;height:20px;" src="%s"><span class="ab-label">POS Entegratör %s</span></span>',
					$this->get_icon(),
					gpos_is_test_mode() ? __( 'Test Mode Active', 'gurmepos' ) : ''
				),
				'href'  => admin_url( 'admin.php?page=gurmepos' ),
			);

			if ( gpos_is_test_mode() ) {
				$admin_bar_args['meta'] = array(
					'class' => 'gpos-test-mode-active',
				);
			}

			$wp_admin_bar->add_menu( $admin_bar_args );

			global $submenu;
			$menus = get_option( 'gpos_submenu_pages', array() );

			if ( is_array( $submenu ) && array_key_exists( $this->parent_slug, $submenu ) && false === empty( $submenu[ $this->parent_slug ] ) ) {
				$menus = array_filter( $submenu[ $this->parent_slug ], fn ( $menu ) =>  false === isset( $menu[4] ) || 'gpos-target-blank gpos-upgrade-pro' !== $menu[4] );
				update_option( 'gpos_submenu_pages', $menus );
			}

			if ( false === empty( $menus ) ) {
				foreach ( $menus as $key => $sub_menu_page ) {
					$wp_admin_bar->add_node(
						array(
							'parent' => "{$this->parent_slug}-admin-bar",
							'id'     => "menu_{$key}",
							'title'  => $sub_menu_page[0],
							'href'   => false === strpos( $sub_menu_page[2], '?' ) ? admin_url( "admin.php?page={$sub_menu_page[2]}" ) : admin_url( $sub_menu_page[2] ),
						)
					);
				}
			}
		}
	}

	/**
	 * Vue render edildiğinde kullanacağı verileri düzenler..
	 *
	 * @param string $page Yüklenecek sayfa.
	 *
	 * @return array
	 *
	 * @SuppressWarnings("CyclomaticComplexity")
	 */
	private function get_localize_data( $page ) {
		$localize = array(
			'prefix'                       => GPOS_PREFIX,
			'assets_url'                   => GPOS_ASSETS_DIR_URL,
			'version'                      => GPOS_VERSION,
			'home_url'                     => home_url(),
			'admin_url'                    => admin_url(),
			'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
			'nonce'                        => wp_create_nonce( GPOS_AJAX_ACTION ),
			'is_pro_active'                => gpos_is_pro_active(),
			'is_form_active'               => gpos_is_form_active(),
			'is_test_mode'                 => gpos_is_test_mode(),
			'payment_gateways'             => gpos_get_payment_gateways(),
			'virtual_pos_accounts'         => gpos_gateway_accounts()->get_accounts( GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_VIRTUAL_POS ),
			'alternative_payment_accounts' => gpos_gateway_accounts()->get_accounts( GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_ALTERNATIVE ),
			'common_form_accounts'         => gpos_gateway_accounts()->get_accounts( GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_COMMON ),
			'iframe_accounts'              => gpos_gateway_accounts()->get_accounts( GPOS_Transaction_Utils::PAYMENT_METHOD_TYPE_IFRAME ),
			'wc_order_statuses'            => gpos_get_wc_order_statuses(),
			'woocommerce_settings'         => gpos_woocommerce_settings()->get_settings(),
			'form_settings'                => gpos_form_settings()->get_settings(),
			'tag_manager_settings'         => gpos_tag_manager_settings()->get_settings(),
			'notification_settings'        => gpos_notification_settings()->get_settings(),
			'other_settings'               => gpos_other_settings()->get_settings(),
			'total_error_count'            => gpos_status_check()->get_total_error_count(),
			'strings'                      => gpos_get_i18n_texts(),
			'alert_texts'                  => gpos_get_alert_texts(),
			'status'                       => gpos_get_env_info(),
			'dashboard'                    => gpos_dashboard(),
			'ins_display_settings'         => gpos_ins_display_settings()->get_settings(),
			'hide_rating_message'          => (bool) get_user_meta( get_current_user_id(), 'gpos_hide_rating_message', true ),
			'iyzipos'                      => gpos_iyzipos()->check(),
			'locale_languages'             => apply_filters(
				'gpos_locale_languages',
				array(
					'tr' => 'Türkçe',
					'en' => 'English',
				)
			),
			'integrations'                 => array(
				GPOS_Transaction_Utils::WOOCOMMERCE => gpos_is_woocommerce_enabled(),
			),
		);

		$nonce = isset( $_GET['_wpnonce'] ) && wp_verify_nonce( gpos_clean( $_GET['_wpnonce'] ), GPOS_AJAX_ACTION );

		if ( $nonce && isset( $_GET['id'] ) && 'payment-method' === $page ) {
			$localize['gateway_account'] = gpos_gateway_account( (int) gpos_clean( $_GET['id'] ) );
		}
		if ( $nonce && isset( $_GET['gateway'] ) && 'add-payment-method' === $page ) {

			$localize['gateway_account'] = gpos_gateway_account()->load_by_gateway_id( gpos_clean( $_GET['gateway'] ) );
		}
		if ( $nonce && isset( $_GET['transaction'] ) && 'transaction' === $page ) {
			$localize['transaction'] = gpos_transaction( (int) gpos_clean( $_GET['transaction'] ) )->to_array();
		}

		return apply_filters( 'gpos_vue_admin_localize_data', $localize );
	}

	/**
	 * Eklenti gerekli iconunun stilini döner.
	 *
	 * @return string
	 */
	private function status_badge() {
		gpos_status_check()->check_total_errors();
		$total_error_count = (int) gpos_status_check()->get_total_error_count();

		ob_start();
		gpos_get_view( 'required-badge.php', array( 'total_error_count' => $total_error_count ) );
		return ob_get_clean();
	}

	/**
	 * Eklenti ikonunu döndürür
	 *
	 * @return string
	 */
	private function get_icon() {
		return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTUwIiB6b29tQW5kUGFuPSJtYWduaWZ5IiB2aWV3Qm94PSIwIDAgMTEyLjUgMTEyLjQ5OTk5NyIgaGVpZ2h0PSIxNTAiIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIG1lZXQiIHZlcnNpb249IjEuMCI+PGRlZnM+PGNsaXBQYXRoIGlkPSJjODdlZjg5N2YwIj48cGF0aCBkPSJNIDUuMTM2NzE5IDUuMTM2NzE5IEwgMTA3LjEzNjcxOSA1LjEzNjcxOSBMIDEwNy4xMzY3MTkgMTA3LjEzNjcxOSBMIDUuMTM2NzE5IDEwNy4xMzY3MTkgWiBNIDUuMTM2NzE5IDUuMTM2NzE5ICIgY2xpcC1ydWxlPSJub256ZXJvIi8+PC9jbGlwUGF0aD48L2RlZnM+PGcgY2xpcC1wYXRoPSJ1cmwoI2M4N2VmODk3ZjApIj48cGF0aCBmaWxsPSIjZmZmZmZmIiBkPSJNIDU2LjEzNjcxOSA1LjEzNjcxOSBDIDU0LjQ2NDg0NCA1LjEzNjcxOSA1Mi44MDA3ODEgNS4yMTg3NSA1MS4xMzY3MTkgNS4zODI4MTIgQyA0OS40NzY1NjIgNS41NDY4NzUgNDcuODI0MjE5IDUuNzg5MDYyIDQ2LjE4NzUgNi4xMTcxODggQyA0NC41NDY4NzUgNi40NDE0MDYgNDIuOTI5Njg4IDYuODQ3NjU2IDQxLjMzMjAzMSA3LjMzMjAzMSBDIDM5LjczNDM3NSA3LjgxNjQwNiAzOC4xNjQwNjIgOC4zNzg5MDYgMzYuNjE3MTg4IDkuMDE5NTMxIEMgMzUuMDc0MjE5IDkuNjU2MjUgMzMuNTY2NDA2IDEwLjM3MTA5NCAzMi4wOTM3NSAxMS4xNTYyNSBDIDMwLjYyMTA5NCAxMS45NDUzMTIgMjkuMTkxNDA2IDEyLjgwNDY4OCAyNy44MDA3ODEgMTMuNzMwNDY5IEMgMjYuNDE0MDYyIDE0LjY2MDE1NiAyNS4wNzQyMTkgMTUuNjUyMzQ0IDIzLjc4MTI1IDE2LjcxMDkzOCBDIDIyLjQ5MjE4OCAxNy43NzM0MzggMjEuMjUzOTA2IDE4Ljg5MDYyNSAyMC4wNzQyMTkgMjAuMDc0MjE5IEMgMTguODkwNjI1IDIxLjI1MzkwNiAxNy43NzM0MzggMjIuNDkyMTg4IDE2LjcxMDkzOCAyMy43ODEyNSBDIDE1LjY1MjM0NCAyNS4wNzQyMTkgMTQuNjYwMTU2IDI2LjQxNDA2MiAxMy43MzA0NjkgMjcuODAwNzgxIEMgMTIuODA0Njg4IDI5LjE5MTQwNiAxMS45NDUzMTIgMzAuNjIxMDk0IDExLjE1NjI1IDMyLjA5Mzc1IEMgMTAuMzcxMDk0IDMzLjU2NjQwNiA5LjY1NjI1IDM1LjA3NDIxOSA5LjAxOTUzMSAzNi42MTcxODggQyA4LjM3ODkwNiAzOC4xNjQwNjIgNy44MTY0MDYgMzkuNzM0Mzc1IDcuMzMyMDMxIDQxLjMzMjAzMSBDIDYuODQ3NjU2IDQyLjkyOTY4OCA2LjQ0MTQwNiA0NC41NDY4NzUgNi4xMTcxODggNDYuMTg3NSBDIDUuNzg5MDYyIDQ3LjgyNDIxOSA1LjU0Njg3NSA0OS40NzY1NjIgNS4zODI4MTIgNTEuMTM2NzE5IEMgNS4yMTg3NSA1Mi44MDA3ODEgNS4xMzY3MTkgNTQuNDY0ODQ0IDUuMTM2NzE5IDU2LjEzNjcxOSBDIDUuMTM2NzE5IDU3LjgwNDY4OCA1LjIxODc1IDU5LjQ3MjY1NiA1LjM4MjgxMiA2MS4xMzI4MTIgQyA1LjU0Njg3NSA2Mi43OTY4NzUgNS43ODkwNjIgNjQuNDQ1MzEyIDYuMTE3MTg4IDY2LjA4NTkzOCBDIDYuNDQxNDA2IDY3LjcyMjY1NiA2Ljg0NzY1NiA2OS4zNDM3NSA3LjMzMjAzMSA3MC45NDE0MDYgQyA3LjgxNjQwNiA3Mi41MzkwNjIgOC4zNzg5MDYgNzQuMTA5Mzc1IDkuMDE5NTMxIDc1LjY1MjM0NCBDIDkuNjU2MjUgNzcuMTk1MzEyIDEwLjM3MTA5NCA3OC43MDMxMjUgMTEuMTU2MjUgODAuMTc1NzgxIEMgMTEuOTQ1MzEyIDgxLjY0ODQzOCAxMi44MDQ2ODggODMuMDgyMDMxIDEzLjczMDQ2OSA4NC40Njg3NSBDIDE0LjY2MDE1NiA4NS44NTkzNzUgMTUuNjUyMzQ0IDg3LjE5OTIxOSAxNi43MTA5MzggODguNDg4MjgxIEMgMTcuNzczNDM4IDg5Ljc4MTI1IDE4Ljg5MDYyNSA5MS4wMTU2MjUgMjAuMDc0MjE5IDkyLjE5OTIxOSBDIDIxLjI1MzkwNiA5My4zNzg5MDYgMjIuNDkyMTg4IDk0LjUgMjMuNzgxMjUgOTUuNTU4NTk0IEMgMjUuMDc0MjE5IDk2LjYxNzE4OCAyNi40MTQwNjIgOTcuNjEzMjgxIDI3LjgwMDc4MSA5OC41MzkwNjIgQyAyOS4xOTE0MDYgOTkuNDY4NzUgMzAuNjIxMDk0IDEwMC4zMjgxMjUgMzIuMDkzNzUgMTAxLjExMzI4MSBDIDMzLjU2NjQwNiAxMDEuOTAyMzQ0IDM1LjA3NDIxOSAxMDIuNjEzMjgxIDM2LjYxNzE4OCAxMDMuMjUzOTA2IEMgMzguMTY0MDYyIDEwMy44OTQ1MzEgMzkuNzM0Mzc1IDEwNC40NTMxMjUgNDEuMzMyMDMxIDEwNC45NDE0MDYgQyA0Mi45Mjk2ODggMTA1LjQyNTc4MSA0NC41NDY4NzUgMTA1LjgyODEyNSA0Ni4xODc1IDEwNi4xNTYyNSBDIDQ3LjgyNDIxOSAxMDYuNDgwNDY5IDQ5LjQ3NjU2MiAxMDYuNzI2NTYyIDUxLjEzNjcxOSAxMDYuODkwNjI1IEMgNTIuODAwNzgxIDEwNy4wNTQ2ODggNTQuNDY0ODQ0IDEwNy4xMzY3MTkgNTYuMTM2NzE5IDEwNy4xMzY3MTkgQyA1Ny44MDQ2ODggMTA3LjEzNjcxOSA1OS40NzI2NTYgMTA3LjA1NDY4OCA2MS4xMzI4MTIgMTA2Ljg5MDYyNSBDIDYyLjc5Njg3NSAxMDYuNzI2NTYyIDY0LjQ0NTMxMiAxMDYuNDgwNDY5IDY2LjA4NTkzOCAxMDYuMTU2MjUgQyA2Ny43MjI2NTYgMTA1LjgyODEyNSA2OS4zNDM3NSAxMDUuNDI1NzgxIDcwLjk0MTQwNiAxMDQuOTQxNDA2IEMgNzIuNTM5MDYyIDEwNC40NTMxMjUgNzQuMTA5Mzc1IDEwMy44OTQ1MzEgNzUuNjUyMzQ0IDEwMy4yNTM5MDYgQyA3Ny4xOTUzMTIgMTAyLjYxMzI4MSA3OC43MDMxMjUgMTAxLjkwMjM0NCA4MC4xNzU3ODEgMTAxLjExMzI4MSBDIDgxLjY0ODQzOCAxMDAuMzI4MTI1IDgzLjA4MjAzMSA5OS40Njg3NSA4NC40Njg3NSA5OC41MzkwNjIgQyA4NS44NTkzNzUgOTcuNjEzMjgxIDg3LjE5OTIxOSA5Ni42MTcxODggODguNDg4MjgxIDk1LjU1ODU5NCBDIDg5Ljc4MTI1IDk0LjUgOTEuMDE1NjI1IDkzLjM3ODkwNiA5Mi4xOTkyMTkgOTIuMTk5MjE5IEMgOTMuMzc4OTA2IDkxLjAxNTYyNSA5NC41IDg5Ljc4MTI1IDk1LjU1ODU5NCA4OC40ODgyODEgQyA5Ni42MTcxODggODcuMTk5MjE5IDk3LjYxMzI4MSA4NS44NTkzNzUgOTguNTM5MDYyIDg0LjQ2ODc1IEMgOTkuNDY4NzUgODMuMDgyMDMxIDEwMC4zMjgxMjUgODEuNjQ4NDM4IDEwMS4xMTMyODEgODAuMTc1NzgxIEMgMTAxLjkwMjM0NCA3OC43MDMxMjUgMTAyLjYxMzI4MSA3Ny4xOTUzMTIgMTAzLjI1MzkwNiA3NS42NTIzNDQgQyAxMDMuODk0NTMxIDc0LjEwOTM3NSAxMDQuNDUzMTI1IDcyLjUzOTA2MiAxMDQuOTQxNDA2IDcwLjk0MTQwNiBDIDEwNS40MjU3ODEgNjkuMzQzNzUgMTA1LjgyODEyNSA2Ny43MjI2NTYgMTA2LjE1NjI1IDY2LjA4NTkzOCBDIDEwNi40ODA0NjkgNjQuNDQ1MzEyIDEwNi43MjY1NjIgNjIuNzk2ODc1IDEwNi44OTA2MjUgNjEuMTMyODEyIEMgMTA3LjA1NDY4OCA1OS40NzI2NTYgMTA3LjEzNjcxOSA1Ny44MDQ2ODggMTA3LjEzNjcxOSA1Ni4xMzY3MTkgQyAxMDcuMTM2NzE5IDU0LjQ2NDg0NCAxMDcuMDU0Njg4IDUyLjgwMDc4MSAxMDYuODkwNjI1IDUxLjEzNjcxOSBDIDEwNi43MjY1NjIgNDkuNDc2NTYyIDEwNi40ODA0NjkgNDcuODI0MjE5IDEwNi4xNTYyNSA0Ni4xODc1IEMgMTA1LjgyODEyNSA0NC41NDY4NzUgMTA1LjQyNTc4MSA0Mi45Mjk2ODggMTA0Ljk0MTQwNiA0MS4zMzIwMzEgQyAxMDQuNDUzMTI1IDM5LjczNDM3NSAxMDMuODk0NTMxIDM4LjE2NDA2MiAxMDMuMjUzOTA2IDM2LjYxNzE4OCBDIDEwMi42MTMyODEgMzUuMDc0MjE5IDEwMS45MDIzNDQgMzMuNTY2NDA2IDEwMS4xMTMyODEgMzIuMDkzNzUgQyAxMDAuMzI4MTI1IDMwLjYyMTA5NCA5OS40Njg3NSAyOS4xOTE0MDYgOTguNTM5MDYyIDI3LjgwMDc4MSBDIDk3LjYxMzI4MSAyNi40MTQwNjIgOTYuNjE3MTg4IDI1LjA3NDIxOSA5NS41NTg1OTQgMjMuNzgxMjUgQyA5NC41IDIyLjQ5MjE4OCA5My4zNzg5MDYgMjEuMjUzOTA2IDkyLjE5OTIxOSAyMC4wNzQyMTkgQyA5MS4wMTU2MjUgMTguODkwNjI1IDg5Ljc4MTI1IDE3Ljc3MzQzOCA4OC40ODgyODEgMTYuNzEwOTM4IEMgODcuMTk5MjE5IDE1LjY1MjM0NCA4NS44NTkzNzUgMTQuNjYwMTU2IDg0LjQ2ODc1IDEzLjczMDQ2OSBDIDgzLjA4MjAzMSAxMi44MDQ2ODggODEuNjQ4NDM4IDExLjk0NTMxMiA4MC4xNzU3ODEgMTEuMTU2MjUgQyA3OC43MDMxMjUgMTAuMzcxMDk0IDc3LjE5NTMxMiA5LjY1NjI1IDc1LjY1MjM0NCA5LjAxOTUzMSBDIDc0LjEwOTM3NSA4LjM3ODkwNiA3Mi41MzkwNjIgNy44MTY0MDYgNzAuOTQxNDA2IDcuMzMyMDMxIEMgNjkuMzQzNzUgNi44NDc2NTYgNjcuNzIyNjU2IDYuNDQxNDA2IDY2LjA4NTkzOCA2LjExNzE4OCBDIDY0LjQ0NTMxMiA1Ljc4OTA2MiA2Mi43OTY4NzUgNS41NDY4NzUgNjEuMTMyODEyIDUuMzgyODEyIEMgNTkuNDcyNjU2IDUuMjE4NzUgNTcuODA0Njg4IDUuMTM2NzE5IDU2LjEzNjcxOSA1LjEzNjcxOSBaIE0gNjMuNDIxODc1IDczLjkyOTY4OCBDIDYzLjE5NTMxMiA3My45NjQ4NDQgNjIuOTY4NzUgNzMuOTcyNjU2IDYyLjc0MjE4OCA3My45NTcwMzEgQyA2Mi41MTU2MjUgNzMuOTQxNDA2IDYyLjI5Mjk2OSA3My44OTg0MzggNjIuMDc0MjE5IDczLjgzNTkzOCBDIDYxLjg1NTQ2OSA3My43NzM0MzggNjEuNjQ4NDM4IDczLjY4NzUgNjEuNDQ1MzEyIDczLjU3ODEyNSBDIDYxLjI0NjA5NCA3My40Njg3NSA2MS4wNTg1OTQgNzMuMzM5ODQ0IDYwLjg4NjcxOSA3My4xOTE0MDYgQyA2MC43MTQ4NDQgNzMuMDQyOTY5IDYwLjU1ODU5NCA3Mi44Nzg5MDYgNjAuNDIxODc1IDcyLjY5OTIxOSBDIDYwLjI4NTE1NiA3Mi41MTU2MjUgNjAuMTY3OTY5IDcyLjMyNDIxOSA2MC4wNzQyMTkgNzIuMTE3MTg4IEMgNTkuOTc2NTYyIDcxLjkxMDE1NiA1OS45MDYyNSA3MS42OTUzMTIgNTkuODU1NDY5IDcxLjQ3MjY1NiBDIDU5LjgwNDY4OCA3MS4yNSA1OS43ODEyNSA3MS4wMjczNDQgNTkuNzc3MzQ0IDcwLjc5Njg3NSBMIDU5Ljc3NzM0NCA2Mi40MTc5NjkgQyA1OS43NzczNDQgNjIuMDY2NDA2IDU5LjgzNTkzOCA2MS43MTg3NSA1OS45NTMxMjUgNjEuMzg2NzE5IEMgNjAuMDY2NDA2IDYxLjA1MDc4MSA2MC4yMzQzNzUgNjAuNzQ2MDk0IDYwLjQ1NzAzMSA2MC40NjQ4NDQgQyA2MC42NzU3ODEgNjAuMTg3NSA2MC45MzM1OTQgNTkuOTUzMTI1IDYxLjIzNDM3NSA1OS43NjU2MjUgQyA2MS41MzUxNTYgNTkuNTc0MjE5IDYxLjg1NTQ2OSA1OS40NDE0MDYgNjIuMjAzMTI1IDU5LjM1OTM3NSBDIDYyLjg3MTA5NCA1OS4yMDcwMzEgNjMuNTE5NTMxIDU4Ljk5NjA5NCA2NC4xNDQ1MzEgNTguNzIyNjU2IEMgNjQuNzczNDM4IDU4LjQ0NTMxMiA2NS4zNzEwOTQgNTguMTE3MTg4IDY1LjkzNzUgNTcuNzMwNDY5IEMgNjYuNTAzOTA2IDU3LjM0Mzc1IDY3LjAyNzM0NCA1Ni45MTAxNTYgNjcuNTExNzE5IDU2LjQyMTg3NSBDIDY3Ljk5NjA5NCA1NS45Mzc1IDY4LjQzMzU5NCA1NS40MTQwNjIgNjguODE2NDA2IDU0Ljg0Mzc1IEMgNjkuMjAzMTI1IDU0LjI3NzM0NCA2OS41MzEyNSA1My42Nzk2ODggNjkuODA0Njg4IDUzLjA1MDc4MSBDIDcwLjA3ODEyNSA1Mi40MjE4NzUgNzAuMjg5MDYyIDUxLjc3MzQzOCA3MC40Mzc1IDUxLjEwNTQ2OSBDIDcwLjU4OTg0NCA1MC40Mzc1IDcwLjY3NTc4MSA0OS43NjE3MTkgNzAuNjk5MjE5IDQ5LjA3NDIxOSBDIDcwLjcyMjY1NiA0OC4zOTA2MjUgNzAuNjgzNTk0IDQ3LjcxMDkzOCA3MC41NzgxMjUgNDcuMDMxMjUgQyA3MC40NzI2NTYgNDYuMzU1NDY5IDcwLjMwNDY4OCA0NS42OTE0MDYgNzAuMDc4MTI1IDQ1LjA0Njg3NSBDIDY5Ljg0NzY1NiA0NC40MDIzNDQgNjkuNTU4NTk0IDQzLjc4MTI1IDY5LjIxNDg0NCA0My4xOTE0MDYgQyA2OC44NjcxODggNDIuNTk3NjU2IDY4LjQ2ODc1IDQyLjA0Mjk2OSA2OC4wMTk1MzEgNDEuNTI3MzQ0IEMgNjcuNTcwMzEyIDQxLjAxMTcxOSA2Ny4wNzQyMTkgNDAuNTM5MDYyIDY2LjUzOTA2MiA0MC4xMTcxODggQyA2NiAzOS42OTE0MDYgNjUuNDI1NzgxIDM5LjMyMDMxMiA2NC44MTY0MDYgMzkuMDA3ODEyIEMgNjQuMjEwOTM4IDM4LjY5MTQwNiA2My41NzgxMjUgMzguNDMzNTk0IDYyLjkyMTg3NSAzOC4yMzgyODEgQyA2Mi4yNjU2MjUgMzguMDM5MDYyIDYxLjU5Mzc1IDM3LjkwNjI1IDYwLjkxNDA2MiAzNy44MzIwMzEgQyA2MC4yMzA0NjkgMzcuNzYxNzE5IDU5LjU1MDc4MSAzNy43NTM5MDYgNTguODY3MTg4IDM3LjgxMjUgQyA1OC4xODM1OTQgMzcuODcxMDk0IDU3LjUxMTcxOSAzNy45OTIxODggNTYuODUxNTYyIDM4LjE3NTc4MSBDIDU2LjE5MTQwNiAzOC4zNTU0NjkgNTUuNTU0Njg4IDM4LjYwMTU2MiA1NC45Mzc1IDM4LjkwNjI1IEMgNTQuMzI0MjE5IDM5LjIwNzAzMSA1My43NDIxODggMzkuNTY2NDA2IDUzLjE5NTMxMiAzOS45ODA0NjkgQyA1Mi42NDg0MzggNDAuMzkwNjI1IDUyLjE0NDUzMSA0MC44NTE1NjIgNTEuNjgzNTk0IDQxLjM1OTM3NSBDIDUxLjIyMjY1NiA0MS44NjcxODggNTAuODE2NDA2IDQyLjQxNDA2MiA1MC40NTcwMzEgNDIuOTk2MDk0IEMgNTAuMDk3NjU2IDQzLjU4MjAzMSA0OS44MDA3ODEgNDQuMTk1MzEyIDQ5LjU1NDY4OCA0NC44MzU5MzggQyA0OS4zMTI1IDQ1LjQ3NjU2MiA0OS4xMzI4MTIgNDYuMTMyODEyIDQ5LjAxNTYyNSA0Ni44MDg1OTQgQyA0OC44OTQ1MzEgNDcuNDg0Mzc1IDQ4LjgzOTg0NCA0OC4xNjQwNjIgNDguODUxNTYyIDQ4Ljg1MTU2MiBMIDQ4Ljg1MTU2MiA4My40NTcwMzEgQyA0OC44NTE1NjIgODMuODE2NDA2IDQ4LjgxNjQwNiA4NC4xNzE4NzUgNDguNzQ2MDk0IDg0LjUyMzQzOCBDIDQ4LjY3NTc4MSA4NC44NzUgNDguNTcwMzEyIDg1LjIxODc1IDQ4LjQzMzU5NCA4NS41NDY4NzUgQyA0OC4yOTY4NzUgODUuODc4OTA2IDQ4LjEyODkwNiA4Ni4xOTUzMTIgNDcuOTI5Njg4IDg2LjQ5MjE4OCBDIDQ3LjczMDQ2OSA4Ni43OTI5NjkgNDcuNTAzOTA2IDg3LjA2NjQwNiA0Ny4yNSA4Ny4zMjAzMTIgQyA0Ni45OTYwOTQgODcuNTc0MjE5IDQ2LjcxODc1IDg3LjgwMDc4MSA0Ni40MjE4NzUgODggQyA0Ni4xMjUgODguMTk5MjE5IDQ1LjgwODU5NCA4OC4zNjcxODggNDUuNDc2NTYyIDg4LjUwMzkwNiBDIDQ1LjE0NDUzMSA4OC42NDQ1MzEgNDQuODA0Njg4IDg4Ljc0NjA5NCA0NC40NTMxMjUgODguODE2NDA2IEMgNDQuMTAxNTYyIDg4Ljg4NjcxOSA0My43NDYwOTQgODguOTIxODc1IDQzLjM4NjcxOSA4OC45MjE4NzUgTCAzOS43NDIxODggODguOTIxODc1IEMgMzkuMzgyODEyIDg4LjkyMTg3NSAzOS4wMjczNDQgODguODg2NzE5IDM4LjY3NTc4MSA4OC44MTY0MDYgQyAzOC4zMjQyMTkgODguNzQ2MDk0IDM3Ljk4NDM3NSA4OC42NDQ1MzEgMzcuNjUyMzQ0IDg4LjUwMzkwNiBDIDM3LjMyMDMxMiA4OC4zNjcxODggMzcuMDAzOTA2IDg4LjE5OTIxOSAzNi43MDcwMzEgODggQyAzNi40MTAxNTYgODcuODAwNzgxIDM2LjEzMjgxMiA4Ny41NzQyMTkgMzUuODc4OTA2IDg3LjMyMDMxMiBDIDM1LjYyNSA4Ny4wNjY0MDYgMzUuMzk4NDM4IDg2Ljc5Mjk2OSAzNS4xOTkyMTkgODYuNDkyMTg4IEMgMzUgODYuMTk1MzEyIDM0LjgzMjAzMSA4NS44Nzg5MDYgMzQuNjk1MzEyIDg1LjU0Njg3NSBDIDM0LjU1ODU5NCA4NS4yMTg3NSAzNC40NTMxMjUgODQuODc1IDM0LjM4MjgxMiA4NC41MjM0MzggQyAzNC4zMTI1IDg0LjE3MTg3NSAzNC4yNzczNDQgODMuODE2NDA2IDM0LjI3NzM0NCA4My40NTcwMzEgTCAzNC4yNzczNDQgNDkuNTA3ODEyIEMgMzQuMjY5NTMxIDQ4LjY4NzUgMzQuMzAwNzgxIDQ3Ljg2NzE4OCAzNC4zNjcxODggNDcuMDUwNzgxIEMgMzQuNDM3NSA0Ni4yMzQzNzUgMzQuNTQyOTY5IDQ1LjQyMTg3NSAzNC42ODc1IDQ0LjYxNzE4OCBDIDM0LjgzMjAzMSA0My44MDg1OTQgMzUuMDE1NjI1IDQzLjAxMTcxOSAzNS4yMzgyODEgNDIuMjIyNjU2IEMgMzUuNDU3MDMxIDQxLjQzMzU5NCAzNS43MTQ4NDQgNDAuNjU2MjUgMzYuMDExNzE5IDM5Ljg5MDYyNSBDIDM2LjMwNDY4OCAzOS4xMjUgMzYuNjM2NzE5IDM4LjM3ODkwNiAzNy4wMDM5MDYgMzcuNjQ0NTMxIEMgMzcuMzY3MTg4IDM2LjkxMDE1NiAzNy43Njk1MzEgMzYuMTk1MzEyIDM4LjIwMzEyNSAzNS41IEMgMzguNjM2NzE5IDM0LjgwNDY4OCAzOS4xMDE1NjIgMzQuMTMyODEyIDM5LjU5NzY1NiAzMy40ODA0NjkgQyA0MC4wOTc2NTYgMzIuODI4MTI1IDQwLjYyNSAzMi4yMDMxMjUgNDEuMTc5Njg4IDMxLjYwMTU2MiBDIDQxLjczODI4MSAzMSA0Mi4zMjAzMTIgMzAuNDI1NzgxIDQyLjkzMzU5NCAyOS44Nzg5MDYgQyA0My41NDI5NjkgMjkuMzM1OTM4IDQ0LjE3OTY4OCAyOC44MjAzMTIgNDQuODM5ODQ0IDI4LjMzMjAzMSBDIDQ1LjUgMjcuODQ3NjU2IDQ2LjE4MzU5NCAyNy4zOTQ1MzEgNDYuODg2NzE5IDI2Ljk3MjY1NiBDIDQ3LjU4OTg0NCAyNi41NTQ2ODggNDguMzA4NTk0IDI2LjE2Nzk2OSA0OS4wNTA3ODEgMjUuODEyNSBDIDQ5Ljc4OTA2MiAyNS40NjA5MzggNTAuNTQ2ODc1IDI1LjE0NDUzMSA1MS4zMTY0MDYgMjQuODYzMjgxIEMgNTIuMDg1OTM4IDI0LjU4MjAzMSA1Mi44NjcxODggMjQuMzM5ODQ0IDUzLjY2MDE1NiAyNC4xMzI4MTIgQyA1NC40NTMxMjUgMjMuOTI1NzgxIDU1LjI1MzkwNiAyMy43NTc4MTIgNTYuMDYyNSAyMy42MjUgQyA1Ni44NzEwOTQgMjMuNDk2MDk0IDU3LjY4NzUgMjMuNDAyMzQ0IDU4LjUwMzkwNiAyMy4zNTE1NjIgQyA1OS4yOTI5NjkgMjMuMzM5ODQ0IDYwLjA4MjAzMSAyMy4zNjcxODggNjAuODcxMDk0IDIzLjQyOTY4OCBDIDYxLjY2MDE1NiAyMy40OTIxODggNjIuNDQxNDA2IDIzLjU5Mzc1IDYzLjIxODc1IDIzLjczMDQ2OSBDIDY0IDIzLjg2MzI4MSA2NC43Njk1MzEgMjQuMDM1MTU2IDY1LjUzMTI1IDI0LjI0NjA5NCBDIDY2LjI5Mjk2OSAyNC40NTMxMjUgNjcuMDQ2ODc1IDI0LjY5NTMxMiA2Ny43ODUxNTYgMjQuOTcyNjU2IEMgNjguNTI3MzQ0IDI1LjI1IDY5LjI1IDI1LjU2MjUgNjkuOTYwOTM4IDI1LjkwNjI1IEMgNzAuNjcxODc1IDI2LjI1IDcxLjM2NzE4OCAyNi42Mjg5MDYgNzIuMDQyOTY5IDI3LjAzOTA2MiBDIDcyLjcxODc1IDI3LjQ0OTIxOSA3My4zNzUgMjcuODg2NzE5IDc0LjAwNzgxMiAyOC4zNTkzNzUgQyA3NC42NDQ1MzEgMjguODI4MTI1IDc1LjI1NzgxMiAyOS4zMjgxMjUgNzUuODQzNzUgMjkuODU1NDY5IEMgNzYuNDMzNTk0IDMwLjM4MjgxMiA3Ni45OTYwOTQgMzAuOTM3NSA3Ny41MzUxNTYgMzEuNTE1NjI1IEMgNzguMDcwMzEyIDMyLjA5NzY1NiA3OC41ODIwMzEgMzIuNjk5MjE5IDc5LjA2MjUgMzMuMzI4MTI1IEMgNzkuNTQyOTY5IDMzLjk1MzEyNSA3OS45OTIxODggMzQuNjAxNTYyIDgwLjQxNDA2MiAzNS4yNjk1MzEgQyA4MC44MzU5MzggMzUuOTM3NSA4MS4yMjI2NTYgMzYuNjI1IDgxLjU4MjAzMSAzNy4zMzIwMzEgQyA4MS45Mzc1IDM4LjAzNTE1NiA4Mi4yNjE3MTkgMzguNzU3ODEyIDgyLjU1MDc4MSAzOS40OTIxODggQyA4Mi44Mzk4NDQgNDAuMjI2NTYyIDgzLjA5NzY1NiA0MC45NzI2NTYgODMuMzE2NDA2IDQxLjczNDM3NSBDIDgzLjUzOTA2MiA0Mi40OTIxODggODMuNzIyNjU2IDQzLjI2MTcxOSA4My44NzEwOTQgNDQuMDM1MTU2IEMgODQuMDE5NTMxIDQ0LjgxMjUgODQuMTMyODEyIDQ1LjU5Mzc1IDg0LjIxMDkzOCA0Ni4zNzg5MDYgQyA4NC4yODUxNTYgNDcuMTY3OTY5IDg0LjMyODEyNSA0Ny45NTcwMzEgODQuMzI4MTI1IDQ4Ljc0NjA5NCBDIDg0LjMzMjAzMSA0OS41MzUxNTYgODQuMzAwNzgxIDUwLjMyNDIxOSA4NC4yMzA0NjkgNTEuMTEzMjgxIEMgODQuMTYwMTU2IDUxLjg5ODQzOCA4NC4wNTA3ODEgNTIuNjgzNTk0IDgzLjkxMDE1NiA1My40NjA5MzggQyA4My43NjU2MjUgNTQuMjM4MjgxIDgzLjU4NTkzOCA1NS4wMDM5MDYgODMuMzc1IDU1Ljc2NTYyNSBDIDgzLjE2MDE1NiA1Ni41MjczNDQgODIuOTEwMTU2IDU3LjI3NzM0NCA4Mi42MjUgNTguMDExNzE5IEMgODIuMzM5ODQ0IDU4Ljc1IDgyLjAyMzQzOCA1OS40NzI2NTYgODEuNjcxODc1IDYwLjE4MzU5NCBDIDgxLjMyMDMxMiA2MC44OTA2MjUgODAuOTM3NSA2MS41NzgxMjUgODAuNTIzNDM4IDYyLjI1MzkwNiBDIDgwLjEwNTQ2OSA2Mi45MjU3ODEgNzkuNjYwMTU2IDYzLjU3ODEyNSA3OS4xODM1OTQgNjQuMjA3MDMxIEMgNzguNzEwOTM4IDY0LjgzOTg0NCA3OC4yMDMxMjUgNjUuNDQ1MzEyIDc3LjY3MTg3NSA2Ni4wMzEyNSBDIDc3LjE0MDYyNSA2Ni42MTMyODEgNzYuNTgyMDMxIDY3LjE3MTg3NSA3NS45OTYwOTQgNjcuNzAzMTI1IEMgNzUuNDE0MDYyIDY4LjIzNDM3NSA3NC44MDQ2ODggNjguNzM4MjgxIDc0LjE3MTg3NSA2OS4yMTQ4NDQgQyA3My41NDI5NjkgNjkuNjkxNDA2IDcyLjg5MDYyNSA3MC4xMzY3MTkgNzIuMjE4NzUgNzAuNTUwNzgxIEMgNzEuNTQyOTY5IDcwLjk2NDg0NCA3MC44NTU0NjkgNzEuMzQ3NjU2IDcwLjE0NDUzMSA3MS42OTkyMTkgQyA2OS40Mzc1IDcyLjA1MDc4MSA2OC43MTQ4NDQgNzIuMzY3MTg4IDY3Ljk3NjU2MiA3Mi42NTIzNDQgQyA2Ny4yMzgyODEgNzIuOTMzNTk0IDY2LjQ4ODI4MSA3My4xODM1OTQgNjUuNzMwNDY5IDczLjM5ODQzOCBDIDY0Ljk2ODc1IDczLjYwOTM3NSA2NC4xOTkyMTkgNzMuNzg5MDYyIDYzLjQyMTg3NSA3My45Mjk2ODggWiBNIDYzLjQyMTg3NSA3My45Mjk2ODggIiBmaWxsLW9wYWNpdHk9IjEiIGZpbGwtcnVsZT0ibm9uemVybyIvPjwvZz48L3N2Zz4=';
	}
}
