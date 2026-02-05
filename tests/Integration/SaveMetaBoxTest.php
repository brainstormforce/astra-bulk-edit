<?php
/**
 * Integration tests for save_meta_box functionality.
 *
 * @package Astra_Bulk_Edit
 */

namespace Astra_Bulk_Edit\Tests\Integration;

use WP_UnitTestCase;

/**
 * Save Meta Box Test class.
 *
 * Tests the save_meta_box() method which handles saving post meta
 * when a single post is saved via quick edit.
 *
 * @requires WordPress test environment
 */
class SaveMetaBoxTest extends WP_UnitTestCase {

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
	 * Helper to simulate POST data and nonce for save_meta_box.
	 *
	 * @param array $data POST data to set.
	 */
	private function setup_post_data( $data ) {
		// Set up nonce.
		$_POST['astra_settings_bulk_meta_box'] = wp_create_nonce( basename( ASTRA_BLK_DIR . 'classes/class-astra-blk-meta-boxes-bulk-edit.php' ) );

		// Merge provided data.
		foreach ( $data as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	/**
	 * Clean up POST data after test.
	 */
	private function cleanup_post_data() {
		$_POST = array();
	}

	/**
	 * Test that meta is not saved without valid nonce.
	 */
	public function test_meta_not_saved_without_nonce() {
		$_POST['site-sidebar-layout'] = 'left-sidebar';

		$this->instance->save_meta_box( $this->post_id );

		$saved_value = get_post_meta( $this->post_id, 'site-sidebar-layout', true );
		$this->assertEmpty( $saved_value );

		$this->cleanup_post_data();
	}

	/**
	 * Test that meta is not saved with invalid nonce.
	 */
	public function test_meta_not_saved_with_invalid_nonce() {
		$_POST['astra_settings_bulk_meta_box'] = 'invalid-nonce';
		$_POST['site-sidebar-layout']          = 'left-sidebar';

		$this->instance->save_meta_box( $this->post_id );

		$saved_value = get_post_meta( $this->post_id, 'site-sidebar-layout', true );
		$this->assertEmpty( $saved_value );

		$this->cleanup_post_data();
	}

	/**
	 * Test that meta is saved with valid nonce.
	 */
	public function test_meta_saved_with_valid_nonce() {
		$this->setup_post_data(
			array(
				'site-sidebar-layout' => 'left-sidebar',
			)
		);

		$this->instance->save_meta_box( $this->post_id );

		$saved_value = get_post_meta( $this->post_id, 'site-sidebar-layout', true );
		$this->assertEquals( 'left-sidebar', $saved_value );

		$this->cleanup_post_data();
	}

	/**
	 * Test that no-change value is not saved.
	 */
	public function test_no_change_value_not_saved() {
		// First set an existing value.
		update_post_meta( $this->post_id, 'site-sidebar-layout', 'right-sidebar' );

		$this->setup_post_data(
			array(
				'site-sidebar-layout' => 'no-change',
			)
		);

		$this->instance->save_meta_box( $this->post_id );

		// Original value should be preserved.
		$saved_value = get_post_meta( $this->post_id, 'site-sidebar-layout', true );
		$this->assertEquals( 'right-sidebar', $saved_value );

		$this->cleanup_post_data();
	}

	/**
	 * Test that multiple meta values can be saved at once.
	 */
	public function test_multiple_meta_values_saved() {
		$this->setup_post_data(
			array(
				'site-sidebar-layout'     => 'left-sidebar',
				'ast-main-header-display' => 'disabled',
				'ast-featured-img'        => 'enabled',
			)
		);

		$this->instance->save_meta_box( $this->post_id );

		$this->assertEquals( 'left-sidebar', get_post_meta( $this->post_id, 'site-sidebar-layout', true ) );
		$this->assertEquals( 'disabled', get_post_meta( $this->post_id, 'ast-main-header-display', true ) );
		$this->assertEquals( 'enabled', get_post_meta( $this->post_id, 'ast-featured-img', true ) );

		$this->cleanup_post_data();
	}

	/**
	 * Test that non-admin cannot save meta.
	 */
	public function test_non_admin_cannot_save_meta() {
		// Switch to subscriber.
		$subscriber_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		$this->setup_post_data(
			array(
				'site-sidebar-layout' => 'left-sidebar',
			)
		);

		$this->instance->save_meta_box( $this->post_id );

		$saved_value = get_post_meta( $this->post_id, 'site-sidebar-layout', true );
		$this->assertEmpty( $saved_value );

		$this->cleanup_post_data();
	}

	/**
	 * Test that editor can save meta on their own posts.
	 */
	public function test_editor_can_save_meta() {
		// Create editor and their post.
		$editor_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $editor_id );

		$editor_post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Editor Post',
				'post_author' => $editor_id,
			)
		);

		$this->setup_post_data(
			array(
				'site-sidebar-layout' => 'right-sidebar',
			)
		);

		$this->instance->save_meta_box( $editor_post_id );

		$saved_value = get_post_meta( $editor_post_id, 'site-sidebar-layout', true );
		$this->assertEquals( 'right-sidebar', $saved_value );

