<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Text extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->text($name);
	}


	public function render(){}
}