<?php
namespace hedronium\Jables;

use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Seld\JsonLint\JsonParser;
use Illuminate\Database\Schema\Blueprint;

class Runner
{
	protected $fs = null;
	protected $db_manager = null;
	protected $db = null;
	protected $parser = null;
	protected $loader = null;

	protected $tables = [];
	protected $types = [];
	protected $foreigns = [];

	protected $app = null;

	protected function buildTableList()
	{
		$files = $this->fs->files($this->app->databasePath().'/'.config('jables.folder'));

		foreach ($files as $file) {
			if ($this->fs->extension($file) == 'json') {
				$this->tables[$this->fs->name($file)] = $this->parser->parse($this->loader->get($file)); 
			}
		}
	}

	/**
	 *  Injecting FileSystem , DatabaseManager and loader Class
	 */

	public function __construct($app, Filesystem $fs, DatabaseManager $db, Loader $loader)
	{
		$this->app = $app;
		$this->fs = $fs;
		$this->db_manager = $db;
		$this->loader = $loader;
		$this->parser = new JsonParser;
	}

	

	public function createTable()
	{
		$builder = $this->db->getSchemaBuilder();

		$table = config('jables.table');

		if ($builder->hasTable($table)) {
			return null;
		}

		$builder->create($table, function(Blueprint $table){
			$table->increments('id');
			$table->longText('data');

			$table->timestamps();
		});

		return true;
	}

	public function connection($connection = null)
	{
		$this->db = $this->db_manager->connection($connection);
	}

	protected function fieldTypeObject($type)
	{
		if (isset($this->types[$type])) {
			return $this->types[$type];
		}

		$obj = null;

		switch ($type) {
			case 'big-integer':
				$obj = new types\BigInteger;
				break;
			case 'binary':
				$obj = new types\Binary;
				break;
			case 'boolean':
				$obj = new types\Boolean;
				break;
			case 'char':
				$obj = new types\Char;
				break;
			case 'date-time':
				$obj = new types\DateTime;
				break;
			case 'date':
				$obj = new types\Date;
				break;
			case 'decimal':
				$obj = new types\Decimal;
				break;
			case 'double':
				$obj = new types\Double;
				break;
			case 'enum':
				$obj = new types\Enum;
				break;
			case 'float':
				$obj = new types\Float;
				break;
			case 'integer':
				$obj = new types\Integer;
				break;
			case 'json':
				$obj = new types\Json;
				break;
			case 'jsonb':
				$obj = new types\Jsonb;
				break;
			case 'long-text':
				$obj = new types\LongText;
				break;
			case 'medium-integer':
				$obj = new types\MediumInteger;
				break;
			case 'medium-text':
				$obj = new types\MediumText;
				break;
			case 'morphs':
				$obj = new types\Morphs;
				break;
			case 'small-integer':
				$obj = new types\SmallInteger;
				break;
			case 'string':
				$obj = new types\String;
				break;
			case 'text':
				$obj = new types\Text;
				break;
			case 'timestamp':
				$obj = new types\Timestamp;
				break;
			case 'time':
				$obj = new types\Time;
				break;
			case 'tiny-integer':
				$obj = new types\TinyInteger;
				break;
		}

		$this->types[$type] = $obj;

		return $obj;
	}

	protected function field($table, $name, $field)
	{
		$obj = $this->fieldTypeObject($field->type);
		$obj->setSchema($field);
		$obj->setTable($table);
		$obj->setName($name);

		$obj->initField();
		$obj->render();
		$obj->base();
	}

	public function foreigns()
	{
		$builder = $this->db->getSchemaBuilder();

		foreach ($this->foreigns as $table => $foreigns) {
			$table_name = $table;
			$builder->table($table, function($table) use ($foreigns, $table_name) {
				
				foreach ($foreigns as $field => $foreign) {
					
					list($foreign_table, $foreign_field) = explode('.', $foreign);
					$table->foreign($field)->references($foreign_field)->on($foreign_table);
				
				}

			});

		}
	}

	public function up()
	{
		$this->buildTableList();
		
		$creator = function(Blueprint $table, $table_name, $definition, $uniques){
			
			foreach ($definition->fields as $name=>$field) {
				
				if ($name === 'timestamps') {
					$table->timestamps();
				} elseif ($name === 'soft-deletes') {
					$table->softDeletes();
				} else {
					$this->field($table, $name, $field);

					if (isset($field->foreign)) {
						$this->foreigns[$table_name][$name] = $field->foreign;
					}

				}

			}

			foreach ($uniques as $unique) {
				$table->unique($unique);
			}

		};

		$creator->bindTo($this);

		foreach ($this->tables as $name => $definition) {

			$builder = $this->db->getSchemaBuilder();

			$this->foreigns[$name] = [];

			if (isset($definition->foreign)) {
				$this->foreigns[$name] = (array) $definition->foreign;
			}

			$uniques = [];
			
			if (isset($definition->unique)) {
				$uniques = (array) $definition->unique;
			}

			$builder->create($name, function(Blueprint $table) use ($creator, $name, $definition, $uniques){
				
				$creator($table, $name, $definition, $uniques);

			});

		}

		$log = new JablesTableModel();
		$log->setConnection($this->db->getName());
		$log->data = json_encode($this->tables);
		$log->save();
	}
}