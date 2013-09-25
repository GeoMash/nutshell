<?php
namespace nutshell\test
{
	use \PHPUnit_Framework_TestCase;
	/**
	 * Nutshell Test Suite
	 *
	 * Diagnostics
	 *
	 * @since 25/10/2011
	 */
	class Diagnostics extends PHPUnit_Framework_TestCase
	{
		public function testPHPVersion()
		{
			$this->assertTrue(version_compare(PHP_VERSION,'5.3.3','>='));
			$this->assertTrue(version_compare(PHP_VERSION,'5.4','<'));
		}
	}
}
?>