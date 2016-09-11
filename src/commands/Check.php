<?php
namespace hedronium\Jables\commands;

use \hedronium\Jables\Checker;
use \hedronium\Jables\Command;

use \hedronium\Jables\exceptions\ParseException;

class Check extends Command
{
	protected $signature = 'jables:check';
	protected $description = 'Checks the Schema files for inconsistencies.';

	protected $app = null;
	protected $checker = null;

	public function __construct(Checker $checker)
	{
		parent::__construct();
		$this->checker = $checker;
	}

	public function check()
	{
		$this->info('Checking for Schema Errors...');
		$this->checker->schemaError();

		$this->info('Checking for Foreign key Constraint Errors...');
		$this->checker->foreignKeyError();

		$this->info('Checking for Unique Constraint Errors...');
		$this->checker->uniqueError();

		$this->info('--------------------------');
		$this->info('Looks Fine.');
		return true;
	}

	public function handle()
	{
		$this->check();
	}
}
