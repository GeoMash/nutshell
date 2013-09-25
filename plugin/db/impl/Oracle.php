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
	class Oracle extends AbstractDb
	{
		const DEFAULT_ORACLE_PORT = 1521;
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\db\impl.AbstractDb::prepareConstructorOptions()
		 */
		protected function prepareConstructorOptions(&$options) 
		{
			parent::prepareConstructorOptions($options);
			$options[self::OPT_TYPE] = self::HANDLER_OCI;
			
			if(!isset($options[self::OPT_PORT])) 
			{
				$options[self::OPT_PORT] = self::DEFAULT_ORACLE_PORT;
			}
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\db\impl.AbstractDb::getPDOConnectionString()
		 */
		protected function getPDOConnectionString($options) 
		{
			return $options[self::OPT_TYPE] . ':dbname=' . $options[self::OPT_HOST] . ':' . $options[self::OPT_PORT] . '/' . $options[self::OPT_DB];
		}
	}
}