<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\session
{
	use nutshell\Nutshell;

	use nutshell\plugin\session\exception\SessionException;

	use nutshell\core\plugin\Plugin;
	use nutshell\core\plugin\PluginExtension;
	
	use nutshell\behaviour\Native;
	use nutshell\behaviour\Singleton;
	use nutshell\behaviour\AbstractFactory;
	
	/**
	 * @package nutshell-plugin
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @abstract
	 */
	abstract class Handler extends PluginExtension
	{
		protected static $instance = null;
		
		protected $now = null;
		
		const DEFAULT_COOKIE_LIFETIME = 0;

		const DEFAULT_COOKIE_PATH = '/';

		const DEFAULT_COOKIE_SECURE_FLAG = false;

		const DEFAULT_COOKIE_HTTPONLY_FLAG = false;

		const DEFAULT_SESSION_TIMEOUT = 1200;
		
		const DEFAULT_ID_REGENERATION = 300;
		
		const SESSION_KEY_LAST_ACTIVITY = '__activity_ts';
		
		const SESSION_KEY_LAST_ID_REGEN = '__id_regen_ts';
		
		const SESSION_STORAGE_FILE = 'file';
		
		const SESSION_STORAGE_DB = 'db';
		
		protected $timeout = self::DEFAULT_SESSION_TIMEOUT;
		
		protected $idRegenRate = self::DEFAULT_ID_REGENERATION;

		protected $acceptSessionId = false;

		public function init()
		{
			$this->now = time();
			$this->parseConfig();
			$this->initStorage();

			$name = session_name();

			// check if we allow for the client to specify its desired session id
			if($this->acceptSessionId && isset($_COOKIE[$name]) && $_COOKIE[$name])
			{
				$this->setId($_COOKIE[$name]);
			}

			$this->configureCookieParams();

			// start the session
			$this->start();
			
			//initialise the last activity timestamp if doesn't exist yet
			if(!isset($_SESSION[self::SESSION_KEY_LAST_ACTIVITY]))
			{
				$_SESSION[self::SESSION_KEY_LAST_ACTIVITY] = $this->now;
				$_SESSION[self::SESSION_KEY_LAST_ID_REGEN] = $this->now;
			}
			
			//check for session expiration
			if($this->now - $_SESSION[self::SESSION_KEY_LAST_ACTIVITY] > $this->timeout)
			{
				//discard and start a new session
				$this->destroy();
				$this->start();
				//force regenerate a new session ID
				$this->regenerateId();
			}
			
			//check for session id regeneration
			if($this->idRegenRate != false && ($this->now - $_SESSION[self::SESSION_KEY_LAST_ID_REGEN] > $this->idRegenRate))
			{
				//force regenerate a new session ID
				$this->regenerateId();
			}
			
			//update the last activity timestamp
			$_SESSION[self::SESSION_KEY_LAST_ACTIVITY] = $this->now;
		}
		
		protected function parseConfig()
		{
			if(!is_null($this->config->timeout))
			{
				$this->setTimeout($this->config->timeout);
			}
			
			if(!is_null($this->config->idRegenRate))
			{
				$this->setIdRegenRate($this->config->idRegenRate);
			}
			
			if(!is_null($this->config->acceptSessionId))
			{
				$this->acceptSessionId = $this->config->acceptSessionId != false;
			}
			
			return $this;
		}

		protected function configureCookieParams()
		{
			if(!is_null($this->config->cookieParams))
			{
				$cookieConfig = array();

				// lifetime
				$cookieConfig[] = is_null($this->config->cookieParams->lifeTime) ? self::DEFAULT_COOKIE_LIFETIME : $this->config->cookieParams->lifeTime;

				// cookie path
				$cookieConfig[] = is_null($this->config->cookieParams->path) ? self::DEFAULT_COOKIE_PATH : $this->config->cookieParams->path;

				// cookie domain
				$domain = null;
				if(!is_null($this->config->cookieParams->domain))
				{
					$domain = $this->config->cookieParams->domain;
				}
				else if(isset($_SERVER['HTTP_HOST']))
				{
					$domain = $_SERVER['HTTP_HOST'];
				}
				$cookieConfig[] = $domain;

				// cookie secure flag
				$cookieConfig[] = is_null($this->config->cookieParams->secure) ? self::DEFAULT_COOKIE_SECURE_FLAG : $this->config->cookieParams->secure;

				// cookie httponly flag
				$cookieConfig[] = is_null($this->config->cookieParams->httpOnly) ? self::DEFAULT_COOKIE_HTTPONLY_FLAG : $this->config->cookieParams->httpOnly;

				call_user_func_array('session_set_cookie_params', $cookieConfig);
			}
		}
		
		abstract protected function initStorage();
		
		public function regenerateId($deleteOldSession = null)
		{
			if(is_null($deleteOldSession) || !is_bool($deleteOldSession))
			{
				$deleteOldSession = false;
			}
			//stores the old id
			$oldId = $this->getId();
			session_regenerate_id($deleteOldSession);
			$_SESSION[self::SESSION_KEY_LAST_ID_REGEN] = $this->now;
			
			//calls a hook
			$this->regenerateIdHook($oldId, $this->getId());
			return $this;
		}
		
		protected function regenerateIdHook($oldId, $newId)
		{
			//default does nothing
		}
		
		public function getId()
		{
			return session_id();
		}

		protected function setId($sessionId)
		{
			session_id($sessionId);
		}
		
		protected function start()
		{
			session_start();
			return $this;
		}
		
		public function destroy()
		{
			//force empty
			$_SESSION = array();
			//destroy
			session_destroy();
			return $this;
		}
		
		public function setTimeout($timeout)
		{
			$this->timeout = $timeout;
			ini_set('session.gc-maxlifetime', $timeout);
			return $this;
		}
		
		public function getTimeout()
		{
			return $this->timeout;
		}
		
		public function setIdRegenRate($rate)
		{
			$this->idRegenRate = $rate;
			return $this;
		}
		
		public function getIdRegenRate()
		{
			return $this->idRegenRate;
		}
		
		public function __get($key)
		{
			return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
		}
		
		public function __set($key, $value)
		{
			$_SESSION[$key] = $value;
		}
		
		public function __isset($key)
		{
			return isset($_SESSION[$key]);
		}
		
		public function __unset($key)
		{
			unset($_SESSION[$key]);
		}
	}
}
