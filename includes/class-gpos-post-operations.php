<?php
/**
 * GurmePOS için gerekli WordPress post tiplerini (post_type) kayıt eden sınıfını barındıran dosya.
 *
 * @package GurmeHub
 */

/**
 * Eklenti için gerekli post tiplerini kayıt eder.
 */
class GPOS_Post_Operations {

	/**
	 * Eklenti prefix
	 *
	 * @var string $prefix
	 */
	protected $prefix = GPOS_PREFIX;

	/**
	 * Eklenti için gerekli post tiplerini barındırır.
	 *
	 * @var array $post_types
	 */
	protected $post_types;

	/**
	 * Eklenti için gerekli post taxonomilerini barındırır.
	 *
	 * @var array $post_taxonomies
	 */
	protected $post_taxonomies;

	/**
	 * Eklenti için gerekli post durumlarını barındırır.
	 *
	 * @var array $post_statuses
	 */
	protected $post_statuses;


	/**
	 * Eklenti için gerekli post tiplerini ve taxonomilerini kayıt eder;
	 * Init kancası dışında kullanılması tavsiye edilmez.
	 *
	 * @return void
	 */
	public function register() {

		foreach ( $this->get_post_types() as $type => $args ) {
			register_post_type( $type, $args );
		}

		foreach ( $this->get_post_taxonomies() as $taxonomy => $args ) {
			$registered_taxonomy = register_taxonomy( $taxonomy, $args['object_type'], $args['args'] );

			if ( array_key_exists( 'default_terms', $args['args'] ) && false === empty( $args['args']['default_terms'] ) ) {
				foreach ( $args['args']['default_terms']  as $slug => $term ) {
					if ( ! term_exists( $slug, $registered_taxonomy->name ) ) {
						wp_insert_term( $term, $registered_taxonomy->name, array( 'slug' => $slug ) );
					}
				}
			}
		}

		foreach ( $this->get_post_statuses() as $status => $args ) {

			$default_args = array(
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'show_in_metabox_dropdown'  => true,
				'show_in_inline_dropdown'   => true,
			);

			register_post_status( $status, wp_parse_args( $args, $default_args ) );
		}
	}