		wp_delete_post( $editor_post_id, true );
		$this->cleanup_post_data();
	}

	/**
	 * Test that all header display options can be saved.
	 */
	public function test_header_display_options_saved() {
		$header_options = array(
			'ast-main-header-display'       => 'disabled',
			'ast-above-header-display'      => 'enabled',
			'ast-below-header-display'      => 'disabled',
			'ast-hfb-above-header-display'  => 'enabled',
			'ast-hfb-below-header-display'  => 'disabled',
			'ast-hfb-mobile-header-display' => 'enabled',
		);

		$this->setup_post_data( $header_options );

		$this->instance->save_meta_box( $this->post_id );

		foreach ( $header_options as $key => $expected_value ) {
			$saved_value = get_post_meta( $this->post_id, $key, true );
			$this->assertEquals( $expected_value, $saved_value, "Failed for $key" );
		}

		$this->cleanup_post_data();
	}

	/**
	 * Test that layout options can be saved.
	 */
	public function test_layout_options_saved() {
		$layout_options = array(
			'site-content-layout'     => 'boxed-container',
			'ast-site-content-layout' => 'normal-width-container',
			'site-content-style'      => 'boxed',
			'site-sidebar-style'      => 'unboxed',
		);

		$this->setup_post_data( $layout_options );

		$this->instance->save_meta_box( $this->post_id );

		foreach ( $layout_options as $key => $expected_value ) {
			$saved_value = get_post_meta( $this->post_id, $key, true );
			$this->assertEquals( $expected_value, $saved_value, "Failed for $key" );
		}

		$this->cleanup_post_data();
	}

	/**
	 * Test that footer options can be saved.
	 */
	public function test_footer_options_saved() {
		$footer_options = array(
			'footer-sml-layout'  => 'disabled',
			'footer-adv-display' => 'enabled',
		);

		$this->setup_post_data( $footer_options );

		$this->instance->save_meta_box( $this->post_id );

		foreach ( $footer_options as $key => $expected_value ) {
			$saved_value = get_post_meta( $this->post_id, $key, true );
			$this->assertEquals( $expected_value, $saved_value, "Failed for $key" );
		}

		$this->cleanup_post_data();
	}

	/**
	 * Test that sticky header options can be saved.
	 */
	public function test_sticky_header_options_saved() {
		$sticky_options = array(
			'stick-header-meta'       => 'enabled',
			'header-above-stick-meta' => 'disabled',
			'header-main-stick-meta'  => 'enabled',
			'header-below-stick-meta' => 'disabled',
		);

		$this->setup_post_data( $sticky_options );

		$this->instance->save_meta_box( $this->post_id );

		foreach ( $sticky_options as $key => $expected_value ) {
			$saved_value = get_post_meta( $this->post_id, $key, true );
			$this->assertEquals( $expected_value, $saved_value, "Failed for $key" );
		}

		$this->cleanup_post_data();
	}

	/**
	 * Test visibility options (title, featured image, breadcrumbs).
	 */
	public function test_visibility_options_saved() {
		$visibility_options = array(
			'site-post-title'         => 'disabled',
			'ast-featured-img'        => 'enabled',
			'ast-breadcrumbs-content' => 'disabled',
		);

		$this->setup_post_data( $visibility_options );

		$this->instance->save_meta_box( $this->post_id );

		foreach ( $visibility_options as $key => $expected_value ) {
			$saved_value = get_post_meta( $this->post_id, $key, true );
			$this->assertEquals( $expected_value, $saved_value, "Failed for $key" );
		}

		$this->cleanup_post_data();
	}

	/**
	 * Test transparent header meta option.
	 */
	public function test_transparent_header_option_saved() {
		$this->setup_post_data(
			array(
				'theme-transparent-header-meta' => 'enabled',
			)
		);

		$this->instance->save_meta_box( $this->post_id );

		$saved_value = get_post_meta( $this->post_id, 'theme-transparent-header-meta', true );
		$this->assertEquals( 'enabled', $saved_value );

		$this->cleanup_post_data();
	}

	/**
	 * Test that existing meta is updated, not duplicated.
	 */
	public function test_existing_meta_updated_not_duplicated() {
		// Set initial value.
		update_post_meta( $this->post_id, 'site-sidebar-layout', 'left-sidebar' );

		// Update with new value.
		$this->setup_post_data(
			array(
				'site-sidebar-layout' => 'right-sidebar',
			)
		);

		$this->instance->save_meta_box( $this->post_id );

		// Should have only one value.
		$all_values = get_post_meta( $this->post_id, 'site-sidebar-layout' );
		$this->assertCount( 1, $all_values );
		$this->assertEquals( 'right-sidebar', $all_values[0] );

		$this->cleanup_post_data();
	}

	/**
	 * Test save with mixed no-change and actual values.
	 */
	public function test_mixed_no_change_and_actual_values() {
		// Set initial values.
		update_post_meta( $this->post_id, 'site-sidebar-layout', 'left-sidebar' );
		update_post_meta( $this->post_id, 'ast-featured-img', 'disabled' );

		$this->setup_post_data(
			array(
				'site-sidebar-layout' => 'no-change',       // Should preserve existing.
				'ast-featured-img'    => 'enabled',         // Should update.
				'site-post-title'     => 'disabled',        // Should add new.
			)
		);

		$this->instance->save_meta_box( $this->post_id );

		$this->assertEquals( 'left-sidebar', get_post_meta( $this->post_id, 'site-sidebar-layout', true ) );
		$this->assertEquals( 'enabled', get_post_meta( $this->post_id, 'ast-featured-img', true ) );
		$this->assertEquals( 'disabled', get_post_meta( $this->post_id, 'site-post-title', true ) );

		$this->cleanup_post_data();
	}
}
