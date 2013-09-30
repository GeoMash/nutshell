<?php
namespace nutshell\plugin\format
{
	/**
	 * This class has been ported from Leadgen v1. It's previous name is Application_Model_Format_DSV.
	 */
	class PHPTemplate extends FormatBase
	{
		/**
		 * This property stores the template to be used when formatting.
		 * @var string
		 */
		protected $templateStr = '';
		
		/**
		 * This property stores the error message found while processing the last record.
		 * @var string
		 */
		protected $templateErrorMessage = '';
		
		/**
		 * Sets a template to be used when formatting.
		 * @param string $str
		 */
		public function setTemplate($str)
		{
			$this->templateStr = $str;
		}
		
		/**
		 * This function returns the error messsage of the last processed record.
		 * @return string
		 */
		public function getLastTemplateErrorMessage()
		{
			return $this->templateErrorMessage;
		}
		
		/**
		 * This function encodes a record into the objects template.
		 * @param array $record
		 * @return string
		 */
		public function encode($record, $caseInsensitive = true)
		{
			ob_start();
			try 
			{
				\nutshell\plugin\format\strictTemplate\evalInStrictNameSpace('?>'  . $this->templateStr, $record, $caseInsensitive);
				$output = ob_get_clean();
				$this->templateErrorMessage = '';
			} catch (NutshellException $e)
			{
				$output = null;
				ob_end_clean();
				$this->templateErrorMessage = $e->getMessage();
			}

			return $output;
		}
		
		/**
		 * This function sets a template and econdes a string.
		 * @param array $record
		 * @param string $pTemplate
		 * @return string
		 * @see encode
		 * @see setTemplate
		 */
		public function encodeWithTemplate($record, $pTemplate, $caseInsensitive = true)
		{
			$this->setTemplate($pTemplate);
			return $this->encode($record, $caseInsensitive);
		}

		/**
		 * Writes a record encoded using the current template.
		 * @see nutshell\plugin\format.FormatBase::writeRecord()
		 */
		protected function writeRecord($batch)
		{
			$encodedStr = $this->encode($batch);

			$this->writef($encodedStr);
		}
	}
}