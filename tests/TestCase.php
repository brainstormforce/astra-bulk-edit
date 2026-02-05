<?php
/**
 * Base test case for standalone unit tests (without WordPress).
 *
 * @package Astra_Bulk_Edit
 */

namespace Astra_Bulk_Edit\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case class.
 */
class TestCase extends PHPUnitTestCase {

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tearDown(): void {
		parent::tearDown();
	}
}
