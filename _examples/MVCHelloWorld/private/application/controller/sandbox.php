<?php
namespace application\controller
{
	use nutshell\plugin\mvc\Controller;

	class Sandbox extends Controller
	{
		public function index()
		{
			$var1=$this->plugin->debug;
			$var2=$this->plugin->debug(2);
			var_dump($var1);
			var_dump($var2);
//			$this->model->account->read(1);
		}
		
		public function hello($who='Noone',$what='?')
		{
			$this->view->setTemplate('hello');
			
			$this->view->setVar('title','Hello Someone');
			$this->view->setVar('who',$who);
			
			$this->view->render();
			
//			print 'Hello '.$who.'. ['.$what.']';
		}
		
		public function foo()
		{
			print $this->plugin->Url->node(2);
		}
	}
}
?>