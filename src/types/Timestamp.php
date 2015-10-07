<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Timestamp extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->timestamp($name);
	}

	public function render(){}
}