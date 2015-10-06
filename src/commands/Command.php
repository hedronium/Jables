<?php
namespace hedronium\Jables\commands;

use Illuminate\Console\Command as LaravelCommand;
use hedronium\Jables\Jables;

class Command extends LaravelCommand {
	protected $jables = null;

	public function __construct($jables)
	{
		parent::__construct();

		$this->jables = $jables;
	}
}