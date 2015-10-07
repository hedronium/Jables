<?php
namespace hedronium\Jables\types;

use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class Integer extends BaseType implements Field {

	public function init($table, $name)
	{
		if ($this->definition('ai') === true) {
			return $table->increments($name);
		}

		return $table->integer($name);
	}

	public function attributes()
	{
		$attributes = $this->definition('attributes');
		if (is_array($attributes) && in_array('unsigned', $attributes)) {
			$this->field->unsigned();
		}
	}

	public function render()
	{
		$this->attributes();
	}
}