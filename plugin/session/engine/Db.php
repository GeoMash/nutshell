<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\Session\engine
{
	use nutshell\Nutshell;

	use nutshell\plugin\session\exception\SessionException;

	use nutshell\plugin\session\Handler;
	
	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class Db extends Handler
	{
		protected $activeConnector;
		
		protected $activityUpdated = false;
		
		protected $table;
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\session.Handler::parseConfig()
		 */
		protected function parseConfig()
		{
			parent::parseConfig();
			
			if(!is_null($this->config->connector))
			{
				try 
				{
					$this->activeConnector = Nutshell::getInstance()->plugin->Db->{$this->config->connector};
				}
				catch(\Exception $e)
				{
					throw new SessionException(SessionException::CONNECTOR_FAILED, sprintf("Could not instantiate %s database connector for session handler: %s", $this->config->connector, $e->getMessage()), 0, $e);
				}
			}
			else
			{
				throw new SessionException(SessionException::CONNECTOR_DEFINITION_MISSING, sprintf("Could not find a database connector definition in the config."));
			}
			
			if(!is_null($this->config->table))
			{
				if(preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $this->config->table)) 
				{
					$this->table = $this->config->table;
				}
				else 
				{
					throw new SessionException(SessionException::HANDLER_MISCONFIGURED, sprintf("Invalid table definition name in the database session handler config: %s", $this->config->table));
				}
			}
			else
			{
				throw new SessionException(SessionException::HANDLER_MISCONFIGURED, sprintf("Could not find a table definition in the database session handler config."));
			}
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\session.Handler::initStorage()
		 */
		protected function initStorage()
		{
			register_shutdown_function('session_write_close');
			session_set_save_handler(
				array($this, 'openSessionHandler'),
				array($this, 'closeSessionHandler'),
				array($this, 'readSessionHandler'),
				array($this, 'writeSessionHandler'),
				array($this, 'destroySessionHandler'),
				array($this, 'gcSessionHandler')
			);
			
			$this->activeConnector->query(<<<SQL
CREATE TABLE IF NOT EXISTS {$this->table}
(
	session_id VARCHAR(32) PRIMARY KEY,
	session_ts TIMESTAMP NOT NULL, 
	session_data BLOB NOT NULL
)		
SQL
			);
		}
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\session.Handler::regenerateIdHook()
		 */
		protected function regenerateIdHook($oldId, $newId)
		{
			$this->activeConnector->update(<<<SQL
UPDATE {$this->table} SET
	session_id = ?
WHERE
	session_id = ?
SQL
				, $oldId
				, $newId
			);
		}
		
		protected function updateActivityTs($sessionId)
		{
			if(!$this->activityUpdated)
			{
				$this->activeConnector->update(<<<SQL
UPDATE {$this->table} SET
	session_ts = CURRENT_TIMESTAMP
WHERE
	session_id = ?
SQL
					, $sessionId
				);
				$this->activityUpdated = true;
			}
		}
		
		/**
		 * Open function, this works like a constructor in classes and is executed when the session is being opened. 
		 * The open function expects two parameters, where the first is the save path and the second is the session name.
		 * @param String $savePath
		 * @param String $sessionName
		 */
		public function openSessionHandler($savePath, $sessionName)
		{
			return true;
		}
		
		/**
		 * Close function, this works like a destructor in classes and is executed when the session operation is done.
		 */
		public function closeSessionHandler()
		{
			return true;
		}

		/**
		 * Read function must return string value always to make save handler work as expected. 
		 * Return empty string if there is no data to read. 
		 * @param String $sessionId
		 */
		public function readSessionHandler($sessionId)
		{
			$this->activeConnector->select(<<<SQL
SELECT 
	session_data
FROM
	{$this->table}
WHERE
	session_id = ?

SQL
				, $sessionId
			);
			$result = $this->activeConnector->result('assoc');
			$this->updateActivityTs($sessionId);
			return $result ? $result[0]['session_data'] : "";
		}
		
		/**
		 * Write function that is called when session data is to be saved. 
		 * This function expects two parameters: an identifier and the data associated with it.
		 * @param String $sessionId
		 * @param String $sessionData
		 */
		public function writeSessionHandler($sessionId, $sessionData)
		{
			$query = <<<SQL
INSERT INTO {$this->table} 
	(session_id, session_ts, session_data)
VALUES
	(?, CURRENT_TIMESTAMP, ?) 
ON DUPLICATE KEY UPDATE
	session_ts = CURRENT_TIMESTAMP,
	session_data = ?
SQL;

			if($this->activeConnector->query($query, $sessionId, $sessionData, $sessionData))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * The destroy handler, this is executed when a session is destroyed with session_destroy() and takes the session id as its only parameter.
		 * @param String $sessionId
		 */
		public function destroySessionHandler($sessionId)
		{
			$this->activeConnector->delete(<<<SQL
DELETE FROM {$this->table} WHERE session_id = ?		
SQL
			, $sessionId
			);
			return true;
		}
		
		/**
		 * The garbage collector, this is executed when the session garbage collector is executed and takes the max session lifetime as its only parameter.
		 * @param integer $maxlifetime
		 */
		public function gcSessionHandler($maxlifetime)
		{
		
			$sql =
<<<SQL
DELETE FROM {$this->table} WHERE DATE_ADD(session_ts, INTERVAL ? SECOND) < CURRENT_TIMESTAMP
SQL;
			$this->activeConnector->delete($sql, $maxlifetime);
			return true;
		}
	}
}