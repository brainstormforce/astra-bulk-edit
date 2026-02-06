<?php
/**
 * Integration tests for admin column functionality.
 *
 * @package Astra_Bulk_Edit
 */

namespace Astra_Bulk_Edit\Tests\Integration;

use WP_UnitTestCase;

/**
 * Admin Columns Test class.
 *
 * Tests the admin column display functionality including
 * add_custom_admin_column() and manage_custom_admin_columns().
 *
 * @requires WordPress test environment
 */
class AdminColumnsTest extends WP_UnitTestCase {

	/**
	 * Instance of the bulk edit class.
	 *
	 * @var \Astra_Blk_Meta_Boxes_Bulk_Edit
	 */
	private $instance;

	/**
	 * Test post ID.
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * Set up test fixtures.
	 */
	public function set_up() {
		parent::set_up();

		// Create a test post.
		$this->post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Test Post',
				'post_status' => 'publish',
			)
		);

		// Get instance of the bulk edit class.
		$this->instance = \Astra_Blk_Meta_Boxes_Bulk_Edit::get_instance();

		// Set up admin user.
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
	}

	/**
	 * Tear down test fixtures.
	 */
	public function tear_down() {
		wp_delete_post( $this->post_id, true );
		parent::tear_down();
	}

	/**
	 * Test that add_custom_admin_column adds the Astra Settings column.
	 */
	public function test_adds_astra_settings_column() {
		$existing_columns = array(
			'cb'     => '<input type="checkbox" />',
			'title'  => 'Title',
			'author' => 'Author',
			'date'   => 'Date',
		);

		$result = $this->instance->add_custom_admin_column( $existing_columns );

		$this->assertArrayHasKey( 'astra-settings', $result );
	}

	/**
	 * Test that column header contains expected text.
	 */
	public function test_column_header_contains_settings_text() {
		$existing_columns = array( 'title' => 'Title' );
		$result           = $this->instance->add_custom_admin_column( $existing_columns );

		$this->assertStringContainsString( 'Settings', $result['astra-settings'] );
	}

	/**
	 * Test that existing columns are preserved.
	 */
	public function test_existing_columns_preserved() {
		$existing_columns = array(
			'cb'     => '<input type="checkbox" />',
			'title'  => 'Title',
			'author' => 'Author',
			'date'   => 'Date',
		);

		$result = $this->instance->add_custom_admin_column( $existing_columns );

		// Original columns should still exist.
		$this->assertArrayHasKey( 'cb', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'author', $result );
		$this->assertArrayHasKey( 'date', $result );
	}

	/**
	 * Test that column is added at the end.
	 */
	public function test_column_added_at_end() {
		$existing_columns = array(
			'cb'    => '<input type="checkbox" />',
			'title' => 'Title',
		);

		$result = $this->instance->add_custom_admin_column( $existing_columns );

		$keys = array_keys( $result );
		$this->assertEquals( 'astra-settings', end( $keys ) );
	}

	/**
	 * Test that astra_page_title filter can customize column header.
	 */
	public function test_page_title_filter_customizes_header() {
		add_filter(
			'astra_page_title',
			function () {
				return 'Custom Theme';
			}
		);

		$existing_columns = array( 'title' => 'Title' );
		$result           = $this->instance->add_custom_admin_column( $existing_columns );

		$this->assertStringContainsString( 'Custom Theme', $result['astra-settings'] );

		remove_all_filters( 'astra_page_title' );
	}

	/**
	 * Test column content is output for correct column.
	 */
	public function test_column_content_output_for_correct_column() {
		ob_start();
		$this->instance->manage_custom_admin_columns( 'astra-settings', $this->post_id );
		$output = ob_get_clean();

		// Should output something for astra-settings column.
		$this->assertNotEmpty( $output );
	}

	/**
	 * Test column content is empty for other columns.
	 */
	public function test_no_output_for_other_columns() {
		ob_start();
		$this->instance->manage_custom_admin_columns( 'title', $this->post_id );
		$output = ob_get_clean();

		// Should output nothing for other columns.
		$this->assertEmpty( $output );
	}

	/**
	 * Test column content includes data attributes.
	 */
	public function test_column_content_includes_data_attributes() {
		ob_start();
		$this->instance->manage_custom_admin_columns( 'astra-settings', $this->post_id );
		$output = ob_get_clean();

		// Should include data-name attributes for JavaScript population.
		$this->assertStringContainsString( 'data-name=', $output );
	}

	/**
	 * Test column content includes post-specific class.
	 */
	public function test_column_content_includes_post_class() {
		ob_start();
		$this->instance->manage_custom_admin_columns( 'astra-settings', $this->post_id );
		$output = ob_get_clean();

		// Should include post ID in class for JavaScript targeting.
		$this->assertStringContainsString( "astra-bulk-edit-field-{$this->post_id}", $output );
	}

	/**
	 * Test column displays stored meta values.
	 */
	public function test_column_displays_stored_meta() {
		update_post_meta( $this->post_id, 'site-sidebar-layout', 'left-sidebar' );

		ob_start();
		$this->instance->manage_custom_admin_columns( 'astra-settings', $this->post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'left-sidebar', $output );
	}

	/**
	 * Test column handles post without meta.
	 */
	public function test_column_handles_post_without_meta() {
		// New post with no meta set.
		$new_post_id = $this->factory->post->create(
			array(
				'post_title'  => 'No Meta Post',
				'post_status' => 'publish',
			)
		);

		ob_start();
		$this->instance->manage_custom_admin_columns( 'astra-settings', $new_post_id );
		$output = ob_get_clean();

		// Should still output structure without errors.
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'div', $output );

		wp_delete_post( $new_post_id, true );
	}

	/**
	 * Test column output is properly escaped.
	 */
	public function test_column_output_escapes_data() {
		// Set meta with potentially dangerous content.
		update_post_meta( $this->post_id, 'site-sidebar-layout', '<script>alert("xss")</script>' );

		ob_start();
		$this->instance->manage_custom_admin_columns( 'astra-settings', $this->post_id );
		$output = ob_get_clean();

		// The actual script tags should not appear in attributes (they should be escaped).
		$this->assertStringContainsString( 'data-name="site-sidebar-layout"', $output );
		// Value might be in the content but attributes should be escaped.
		$this->assertStringNotContainsString( '<script', $output );
	}

	/**
	 * Test column includes all expected meta keys.
	 */
	public function test_column_includes_all_meta_keys() {
		$expected_keys = array(
			'ast-above-header-display',
			'ast-main-header-display',
			'ast-below-header-display',
			'site-sidebar-layout',
			'site-content-layout',
			'ast-featured-img',
			'site-post-title',
			'footer-sml-layout',
			'footer-adv-display',
		);

		ob_start();
		$this->instance->manage_custom_admin_columns( 'astra-settings', $this->post_id );
		$output = ob_get_clean();

		foreach ( $expected_keys as $key ) {
			$this->assertStringContainsString(
				"data-name=\"$key\"",
				$output,
				"Column output should include data-name for $key"
			);
		}
	}

	/**
	 * Test column handles multiple meta values.
	 */
	public function test_column_handles_multiple_meta_values() {
		$meta_values = array(
			'site-sidebar-layout'     => 'left-sidebar',
			'ast-main-header-display' => 'disabled',
			'ast-featured-img'        => 'enabled',
		);

		foreach ( $meta_values as $key => $value ) {
			update_post_meta( $this->post_id, $key, $value );
		}

		ob_start();
		$this->instance->manage_custom_admin_columns( 'astra-settings', $this->post_id );
		$output = ob_get_clean();

		foreach ( $meta_values as $key => $value ) {
			$this->assertStringContainsString( $value, $output );
		}
	}

	/**
	 * Test get_meta_option returns expected structure.
	 */
	public function test_get_meta_option_returns_array() {
		// Initialize meta options.
		$this->instance->setup_bulk_options();

		$meta_options = \Astra_Blk_Meta_Boxes_Bulk_Edit::get_meta_option();

		$this->assertIsArray( $meta_options );
		$this->assertNotEmpty( $meta_options );
	}

	/**
	 * Test setup_admin_init registers hooks for post types.
	 */
	public function test_setup_admin_init_registers_hooks() {
		// Run setup.
		$this->instance->setup_admin_init();

		// Check that filters are registered for 'post' type.
		$this->assertTrue(
			has_action( 'manage_post_posts_columns' ) !== false,
			'Should register column filter for posts'
		);
	}

	/**
	 * Test that attachment post type is excluded.
	 */
	public function test_attachment_post_type_excluded() {
		$this->instance->setup_admin_init();

		// Attachment should not have the filter.
		$this->assertFalse(
			has_action( 'manage_attachment_posts_columns', array( $this->instance, 'add_custom_admin_column' ) ) !== false,
			'Should not register column filter for attachments'
		);
	}

	/**
	 * Test column works with custom post type.
	 */
	public function test_column_works_with_custom_post_type() {
		// Register a custom post type.
		register_post_type(
			'test_cpt',
			array(
				'public' => true,
				'label'  => 'Test CPT',
			)
		);

		// Re-run admin init.
		$this->instance->setup_admin_init();

		// Create a CPT post.
		$cpt_post_id = $this->factory->post->create(
			array(
				'post_type'   => 'test_cpt',
				'post_title'  => 'CPT Test Post',
				'post_status' => 'publish',
			)
		);

		update_post_meta( $cpt_post_id, 'site-sidebar-layout', 'no-sidebar' );

		ob_start();
		$this->instance->manage_custom_admin_columns( 'astra-settings', $cpt_post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'no-sidebar', $output );

		wp_delete_post( $cpt_post_id, true );
		unregister_post_type( 'test_cpt' );
	}

	/**
	 * Test column output contains post ID in element IDs.
	 */
	public function test_column_output_contains_post_id_in_ids() {
		ob_start();
		$this->instance->manage_custom_admin_columns( 'astra-settings', $this->post_id );
		$output = ob_get_clean();

		// IDs should include post ID for unique identification.
		$this->assertMatchesRegularExpression(
			'/id="[^"]*-' . $this->post_id . '"/',
			$output
		);
	}
}
