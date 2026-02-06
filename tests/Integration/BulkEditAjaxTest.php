<?php
/**
 * Integration tests for bulk edit AJAX handler.
 *
 * @package Astra_Bulk_Edit
 */

namespace Astra_Bulk_Edit\Tests\Integration;

use WP_Ajax_UnitTestCase;

/**
 * Bulk Edit AJAX Test class.
 *
 * Tests the save_post_bulk_edit() AJAX handler which handles
 * saving post meta for multiple posts at once via bulk edit.
 *
 * @requires WordPress test environment with AJAX support
 */
class BulkEditAjaxTest extends WP_Ajax_UnitTestCase {

	/**
	 * Instance of the bulk edit class.
	 *
	 * @var \Astra_Blk_Meta_Boxes_Bulk_Edit
	 */
	private $instance;

	/**
	 * Test post IDs.
	 *
	 * @var array
	 */
	private $post_ids = array();

	/**
	 * Set up test fixtures.
	 */
	public function set_up() {
		parent::set_up();

		// Create multiple test posts.
		for ( $i = 1; $i <= 5; $i++ ) {
			$this->post_ids[] = $this->factory->post->create(
				array(
					'post_title'  => "Test Post $i",
					'post_status' => 'publish',
				)
			);
		}

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
		foreach ( $this->post_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}
		$this->post_ids = array();
		parent::tear_down();
	}

	/**
	 * Helper to set up AJAX request data.
	 *
	 * @param array $post_ids Post IDs to include.
	 * @param array $meta_data Meta data to save.
	 */
	private function setup_ajax_request( $post_ids, $meta_data ) {
		$_POST['action']      = 'astra_save_post_bulk_edit';
		$_POST['astra_nonce'] = wp_create_nonce( 'astra-blk-nonce' );
		$_POST['post']        = $post_ids;

		foreach ( $meta_data as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	/**
	 * Clean up request data.
	 */
	private function cleanup_request() {
		$_POST    = array();
		$_GET     = array();
		$_REQUEST = array();
	}

	/**
	 * Test AJAX handler rejects request without nonce.
	 */
	public function test_ajax_rejects_without_nonce() {
		$_POST['action'] = 'astra_save_post_bulk_edit';
		$_POST['post']   = $this->post_ids;

		$this->expectException( 'WPAjaxDieStopException' );
		$this->_handleAjax( 'astra_save_post_bulk_edit' );

		$this->cleanup_request();
	}

	/**
	 * Test AJAX handler rejects request with invalid nonce.
	 */
	public function test_ajax_rejects_with_invalid_nonce() {
		$_POST['action']      = 'astra_save_post_bulk_edit';
		$_POST['astra_nonce'] = 'invalid-nonce';
		$_POST['post']        = $this->post_ids;

		$this->expectException( 'WPAjaxDieStopException' );
		$this->_handleAjax( 'astra_save_post_bulk_edit' );

		$this->cleanup_request();
	}

	/**
	 * Test AJAX handler updates meta for multiple posts.
	 */
	public function test_ajax_updates_multiple_posts() {
		$this->setup_ajax_request(
			$this->post_ids,
			array(
				'site-sidebar-layout' => 'left-sidebar',
			)
		);

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected for successful AJAX.
		}

		// Verify all posts were updated.
		foreach ( $this->post_ids as $post_id ) {
			$saved_value = get_post_meta( $post_id, 'site-sidebar-layout', true );
			$this->assertEquals( 'left-sidebar', $saved_value, "Post $post_id was not updated" );
		}

		$this->cleanup_request();
	}

	/**
	 * Test AJAX handler updates multiple meta values.
	 */
	public function test_ajax_updates_multiple_meta_values() {
		$meta_data = array(
			'site-sidebar-layout'     => 'right-sidebar',
			'ast-main-header-display' => 'disabled',
			'ast-featured-img'        => 'enabled',
		);

		$this->setup_ajax_request( $this->post_ids, $meta_data );

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected for successful AJAX.
		}

		// Verify all posts have all meta values.
		foreach ( $this->post_ids as $post_id ) {
			foreach ( $meta_data as $key => $expected_value ) {
				$saved_value = get_post_meta( $post_id, $key, true );
				$this->assertEquals( $expected_value, $saved_value, "Post $post_id meta $key mismatch" );
			}
		}

		$this->cleanup_request();
	}

