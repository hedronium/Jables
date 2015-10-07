<?php
namespace hedronium\Jables\types;

class MediumInteger extends Integer {

	public function init(Blueprint $table, $name)
	{
		return $table->mediumInteger($name);
	}

}