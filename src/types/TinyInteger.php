<?php
namespace hedronium\Jables\types;

class TinyInteger extends Integer {

	public function init($table, $name)
	{
		return $table->tinyInteger($name);
	}

}