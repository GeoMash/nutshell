<?php
namespace nutshell\plugin\db\impl
{
	use \PDO;
	use \PDOException;
	
	use nutshell\plugin\db\impl\base\AbstractDb;
	use nutshell\plugin\db\exception\DbException;
	
	/**
	 * NOTE: This class REQUIRES PDO AND a PDO Driver per database type.
	 * 
	 * @author Timothy Chandler <tim.chandler@spinifexgroup.com>
	 */
	class SQLite extends AbstractDb
	{
		protected function prepareConstructorOptions(&$options) 
		{
			parent::prepareConstructorOptions($options);
			$options[self::OPT_TYPE] = self::HANDLER_SQLITE;
		}
		
		/**
		 * Specific version for SQLite
		 * (non-PHPdoc)
		 * @see nutshell\plugin\db\impl.AbstractDb::getPDOConnectionString()
		 */
		protected function getPDOConnectionString($options) 
		{
			return $options[self::OPT_TYPE] . ':' . $options[self::OPT_DB];
		}
	}
}