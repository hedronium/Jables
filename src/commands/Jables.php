<?php
namespace hedronium\Jables\commands;

use hedronium\Jables\Runner;
use hedronium\Jables\Checker;
use hedronium\Jables\Loader;
use hedronium\Jables\Command;
use hedronium\Jables\DependencyResolver;
use hedronium\Jables\TagIndexer;

class Jables extends Command
{
	protected $signature = 'jables {tables?*} {--tag=} {--database=} {--engine=} {--nodeps}';
	protected $description = 'Creates database tables from jable schema.';

	protected $runner = null;
	protected $dependency = null;
	protected $tags = null;

	public function __construct(Runner $runner, Loader $loader, DependencyResolver $dependency, TagIndexer $tags)
	{
		parent::__construct();

		$this->runner = $runner;
		$this->loader = $loader;
		$this->dependency = $dependency;
		$this->tags = $tags;
	}

	public function createTable()
	{
		$this->info('Creating Jables Tracker table...');

		if ($this->runner->createTable() === null) {
			$this->info('Tracker table already exists.');
			return;
		}

		$this->info('Tracker table created.');
	}

	public function create()
	{
		$database = $this->option('database');
		$this->runner->connection($database);

		$this->createTable();

		$engine = $this->option('engine');

		$this->info('Creating Database Tables...');

		$tables = $this->argument('tables') ? $this->argument('tables') : [];

		if ($this->option('tag')) {
			$tags = explode(',', $this->option('tag'));

			foreach ($tags as $tag) {
				$tag_tables = $this->tags->get($tag);
				$tables = array_merge($tables, $tag_tables);
			}
		}

		$tables = array_unique($tables);

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
