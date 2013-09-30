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
	class MySQL extends AbstractDb
	{
		const DEFAULT_MYSQL_PORT = 3306;
		
		protected function prepareConstructorOptions(&$options) 
		{
			parent::prepareConstructorOptions($options);
			$options[self::OPT_TYPE] = self::HANDLER_MYSQL;
			
			if (!isset($options[self::OPT_PORT]))
			{
				$options[self::OPT_PORT] = self::DEFAULT_MYSQL_PORT;
			}
		}
		
		/**
		 * This function returns all tables in the connected MySQL schema (database).
		 */
		public function getTablesFromMysqlSchema()
		{
			$sql =
<<<SQL
			select 
				TABLE_NAME, TABLE_TYPE, ENGINE, VERSION, TABLE_ROWS, AVG_ROW_LENGTH, DATA_LENGTH, MAX_DATA_LENGTH, 
				INDEX_LENGTH, DATA_FREE, AUTO_INCREMENT, CREATE_TIME, UPDATE_TIME, CHECK_TIME, TABLE_COLLATION, TABLE_COMMENT 
			from information_schema.tables 
			where table_schema = ? 
			order by TABLE_NAME ASC
SQL;
			
			return $this->getResultFromQuery($sql, $this->dbSchema);
		}
		
		/**
		 * This method is very similar to getTablesFromMysqlSchema. But this method has an additional column called COLUMNS that has an
		 * array with column information.
		 */
		public function getTablesFromMysqlSchemaWithColumnInfo()
		{
			$tables = $this->getTablesFromMysqlSchema();
			foreach($tables as &$table_data)
			{
				$table_data['COLUMNS'] = $this->getColumnsFromMysqlTable($table_data['TABLE_NAME']);
			}
			return $tables;
		}
		
		/**
		 * This function returns all columns in the table $table_name from the connected MySQL schema.
		 */
		public function getColumnsFromMysqlTable($table_name)
		{
			$sql =
<<<SQL
			select 
				COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, 
				CHARACTER_MAXIMUM_LENGTH, CHARACTER_OCTET_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, 
				CHARACTER_SET_NAME, COLLATION_NAME, COLUMN_TYPE, COLUMN_KEY, EXTRA, COLUMN_COMMENT 
			from information_schema.columns 
			where 
				table_schema = ? and 
				table_name = ? 
			order by ORDINAL_POSITION ASC
SQL;
			
			return $this->getResultFromQuery($sql, $this->dbSchema, $table_name);
		}
		
		public function setCharset($charset) 
		{
			$this->connection->query('SET NAMES ' . $charset);
		}
	}
}