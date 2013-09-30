<?php
namespace application\controller
{
	use nutshell\plugin\mvc\Controller;

	class Index extends Controller
	{
		public function index()
		{
			echo "<pre>";
			echo "Showing how models work in nutshell. \n";
			$this->simple_pk_examples();
			$this->composed_pk_examples();
			echo "</pre>";
		}
		
		/**
		* This method shows:
		* How to read, delete, insert, and update records using a single pk.
		*/
		private function simple_pk_examples()
		{
			echo "-----------------------\n";
			echo "Showing single pk usge. \n";
			echo "-----------------------\n";
				
			$spk = $this->model->pk->singlepk();
			
			$data = $spk->read();
			echo "\n"."Before we start, \$spk->read() should be empty:\n";
			var_dump($data);
		
			// first record
			$spk->insert(array('foo'));
			echo "\n"."Primary key isn't required because we have an auto increment column.\n";
			echo "After insert with \$spk->insert(array('foo')), \$spk->read() should show 'foo' (only 1 record) :\n";
			$data = $spk->read(); var_dump($data); // should show 1 (foo) record
			$pk = $data[0]['id'];
		
			echo "\n"."Because we have only one field in the PK, we can pass a PK as a direct value when updating, reading and deleting.\n";
			echo "After updating with \$spk->update(array('name'=>'bar'), $pk ), \$spk->read() should show 'bar' (only 1 record) :\n";
			
			$spk->update(array('name'=>'bar'), $pk );
			var_dump($spk->read()); // should show an updated record (bar) record
				
			echo "\n"."After deleting with \$spk->delete($pk), \$spk->read() should show an empty array :\n";
			$spk->delete($pk);
			var_dump($spk->read()); // should be empty
		}
		
		/**
		* This method shows:
		* How to read, delete, insert, and update records using a composed pk.
		*/
		private function composed_pk_examples()
		{
			echo "\n";
			echo "------------------------------\n";
			echo "Showing composed pk usge. \n";
			echo "------------------------------\n";
			echo "In this example, the primary key is composed by 2 fields as follows:\n";
			$pk = array('id'=>2, 'id2'=>'3');
			var_dump($pk);
			
			$cpk = $this->model->pk->composedpk();

			echo "\nReading with \$cpk->read(\$pk),it should be empty.\n";
			$data = $cpk->read($pk);
			var_dump($data); // should show and empty result.
		
			echo 
"
When inserting data into a table that has a composed pk or it isn't of the type 'auto increment',
the primary key has to be inserted as example: \$cpk->insert(array(2,3,'test',4))"; 
			
			$cpk->insert(array(2,3,'test',4));
			echo "\nAfter inserting, the following can be read with \$cpk->read(): \n";
			$data = $cpk->read();	var_dump($data); // should show 1 record
			
			echo "\nAfter updating with \$cpk->update(array('name'=>'updated test', 'i' => 400), \$pk), the following can be read with \$cpk->read(): \n";
			$cpk->update(array('name'=>'updated test', 'i' => 400), $pk);
			$data = $cpk->read();	var_dump($data); // should show an updated record.
				
			echo "\nAfter deleting with \$cpk->delete(\$pk), the following can be read with \$cpk->read(): \n";
			$cpk->delete($pk);
			$data = $cpk->read();	var_dump($data); // should show and empty result.
		}
	}
}
?>