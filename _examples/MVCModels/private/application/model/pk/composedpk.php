<?php
// this file shows an example about how to use a table with a composed PK.
namespace application\model\pk
{
	use nutshell\plugin\mvc\model\CRUD;

	class composedpk extends CRUD
	{
		public $name	   ='composedpk';      // table name   
		public $primary	   =array('id','id2'); // array containing columns that compose the primary key.
		public $primary_ai =false;             // there is no auto increment.
		public $autoCreate =true;              // table is created if it doesn't exists.

		public $columns=array
		(
			'id'		=> 'int',
			'id2'       => 'int',
			'name'	    => 'varchar(36)',
			'i'         => 'int'
		);
	}
}