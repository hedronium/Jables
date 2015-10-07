<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class BigInteger extends Integer {
	public function init($table, $name)
	{
		if ($this->definition('ai') === true) {
			return $table->bigIncrements($name);
		}

		return $table->bigInteger($name);
	}
}