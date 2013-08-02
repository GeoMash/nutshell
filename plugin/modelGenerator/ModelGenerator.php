<?php
/**
 * ModelGenerator is used to generate table models. Follows an usage example:
 * $mg = $this->plugin->ModelGenerator;
 * $modelcode = $mg->getModelStrFromTable('tbl_formats', 'tbl\\', 'format');
 * 
 * @package nutshell-plugin
 * @author joao
 */
namespace nutshell\plugin\modelGenerator
{
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\Singleton;
	use nutshell\Nutshell;
	use nutshell\helper\StringHelper;
	use nutshell\plugin\modelGenerator\exception\ModelGeneratorException;

	/**
	 * @author joao
	 * @package nutshell-plugin
	 */
	class ModelGenerator extends Plugin implements Native,Singleton
	{
		protected $db;
		
		private $baseClassNamespace		= 'nutshell\\plugin\\mvc\\model\\';
		private	$baseClass				= 'CRUD';
		private $applicationNamespace	='';
		
		public function getBaseClassNamespace()
		{
		    return $this->baseClassNamespace;
		}
		
		public function setBaseClassNamespace($newBaseClassNamespace)
		{
		    $this->baseClassNamespace = $newBaseClassNamespace;
		    return $this;
		}
		
		public function setAppliacationNamespace($applicationNamespace)
		{
			$this->applicationNamespace=$applicationNamespace;
			return $this;
		}
		
		public function getBaseClass()
		{
		    return $this->baseClass;
		}
		
		public function setBaseClass($newBaseClass)
		{
		    $this->baseClass = $newBaseClass;
		    return $this;
		}
		
		public static function registerBehaviours()
		{
			
		}
		
		public function init()
		{
			if ($connection=Nutshell::getInstance()->config->plugin->Mvc->connection)
			{
				$this->db=Nutshell::getInstance()->plugin->Db->{$connection};
			}
			else
			{
				throw new ModelGeneratorException(ModelGeneratorException::DB_NOT_CONFIGURED, 'Model generator can\'t find a db connection.');
			} 
		}
		
		/**
		 * This function returns a PHP code containing an array with columns that compose the primary key.
 		 * @param array $tableStructure
		 * @return string
		 */
		protected function getPKArray(&$tableStructure)
		{
			$PKs = array();
			foreach($tableStructure as &$column)
			{
				if ($column['COLUMN_KEY']=='PRI')
				{
					$PKs[] = "'".$column['COLUMN_NAME']."'";
				}
			}
			return 'array('.implode(',',$PKs).')';
		}
		
		/**
		 * This function returns a PHP code containing an array with column definition.
 		 * @param array $tableStructure
		 * @return string
		 */
		protected function getColumnDefinition(&$tableStructure)
		{
			$rows = array();
			foreach($tableStructure as &$column)
			{
				$column_name    = $column['COLUMN_NAME'];
				$column_type    = $column['COLUMN_TYPE'];
				$column_comment = trim(StringHelper::removeCrLf($column['COLUMN_COMMENT']));
				
				if (strlen($column_comment)>0)
				{
					$column_comment = ' /* '.$column_comment.' */ ' ;
				}
				
				if ($column['IS_NULLABLE']=='NO')
				{
					$column_type = $column_type.' NOT NULL ';
				}
				
				$column_type = str_replace("'", "\'", $column_type);
				
				$rows[] = "\t\t\t'$column_name' => '$column_type'{$column_comment} ";
			}

			return "array\n\t\t(\n".implode(",\n", $rows)."\n\t\t)";
		}

		/**
		 * This function indicates if auto increment is used.
		 * @param array $tableStructure
		 * @return boolean
		 */
		protected function isAutoIncrement(&$tableStructure)
		{
			$result = false;
			foreach($tableStructure as &$column)
			{
				$result = $result || ($column['EXTRA']=='auto_increment');
			}
			return $result;
		}
		
		/**
		 * This function returns a PHP code containing a full database model code for a given table.
 		 * @param array $table_name  Name of the table in the database.
 		 * @param array $folder      Folder inside the "model" folder. In many cases this will be an empty string. In the case you provide a folder name, it has to end with a '\\'.
 		 * @param array $model_name  This is the class name
 		 * @param array $autoCreate  Indicates if tables should be created when missing. In most cases this value should be false.
		 * @return string
		 */
		public function getModelStrFromTable($table_name, $folder, $model_name, $autoCreate = false, $table_comment='')
		{
			$tableStructure		= $this->db->getColumnsFromMysqlTable($table_name);
			$pk_array_str		= $this->getPKArray($tableStructure);
			$primary_ai_str		= $this->isAutoIncrement($tableStructure) ? "true" : "false";
			$autoCreate_str		= $autoCreate ? "true" : "false";
			$column_definition	= $this->getColumnDefinition($tableStructure);
			
			$table_comment = trim(StringHelper::removeCrLf($table_comment));
			
			if (strlen($table_comment)>0)
			{
				$table_comment = '// '.$table_comment."\n	";
			}

			// make sure the linux '/' are converted to namespace '\'
			// FIXME we need to be able to customise the base namespace, not only application\model\*
			$namespace = sprintf('%smodel%s', $this->applicationNamespace ,str_replace('/', '\\', $folder));
			
			
			$template	=$this->plugin->Template(__DIR__._DS_.'model.tpl.php');
			$template	->setKeyVal('table_name',		$table_name)
						->setKeyVal('folder',			$folder)
						->setKeyVal('namespace',		$namespace)
						->setKeyVal('table_comment',	$table_comment)
						->setKeyVal('model_name',		$model_name)
						->setKeyVal('pk_array_str',		$pk_array_str)
						->setKeyVal('primary_ai_str',	$primary_ai_str)
						->setKeyVal('autoCreate_str',	$autoCreate_str)
						->setKeyVal('column_definition',$column_definition)
						->setKeyVal('baseClass',		$this->getBaseClass())
						->setKeyVal('baseClassNamespace',$this->getBaseClassNamespace());
			
			$template->compile();
			
			return $template->getCompiled();
		}
		
	}// class
}// namespace