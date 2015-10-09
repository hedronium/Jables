<?php
namespace hedronium\Jables\commands;

use Illuminate\Database\DatabaseManager;
use hedronium\Jables\Destroyer;

class Destroy extends Command
{
	use Destroys;

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
		$this->destroy();
	}
}