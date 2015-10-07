<?php
namespace hedronium\Jables\types;

class SmallInteger extends Integer {

	public function init($table, $name)
	{
		return $table->smallInteger($name);
	}

}