<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Morphs extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->morphs($name);
	}

	public function render(){}
}