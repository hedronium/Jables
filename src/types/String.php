<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class String extends BaseType implements Field {

	public function init($table, $name)
	{
		if ($length = $this->definition('length')) {
			return $table->string($name, $length);
		}

		return $table->string($name);
	}


	public function render()
	{
		
	}
}