<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\direct
{
	use nutshell\helper\ObjectHelper;
	use nutshell\plugin\mvc\Controller;
	use nutshell\plugin\direct\exception\DirectException;
	use nutshell\core\plugin\PluginExtension;
	use nutshell\behaviour\direct\Pollable;
	
	/**
	 * @package nutshell-plugin
	 * @author guillaume
	 */
	class Service extends PluginExtension
	{
		const DEFAULT_INTERVAL	=3000;
		
		private $controller		=null;
		private $responder		=null;
		private $ref			=null;
		private $name			=null;
		private $description	=null;
		private $version		=null;
		private $nsPrefix		=null;
		
		public function __construct(Controller $controller,$ref)
		{
			parent::__construct();
			$this->controller	=$controller;
			$this->ref			=$ref;
			$this->name			=$this->config->{$this->ref}->name;
			$this->description	=$this->config->{$this->ref}->description;
			$this->version		=$this->config->{$this->ref}->version;
			$this->nsPrefix		=$this->config->{$this->ref}->nsPrefix;
			
			/* Create a responder object which will hold 
			 * all the request responses and output them.
			 */
			$this->responder	=new Responder();
		}
		
		public function buildDescriptor()
		{
			$descriptor=array();
			foreach ($this->config->{$this->ref}->providers as $providerName=>$provider)
			{
				
				if ($provider->type=='polling')
				{
					$thisProvider=array
					(
						'type'		=>$provider->type,
						'url'		=>$this->plugin->Url->makeURL(strtolower(ObjectHelper::getBaseClassName($this->controller)).'/'.$providerName),
						'interval'	=>(isset($provider->interval))?$provider->interval:self::DEFAULT_INTERVAL,
					);
				}
				else if ($provider->type=='remoting')
				{
					$thisProvider=array
					(
						'type'		=>$provider->type,
						'url'		=>$this->plugin->Url->makeURL(strtolower(ObjectHelper::getBaseClassName($this->controller)).'/'.$providerName),
						'namespace'	=>$this->nsPrefix.'.'.$providerName,
						'actions'	=>array()
					);
					foreach ($provider->modules as $moduleName=>$module)
					{
						for ($i=0,$j=count($module); $i<$j; $i++)
						{
							$thisProvider['actions'][$moduleName][]=array
							(
								'name'	=>$module[$i]->name,
								'len'	=>$module[$i]->args
							);
						}
					}
				}
				else
				{
					throw new DirectException(DirectException::INVALID_PROVIDER_TYPE, $provider->type);
				}
				$descriptor[]=$thisProvider;
			}
			header('Content-type:application/json;');
			print json_encode($descriptor);
		}
		
		public function processRequest($provider)
		{
			if (!is_null($this->request->getRaw()))
			{
				$request=json_decode($this->request->getRaw());
			}
			//Remoting Request
			if (!empty($request))
			{
				//Non-Batch
				if (!is_array($request))
				{
					$this->handleRemotingRequest($provider,$request);
				}
				//Batch
				else
				{
					for ($i=0,$j=count($request); $i<$j; $i++)
					{
						$this->handleRemotingRequest($provider,$request[$i]);
					}
				}
			}
			//Polling Request
			else
			{
				$this->handlePollingRequest($provider);
			}
			$this->responder->send();
		}
		
		private function handleRemotingRequest($provider,$request)
		{
			if ($this->moduleExists($provider,$request->action))
			{
				$file = APP_HOME.$this->config->{$this->ref}->dir.$provider._DS_.ucfirst($request->action).'.php';
				include_once($file);
				$moduleName	='application\controller\provider\\'.$provider.'\\'.$request->action;
				$module		=new $moduleName($this->responder,$request);
				if (method_exists($module,$request->method))
				{
					if (!is_array($request->data))$request->data=array();
					call_user_func_array(array($module,$request->method),$request->data);
					$module->sendResponse();
				}
				else
				{
					die('Module "'.$provider.'::'.$request->action.'" method "'.$request->method.'" is invalid.');
				}
			}
			else
			{
				die('Module "'.$provider.'::'.$request->action.'" could not be found.');
			}
		}
		
		private function handlePollingRequest($provider)
		{
			$file = APP_HOME.$this->config->{$this->ref}->dir.$provider.'.php';
			include_once($file);
			$providerName	='application\controller\provider\\'.ucfirst($provider);
			$provider		=new $providerName($this->responder);
			if ($provider instanceof Pollable)
			{
				$provider->poll();
				$provider->sendResponse();
			}
		}
		
		public function providerExists($provider=false)
		{
			$dir = APP_HOME.$this->config->{$this->ref}->dir.$provider;
			$file = APP_HOME.$this->config->{$this->ref}->dir.$provider.'.php';
			//Remoting Provider
			if (is_dir($dir)
			//Polling Provider
			|| is_file($file))
			{
				return true;
			}
			return false;
		}
		
		private function moduleExists($provider,$module)
		{
			$file = APP_HOME.$this->config->{$this->ref}->dir.$provider._DS_.ucfirst($module).'.php';
			return is_file($file);
		}
	}
}
?>