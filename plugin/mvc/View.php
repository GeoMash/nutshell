<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\mvc
{
	use nutshell\core\plugin\PluginExtension;
	
	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class View extends PluginExtension
	{
		const DEFAULT_MIME_TYPE	= 'text/html';
		
		const DEFAULT_STATUS 	= Http::SUCCESS_200_OK;
		
		private $MVC			=null;
		private $templateVars	=array();
		private $viewFile		=null;
		public $templateContext	=null;
		
		private $mimeType		= self::DEFAULT_MIME_TYPE;
		
		private $status			= self::DEFAULT_STATUS;
		
		public function __construct(Mvc $MVC)
		{
			parent::__construct();
			$this->MVC=$MVC;
			
			//Register some fucntions that can be used in the template.
			$this->template=$this->plugin->Template();
			$this->templateContext=$this->template->getContext();
			
			
			$scope=$this;
			$this->templateContext->registerCallback
			(
				'loadView',
				function($viewName,$viewKeyVals=array(),$print=true) use ($scope)
				{
					$template=$scope->plugin->Template($scope->buildViewPath($viewName));
					$scope->templateContext->setKeyValArray($viewKeyVals);
					$template->setContext($scope->templateContext);
					if ($print)
					{
						print $template->compile();
					}
					else
					{
						return $template->compile();
					}
				}
			);
		}
		
		public function setTemplate($viewName)
		{
			$this->viewFile=$this->buildViewPath($viewName);
		}
		
		public function getTemplate()
		{
			return $this->viewFile;
		}
			
		public function getTemplateSource($viewName)
		{
			return $this->_render($this->buildViewPath($viewName));
		}	
		
		public function setMimeType($type = null)
		{
			if(empty($type))
			{
				$type = self::DEFAULT_MIME_TYPE;
			}
			$this->mimeType = $type;
		}
		
		public function getMimeType()
		{
			return $this->mimeType;
		}
		
		/**
		 * Sets the HTTP status header to use
		 * @param String $status the content of the status header to use
		 * @access public
		 */
		public function setStatus($status = null) 
		{
			if(empty($status))
			{
				$status = self::DEFAULT_STATUS;
			}
			$this->status = $status;
		}
		
		/**
		 * Gets the HTTP status header that will be used for rendering the view
		 * @return String the status
		 * @access public
		 */
		public function getStatus()
		{
			return $this->status;
		}
		
		protected function getProtocol() 
		{
			if($this->config->fastcgi)
			{
				return Http::FASTCGI_PROTOCOL;
			}
			
			return Http::DEFAULT_PROTOCOL;
		}
		
		public function buildViewPath($viewName)
		{
			return APP_HOME.$this->MVC->application._DS_.$this->MVC->config->dir->views.$viewName.'.php';
		}
		
		private function _render($viewFile)
		{
			$this->template->setTemplate($viewFile);
			$this->template->setKeyVal
			(
				array_keys($this->templateVars),
				array_values($this->templateVars)
			);
			return $this->template->compile();
		}
		
		public function render()
		{
			header(sprintf('Content-Type: %s', $this->mimeType));
			header(sprintf('%s %s', $this->getProtocol(), $this->status));
			
			print $this->_render($this->viewFile);
		}
		
		public function setVar($key,$value)
		{
			$this->templateVars[$key]=$value;
			return $this;
		}
		
		public function setVars(Array $keyVals)
		{
			foreach ($keyVals as $key=>$val)
			{
				$this->templateVars[$key]=$val;
			}
			return $this;
		}
		
		public function getVar($key)
		{
			return (isset($this->templateVars[$key]))?$this->templateVars[$key]:null;
		}
		
		public function getContext()
		{
			return $this->templateContext;
		}
	}
}
?>