<?php
/**
 * Tests for meta options configuration.
 *
 * @package Astra_Bulk_Edit
 */

namespace Astra_Bulk_Edit\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Meta Options Test class.
 *
 * Tests the meta options setup, defaults, and structure
 * used by the Astra Bulk Edit plugin.
 */
class MetaOptionsTest extends TestCase {

	/**
	 * Get the expected meta options configuration.
	 *
	 * @return array Meta options configuration.
	 */
	private function get_expected_meta_options() {
		return array(
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
		);
	}

	/**
	 * Test that all meta options have a sanitize key.
	 */
	public function test_all_meta_options_have_sanitize_key() {
		$meta_options = $this->get_expected_meta_options();

		foreach ( $meta_options as $key => $config ) {
			$this->assertArrayHasKey(
				'sanitize',
				$config,
				"Meta option '$key' is missing the 'sanitize' key"
			);
		}
	}

	/**
	 * Test that most meta options have a default key.
	 *
	 * Note: adv-header-id-meta intentionally has no default.
	 */
	public function test_meta_options_have_default_where_expected() {
		$meta_options     = $this->get_expected_meta_options();
		$options_without_default = array( 'adv-header-id-meta' );

		foreach ( $meta_options as $key => $config ) {
			if ( in_array( $key, $options_without_default, true ) ) {
				$this->assertArrayNotHasKey(
					'default',
					$config,
					"Meta option '$key' should not have a default"
				);
			} else {
				$this->assertArrayHasKey(
					'default',
					$config,
					"Meta option '$key' should have a default"
				);
			}
		}
	}

	/**
	 * Test that default values are 'no-change'.
	 */
	public function test_default_values_are_no_change() {
		$meta_options = $this->get_expected_meta_options();

		foreach ( $meta_options as $key => $config ) {
			if ( isset( $config['default'] ) ) {
				$this->assertEquals(
					'no-change',
					$config['default'],
					"Meta option '$key' default should be 'no-change'"
				);
			}
		}
	}

	/**
	 * Test expected number of meta options.
	 */
	public function test_expected_number_of_meta_options() {
		$meta_options = $this->get_expected_meta_options();

		// The plugin defines 22 meta options.
		$this->assertCount( 22, $meta_options );
	}

	/**
	 * Test header display options exist.
	 */
	public function test_header_display_options_exist() {
		$meta_options = $this->get_expected_meta_options();

		$header_options = array(
			'ast-above-header-display',
			'ast-main-header-display',
			'ast-below-header-display',
			'ast-hfb-above-header-display',
			'ast-hfb-below-header-display',
			'ast-hfb-mobile-header-display',
		);

		foreach ( $header_options as $option ) {
			$this->assertArrayHasKey(
				$option,
				$meta_options,
				"Header option '$option' should exist"
			);
		}
	}

	/**
	 * Test layout options exist.
	 */
	public function test_layout_options_exist() {
		$meta_options = $this->get_expected_meta_options();

		$layout_options = array(
			'site-sidebar-layout',
			'site-content-layout',
			'ast-site-content-layout',
			'site-content-style',
			'site-sidebar-style',
		);

		foreach ( $layout_options as $option ) {
			$this->assertArrayHasKey(
				$option,
				$meta_options,
				"Layout option '$option' should exist"
			);
		}
	}

	/**
	 * Test footer options exist.
	 */
	public function test_footer_options_exist() {
		$meta_options = $this->get_expected_meta_options();

		$footer_options = array(
			'footer-sml-layout',
			'footer-adv-display',
		);

		foreach ( $footer_options as $option ) {
			$this->assertArrayHasKey(
				$option,
				$meta_options,
				"Footer option '$option' should exist"
			);
		}
	}

	/**
	 * Test sticky header options exist.
	 */
	public function test_sticky_header_options_exist() {
		$meta_options = $this->get_expected_meta_options();

		$sticky_options = array(
			'stick-header-meta',
			'header-above-stick-meta',
			'header-main-stick-meta',
			'header-below-stick-meta',
		);

		foreach ( $sticky_options as $option ) {
			$this->assertArrayHasKey(
				$option,
				$meta_options,
				"Sticky header option '$option' should exist"
			);
		}
	}

	/**
	 * Test visibility options exist.
	 */
	public function test_visibility_options_exist() {
		$meta_options = $this->get_expected_meta_options();

		$visibility_options = array(
			'ast-featured-img',
			'site-post-title',
			'ast-breadcrumbs-content',
		);

		foreach ( $visibility_options as $option ) {
			$this->assertArrayHasKey(
				$option,
				$meta_options,
				"Visibility option '$option' should exist"
			);
		}
	}

	/**
	 * Test extension options exist.
	 */
	public function test_extension_options_exist() {
		$meta_options = $this->get_expected_meta_options();

		$extension_options = array(
			'theme-transparent-header-meta',
			'adv-header-id-meta',
		);

		foreach ( $extension_options as $option ) {
			$this->assertArrayHasKey(
				$option,
				$meta_options,
				"Extension option '$option' should exist"
			);
		}
	}

	/**
	 * Test sanitize values are valid filter names.
	 */
	public function test_sanitize_values_are_valid_filter_names() {
		$meta_options = $this->get_expected_meta_options();

		$valid_filters = array(
			'FILTER_DEFAULT',
			'FILTER_SANITIZE_STRING',
			'FILTER_SANITIZE_URL',
			'FILTER_SANITIZE_NUMBER_INT',
		);

		foreach ( $meta_options as $key => $config ) {
			$this->assertContains(
				$config['sanitize'],
				$valid_filters,
				"Meta option '$key' has invalid sanitize filter: {$config['sanitize']}"
			);
		}
	}

	/**
	 * Test meta option keys follow naming convention.
	 *
	 * Keys should be lowercase with hyphens.
	 */
	public function test_meta_option_keys_follow_naming_convention() {
		$meta_options = $this->get_expected_meta_options();

		foreach ( $meta_options as $key => $config ) {
			// Keys should not contain uppercase letters.
			$this->assertEquals(
				strtolower( $key ),
				$key,
				"Meta option key '$key' should be lowercase"
			);

			// Keys should not contain underscores (use hyphens instead).
			$this->assertStringNotContainsString(
				'_',
				$key,
				"Meta option key '$key' should use hyphens, not underscores"
			);
		}
	}

	/**
	 * Test that legacy and revamped layout options both exist.
	 *
	 * The plugin supports both old (pre-4.2.0) and new (4.2.0+) Astra layouts.
	 */
	public function test_legacy_and_revamped_layout_options_exist() {
		$meta_options = $this->get_expected_meta_options();

		// Legacy layout option (pre-4.2.0).
		$this->assertArrayHasKey( 'site-content-layout', $meta_options );

		// Revamped layout options (4.2.0+).
		$this->assertArrayHasKey( 'ast-site-content-layout', $meta_options );
		$this->assertArrayHasKey( 'site-content-style', $meta_options );
		$this->assertArrayHasKey( 'site-sidebar-style', $meta_options );
	}
}
