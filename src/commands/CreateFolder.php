<?php
namespace hedronium\Jables\commands;

use Illuminate\Filesystem\Filesystem;
use hedronium\Jables\Command;

class CreateFolder extends Command
{
	protected $signature = 'jables:create-folder {--database=}';
	protected $description = 'Creates the folder to store your Schema.';

	protected $fs = null;

	public function __construct($app, Filesystem $fs) {
		parent::__construct();
		$this->fs = $fs;
		$this->app = $app;
	}

	public function handle()
	{
		$path = $this->app->databasePath().'/'.config('jables.folder');

		$this->info('Checking Jables Directory...');

		if (!$this->fs->exists($path)) {
			$this->info('Creating Directory...');
			$this->fs->makeDirectory($path);
			$this->info('Directory Created.');

			return;
		}

		if (!$this->fs->isdirectory($path)) {
			$this->error("$path is not a directory.");

			return;
		}

		$this->info('Directory already exists.');
	}
}