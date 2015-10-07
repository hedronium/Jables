<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Char extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->char($name, $this->schema->length);
	}

	public function render(){}
}