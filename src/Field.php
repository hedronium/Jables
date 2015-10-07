<?php
namespace hedronium\Jables;

interface Field {
	public function init($table, $name);
	public function render();
}