<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\mvc
{
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\Singleton;
	use nutshell\core\loader\Loader;
	use nutshell\plugin\router\Router;
	use nutshell\plugin\mvc\exception\MvcException;
	
	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class Mvc extends Plugin implements Native,Singleton
	{
		private $modelLoader	=null;
		private $controller		=null;
		private $router			=null;
		private $route			=null;
		
		public $application		=null;
		
		public static function registerBehaviours()
		{
			
		}
		
		public function init($application)
		{
			//Bind the application.
			$this->application=$application;
			
			//Setup a model loader.
			$this->modelLoader=new Loader();
			
			//Get the router handler.
			$this->router	=$this->plugin->Router->getRouter();
			
			//Get the route from the rotuer.
			$this->route	=$this->plugin->Router->getRoute();
			
			$this->initController();
		}
		
		public function initController()
		{
			//Load the controller.
			$this->loadController();
			
			//Construct the namespaced class name.
			$className='application\\'.$this->application.'\controller\\'.$this->route->getControlNamespace().$this->route->getControl();
			//Initiate the controller.
			$this->controller=new $className($this);
			
			/* A hack to get around PHP4's constructor.
			 * This only applies to version of PHP 5.3 lower than 5.3.3.
			 * 
			 * We don't kick off the action if the action is the same
			 * name as the constructor.
			 * 
			 * 
			 * Note that the default action cannot accept any args.
			 * So nothing is passed to the constructor.
			 */
			if (method_exists($this->controller,$this->route->getAction())
			|| method_exists($this->controller,'__call'))
			{
				if (version_compare(PHP_VERSION,'5.3.3','>='))
				{
					//Call the controller action.
					call_user_func_array
					(
						array($this->controller,$this->route->getAction()),
						$this->route->getArgs()
					);
				}
				else if ($this->route->getControl()!=$this->route->getAction())
				{
					call_user_func_array
					(
						array($this->controller,$this->route->getAction()),
						$this->route->getArgs()
					);
				}
			}
			else
			{
				throw new MvcException(MvcException::ACTION_NOT_DEFINED, 'Unable to execute action. The action "'.$this->route->getAction().'" is not defined or is not a public method.');
			}
		}
		
		/**
		 * Looks at $this->route to load the appropriate controller
		 * Works its way through URI nodes until it finds a controller with the name of that node.
		 * If a file exists in the controllers directory by that name, includes the file and returns 
		 * Otherwise, if a directory exists inside the controllers directory by this name, It looks in there.
		 * If, inside the directory by this node's name, there is an Index.php, it loads that.
		 * Otherwise, it tries to load a file by the name of the next uri node's name.
		 * If there is not an index file, or a file by the node's name, it looks into a folder by that node's name and proceeds.
		 * Note that folders must be named in lowerCamelCase, and files must be named in UpperCamelCase -- otherwise your site will die on linux servers
		 */
		public function loadController()
		{
			// The first URI node
			$controller	=str_replace('\\','/',$this->route->getControl());
			
			// The Directory with controllers in it
			$dir=APP_HOME.$this->application._DS_.$this->config->dir->controllers;
			
			// Maybe we're at website.com/hello/. Check the directory for a Hello.php
			// $file=$dir.ucFirst($controller).'.php';
			
			$triedFiles=array();
			
			// This loop will be broken out from once we find the file. Otherwise it will throw an error.
			while (true)
			{
				//Retarded cross-platform hack.
				$file=$dir.$controller.'.php';
				$triedFiles[] = $file;
				// die($file);
				if (is_file($file))
				{
					break;
				}
				$file=$dir.ucFirst($controller).'.php';
				$triedFiles[] = $file;
				if (is_file($file))
				{
					break;
				}
				else if (is_dir($dir))
				{
					// Now check for a directory by this node's name. Directories are all lowerCamelCase
					$dir.=lcFirst($controller)._DS_;
					
					// Update the controller to point to the next URI node 
					$this->router->advancePointer();
					$controller=$this->route->getControl();
					
					// If we are on the final node (controller is null) then Check this directory for an Index.php
					$indexFile=$dir.lcFirst($controller).'Index.php';
					
					if (!$controller && is_file($indexFile))
					{
						$this->router->advancePointer();
						$this->route->setControl('Index');
						// $dir.=_DS_.lcFirst($controller);
						$file=$dir.'Index.php';
						break;
					}
					
					// Update the file to look for a file the new node's name in the directory
					$file=$dir.ucFirst($controller).'.php';
				}
				else
				{
					$files=implode("<br>", $triedFiles);
					throw new MvcException(MvcException::INVALID_FILE, "Unable to load controller. <br>None of the following files exist:<br>$files");
				}
			}
			include_once($file);
		}
		
		public function getModelLoader()
		{
			return $this->modelLoader;
		}
		
		public function __get($key)
		{
			if ($key=='model')
			{
				return $this->getModelLoader();
			}
			else
			{
				return parent::__get($key);
			}
		}
	}
}
?>