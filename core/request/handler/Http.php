<?php
namespace nutshell\core\request\handler
{
	use nutshell\core\request\Handler;
	use nutshell\core\exception\RequestException;
	
	class Http extends Handler
	{
		protected function setupNodes()
		{
			$baseURL = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			if(defined('NS_APP_WEB_HOME'))
			{
				$baseURL = preg_replace('/^' . preg_quote(NS_APP_WEB_HOME, '/') . '/', '', $baseURL);
			}
			$nodes=explode('/', $baseURL);
			if (reset($nodes)=='')	array_shift($nodes);
			if (end($nodes)=='')	array_pop($nodes);
			if (!isset($nodes[0]))
			{
				$nodes[0]='';
			}
			if (substr(current($nodes),0,1)=='?')
			{
				array_pop($nodes);
			}
			$this->nodes=$nodes;
		}
		
		protected function setupData()
		{
			$this->data =&$_REQUEST;
			$this->raw	= file_get_contents('php://input');
		}
		
		protected function gatherFiles()
		{
			$this->files=&$_FILES;
		}
		
		
		public function moveFile($ref,$destination)
		{
			$files=$this->getFiles();
			if (isset($files[$ref]))
			{
				$pathinfo=pathinfo($destination);
				//Is a dir?
				if (!isset($pathinfo['extension']))
				{
					$testDir=$destination;
					$moveTo	=$destination._DS_.$files[$ref]['name'];
				}
				//Nope, is a file.
				else
				{
					$testDir=$pathinfo['dirname'];
					$moveTo	=$destination;
				}
				if (!is_dir($testDir))
				{
					if (!mkdir($testDir,0777,true))
					{
						throw new RequestException(RequestException::FILE_PERMISSION,'Permission error. Unable to create upload move destination folder.');
					}
				}
				if (move_uploaded_file($files[$ref]['tmp_name'], $moveTo))
				{
					return true;
				}
				else
				{
					throw new RequestException(RequestException::FILE_INVALID_FILE,'Security exception. Unable to move invalid file ref "'.$ref.'".');
				}
			}
			else
			{
				throw new RequestException(RequestException::FILE_INVALID_REF,'Invalid file ref "'.$ref.'". Are you specifying the correct field name? Did you set enctype="multipart/form-data" in the form?');
			}
		}
		
		public function getFileError($ref)
		{
			$files=$this->getFiles();
			if (isset($files[$ref]))
			{
				switch ($files[$ref]['error'])
				{
					case UPLOAD_ERR_INI_SIZE:	return 'File size exceeds file size limit of '.ini_get('upload_max_filesize').'. (ini)';
					case UPLOAD_ERR_FORM_SIZE:	return 'File size exceeds file size limit of '.$this->get('MAX_FILE_SIZE').'. (form)';
					case UPLOAD_ERR_PARTIAL:	return 'Upload interrupted. Only received part of the file.';
					case UPLOAD_ERR_NO_FILE:	return 'No file was uploaded';
					case UPLOAD_ERR_NO_TMP_DIR:	return 'Temporary upload folder has not been configured.';
					case UPLOAD_ERR_CANT_WRITE:	return 'Write permission error. Unable to store uploaded file.';
					case UPLOAD_ERR_EXTENSION:	return 'File upload blocked by PHP extension.';
					case UPLOAD_ERR_OK:
					default:					return false;
				}
			}
			else
			{
				throw new RequestException(RequestException::FILE_INVALID_REF,'Invalid file ref "'.$ref.'". Are you specifying the correct field name? Did you set enctype="multipart/form-data" in the form?');
			}
		}
	}
}
?>
