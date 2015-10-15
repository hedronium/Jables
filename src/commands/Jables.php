<?php
namespace hedronium\Jables\commands;

use hedronium\Jables\Runner;
use hedronium\Jables\Checker;
use hedronium\Jables\Command;

class Jables extends Command
{
	use traits\Checks;
	use traits\CreatesTable;
	use traits\Creates;

	protected $signature = 'jables {--database=}';
	protected $description = 'Creates database tables from jable schema.';

	protected $runner = null;
	protected $checker = null;

	public function __construct(Runner $runner, Checker $checker)
	{
		parent::__construct();
		$this->checker = $checker;
		$this->runner = $runner;
	}

	public function handle()
	{
		if (!$this->check()) {
			return false;
		}
		
		$this->create();
	}
}