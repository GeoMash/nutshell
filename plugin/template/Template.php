<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\template
{
	use nutshell\plugin\template\exception\TemplateException;
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\Factory;
	
	/**
	 * @package nutshell-plugin
	 * @author guillaume
	 */
	class Template extends Plugin implements Native,Factory
	{
		private $templateFile	=null;
		private $template		=null;
		private $context		=null;
		private $compiled		=null;
		
		public static function registerBehaviours()
		{
			
		}
		
		public function init($template=null)
		{
			if ($template)$this->setTemplate($template);
			$this->context=new Context();
		}
		
		public function setTemplate($template)
		{
			if (is_file($template))
			{
				if (is_readable($template))
				{
					$this->templateFile=$template;
				}
				else
				{
					throw new TemplateException(TemplateException::CANNOT_READ_FILE, 'Unable to load template. File is unreadable. FILE: "'.$template.'".');
				}
			}
			else
			{
				throw new TemplateException(TemplateException::FILE_MISSING, 'Unable to load template. File is missing. FILE: "'.$template.'".');
			}
		}
		
		public function setKeyVal($key,$val)
		{
			$this->context->setKeyVal($key,$val);
			return $this;
		}
		
		public function setKeyValArray(Array $keyVals)
		{
			$this->context->setKeyValArray($keyVals);
			return $this;
		}
		
		public function compile($clear=true)
		{
			$tpl=$this->context;
			$closedScopeClosure=function($templateFile) use (&$tpl)
			{
				ob_start();
				include($templateFile);
				$compiled=ob_get_contents();
				ob_end_clean();
				return $compiled;
			};
			$this->compiled=$closedScopeClosure($this->templateFile);
			return $this->compiled;
		}
		
		public function getCompiled()
		{
			if (is_null($this->compiled))
			{
				$this->compile();
			}
			return $this->compiled;
		}
		
		public function setContext(Context $context)
		{
			$this->context=$context;
			return $this;
		}
		
		public function getContext()
		{
			return $this->context;
		}
	}
}
?>