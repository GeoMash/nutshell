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
	class ODBC extends AbstractDb
	{
		const DEFAULT_ODBC_PORT = 1433;
		
		const OPT_DRIVER = 'driver';
		
		const DEFAULT_DRIVER = 'SQL Native Client';
		
		private $pdoUsername = null;
		
		private $pdoPassword = null;
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\db\impl.AbstractDb::prepareConstructorOptions()
		 */
		protected function prepareConstructorOptions(&$options) 
		{
			parent::prepareConstructorOptions($options);
			$options[self::OPT_TYPE] = self::HANDLER_ODBC;
			
			if(!isset($options[self::OPT_DRIVER])) 
			{
				$options[self::OPT_DRIVER] = self::DEFAULT_DRIVER;
			}
			
			if(!isset($options[self::OPT_PORT])) 
			{
				$options[self::OPT_PORT] = self::DEFAULT_ODBC_PORT;
			}
			
			if(isset($options[self::OPT_USERNAME])) 
			{
				$this->pdoUsername = $options[self::OPT_USERNAME];
				$this->pdoPassword = $options[self::OPT_PASSWORD];
				
				//we don't want to use the second constructor type
				unset($options[self::OPT_USERNAME]);
				unset($options[self::OPT_PASSWORD]);
			}
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\db\impl.AbstractDb::getPDOConnectionString()
		 */
		protected function getPDOConnectionString($options) 
		{
			return $options[self::OPT_TYPE] 
				. ':Driver={' . $options[self::OPT_DRIVER] . '}' 
				. ';Server=' . $options[self::OPT_HOST] . ',' . $options[self::OPT_PORT] 
				. ';Database=' . $options[self::OPT_DB] 
				. ';Uid=' . $this->pdoUsername 
				. ';Pwd=' . $this->pdoPassword . ';';
		}
	}
}