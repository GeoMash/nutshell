<?php
/**
 * @package nutshell-plugin
 * @author guillaume
 */
namespace nutshell\plugin\url
{
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\Singleton;
	use nutshell\Nutshell;
	
	/**
	 * @author guillaume
	 * @package nutshell-plugin
	 */
	class Url extends Plugin implements Native,Singleton
	{
		private $nodes=array();
		
		public static function registerBehaviours()
		{
			
		}
		
		public function init()
		{
			
		}
		
		public function node($num)
		{
			return $this->request->node($num);
		}
		
		public function nodeEmpty($num)
		{
			return $this->request->nodeEmpty($num);
		}
		
		/**
		 * @deprecated
		 */
		public function getNodes()
		{
			return $this->request->getNodes();
		}
		
		public function makeURL($URL='',$protocol='http://')
		{
			$URL=trim($URL,'/');
			$URL=str_replace('?ns_url=','',$URL); // If we are in CLI mode, remove the ns_url tag, we will re-add it later.
			if (strstr($URL,'?'))
			{
				list($prefix,$suffix)=explode('?',$URL,2);
				$URL=trim($prefix,'/');
				$URL.=(empty($prefix)?'?':'/?').$suffix;
			}
			if (NS_INTERFACE==Nutshell::INTERFACE_CGI) // If we are in CLI mode, put ns_url in
			{ 
				$URL=str_replace('?','&',$URL);
				$return=$protocol.$_SERVER['HTTP_HOST'].'/?ns_url='.$URL;
			}
			else
			{
				$return=$protocol.$_SERVER['HTTP_HOST'].'/'.$URL;
			}
			return $return;
		}
		
		public function getCurrentURL()
		{
			return $this->makeURL().implode('/',$this->request->getNodes());
		}
	}
}
?>