<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Jsonb extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->jsonb($name);
	}

	public function render(){}
}