	/**
	 * Test AJAX handler respects no-change value.
	 */
	public function test_ajax_respects_no_change_value() {
		// Set initial values.
		foreach ( $this->post_ids as $post_id ) {
			update_post_meta( $post_id, 'site-sidebar-layout', 'left-sidebar' );
		}

		$this->setup_ajax_request(
			$this->post_ids,
			array(
				'site-sidebar-layout' => 'no-change',
			)
		);

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected for successful AJAX.
		}

		// Verify original values are preserved.
		foreach ( $this->post_ids as $post_id ) {
			$saved_value = get_post_meta( $post_id, 'site-sidebar-layout', true );
			$this->assertEquals( 'left-sidebar', $saved_value, "Post $post_id should keep original value" );
		}

		$this->cleanup_request();
	}

	/**
	 * Test AJAX handler respects user capabilities per post.
	 */
	public function test_ajax_respects_user_capabilities() {
		// Create author user.
		$author_id = $this->factory->user->create( array( 'role' => 'author' ) );

		// Create a post owned by author.
		$author_post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Author Post',
				'post_author' => $author_id,
			)
		);

		// Create a post owned by admin.
		$admin_id      = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$admin_post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Admin Post',
				'post_author' => $admin_id,
			)
		);

		// Switch to author user.
		wp_set_current_user( $author_id );

		$this->setup_ajax_request(
			array( $author_post_id, $admin_post_id ),
			array(
				'site-sidebar-layout' => 'left-sidebar',
			)
		);

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected.
		}

		// Author's post should be updated.
		$this->assertEquals(
			'left-sidebar',
			get_post_meta( $author_post_id, 'site-sidebar-layout', true )
		);

		// Admin's post should NOT be updated (author can't edit it).
		$this->assertEmpty( get_post_meta( $admin_post_id, 'site-sidebar-layout', true ) );

		wp_delete_post( $author_post_id, true );
		wp_delete_post( $admin_post_id, true );
		$this->cleanup_request();
	}

	/**
	 * Test AJAX handler handles empty post array.
	 */
	public function test_ajax_handles_empty_post_array() {
		$this->setup_ajax_request(
			array(),
			array(
				'site-sidebar-layout' => 'left-sidebar',
			)
		);

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Should complete successfully without errors.
		}

		$this->cleanup_request();
	}

	/**
	 * Test AJAX handler handles single post.
	 */
	public function test_ajax_handles_single_post() {
		$single_post = array( $this->post_ids[0] );

		$this->setup_ajax_request(
			$single_post,
			array(
				'site-sidebar-layout' => 'no-sidebar',
			)
		);

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected.
		}

		$this->assertEquals(
			'no-sidebar',
			get_post_meta( $this->post_ids[0], 'site-sidebar-layout', true )
		);

		$this->cleanup_request();
	}

	/**
	 * Test AJAX handler returns success response.
	 */
	public function test_ajax_returns_success_response() {
		$this->setup_ajax_request(
			$this->post_ids,
			array(
				'site-sidebar-layout' => 'left-sidebar',
			)
		);

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			$response = json_decode( $this->_last_response, true );
			$this->assertTrue( $response['success'] );
		}

		$this->cleanup_request();
	}

	/**
	 * Test bulk update of all header display options.
	 */
	public function test_bulk_update_header_options() {
		$header_options = array(
			'ast-main-header-display'       => 'disabled',
			'ast-above-header-display'      => 'enabled',
			'ast-below-header-display'      => 'disabled',
			'ast-hfb-above-header-display'  => 'enabled',
			'ast-hfb-below-header-display'  => 'disabled',
			'ast-hfb-mobile-header-display' => 'enabled',
		);

		$this->setup_ajax_request( $this->post_ids, $header_options );

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected.
		}

		foreach ( $this->post_ids as $post_id ) {
			foreach ( $header_options as $key => $expected ) {
				$this->assertEquals(
					$expected,
					get_post_meta( $post_id, $key, true ),
					"Post $post_id, key $key mismatch"
				);
			}
		}

		$this->cleanup_request();
	}

	/**
	 * Test bulk update of layout options.
	 */
	public function test_bulk_update_layout_options() {
		$layout_options = array(
			'site-sidebar-layout'     => 'left-sidebar',
			'site-content-layout'     => 'boxed-container',
			'ast-site-content-layout' => 'normal-width-container',
			'site-content-style'      => 'boxed',
			'site-sidebar-style'      => 'unboxed',
		);

		$this->setup_ajax_request( $this->post_ids, $layout_options );

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected.
		}

		foreach ( $this->post_ids as $post_id ) {
			foreach ( $layout_options as $key => $expected ) {
				$this->assertEquals(
					$expected,
					get_post_meta( $post_id, $key, true ),
					"Post $post_id, key $key mismatch"
				);
			}
		}

		$this->cleanup_request();
	}

	/**
	 * Test bulk update with mixed existing values.
	 *
	 * Some posts have existing values, some don't.
	 */
	public function test_bulk_update_with_mixed_existing_values() {
		// Set values for some posts only.
		update_post_meta( $this->post_ids[0], 'site-sidebar-layout', 'left-sidebar' );
		update_post_meta( $this->post_ids[1], 'site-sidebar-layout', 'right-sidebar' );
		// post_ids[2-4] have no existing value.

		$this->setup_ajax_request(
			$this->post_ids,
			array(
				'site-sidebar-layout' => 'no-sidebar',
			)
		);

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected.
		}

		// All posts should now have the same value.
		foreach ( $this->post_ids as $post_id ) {
			$this->assertEquals(
				'no-sidebar',
				get_post_meta( $post_id, 'site-sidebar-layout', true )
			);
		}

		$this->cleanup_request();
	}

	/**
	 * Test that non-existent post IDs are handled gracefully.
	 */
	public function test_handles_non_existent_post_ids() {
		$post_ids_with_invalid = array_merge(
			$this->post_ids,
			array( 999999, 888888 ) // Non-existent IDs.
		);

		$this->setup_ajax_request(
			$post_ids_with_invalid,
			array(
				'site-sidebar-layout' => 'left-sidebar',
			)
		);

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Should complete without fatal error.
			$response = json_decode( $this->_last_response, true );
			$this->assertTrue( $response['success'] );
		}

		// Valid posts should still be updated.
		foreach ( $this->post_ids as $post_id ) {
			$this->assertEquals(
				'left-sidebar',
				get_post_meta( $post_id, 'site-sidebar-layout', true )
			);
		}

		$this->cleanup_request();
	}

	/**
	 * Test large batch of posts.
	 */
	public function test_large_batch_of_posts() {
		// Create additional posts for a large batch.
		$large_batch = array();
		for ( $i = 0; $i < 50; $i++ ) {
			$large_batch[] = $this->factory->post->create(
				array(
					'post_title'  => "Batch Post $i",
					'post_status' => 'publish',
				)
			);
		}

		$this->setup_ajax_request(
			$large_batch,
			array(
				'site-sidebar-layout' => 'right-sidebar',
			)
		);

		try {
			$this->_handleAjax( 'astra_save_post_bulk_edit' );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Expected.
		}

		// Verify all posts were updated.
		foreach ( $large_batch as $post_id ) {
			$this->assertEquals(
				'right-sidebar',
				get_post_meta( $post_id, 'site-sidebar-layout', true )
			);
		}

		// Clean up.
		foreach ( $large_batch as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		$this->cleanup_request();
	}
}
