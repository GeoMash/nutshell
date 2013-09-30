<?php
namespace nutshell\plugin\formatParser
{
	use nutshell\plugin\formatParser\exception\FormatParserException;
	
	abstract class FormatParserBase
	{
		const LINE_ENDING_WIN = "\r\n";

		const LINE_ENDING_UNIX = "\n";

		const LINE_ENDING_LEGACY_MAC = "\r";

		public function __construct($values = array()) {
			
		}

		public function parse($content)
		{
			$returnValue = array();

			$this->preProcess($returnValue, $content);

			$this->process($returnValue, $content);

			$this->postProcess($returnValue);

			return $returnValue;
		}

		protected function preProcess(&$returnValue, &$content)
		{
			// do nothing
		}

		abstract protected function process(&$returnValue, &$content);

		protected function postProcess(&$returnValue)
		{
			// do nothing
		}

		public function parseFromFile($path)
		{
			$realPath = realpath($path);
			if($realPath && file_exists($realPath) && is_readable($realPath))
			{
				return $this->parse(file_get_contents($realPath));
			}
			else
			{
				throw new FormatParserException(FormatParserException::FILE_READ_ERROR, sprintf('File not found or not readable: %s', $path));
			}
		}
	}
}