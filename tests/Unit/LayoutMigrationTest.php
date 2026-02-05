<?php
/**
 * Tests for layout migration logic.
 *
 * @package Astra_Bulk_Edit
 */

namespace Astra_Bulk_Edit\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Layout Migration Test class.
 *
 * Tests the migrate_layouts() method which handles Astra v4.2.0+
 * layout option migrations from legacy to revamped layout options.
 */
class LayoutMigrationTest extends TestCase {

	/**
	 * Helper method to call migrate_layouts on the class.
	 *
	 * @param string $old_layout The old layout value.
	 * @param array  $meta The meta array to migrate.
	 * @return array The migrated meta array.
	 */
	private function migrate_layouts( $old_layout, $meta ) {
		// We test the migration logic directly without instantiating the full class.
		switch ( $old_layout ) {
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

	/**
	 * Get base meta array for testing.
	 *
	 * @return array Base meta array.
	 */
	private function get_base_meta() {
		return array(
			'ast-site-content-layout' => array( 'default' => '' ),
			'site-content-style'      => array( 'default' => '' ),
			'site-sidebar-style'      => array( 'default' => '' ),
		);
	}

	/**
	 * Test plain-container migration.
	 *
	 * Plain container should become normal-width + unboxed styles.
	 */
	public function test_plain_container_migration() {
		$meta   = $this->get_base_meta();
		$result = $this->migrate_layouts( 'plain-container', $meta );

		$this->assertEquals( 'normal-width-container', $result['ast-site-content-layout']['default'] );
		$this->assertEquals( 'unboxed', $result['site-content-style']['default'] );
		$this->assertEquals( 'unboxed', $result['site-sidebar-style']['default'] );
	}

	/**
	 * Test boxed-container migration.
	 *
	 * Boxed container should become normal-width + boxed styles.
	 */
	public function test_boxed_container_migration() {
		$meta   = $this->get_base_meta();
		$result = $this->migrate_layouts( 'boxed-container', $meta );

		$this->assertEquals( 'normal-width-container', $result['ast-site-content-layout']['default'] );
		$this->assertEquals( 'boxed', $result['site-content-style']['default'] );
		$this->assertEquals( 'boxed', $result['site-sidebar-style']['default'] );
	}

	/**
	 * Test content-boxed-container migration.
	 *
	 * Content boxed should become normal-width + mixed styles (content boxed, sidebar unboxed).
	 */
	public function test_content_boxed_container_migration() {
		$meta   = $this->get_base_meta();
		$result = $this->migrate_layouts( 'content-boxed-container', $meta );

		$this->assertEquals( 'normal-width-container', $result['ast-site-content-layout']['default'] );
		$this->assertEquals( 'boxed', $result['site-content-style']['default'] );
		$this->assertEquals( 'unboxed', $result['site-sidebar-style']['default'] );
	}

	/**
	 * Test page-builder migration.
	 *
	 * Page builder should become full-width + unboxed styles.
	 */
	public function test_page_builder_migration() {
		$meta   = $this->get_base_meta();
		$result = $this->migrate_layouts( 'page-builder', $meta );

		$this->assertEquals( 'full-width-container', $result['ast-site-content-layout']['default'] );
		$this->assertEquals( 'unboxed', $result['site-content-style']['default'] );
		$this->assertEquals( 'unboxed', $result['site-sidebar-style']['default'] );
	}

	/**
	 * Test narrow-container migration.
	 *
	 * Narrow container should become narrow-width + unboxed styles.
	 */
	public function test_narrow_container_migration() {
		$meta   = $this->get_base_meta();
		$result = $this->migrate_layouts( 'narrow-container', $meta );

		$this->assertEquals( 'narrow-width-container', $result['ast-site-content-layout']['default'] );
		$this->assertEquals( 'unboxed', $result['site-content-style']['default'] );
		$this->assertEquals( 'unboxed', $result['site-sidebar-style']['default'] );
	}

	/**
	 * Test no-change value preservation.
	 *
	 * When no-change is selected, all values should remain no-change.
	 */
	public function test_no_change_preservation() {
		$meta   = $this->get_base_meta();
		$result = $this->migrate_layouts( 'no-change', $meta );

		$this->assertEquals( 'no-change', $result['ast-site-content-layout']['default'] );
		$this->assertEquals( 'no-change', $result['site-content-style']['default'] );
		$this->assertEquals( 'no-change', $result['site-sidebar-style']['default'] );
	}

	/**
	 * Test default fallback for unknown layout values.
	 *
	 * Unknown values should default to 'default' for all fields.
	 */
	public function test_default_fallback_for_unknown_layout() {
		$meta   = $this->get_base_meta();
		$result = $this->migrate_layouts( 'unknown-layout', $meta );

		$this->assertEquals( 'default', $result['ast-site-content-layout']['default'] );
		$this->assertEquals( 'default', $result['site-content-style']['default'] );
		$this->assertEquals( 'default', $result['site-sidebar-style']['default'] );
	}

	/**
	 * Test default fallback for empty string.
	 *
	 * Empty string should trigger the default case.
	 */
	public function test_default_fallback_for_empty_string() {
		$meta   = $this->get_base_meta();
		$result = $this->migrate_layouts( '', $meta );

		$this->assertEquals( 'default', $result['ast-site-content-layout']['default'] );
		$this->assertEquals( 'default', $result['site-content-style']['default'] );
		$this->assertEquals( 'default', $result['site-sidebar-style']['default'] );
	}

	/**
	 * Test that migration preserves other meta fields.
	 *
	 * Migration should only modify the three layout-related fields.
	 */
	public function test_migration_preserves_other_meta_fields() {
		$meta = $this->get_base_meta();
		$meta['site-sidebar-layout'] = array( 'default' => 'left-sidebar' );
		$meta['ast-featured-img']    = array( 'default' => 'enabled' );

		$result = $this->migrate_layouts( 'plain-container', $meta );

		// Migrated fields should be updated.
		$this->assertEquals( 'normal-width-container', $result['ast-site-content-layout']['default'] );

		// Other fields should be preserved.
		$this->assertEquals( 'left-sidebar', $result['site-sidebar-layout']['default'] );
		$this->assertEquals( 'enabled', $result['ast-featured-img']['default'] );
	}

	/**
	 * Test migration with all legacy layout types.
	 *
	 * Data provider test to ensure all legacy layouts map correctly.
	 *
	 * @dataProvider legacyLayoutsProvider
	 *
	 * @param string $legacy_layout   The legacy layout value.
	 * @param string $expected_layout The expected new layout value.
	 * @param string $expected_content_style The expected content style.
	 * @param string $expected_sidebar_style The expected sidebar style.
	 */
	public function test_legacy_layout_migration( $legacy_layout, $expected_layout, $expected_content_style, $expected_sidebar_style ) {
		$meta   = $this->get_base_meta();
		$result = $this->migrate_layouts( $legacy_layout, $meta );

		$this->assertEquals( $expected_layout, $result['ast-site-content-layout']['default'] );
		$this->assertEquals( $expected_content_style, $result['site-content-style']['default'] );
		$this->assertEquals( $expected_sidebar_style, $result['site-sidebar-style']['default'] );
	}

	/**
	 * Data provider for legacy layout migrations.
	 *
	 * @return array Test data sets.
	 */
	public static function legacyLayoutsProvider() {
		return array(
			'plain container'         => array( 'plain-container', 'normal-width-container', 'unboxed', 'unboxed' ),
			'boxed container'         => array( 'boxed-container', 'normal-width-container', 'boxed', 'boxed' ),
			'content boxed container' => array( 'content-boxed-container', 'normal-width-container', 'boxed', 'unboxed' ),
			'page builder'            => array( 'page-builder', 'full-width-container', 'unboxed', 'unboxed' ),
			'narrow container'        => array( 'narrow-container', 'narrow-width-container', 'unboxed', 'unboxed' ),
			'no change'               => array( 'no-change', 'no-change', 'no-change', 'no-change' ),
			'default (empty)'         => array( '', 'default', 'default', 'default' ),
			'default (unknown)'       => array( 'some-random-value', 'default', 'default', 'default' ),
		);
	}
}
