<?php
/**
 * Post Meta Box Bulk Edit
 *
 * @package     Astra_Bulk_Edit
 * @copyright   Copyright (c) 2017, Astra
 * @link        http://wpastra.com/
 * @since       1.0.0
 */

/**
 * Meta Boxes setup
 */
if ( ! class_exists( 'Astra_Blk_Meta_Boxes_Bulk_Edit' ) ) {

	/**
	 * Meta Boxes setup
	 */
	class Astra_Blk_Meta_Boxes_Bulk_Edit {

		/**
		 * Instance
		 *
		 * @var $instance
		 */
		private static $instance;

		/**
		 * Meta Option
		 *
		 * @var $meta_option
		 */
		private static $meta_option;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			add_action( 'admin_init', array( $this, 'setup_admin_init' ), 999 );

			// output form elements for quickedit interface.
			add_action( 'bulk_edit_custom_box', array( $this, 'display_quick_edit_custom' ), 10, 2 );
			add_action( 'quick_edit_custom_box', array( $this, 'display_quick_edit_custom' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts_and_styles' ) );

			add_action( 'save_post', array( $this, 'save_meta_box' ) );

			add_action( 'wp_ajax_astra_save_post_bulk_edit', array( $this, 'save_post_bulk_edit' ) );

		}

		/**
		 *  Admin Init actions
		 */
		public function setup_admin_init() {

			$this->setup_bulk_options();

			// Get all public posts.
			$post_types = get_post_types(
				array(
					'public' => true,
				)
			);

			// Enable for all posts.
			foreach ( $post_types as $type ) {

				if ( 'attachment' !== $type && 'fl-theme-layout' !== $type ) {
					// add custom column.
					add_action( 'manage_' . $type . '_posts_columns', array( $this, 'add_custom_admin_column' ), 10, 1 );
					// populate column.
					add_action( 'manage_' . $type . '_posts_custom_column', array( $this, 'manage_custom_admin_columns' ), 10, 2 );
				}
			}
		}

		/**
		 *  Init bulk options
		 */
		public function setup_bulk_options() {

			/**
			 * Set metabox options
			 *
			 * @see http://php.net/manual/en/filter.filters.sanitize.php
			 */
			self::$meta_option = apply_filters(
				'astra_meta_box_bulk_edit_options',
				array(
					'ast-above-header-display'      => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'ast-main-header-display'       => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'ast-below-header-display'      => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'ast-hfb-above-header-display'  => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'ast-hfb-below-header-display'  => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'ast-hfb-mobile-header-display' => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'ast-featured-img'              => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'site-post-title'               => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'site-sidebar-layout'           => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'site-content-layout'           => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'ast-site-content-layout'       => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'site-content-style'            => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'site-sidebar-style'            => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'footer-sml-layout'             => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'footer-adv-display'            => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'theme-transparent-header-meta' => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'adv-header-id-meta'            => array(
						'sanitize' => 'FILTER_DEFAULT',
					),
					'stick-header-meta'             => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'header-above-stick-meta'       => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'header-main-stick-meta'        => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'header-below-stick-meta'       => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'ast-breadcrumbs-content'       => array(
						'default'  => 'no-change',
						'sanitize' => 'FILTER_DEFAULT',
					),
				)
			);
		}

		/**
		 * Get metabox options
		 */
		public static function get_meta_option() {
			return self::$meta_option;
		}

		/**
		 * Metabox Save
		 *
		 * @param  number $post_id Post ID.
		 * @return void
		 */
		public function save_meta_box( $post_id ) {

			// Checks save status.
			$is_autosave     = wp_is_post_autosave( $post_id );
			$is_revision     = wp_is_post_revision( $post_id );
			$is_valid_nonce  = ( isset( $_POST['astra_settings_bulk_meta_box'] ) && wp_verify_nonce( $_POST['astra_settings_bulk_meta_box'], basename( __FILE__ ) ) ) ? true : false;
			$user_capability = ( current_user_can( 'edit_post', $post_id ) );

			// Exits script depending on save status.
			if ( $is_autosave || $is_revision || ! $is_valid_nonce || ! $user_capability ) {
				return;
			}

			/**
			 * Get meta options
			 */
			$post_meta = self::get_meta_option();

			foreach ( $post_meta as $key => $data ) {

				// Sanitize values.
				$sanitize_filter = ( isset( $data['sanitize'] ) ) ? $data['sanitize'] : 'FILTER_DEFAULT';

				switch ( $sanitize_filter ) {

					case 'FILTER_SANITIZE_STRING':
							$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );
						break;

					case 'FILTER_SANITIZE_URL':
							$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_URL );
						break;

					case 'FILTER_SANITIZE_NUMBER_INT':
							$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT );
						break;

					default:
							$meta_value = filter_input( INPUT_POST, $key, FILTER_DEFAULT );
						break;
				}

				// Store values.
				if ( 'no-change' !== $meta_value ) {
					update_post_meta( $post_id, $key, $meta_value );
				}
			}

		}

		/**
		 * Save bulk edit options.
		 */
		public function save_post_bulk_edit() {

			if ( ! check_ajax_referer( 'astra-blk-nonce', 'astra_nonce' ) ) {
				wp_send_json_error( esc_html__( 'Action failed. Invalid Security Nonce.', 'astra-bulk-edit' ) );
			}

			$post_ids = ! empty( $_POST['post'] ) ? $_POST['post'] : array();
			if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {

				/**
				 * Get meta options
				 */
				$post_meta = self::get_meta_option();

				foreach ( $post_ids as $post_id ) {
					if ( current_user_can( 'edit_post', $post_id ) ) {
						foreach ( $post_meta as $key => $data ) {

							// Sanitize values.
							$sanitize_filter = ( isset( $data['sanitize'] ) ) ? $data['sanitize'] : 'FILTER_DEFAULT';

							switch ( $sanitize_filter ) {

								case 'FILTER_SANITIZE_STRING':
										$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );
									break;

								case 'FILTER_SANITIZE_URL':
										$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_URL );
									break;

								case 'FILTER_SANITIZE_NUMBER_INT':
										$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT );
									break;

								default:
										$meta_value = filter_input( INPUT_POST, $key, FILTER_DEFAULT );
									break;
							}

							// Store values.
							if ( 'no-change' !== $meta_value ) {
								update_post_meta( $post_id, $key, $meta_value );
							}
						}
					}
				}
			}
			wp_send_json_success();
		}

		/**
		 * Quick edit custom column to hold our data
		 *
		 * @param  number $columns Columns.
		 * @return array Column array.
		 */
		public function add_custom_admin_column( $columns ) {
			$new_columns = array();
			$theme_name  = apply_filters( 'astra_page_title', __( 'Astra', 'astra-bulk-edit' ) );

			$new_columns['astra-settings'] = esc_html( $theme_name ) . ' Settings';

			return array_merge( $columns, $new_columns );
		}

		/**
		 * Customize the data for our custom column,
		 * It's here we pull in metadata info for each post.
		 * These will be referred to in our JavaScript file for pre-populating our quick-edit screen
		 *
		 * @param  string $column_name Column name.
		 * @param  number $post_id Post ID.
		 * @return void
		 */
		public function manage_custom_admin_columns( $column_name, $post_id ) {

			if ( 'astra-settings' == $column_name ) {

				$html = '';

				$stored = get_post_meta( $post_id );
				$meta   = self::get_meta_option();

				// Set stored and override defaults.
				foreach ( $stored as $key => $value ) {
					if ( array_key_exists( $key, $meta ) ) {
						$meta[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? $stored[ $key ][0] : '';
					}
				}

				foreach ( $meta as $key => $value ) {

					$default_value = '';

					$html .= '<div class="astra-bulk-edit-field-' . esc_attr( $post_id ) . '" data-name="' . esc_attr( $key ) . '"  id="' . esc_attr( $key . '-' . $post_id ) . '">';

					if ( isset( $meta[ $key ]['default'] ) ) {

						// Apply migrations for old layout options & transition to revamped layout options, if set.
						if ( defined( 'ASTRA_THEME_VERSION' ) && version_compare( ASTRA_THEME_VERSION, '4.2.0', '>=' ) ) {
							if ( 'site-content-layout' === $key && isset( $meta[ $key ]['default'] ) && ! empty( $meta[ $key ]['default'] ) ) {
								$meta = self::migrate_layouts( $meta[ $key ]['default'], $meta );
							}
						}

						$default_value = $meta[ $key ]['default'];
					}

					$html .= $default_value;
					$html .= '</div>';
				}

				echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

		}

		/**
		 * Display our custom content on the quick-edit interface,
		 * no values can be pre-populated (all done in JavaScript)
		 *
		 * @param  string $column Column name.
		 * @param  string $screen Screen.
		 * @return void
		 */
		public function display_quick_edit_custom( $column, $screen ) {

			$html = '';
			wp_nonce_field( basename( __FILE__ ), 'astra_settings_bulk_meta_box' );
			$theme_name        = apply_filters( 'astra_page_title', __( 'Astra', 'astra-bulk-edit' ) );
			$is_builder_active = is_callable( 'Astra_Builder_Helper::is_header_footer_builder_active' ) ? Astra_Builder_Helper::is_header_footer_builder_active() : false;
			if ( 'astra-settings' == $column ) { ?>
				<fieldset class="astra-bulk-settings inline-edit-col ">
					<div class="inline-edit-col wp-clearfix">
						<h4 class="title"><?php esc_html_e( $theme_name . ' Settings', 'astra-bulk-edit' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?></h4>

						<div class="ast-float-left inline-edit-col-left wp-clearfix">

							<?php if ( defined( 'ASTRA_THEME_VERSION' ) && version_compare( ASTRA_THEME_VERSION, '4.2.0', '<' ) ) { ?>

								<label class="inline-edit" for="site-sidebar-layout">
									<span class="title"><?php esc_html_e( 'Sidebar', 'astra-bulk-edit' ); ?></span>
									<select name="site-sidebar-layout" id="site-sidebar-layout">
										<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
										<option value="default"><?php esc_html_e( 'Customizer Setting', 'astra-bulk-edit' ); ?></option>
										<option value="left-sidebar"><?php esc_html_e( 'Left Sidebar', 'astra-bulk-edit' ); ?></option>
										<option value="right-sidebar"><?php esc_html_e( 'Right Sidebar', 'astra-bulk-edit' ); ?></option>
										<option value="no-sidebar"><?php esc_html_e( 'No Sidebar', 'astra-bulk-edit' ); ?></option>
									</select>
								</label>
								<!-- Legacy Layout Options -->
								<label class="inline-edit" for="site-content-layout">
									<span class="title"><?php esc_html_e( 'Content Layout', 'astra-bulk-edit' ); ?></span>
									<select name="site-content-layout" id="site-content-layout">
										<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
										<option value="default"><?php esc_html_e( 'Customizer Setting', 'astra-bulk-edit' ); ?></option>
										<option value="boxed-container"><?php esc_html_e( 'Boxed', 'astra-bulk-edit' ); ?></option>
										<option value="content-boxed-container"><?php esc_html_e( 'Content Boxed', 'astra-bulk-edit' ); ?></option>
										<option value="plain-container"><?php esc_html_e( 'Full Width / Contained', 'astra-bulk-edit' ); ?></option>
										<option value="page-builder"><?php esc_html_e( 'Full Width / Stretched', 'astra-bulk-edit' ); ?></option>
									</select>
								</label>

							<?php } else { ?>

								<!-- Revamped Layout Options -->
								<label class="inline-edit" for="ast-site-content-layout">
									<span class="title"><?php esc_html_e( 'Container Layout', 'astra-bulk-edit' ); ?></span>
									<select name="ast-site-content-layout" id="ast-site-content-layout">
										<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
										<option value="default"><?php esc_html_e( 'Customizer Setting', 'astra-bulk-edit' ); ?></option>
										<option value="normal-width-container"><?php esc_html_e( 'Normal', 'astra-bulk-edit' ); ?></option>
										<option value="narrow-width-container"><?php esc_html_e( 'Narrow', 'astra-bulk-edit' ); ?></option>
										<option value="full-width-container"><?php esc_html_e( 'Full Width', 'astra-bulk-edit' ); ?></option>
									</select>
								</label>

								<label class="inline-edit" for="site-content-style">
									<span class="title"><?php esc_html_e( 'Container Style', 'astra-bulk-edit' ); ?></span>
									<select name="site-content-style" id="site-content-style">
										<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
										<option value="default"><?php esc_html_e( 'Customizer Setting', 'astra-bulk-edit' ); ?></option>
										<option value="unboxed"><?php esc_html_e( 'Unboxed', 'astra-bulk-edit' ); ?></option>
										<option value="boxed"><?php esc_html_e( 'Boxed', 'astra-bulk-edit' ); ?></option>
									</select>
								</label>

								<label class="inline-edit" for="site-sidebar-layout">
									<span class="title"><?php esc_html_e( 'Sidebar Layout', 'astra-bulk-edit' ); ?></span>
									<select name="site-sidebar-layout" id="site-sidebar-layout">
										<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
										<option value="default"><?php esc_html_e( 'Customizer Setting', 'astra-bulk-edit' ); ?></option>
										<option value="left-sidebar"><?php esc_html_e( 'Left Sidebar', 'astra-bulk-edit' ); ?></option>
										<option value="right-sidebar"><?php esc_html_e( 'Right Sidebar', 'astra-bulk-edit' ); ?></option>
										<option value="no-sidebar"><?php esc_html_e( 'No Sidebar', 'astra-bulk-edit' ); ?></option>
									</select>
								</label>

								<label class="inline-edit" for="site-sidebar-style">
									<span class="title"><?php esc_html_e( 'Sidebar Style', 'astra-bulk-edit' ); ?></span>
									<select name="site-sidebar-style" id="site-sidebar-style">
										<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
										<option value="default"><?php esc_html_e( 'Customizer Setting', 'astra-bulk-edit' ); ?></option>
										<option value="unboxed"><?php esc_html_e( 'Unboxed', 'astra-bulk-edit' ); ?></option>
										<option value="boxed"><?php esc_html_e( 'Boxed', 'astra-bulk-edit' ); ?></option>
									</select>
								</label>

							<?php } ?>

							<?php do_action( 'astra_meta_bulk_edit_left_bottom' ); ?>
						</div>

						<div class="ast-float-left inline-edit-col-left wp-clearfix" id="center-col">
							<label class="inline-edit" for="ast-main-header-display">
								<span class="title"><?php esc_html_e( 'Primary Header', 'astra-bulk-edit' ); ?></span>
								<select name="ast-main-header-display" id="ast-main-header-display">
									<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
									<option value="enabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
									<option value="disabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
								</select>
							</label>

							<?php

							// Above Header Layout.
							if ( $is_builder_active ) {
								$above_header_meta     = ( Astra_Builder_Helper::is_row_empty( 'above', 'header', 'desktop' ) || Astra_Builder_Helper::is_row_empty( 'above', 'header', 'mobile' ) ) ? true : false;
								$above_header_meta_key = 'ast-hfb-above-header-display';
							} elseif ( ! $is_builder_active && is_callable( 'Astra_Ext_Extension::is_active' ) && Astra_Ext_Extension::is_active( 'header-sections' ) ) {
								$above_header_layout   = astra_get_option( 'above-header-layout' );
								$above_header_meta     = 'disabled' != $above_header_layout ? true : false;
								$above_header_meta_key = 'ast-above-header-display';
							} else {
								$above_header_meta     = false;
								$above_header_meta_key = 'ast-above-header-display';
							}
							if ( $above_header_meta ) {
								?>
							<label class="inline-edit" for="<?php echo esc_attr( $above_header_meta_key ); ?>">
								<span class="title"><?php esc_html_e( 'Above Header', 'astra-bulk-edit' ); ?></span>
								<select name="<?php echo esc_attr( $above_header_meta_key ); ?>" id="<?php echo esc_attr( $above_header_meta_key ); ?>">
									<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
									<option value="enabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
									<option value="disabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
								</select>
							</label>
							<?php } ?>
								<?php
								// Below Header Layout.
								if ( $is_builder_active ) {
									$below_header_meta     = ( Astra_Builder_Helper::is_row_empty( 'below', 'header', 'desktop' ) || Astra_Builder_Helper::is_row_empty( 'below', 'header', 'mobile' ) ) ? true : false;
									$below_header_meta_key = 'ast-hfb-below-header-display';
								} elseif ( ! $is_builder_active && is_callable( 'Astra_Ext_Extension::is_active' ) && Astra_Ext_Extension::is_active( 'header-sections' ) ) {
									$below_header_layout   = astra_get_option( 'below-header-layout' );
									$below_header_meta     = 'disabled' != $below_header_layout ? true : false;
									$below_header_meta_key = 'ast-below-header-display';
								} else {
									$below_header_meta     = false;
									$below_header_meta_key = 'ast-below-header-display';
								}
								if ( $below_header_meta ) {
									?>
							<label class="inline-edit" for="<?php echo esc_attr( $below_header_meta_key ); ?>">
								<span class="title"><?php esc_html_e( 'Below Header', 'astra-bulk-edit' ); ?></span>
								<select name="<?php echo esc_attr( $below_header_meta_key ); ?>" id="<?php echo esc_attr( $below_header_meta_key ); ?>">
									<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
									<option value="enabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
									<option value="disabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
								</select>
							</label>
									<?php
								}
								if ( $is_builder_active && ( Astra_Builder_Helper::is_row_empty( 'primary', 'header', 'mobile' ) || Astra_Builder_Helper::is_row_empty( 'above', 'header', 'mobile' ) || Astra_Builder_Helper::is_row_empty( 'below', 'header', 'mobile' ) ) ) {
									$mobile_header_meta_key = 'ast-hfb-mobile-header-display';
									?>
										<label class="inline-edit" for="<?php echo esc_attr( $mobile_header_meta_key ); ?>">
											<span class="title"><?php esc_html_e( 'Mobile Header', 'astra-bulk-edit' ); ?></span>
											<select name="<?php echo esc_attr( $mobile_header_meta_key ); ?>" id="<?php echo esc_attr( $mobile_header_meta_key ); ?>">
												<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
												<option value="enabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
												<option value="disabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
											</select>
										</label>
									<?php
								}
								?>

							<?php
							$ast_theme_transparent_header = astra_get_option( 'theme-transparent-header-meta' );
							if ( 'disabled' != $ast_theme_transparent_header ) {
								?>
								<label class="inline-edit" for="theme-transparent-header-meta">
									<span class="title"><?php esc_html_e( 'Transparent Header', 'astra-bulk-edit' ); ?></span>
									<select name="theme-transparent-header-meta" id="theme-transparent-header-meta">
										<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
										<option value="default"> <?php esc_html_e( 'Customizer Setting', 'astra-bulk-edit' ); ?> </option>
										<option value="enabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
										<option value="disabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
									</select>
								</label>
							<?php } ?>

							<?php if ( is_callable( 'Astra_Ext_Extension::is_active' ) ) : ?>
								<?php if ( Astra_Ext_Extension::is_active( 'advanced-headers' ) ) : ?>
									<?php
									$header_options  = Astra_Target_Rules_Fields::get_post_selection( 'astra_adv_header' );
									$show_meta_field = ! astra_check_is_bb_themer_layout();
									if ( empty( $header_options ) ) {
										$header_options = array(
											'' => __( 'No Page Headers Found', 'astra-bulk-edit' ),
										);
									}
									?>
									<?php if ( $show_meta_field ) { ?>
									<label class="inline-edit" for="adv-header-id-meta">
										<span class="title"><?php esc_html_e( 'Page Header', 'astra-bulk-edit' ); ?></span>
										<select name="adv-header-id-meta" id="adv-header-id-meta">
											<?php foreach ( $header_options as $key => $value ) { ?>
												<option value="<?php echo esc_attr( $key ); ?>"> <?php echo esc_html( $value ); ?></option>
											<?php } ?>
										</select>
									</label>
									<?php } ?>
								<?php endif; ?>

								<?php if ( Astra_Ext_Extension::is_active( 'sticky-header' ) ) : ?>
									<label class="inline-edit stick-header-meta-visibility" for="stick-header-meta">
										<span class="title"><?php esc_html_e( 'Sticky Header', 'astra-bulk-edit' ); ?></span>
										<select name="stick-header-meta" id="stick-header-meta">
											<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
											<option value="default"><?php esc_html_e( 'Customizer Setting', 'astra-bulk-edit' ); ?> </option>
											<option value="enabled"><?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
											<option value="disabled"><?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
										</select>
									</label>

									<?php
									// Above Header Layout.
									if ( $is_builder_active ) {
										$above_header_meta = ( Astra_Builder_Helper::is_row_empty( 'above', 'header', 'desktop' ) || Astra_Builder_Helper::is_row_empty( 'above', 'header', 'mobile' ) ) ? true : false;
									} elseif ( ! $is_builder_active && Astra_Ext_Extension::is_active( 'header-sections' ) ) {
										$above_header_layout = astra_get_option( 'above-header-layout' );
										$above_header_meta   = 'disabled' != $above_header_layout ? true : false;
									} else {
										$above_header_meta = false;
									}
									if ( $above_header_meta ) {
										?>
										<label class="inline-edit sticky-header-above-stick-meta" for="header-above-stick-meta">
											<span class="title"><?php esc_html_e( 'Stick Above Header', 'astra-bulk-edit' ); ?></span>
											<select name="header-above-stick-meta" id="header-above-stick-meta">
												<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
												<option value="disabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
												<option value="enabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
											</select>
										</label>
											<?php
									}

									// Primary Header Layout.
									if ( $is_builder_active ) {
										$primary_header_meta = ( Astra_Builder_Helper::is_row_empty( 'primary', 'header', 'desktop' ) || Astra_Builder_Helper::is_row_empty( 'primary', 'header', 'mobile' ) ) ? true : false;
									} elseif ( ! $is_builder_active && Astra_Ext_Extension::is_active( 'header-sections' ) ) {
										$header_layouts      = astra_get_option( 'header-layouts' );
										$primary_header_meta = 'header-main-layout-5' != $header_layouts ? true : false;
									} else {
										$primary_header_meta = false;
									}
									if ( $primary_header_meta ) {
										?>
											<label class="inline-edit sticky-header-main-stick-meta" for="header-main-stick-meta">
												<span class="title"><?php esc_html_e( 'Stick Primary Header', 'astra-bulk-edit' ); ?></span>
												<select name="header-main-stick-meta" id="header-main-stick-meta">
													<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
													<option value="disabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
													<option value="enabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
												</select>
											</label>
										<?php
									}
									// Below Header Layout.
									if ( $is_builder_active ) {
										$below_header_meta = ( Astra_Builder_Helper::is_row_empty( 'below', 'header', 'desktop' ) || Astra_Builder_Helper::is_row_empty( 'below', 'header', 'mobile' ) ) ? true : false;
									} elseif ( ! $is_builder_active && Astra_Ext_Extension::is_active( 'header-sections' ) ) {
										$below_header_layout = astra_get_option( 'below-header-layout' );
										$below_header_meta   = 'disabled' != $below_header_layout ? true : false;
									} else {
										$below_header_meta = false;
									}
									if ( $below_header_meta ) {
										?>
											<label class="inline-edit sticky-header-below-stick-meta" for="header-below-stick-meta">
												<span class="title"><?php esc_html_e( 'Stick Below Header', 'astra-bulk-edit' ); ?></span>
												<select name="header-below-stick-meta" id="header-below-stick-meta">
													<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
													<option value="disabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
													<option value="enabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
												</select>
											</label>
										<?php
									}

									?>
									</div>
								<?php endif; ?>

							<?php endif; ?>
							<?php do_action( 'astra_meta_bulk_edit_center_bottom' ); ?>

						<div class="ast-float-left inline-edit-col-left wp-clearfix">
							<?php
							// Breadcrumbs.
							$ast_breadcrumbs_position = astra_get_option( 'breadcrumb-position', 'none' );
							if ( 'none' !== $ast_breadcrumbs_position ) {
								?>
								<label class="inline-edit" for="ast-breadcrumbs-content">
									<span class="title"><?php esc_html_e( 'Breadcrumbs', 'astra-bulk-edit' ); ?></span>
									<select name="ast-breadcrumbs-content" id="ast-breadcrumbs-content">
										<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
										<option value="enabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
										<option value="disabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
									</select>
								</label>
								<?php
							}
							?>
							<label class="inline-edit" for="site-post-title">
								<span class="title"><?php esc_html_e( 'Title Visibility', 'astra-bulk-edit' ); ?></span>
								<select name="site-post-title" id="site-post-title">
									<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
									<option value="enabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
									<option value="disabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
								</select>
							</label>

							<label class="inline-edit" for="ast-featured-img">
								<span class="title"><?php esc_html_e( 'Featured Image', 'astra-bulk-edit' ); ?></span>
								<select name="ast-featured-img" id="ast-featured-img">
									<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
									<option value="enabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
									<option value="disabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
								</select>
							</label>

							<?php
							$footer_adv_layout = astra_get_option( 'footer-adv' );
							if ( 'disabled' != $footer_adv_layout ) {
								?>
								<label class="inline-edit" for="footer-adv-display">
									<span class="title"><?php esc_html_e( 'Footer Widgets', 'astra-bulk-edit' ); ?></span>
									<select name="footer-adv-display" id="footer-adv-display">
										<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
										<option value="enabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
										<option value="disabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
									</select>
								</label>
							<?php } ?>

							<?php
							$footer_sml_layout = astra_get_option( 'footer-sml-layout' );
							if ( 'disabled' != $footer_sml_layout ) {
								?>
								<label class="inline-edit" for="footer-sml-layout">
									<span class="title"><?php esc_html_e( 'Footer Bar', 'astra-bulk-edit' ); ?></span>
									<select name="footer-sml-layout" id="footer-sml-layout">
										<option value="no-change" selected="selected"><?php esc_html_e( '— No Change —', 'astra-bulk-edit' ); ?></option>
										<option value="enabled"> <?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?> </option>
										<option value="disabled"> <?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?> </option>
									</select>
								</label>
								<?php
							}
							?>
						</div>

						</div>
					</div>
				</fieldset>
				<?php
			}
		}

		/**
		 *  Quick edit and bulk edit script function.
		 */
		public function enqueue_admin_scripts_and_styles() {
			wp_enqueue_style( 'astra-blk-admin', ASTRA_BLK_URI . 'assets/css/astra-admin.css', array(), ASTRA_BLK_VER );

			if ( ! current_user_can( 'edit_posts' ) ) {
				return;
			}

			$post_type = get_post_type();
			if ( 'product' !== $post_type && 'cartflows_flow' !== $post_type && 'cartflows_step' !== $post_type ) {
				wp_enqueue_script( 'astra-blk-admin', ASTRA_BLK_URI . 'assets/js/astra-admin.js', array( 'jquery', 'inline-edit-post' ), ASTRA_BLK_VER, true );
				wp_localize_script(
					'astra-blk-admin',
					'security',
					array(
						'nonce' => wp_create_nonce( 'astra-blk-nonce' ),
					)
				);
			}
		}

		/**
		 * Migrate existing layouts to revamped layout combinations.
		 *
		 * @param  string $old_meta_content_layout Old Meta Layout.
		 * @param  mixed  $meta Meta Options.
		 * @return mixed $meta Meta Options.
		 */
		public function migrate_layouts( $old_meta_content_layout, $meta ) {
			switch ( $old_meta_content_layout ) {
				case 'plain-container':
					$meta['ast-site-content-layout']['default'] = 'normal-width-container';
					$meta['site-content-style']['default']      = 'unboxed';
					$meta['site-sidebar-style']['default']      = 'unboxed';
					break;
				case 'boxed-container':
					$meta['ast-site-content-layout']['default'] = 'normal-width-container';
					$meta['site-content-style']['default']      = 'boxed';
					$meta['site-sidebar-style']['default']      = 'boxed';
					break;
				case 'content-boxed-container':
					$meta['ast-site-content-layout']['default'] = 'normal-width-container';
					$meta['site-content-style']['default']      = 'boxed';
					$meta['site-sidebar-style']['default']      = 'unboxed';
					break;
				case 'page-builder':
					$meta['ast-site-content-layout']['default'] = 'full-width-container';
					$meta['site-content-style']['default']      = 'unboxed';
					$meta['site-sidebar-style']['default']      = 'unboxed';
					break;
				case 'narrow-container':
					$meta['ast-site-content-layout']['default'] = 'narrow-width-container';
					$meta['site-content-style']['default']      = 'unboxed';
					$meta['site-sidebar-style']['default']      = 'unboxed';
					break;
				case 'no-change':
					$meta['ast-site-content-layout']['default'] = 'no-change';
					$meta['site-content-style']['default']      = 'no-change';
					$meta['site-sidebar-style']['default']      = 'no-change';
					break;
				default:
					$meta['ast-site-content-layout']['default'] = 'default';
					$meta['site-content-style']['default']      = 'default';
					$meta['site-sidebar-style']['default']      = 'default';
					break;
			}

			return $meta;
		}
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Astra_Blk_Meta_Boxes_Bulk_Edit::get_instance();
