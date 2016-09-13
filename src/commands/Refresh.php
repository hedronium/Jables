<?php
namespace hedronium\Jables\commands;

use hedronium\Jables\Checker;
use hedronium\Jables\Runner;
use hedronium\Jables\Destroyer;
use hedronium\Jables\Command;

class Refresh extends Command
{
	protected $signature = 'jables:refresh {--database=} {--engine=}';
	protected $description = 'Removes and re-creates the tables in database.';

	protected $app = null;
	protected $checker = null;
	protected $runner = null;
	protected $destroyer = null;

	public function __construct($app, Checker $checker, Destroyer $destroyer, Runner $runner)
	{
		parent::__construct();

		$this->app = $app;
		$this->checker = $checker;
		$this->runner = $runner;
		$this->destroyer = $destroyer;
	}

	public function handle()
	{
		$this->call('jables:destroy', [
			'--database' => $this->option('database')
		]);

		$this->call('jables', [
			'--engine' => $this->option('engine'),
			'--database' => $this->option('database')
		]);
	}
}
