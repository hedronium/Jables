<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Json extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->json($name);
	}

	public function render(){}
}