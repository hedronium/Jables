<?php
namespace hedronium\Jables;

use Illuminate\Database\Schema\Blueprint;

abstract class BaseType {
	protected $schema = null;
	protected $name = null;
	protected $table = null;
	protected $field = null;

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setSchema($schema)
	{
		$this->schema = $schema;
	}

	public function setTable(Blueprint $table)
	{
		$this->table = $table;
	}

	public function initField()
	{
		$this->field = $this->init($this->table, $this->name);
	}

	protected function definition($definition)
	{
		if (isset($this->schema->$definition)) {
			return $this->schema->$definition;
		} else {
			return null;
		}
	}

	protected function unique()
	{
		if($this->definition('unique') === true){
			$this->field->unique();
		}
	}

	protected function nullable()
	{
		if($this->definition('nullable') === true){
			$this->field->nullable();
		}
	}

	protected function defaultValue()
	{
		if($defaultValue = $this->definition('default')){
			$this->field->default($defaultValue);
		}
	}

	public function base()
	{
		$this->unique();
		$this->nullable();
		$this->defaultValue();
	}
}