<?php
namespace hedronium\Jables\commands;

use hedronium\Jables\Runner;
use hedronium\Jables\Checker;

class Jables extends Command
{
	use Checks;

	protected $signature = 'jables {--database=}';
	protected $description = 'Creates database tables from jable schema.';

	protected $runner = null;

	public function __construct(Runner $runner, Checker $checker)
	{
		parent::__construct();
		$this->checker = $checker;
		$this->runner = $runner;
	}

	public function handle()
	{
		if (!$this->check()) {
			return;
		}

		$database = $this->option('database');
		$this->runner->connection($database);

		$this->info('Creating Database Tables...');
		$this->runner->up();

		$this->info('Creating foreign Key Constraints...');
		$this->runner->foreigns();

		$this->info('DONE.');
	}
}