<?php
namespace hedronium\Jables\commands;

use Illuminate\Database\DatabaseManager;
use hedronium\Jables\Destroyer;

class Destroy extends Command
{
	protected $signature = 'jables:destroy {--database=}';
	protected $description = 'Removes all tables that jables created from database.';

	protected $destroyer = null;

	public function __construct($app, Destroyer $destroyer)
	{
		parent::__construct();
		$this->app = $app;
		$this->destroyer = $destroyer;
	}

	public function handle()
	{
		$this->info('Removing User Defined Tables...');
		$this->destroyer->connection($this->option('database'));

		if (!$this->destroyer->destroyUserTables()) {
			$this->error('Jables have not been run. Nothing to destroy.');
			return;
		}

		$this->info('Removing Jables Tracking table...');
		$this->destroyer->destroyJablesTable();
	}
}