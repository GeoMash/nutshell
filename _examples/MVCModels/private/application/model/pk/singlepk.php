<?php
// this file shows an example about how to use a table with a simple (only one column) PK.
namespace application\model\pk
{
	use nutshell\plugin\mvc\model\CRUD;

	class singlepk extends CRUD
	{
		public $name	   ='simplepk';      // table name   
		public $primary	   =array('id');     // array containing columns that compose the primary key.
		public $primary_ai =true;            // This is a table that has only 1 column as PK and it is auto increment.
		public $autoCreate =true;            // table is created if it doesn't exists.

		public $columns=array
		(
			'id'		=> 'int',
			'name'	    => 'varchar(36)',
		);		
	}
}