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
			$table->enum('type', ['table', 'delta', 'foreign']);
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

		$created_foreigns = [];

		foreach ($this->foreigns as $table => $foreigns) {
			$table_name = $table;
			$builder->table($table, function($table) use ($foreigns, $table_name, $builder, &$created_foreigns) {
				foreach ($foreigns as $field => $foreign) {

					list($foreign_table, $foreign_field) = explode('.', $foreign);

					if ($builder->hasTable($foreign_table)) {
						$created_foreigns[] = $table_name.'_'.$field.'_foreign';
						$table->foreign($field)->references($foreign_field)->on($foreign_table);
					}
				}
			});
		}

		$log = new JablesTableModel();
		$log->setConnection($this->db->getName());
		$log->type = 'foreign';
		$log->data = json_encode($created_foreigns);
		$log->save();
	}

	public function up(array $table_names = [], $engine = null, $onerror = null)
	{
		$creator = function(Blueprint $table, $table_name, $definition, $uniques) use ($engine) {
			if ($engine) {
				$table->engine = $engine;
			}

			foreach ($definition->fields as $name => $field) {

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

		if (!$table_names) {
			$table_names = $this->loader->names();
		}

		$tables = [];

		foreach ($table_names as $name) {
			$definition = $this->loader->get($name);

			if (!$definition) {
				$msg = "table definition for '$name' doesn't exist.";

				if ($onerror) {
					$onerror($msg);
				} else {
					throw new \Exception($msg);
				}

				continue;
			}

			$tables[$name] = $definition;
			$builder = $this->db->getSchemaBuilder();

			$this->foreigns[$name] = [];

			if (isset($definition->foreign)) {
				$this->foreigns[$name] = (array) $definition->foreign;
			}

			$uniques = [];

			if (isset($definition->unique)) {
				$uniques = (array) $definition->unique;
			}

			$builder->create($name, function(Blueprint $table) use ($creator, $name, $definition, $uniques) {
				$creator($table, $name, $definition, $uniques);
			});
		}

		$log = new JablesTableModel();
		$log->setConnection($this->db->getName());
		$log->type = 'table';
		$log->data = json_encode($tables);
		$log->save();
	}
}
