<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Decimal extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->decimal(
			$name,
			$this->schema->digits,
			$this->schema->precision
		);
	}

	public function render(){}
}