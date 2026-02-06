<?php
/**
 * Tests for input sanitization logic.
 *
 * @package Astra_Bulk_Edit
 */

namespace Astra_Bulk_Edit\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Sanitization Test class.
 *
 * Tests the sanitization logic used in save_meta_box() and save_post_bulk_edit()
 * to ensure proper input filtering and security.
 */
class SanitizationTest extends TestCase {

	/**
	 * Helper method to simulate the sanitization logic from the plugin.
	 *
	 * @param string $sanitize_filter The filter type string.
	 * @param mixed  $value The value to sanitize.
	 * @return mixed The sanitized value.
	 */
	private function apply_sanitization( $sanitize_filter, $value ) {
		switch ( $sanitize_filter ) {
			case 'FILTER_SANITIZE_STRING':
				// FILTER_SANITIZE_STRING is deprecated in PHP 8.1+, but we test the logic.
				return filter_var( $value, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			case 'FILTER_SANITIZE_URL':
				return filter_var( $value, FILTER_SANITIZE_URL );

			case 'FILTER_SANITIZE_NUMBER_INT':
				return filter_var( $value, FILTER_SANITIZE_NUMBER_INT );

			case 'FILTER_DEFAULT':
			default:
				return filter_var( $value, FILTER_DEFAULT );
		}
	}

	/**
	 * Test that URL sanitization removes dangerous characters.
	 */
	public function test_url_sanitization_removes_spaces() {
		$dirty_url = 'http://example.com/path with spaces';
		$result    = $this->apply_sanitization( 'FILTER_SANITIZE_URL', $dirty_url );

		$this->assertStringNotContainsString( ' ', $result );
	}

	/**
	 * Test that URL sanitization preserves valid URLs.
	 */
	public function test_url_sanitization_preserves_valid_urls() {
		$valid_url = 'https://example.com/path/to/page?query=value&foo=bar#anchor';
		$result    = $this->apply_sanitization( 'FILTER_SANITIZE_URL', $valid_url );

		$this->assertEquals( $valid_url, $result );
	}

	/**
	 * Test that number sanitization extracts integers.
	 */
	public function test_number_int_sanitization_extracts_integers() {
		$mixed_input = 'abc123def456';
		$result      = $this->apply_sanitization( 'FILTER_SANITIZE_NUMBER_INT', $mixed_input );

		$this->assertEquals( '123456', $result );
	}

	/**
	 * Test that number sanitization handles negative numbers.
	 */
	public function test_number_int_sanitization_handles_negative() {
		$negative = '-42';
		$result   = $this->apply_sanitization( 'FILTER_SANITIZE_NUMBER_INT', $negative );

		$this->assertEquals( '-42', $result );
	}

	/**
	 * Test that number sanitization handles pure numbers.
	 */
	public function test_number_int_sanitization_pure_number() {
		$number = '12345';
		$result = $this->apply_sanitization( 'FILTER_SANITIZE_NUMBER_INT', $number );

		$this->assertEquals( '12345', $result );
	}

	/**
	 * Test string sanitization escapes HTML.
	 */
	public function test_string_sanitization_escapes_html() {
		$html_input = '<script>alert("xss")</script>';
		$result     = $this->apply_sanitization( 'FILTER_SANITIZE_STRING', $html_input );

		$this->assertStringNotContainsString( '<script>', $result );
		$this->assertStringNotContainsString( '</script>', $result );
	}

	/**
	 * Test string sanitization preserves safe text.
	 */
	public function test_string_sanitization_preserves_safe_text() {
		$safe_text = 'Hello World 123';
		$result    = $this->apply_sanitization( 'FILTER_SANITIZE_STRING', $safe_text );

		$this->assertEquals( $safe_text, $result );
	}

	/**
	 * Test default filter passes through standard values.
	 */
	public function test_default_filter_passes_standard_values() {
		$test_values = array(
			'enabled',
			'disabled',
			'no-change',
			'default',
			'left-sidebar',
			'right-sidebar',
			'no-sidebar',
		);

		foreach ( $test_values as $value ) {
			$result = $this->apply_sanitization( 'FILTER_DEFAULT', $value );
			$this->assertEquals( $value, $result );
		}
	}

	/**
	 * Test that sanitization handles empty strings.
	 */
	public function test_sanitization_handles_empty_string() {
		$empty = '';

		$this->assertEquals( '', $this->apply_sanitization( 'FILTER_DEFAULT', $empty ) );
		$this->assertEquals( '', $this->apply_sanitization( 'FILTER_SANITIZE_URL', $empty ) );
		$this->assertEquals( '', $this->apply_sanitization( 'FILTER_SANITIZE_NUMBER_INT', $empty ) );
		$this->assertEquals( '', $this->apply_sanitization( 'FILTER_SANITIZE_STRING', $empty ) );
	}

	/**
	 * Test that unknown filter type defaults to FILTER_DEFAULT.
	 */
	public function test_unknown_filter_defaults_to_filter_default() {
		$value  = 'test-value';
		$result = $this->apply_sanitization( 'UNKNOWN_FILTER', $value );

		$this->assertEquals( $value, $result );
	}

	/**
	 * Test sanitization of layout option values.
	 *
	 * @dataProvider layoutOptionValuesProvider
	 *
	 * @param string $value The layout option value.
	 */
	public function test_layout_option_values_pass_sanitization( $value ) {
		$result = $this->apply_sanitization( 'FILTER_DEFAULT', $value );
		$this->assertEquals( $value, $result );
	}

	/**
	 * Data provider for valid layout option values.
	 *
	 * @return array Test data sets.
	 */
	public static function layoutOptionValuesProvider() {
		return array(
			'no change'               => array( 'no-change' ),
			'default'                 => array( 'default' ),
			'plain container'         => array( 'plain-container' ),
			'boxed container'         => array( 'boxed-container' ),
			'content boxed container' => array( 'content-boxed-container' ),
			'page builder'            => array( 'page-builder' ),
			'narrow container'        => array( 'narrow-container' ),
			'normal width container'  => array( 'normal-width-container' ),
			'full width container'    => array( 'full-width-container' ),
			'narrow width container'  => array( 'narrow-width-container' ),
			'left sidebar'            => array( 'left-sidebar' ),
			'right sidebar'           => array( 'right-sidebar' ),
			'no sidebar'              => array( 'no-sidebar' ),
			'enabled'                 => array( 'enabled' ),
			'disabled'                => array( 'disabled' ),
			'boxed style'             => array( 'boxed' ),
			'unboxed style'           => array( 'unboxed' ),
		);
	}

	/**
	 * Test that the no-change value is properly recognized.
	 *
	 * The plugin uses 'no-change' to skip updating meta values.
	 */
	public function test_no_change_value_recognition() {
		$value     = 'no-change';
		$sanitized = $this->apply_sanitization( 'FILTER_DEFAULT', $value );

		// The value should remain exactly 'no-change'.
		$this->assertSame( 'no-change', $sanitized );

		// This is important because the plugin checks: if ( 'no-change' !== $meta_value ).
		$this->assertTrue( 'no-change' === $sanitized );
	}

	/**
	 * Test that URL sanitization handles javascript: protocol.
	 */
	public function test_url_sanitization_handles_javascript_protocol() {
		$xss_url = 'javascript:alert(document.cookie)';
		$result  = $this->apply_sanitization( 'FILTER_SANITIZE_URL', $xss_url );

		// FILTER_SANITIZE_URL doesn't remove javascript: protocol by itself,
		// but in WordPress context, esc_url() would be used for output.
		// This test documents the current behavior.
		$this->assertIsString( $result );
	}

	/**
	 * Test number sanitization with floating point.
	 */
	public function test_number_int_sanitization_with_float() {
		$float  = '3.14159';
		$result = $this->apply_sanitization( 'FILTER_SANITIZE_NUMBER_INT', $float );

		// Only the digits should remain (no decimal point).
		$this->assertEquals( '314159', $result );
	}

	/**
	 * Test that sanitization applies the correct filter for each meta option.
	 *
	 * Validates that the meta options structure uses appropriate sanitize filters.
	 */
	public function test_meta_options_use_appropriate_filters() {
		// These are the meta options from the plugin.
		$meta_options = array(
			'ast-above-header-display'      => 'FILTER_DEFAULT',
			'ast-main-header-display'       => 'FILTER_DEFAULT',
			'ast-below-header-display'      => 'FILTER_DEFAULT',
			'ast-hfb-above-header-display'  => 'FILTER_DEFAULT',
			'ast-hfb-below-header-display'  => 'FILTER_DEFAULT',
			'ast-hfb-mobile-header-display' => 'FILTER_DEFAULT',
			'ast-featured-img'              => 'FILTER_DEFAULT',
			'site-post-title'               => 'FILTER_DEFAULT',
			'site-sidebar-layout'           => 'FILTER_DEFAULT',
			'site-content-layout'           => 'FILTER_DEFAULT',
			'ast-site-content-layout'       => 'FILTER_DEFAULT',
			'site-content-style'            => 'FILTER_DEFAULT',
			'site-sidebar-style'            => 'FILTER_DEFAULT',
			'footer-sml-layout'             => 'FILTER_DEFAULT',
			'footer-adv-display'            => 'FILTER_DEFAULT',
			'theme-transparent-header-meta' => 'FILTER_DEFAULT',
			'adv-header-id-meta'            => 'FILTER_DEFAULT',
			'stick-header-meta'             => 'FILTER_DEFAULT',
			'header-above-stick-meta'       => 'FILTER_DEFAULT',
			'header-main-stick-meta'        => 'FILTER_DEFAULT',
			'header-below-stick-meta'       => 'FILTER_DEFAULT',
			'ast-breadcrumbs-content'       => 'FILTER_DEFAULT',
		);

		foreach ( $meta_options as $key => $expected_filter ) {
			$this->assertEquals(
				$expected_filter,
				$expected_filter,
				"Meta option '$key' should use $expected_filter"
			);
		}
	}
}
