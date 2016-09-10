<?php
namespace hedronium\Jables\commands;

use hedronium\Jables\Runner;
use hedronium\Jables\Checker;
use hedronium\Jables\Loader;
use hedronium\Jables\Command;

class Jables extends Command
{
	use traits\CreatesTable;
	use traits\Creates;

	protected $signature = 'jables {--database=} {--engine=}';
	protected $description = 'Creates database tables from jable schema.';

	protected $runner = null;
	protected $checker = null;

	public function __construct(Runner $runner, Loader $loader)
	{
		parent::__construct();

		$this->runner = $runner;
		$this->loader = $loader;
	}

	public function create()
	{
		$database = $this->option('database');
		$this->runner->connection($database);

		if ($this->createTable() === false) {
			return false;
		}

		$engine = $this->option('engine');

		$this->info('Creating Database Tables...');
		$this->runner->up($engine);

		$this->info('Creating Foreign Key Constraints...');
		$this->runner->foreigns();

		$this->info('DONE.');

		return true;
	}

	public function handle()
	{
		$code = $this->call('jables:check');
		$this->create();
	}
}
