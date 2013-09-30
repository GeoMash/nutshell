<?php
namespace nutshell\test
{
	/**
	* This is the base test class for all Nutshell Tests.
	* This class will simply create a copy of certain items
	* (such as config) before running the individual tests,
	* then remove the copies at the end.
	*/
	class _TestCase extends PHPUnit_Framework_TestCase
	{
		protected static $DIR_TMP		=null;
		protected static $configFile	=null;
		protected static $DB			=null;
		public static $useDB			=false;
		
		public static function setUpBeforeClass()
		{
			self::$DIR_TMP		=DIR_DATA.'tmp/phpunit/';
			self::$configFile	=self::$DIR_TMP.'config.ini';
			if (!is_dir(self::$DIR_TMP))mkdir(self::$DIR_TMP,777);
			copy(DIR_BASE.'config.ini',self::$configFile);
			if (static::$useDB)
			{
				self::setUpDB();
			}
		}
		
		public static function tearDownAfterClass()
		{
			foreach 
			(
				new RecursiveIteratorIterator
				(
					new RecursiveDirectoryIterator(self::$DIR_TMP),
					RecursiveIteratorIterator::CHILD_FIRST
				) as $iteration
			)
			{
				if ($iteration->getFileName()=='.trash')continue;
				if ($iteration->isDir())
				{
					rmdir($iteration->getPathname());
				}
				else
				{
					unlink($iteration->getPathname());
				}
			}
			if (static::$useDB)
			{
				self::tearDownDB();
			}
		}
		
		public static function setUpDB()
		{
			require_once(DIR_HAL.'HAL.php');
			self::$DB=HAL::getDBConnection();
			self::$DB->query(file_get_contents(DIR_DB.'setup.sql'));
		}
		
		public static function tearDownDB()
		{
			self::$DB->query(file_get_contents(DIR_DB.'teardown.sql'));
		}
	}
}
?>