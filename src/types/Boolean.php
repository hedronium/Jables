<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Boolean extends BaseType implements Field {

	public function init($table, $name)
	{
		return $table->boolean($name);
	}

	public function render(){}
}