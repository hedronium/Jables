<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Double extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->double(
			$name,
			$this->schema->digits,
			$this->schema->precision
		);
	}

	public function render(){}
}