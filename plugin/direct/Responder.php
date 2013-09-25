<?php
/**
 * @package nutshell-plugin
 */
namespace nutshell\plugin\direct
{
	/**
	 * @package nutshell-plugin
	 */
	class Responder
	{
		private $responses=array();
		
		public function push($response)
		{
			$this->responses[]=$response;
			return $this;
		}
		
		public function send()
		{
			header('content-type:application/json');
			if (count($this->responses)===1)
			{
				exit(json_encode($this->responses[0]));
			}
			else
			{
				exit(json_encode($this->responses));
			}
		}
	}
}
?>