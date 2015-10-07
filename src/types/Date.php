<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Date extends BaseType implements Field {

	public function init($table, $name)
	{
		return $table->date($name);
	}

	public function render(){}

}