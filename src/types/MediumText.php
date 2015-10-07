<?php
namespace hedronium\Jables\types;
use hedronium\Jables\BaseType;
use hedronium\Jables\Field;

class MediumText extends BaseType implements Field {
	public function init($table, $name)
	{
		return $table->mediumText($name);
	}


	public function render()
	{
		
	}
}