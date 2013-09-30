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
	class MSSQL extends AbstractDb
	{
		const DEFAULT_MSSQL_PORT = 1433;
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\db\impl.AbstractDb::prepareConstructorOptions()
		 */
		protected function prepareConstructorOptions(&$options) 
		{
			parent::prepareConstructorOptions($options);
			
			if(!isset($options[self::OPT_TYPE])) 
			{
				$options[self::OPT_TYPE] = self::HANDLER_MSSQL;
			}
			
			if ($options[self::OPT_TYPE] == self::HANDLER_MSSQL && !isset($options[self::OPT_PORT]))
			{
				$options[self::OPT_PORT] = self::DEFAULT_MSSQL_PORT;
			}
		}
	}
}