<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Enum extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->enum($name, $this->schema->values);
	}

	public function render(){}
}