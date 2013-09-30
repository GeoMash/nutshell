<?php
/**
 * @package nutshell-plugin
 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
 */
namespace nutshell\plugin\logger\writer\file
{
	use nutshell\plugin\logger\writer\file\FileWriter;
	
	/**
	 * @author Guillaume Bodi <guillaume@spinifexgroup.com>
	 * @package nutshell-plugin
	 */
	class DailyFileWriter extends FileWriter
	{
		
		/**
		 * (non-PHPdoc)
		 * @see nutshell\plugin\logger\writer\file.FileWriter::resolveOutputPlaceHolders()
		 */
		protected function resolveOutputPlaceHolders($path)
		{
			return str_replace(
				array('{DATE}'),
				array(date('Y-m-d')),
				parent::resolveOutputPlaceHolders($path)
			);
		}
	}
}