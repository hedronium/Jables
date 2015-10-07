<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class LongText extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->longText($name);
	}

	public function render(){}
}