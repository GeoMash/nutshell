  <?php
namespace application\controller
{
	use nutshell\plugin\mvc\Controller;

	class Index extends Controller
	{
		public function index($who='World')
		{
			//Load the template into the view.
			$this->view->setTemplate('hello');
			
			//Set some template vars.
			$this->view->setVar('title','Hello Someone');
			$this->view->setVar('who',$who);
			
			//Render the view.
			$this->view->render();
		}
	}
}
?>