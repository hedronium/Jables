<?php
namespace hedronium\Jables\commands;

use hedronium\Jables\Runner;
use hedronium\Jables\Command;

class CreateTable extends Command
{
	use traits\CreatesTable;

	protected $signature = 'jables:create-table {--database=}';
	protected $description = 'Creates a database table for tracking.';

	protected $runner = null;

	public function __construct(Runner $runner) {
		parent::__construct();
		$this->runner = $runner;
	}

	public function handle()
	{
		$connection = $this->option('database');
		$this->createTable($connection);
	}
}