<?php
namespace hedronium\Jables;

use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Seld\JsonLint\JsonParser;
use Illuminate\Database\Schema\Blueprint;

class Runner
{
	protected $fs = null;
	protected $db = null;
	protected $parser = null;

	protected $tables = [];
	protected $types = [];

	protected function buildTableList()
	{
		$files = $this->fs->files('database/jables');

		foreach ($files as $file) {
			if ($this->fs->extension($file) == 'json') {
				$this->tables[$this->fs->name($file)] = $this->parser->parse($this->fs->get($file)); 
			}
		}
	}

	public function __construct(Filesystem $fs, DatabaseManager $db) {
		$this->fs = $fs;
		$this->db = $db->connection();
		$this->parser = new JsonParser;

		$this->buildTableList();
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

	public function up()
	{
		$creator = function(Blueprint $table, $definition){
			foreach ($definition->fields as $name=>$field) {
				echo $name;
				echo PHP_EOL;

				if ($name === 'timestamps') {
					$table->timestamps();
				} else {
					$this->field($table, $name, $field);
				}
			}
		};

		$creator->bindTo($this);

		foreach ($this->tables as $name => $definition) {
			$builder = $this->db->getSchemaBuilder();

			echo '-----------'.$name.PHP_EOL;

			$builder->create($name, function(Blueprint $table) use ($creator, $definition){
				$creator($table, $definition);
			});
		}
	}
}