<?php
namespace hedronium\Jables\commands;

use \hedronium\Jables\Checker;

class Check extends Command
{
	use Checks;

	protected $signature = 'jables:check';
	protected $description = 'Checks the Schema files for inconsistencies.';

	protected $app = null;

	public function __construct(Checker $checker)
	{
		parent::__construct();
		$this->checker = $checker;
	}

	public function handle()
	{
		$this->check();
	}
}