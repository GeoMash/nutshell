<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\mvc
{
	use nutshell\core\plugin\PluginExtension;
	use nutshell\Nutshell;
	use nutshell\plugin\mvc\Mvc;

	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 * @abstract
	 */
	abstract class Controller extends PluginExtension
	{
		public $MVC		=null;
		public $view	=null;
		
		public function __construct(Mvc $MVC)
		{
			$this->MVC		=$MVC;
			$this->view		=new View($this->MVC);
			$this->MVC->getModelLoader()->registerContainer('model',APP_HOME.'model'._DS_,'application\model\\');
		}
		
		public function __get($key)
		{
			if ($key=='model')
			{
				return $this->MVC->getModelLoader();
			}
			else
			{
				return parent::__get($key);
			}
		}
	}
}
?>