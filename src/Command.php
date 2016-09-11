<?php
namespace hedronium\Jables;

use Illuminate\Console\Command as LaravelCommand;

class Command extends LaravelCommand
{
	protected $app = null;

	public function __construct()
	{
		parent::__construct();
	}
}
