<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\router
{
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\Singleton;
	use nutshell\plugin\router\exception\RouterException;
	use nutshell\plugin\router\handler\Cli;
	use nutshell\plugin\router\handler\Simple;
	use nutshell\plugin\router\handler\SimpleRest;
	use nutshell\plugin\router\handler\Advanced;
	
	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class Router extends Plugin implements Native,Singleton
	{
		const MODE_CLI			='cli';
		const MODE_SIMPLE		='simple';
		const MODE_SIMPLE_REST	='simpleRest';
		const MODE_ADVANCED		='advanced';
		const MODE_REGEX		='regex';
		
		private $handler=null;
		
		/**
		 * @deprecated
		 */
		private static $handlers = array();
		
		private static $appHandlers = array();
		
		public static function registerBehaviours()
		{
			
		}
		
		/**
		 * @deprecated
		 */
		public static function registerHandler($key, $class)
		{
			return self::registerAppHandler($key, $class);
		}
		
		public static function registerAppHandler($key, $class) 
		{
			if(!isset(self::$appHandlers[$key])) 
			{
				self::$appHandlers[$key] = $class;
			}
			else 
			{
				throw new RouterException(RouterException::CANNOT_RE_REGISTER_HANDLER, sprintf("A handler is already registered for the key: %s (%s)", $key, self::$appHandlers[$key]));
			}
		}
		
		public function init()
		{
			$mode = null;
			$globalMode = $this->config->mode;
			if(!$globalMode) 
			{
				throw new RouterException(RouterException::CONFIGURATION_MISSING, 'No router mode configuration could be found');
			}
			
			if(is_array($globalMode)) 
			{
				foreach($globalMode as $modeOption) 
				{
					if($mode = $this->satisfiesOption($modeOption)) 
					{
						//interrupt the detection process since we found one matching.
						break;
					}
				}
				if(!$mode) 
				{
					throw new RouterException(RouterException::MISCONFIGURED, 'No router mode configuration option could satisfy the request');
				}
			} 
			else 
			{
				$mode = $globalMode;
			}
			
			//*** For BC Break
			if (is_string($mode))
			{
				$xmode=$mode;
				$mode=new \stdClass();
				$mode->mode=$xmode;
			}
			//***
			
			$className='nutshell\plugin\router\handler\\'.$mode->mode;
			if (class_exists($className,true))
			{
				$this->handler=new $className($mode);
				return;
			}
			else
			{
				if (isset(self::$appHandlers[$globalMode]))
				{
					$this->handler=new $appHandlers[$mode->mode]();
					return;
				}
			}
			throw new RouterException(RouterException::HANDLER_MISSING, sprintf('No implementation could be found for Router handler class: %s', $mode->mode));
		}
		
		protected function satisfiesOption($option) 
		{
			//boolean flag on whether to return the option's mode or not.
			$valid = true;
			
			if(isset($option->cond)) 
			{
				//validate interface condition
				//test only if there is a reason to keep testing
				if($valid && $interface = $option->cond->interface)
				{
					//test only if there is a reason to keep testing
					if($valid && strcasecmp(NS_INTERFACE, $interface) !== 0) {
						$valid = false;
					}
				}
			}
			
			return $valid ? $option : false;
		}
		
		public function getMode()
		{
			return $this->config->mode;
		}
		
		public function getRoute()
		{
			return $this->handler->getRoute();
		}
		
		public function getRouter()
		{
			return $this->handler;
		}
	}
}
?>