	/**
	 * Pro ve 3.parti uygulamalarımız için gerekli post tiplerini kanca aracılığı ile bir araya getirir.
	 *
	 * @return array
	 */
	public function get_post_types() {

		$this->post_types = array(
			"{$this->prefix}_account"     => array(
				'labels' => array(
					'name' => 'GPOS Account',
				),
				'public' => false,
			),
			"{$this->prefix}_transaction" => array(
				'labels'              => array(
					'name'               => _x( 'Transactions', 'Post type general name', 'gurmepos' ),
					'singular_name'      => _x( 'Transaction', 'Post type singular name', 'gurmepos' ),
					'menu_name'          => _x( 'Transactions', 'Admin Menu text', 'gurmepos' ),
					'name_admin_bar'     => _x( 'Transaction', 'Add New on Toolbar', 'gurmepos' ),
					'add_new'            => __( 'Add New', 'gurmepos' ),
					'add_new_item'       => __( 'Add New Transaction', 'gurmepos' ),
					'new_item'           => __( 'New Transaction', 'gurmepos' ),
					'edit_item'          => __( 'Edit Transaction', 'gurmepos' ),
					'view_item'          => __( 'View Transaction', 'gurmepos' ),
					'all_items'          => __( 'Transactions', 'gurmepos' ),
					'search_items'       => __( 'Search Transactions', 'gurmepos' ),
					'parent_item_colon'  => __( 'Parent Transactions:', 'gurmepos' ),
					'not_found'          => __( 'No Transactions found.', 'gurmepos' ),
					'not_found_in_trash' => __( 'No Transactions found in Trash.', 'gurmepos' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => 'gurmepos',
				'show_in_admin_bar'   => false,
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'show_in_rest'        => false,
				'query_var'           => false,
				'has_archive'         => false,
				'taxonomies'          => array( 'gpos_transaction_process_type' ),
			),
			"{$this->prefix}_t_line"      => array(
				'labels' => array(
					'name' => 'GPOS Transaction Line',
				),
				'public' => false,
			),
		);

		$hooked_post_types = apply_filters( 'gpos_post_types', array() );
		return has_filter( 'gpos_post_types' ) ? array_merge( $this->post_types, $hooked_post_types ) : $this->post_types;
	}

	/**
	 * Pro ve 3.parti uygulamalarımız için gerekli post taksonomilerini kanca aracılığı ile bir araya getirir.
	 *
	 * @return array
	 */
	public function get_post_taxonomies() {

		$this->post_taxonomies = array(
			"{$this->prefix}_transaction_process_type" => array(
				'object_type' => "{$this->prefix}_transaction",
				'args'        => array(
					'label'             => __( 'Process Type', 'gurmepos' ),
					'public'            => false,
					'rewrite'           => false,
					'hierarchical'      => true,
					'parent_item'       => false,
					'parent_item_colon' => false,
					'default_terms'     => array(
						GPOS_Transaction_Utils::PAYMENT => __( 'Payment', 'gurmepos' ),
						GPOS_Transaction_Utils::CANCEL  => __( 'Cancel', 'gurmepos' ),
						GPOS_Transaction_Utils::REFUND  => __( 'Refund', 'gurmepos' ),
					),
				),
			),
		);

		$hooked_post_taxonomies = apply_filters( 'gpos_post_taxonomies', array() );
		return has_filter( 'gpos_post_taxonomies' ) ? array_merge( $this->post_taxonomies, $hooked_post_taxonomies ) : $this->post_taxonomies;
	}
	/**
	 * Pro ve 3.parti uygulamalarımız için gerekli post durumlarını kanca aracılığı ile bir araya getirir.
	 *
	 * @return array
	 */
	public function get_post_statuses() {

		$this->post_statuses = array(
			/**
			 * GPOS_Transaction için post durumları.
			 *
			 * gpos_started
			 * gpos_redirected
			 * gpos_completed
			 * gpos_pending
			 * gpos_failed
			 */
			GPOS_Transaction_Utils::STARTED               => array(
				'label'       => _x( 'Started ', 'post status label', 'gurmepos' ),
				'public'      => true,
				// translators: %s => Started durumundaki post adedi.
				'label_count' => _n_noop( 'Started <span class="count">(%s)</span>', 'Started <span class="count">(%s)</span>', 'gurmepos' ),
				'post_type'   => array( "{$this->prefix}_transaction" ),
			),
			GPOS_Transaction_Utils::REDIRECTED            => array(
				'label'       => _x( 'Redirected To Payment Page ', 'post status label', 'gurmepos' ),
				'public'      => true,
				// translators: %s => Redirected durumundaki post adedi.
				'label_count' => _n_noop( 'Redirected To Payment Page <span class="count">(%s)</span>', 'Redirected To Payment Page <span class="count">(%s)</span>', 'gurmepos' ),
				'post_type'   => array( "{$this->prefix}_transaction" ),
			),
			GPOS_Transaction_Utils::COMPLETED             => array(
				'label'       => _x( 'Completed ', 'post status label', 'gurmepos' ),
				'public'      => true,
				// translators: %s => Completed durumundaki post adedi.
				'label_count' => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'gurmepos' ),
				'post_type'   => array( "{$this->prefix}_transaction" ),
			),
			GPOS_Transaction_Utils::PENDING               => array(
				'label'       => _x( 'Pending Payment ', 'post status label', 'gurmepos' ),
				'public'      => true,
				// translators: %s => Pending Payment durumundaki post adedi.
				'label_count' => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'gurmepos' ),
				'post_type'   => array( "{$this->prefix}_transaction" ),
			),
			GPOS_Transaction_Utils::FAILED                => array(
				'label'       => _x( 'Failed ', 'post status label', 'gurmepos' ),
				'public'      => true,
				// translators: %s => Failed durumundaki post adedi.
				'label_count' => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'gurmepos' ),
				'post_type'   => array( "{$this->prefix}_transaction" ),
			),
			/**
			 * GPOS_Transaction_Line için post durumları.
			 *
			 * gpos_line_n_refunded
			 * gpos_line_refunded
			 * gpos_line_p_refunded
			 */
			GPOS_Transaction_Utils::LINE_NOT_REFUNDED     => array(
				'label'     => _x( 'Refunded ', 'post status label', 'gurmepos' ),
				'post_type' => array( "{$this->prefix}_t_line" ),
			),
			GPOS_Transaction_Utils::LINE_REFUNDED         => array(
				'label'     => _x( 'Not Refunded ', 'post status label', 'gurmepos' ),
				'post_type' => array( "{$this->prefix}_t_line" ),
			),
			GPOS_Transaction_Utils::LINE_PARTIAL_REFUNDED => array(
				'label'     => _x( 'Partial Refunded ', 'post status label', 'gurmepos' ),
				'post_type' => array( "{$this->prefix}_t_line" ),
			),
		);

		$hooked_post_statuses = apply_filters( 'gpos_post_statuses', array() );
		return has_filter( 'gpos_post_statuses' ) ? array_merge( $this->post_statuses, $hooked_post_statuses ) : $this->post_statuses;
	}
}
