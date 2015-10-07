<?php
namespace hedronium\Jables\commands;

use hedronium\Jables\Runner;

class Jables extends Command
{
	protected $signature = 'jables';
	protected $description = 'Creates database tables from jable schema.';

	protected $runner = null;

	public function __construct(Runner $runner)
	{
		parent::__construct();
		$this->runner = $runner;
	}

	public function handle()
	{
		$this->call('jables:check');
		$this->runner->up();
	}
}