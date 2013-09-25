<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\template
{
	use nutshell\plugin\template\exception\TemplateException;
	use nutshell\core\plugin\PluginExtension;
	use \Closure;
	
	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class Context extends PluginExtension
	{
		private $keyVals	=array();
		private $callbacks	=array();
		
		public function __construct()
		{
			
		}
		
		public function setKeyValArray($keyVals)
		{
			foreach ($keyVals as $key=>$val)
			{
				$this->keyVals[$key]=$val;
			}
			return $this;
		}
		
		public function setKeyVal($key,$val)
		{
			$this->keyVals[$key]=$val;
		}
		
		public function get($key)
		{
			if (isset($this->keyVals[$key]))
			{
				return $this->keyVals[$key];
			}
			return null;
		}
		
		public function __get($key)
		{
			if (isset($this->keyVals[$key]))
			{
				print $this->keyVals[$key];
			}
			print '';
		}
		
		public function registerCallback($name,Closure $closure)
		{
			$this->callbacks[$name]=$closure;
			return $this;
		}
		
		public function __call($method,$args)
		{
			if (isset($this->callbacks[$method]))
			{
				return call_user_func_array($this->callbacks[$method],$args);
			}
			else
			{
				throw new TemplateException(TemplateException::INVALID_FUNCTION, 'Invalid template function. Function "'.$method.'" has not been registered. Register with $context->registerCallback($name,$closure).');
			}
		}
	}
}
?>