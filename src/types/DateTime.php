<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class DateTime extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->dateTime($name);
	}

	public function render(){}
}