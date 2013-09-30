<?php
namespace application\model
{
	use nutshell\plugin\mvc\model\CRUD;
	
	class Account extends CRUD
	{
		public $name	   ='account';   // This is the table name. 
		public $primary	   =array('id'); // All columns that compose the PK should be listed here.
		public $primary_ai =true;        // Default value is true. Indicates that the primary key is "auto increment".
		public $autoCreate =true;        // Default value is true. Indicates that the table has to be created when it is not found in the database.
		
		public $columns=array
		(
			'id'		=>'int(10)',
			'auth_id'	=>'varchar(36)',
			'state'		=>'text',
			'status'	=>'tinyint(1)'
		);
	}
}
?>