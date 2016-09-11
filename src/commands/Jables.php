<?php
namespace hedronium\Jables\commands;

use hedronium\Jables\Runner;
use hedronium\Jables\Checker;
use hedronium\Jables\Loader;
use hedronium\Jables\Command;
use hedronium\Jables\DependencyResolver;

class Jables extends Command
{
	use traits\CreatesTable;
	use traits\Creates;

	protected $signature = 'jables {tables?*} {--database=} {--engine=} {--nodeps}';
	protected $description = 'Creates database tables from jable schema.';

	protected $runner = null;
	protected $dependency = null;

	public function __construct(Runner $runner, Loader $loader, DependencyResolver $dependency)
	{
		parent::__construct();

		$this->runner = $runner;
		$this->loader = $loader;
		$this->dependency = $dependency;
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

		$tables = $this->argument('tables') ? $this->argument('tables') : [];
		$tabs = $tables;
		foreach ($tabs as $table) {
			if ($this->loader->exists($table)) {
				if (!$this->option('nodeps')) {
					$deps = $this->dependency->resolveDependencyList($table);

					foreach ($deps as $dep) {
						$tables[] = $dep['to_table'];
					}
				}
			} else {
				throw new \Exception("$table definition doesn't exist.");
			}
		}

		$command = $this;
		$this->runner->up(array_unique($tables), $engine, function ($msg) use ($command) {
			$command->error($msg);
		});

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
