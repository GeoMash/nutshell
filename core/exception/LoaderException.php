<?php
/**
 * @package nutshell
 * @author Dean Rather
 */
namespace nutshell\core\exception
{
	use nutshell\core\exception\NutshellException;

	class LoaderException extends NutshellException
	{
		const CANNOT_AUTOLOAD_CLASS = 1;
		
		const CANNOT_LOAD_KEY = 2;
		
		const CANNOT_LOAD_CLASS = 3;
		
		/**
		 * This method is called when an exception happens.
		 * @param Exception $exception
		 */
		public static function treatException($exception, $format='html')
		{
			if (NS_INTERFACE!=Nutshell::INTERFACE_PHPUNIT)
			{
				parent::treatException($exception,$format);
			}
			// throw $exception;
		}
		
		/**
		 * This function sets exception/error handlers. Before this call, no error is treated by this class.
		 * All errors are logged.
		 * Errors are shown in the user interface only if NS_ENV (environment variable) is set to "dev". So, errors won't be shown in production.
		 * 
		 * Sets the default Exception Handler to treatException()
		 * Sets the default Error Handler to treatError()
		 */
		public static function setHandlers()
		{
			self::$oldExceptionHandler = set_exception_handler('nutshell\core\exception\LoaderException::treatException');
			self::$oldErrorHandler = set_error_handler('nutshell\core\exception\LoaderException::treatError');
			register_shutdown_function('nutshell\core\exception\NutshellException::shutdown'); 
		}
	}
}