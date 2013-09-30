<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\logger\writer\db
{
	use nutshell\Nutshell;

	use nutshell\core\config\Config; 
	use nutshell\plugin\logger\exception\LoggerException;
	use nutshell\plugin\logger\writer\Writer;
	
	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class DbWriter extends Writer
	{
		const DEFAULT_MESSAGE_PATTERN = '%m';
		
		protected $connector = null;
		
		protected $activeConnector = null;
		
		protected $table = null;
		
		protected $initialised = false;
		
		protected function parseConfig(Config $config)
		{
			parent::parseConfig($config);
			
			$this->parseConfigOption($config, 'connector');
			$this->parseConfigOption($config, 'table');
			
			if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $this->table))
			{
				throw new LoggerException(LoggerException::INVALID_TABLE_NAME, sprintf("Invalid table name for DbWriter: %s", $this->table));
			}
			
			$this->activeConnector = Nutshell::getInstance()->plugin->Db->{$this->connector};
		}
		
		protected function installTable()
		{
			if ($this->initialised) 
			{
				return;
			}
			try
			{
				$this->activeConnector->query(<<<EOL
CREATE TABLE IF NOT EXISTS {$this->table}
(
	log_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	log_time TIMESTAMP NOT NULL,
	log_severity INT NOT NULL,
	log_logger VARCHAR(127) NOT NULL,
	log_msg TEXT NOT NULL
)
EOL
				);
				$this->initialised = true;
			}
			catch(\Exception $e)
			{
				throw LoggerException(LoggerException::COULD_NOT_LOG, sprintf("Could not log message %s", $msg), 0, $e);
			}
		}
		
		protected function doWrite($msg, $context)
		{
			try
			{
				$this->installTable();
				$this->activeConnector->insert(<<<EOL
INSERT INTO {$this->table}
(
	log_severity,
	log_logger,
	log_msg
)
VALUES
(
	?,
	?,
	?
)

EOL
					,
					$context[Writer::CTX_LOG_LEVEL],
					$context[Writer::CTX_LOGGER_NAME],
					$msg
				);
			}
			catch(\Exception $e)
			{
				throw LoggerException(LoggerException::COULD_NOT_LOG, sprintf("Could not log message %s", $msg), 0, $e);
			}
			
		}
	}
}