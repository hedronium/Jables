<?php
namespace hedronium\Jables\commands;

use \hedronium\Jables\Checker;

class Check extends Command
{
	protected $signature = 'jables:check';
	protected $description = 'Checks the Schema files for inconsistencies.';

	protected $checker = null;

	public function __construct(Checker $checker)
	{
		parent::__construct();
		$this->checker = $checker;
	}

	public function handle()
	{
		$this->info('Checking for Structural Errors...');
		$errors = $this->checker->structuralError();

		if ($errors !== null) {
			$this->error($errors);
			return;
		}

		$this->info('Checking for Schematic Errors...');
		$errors = $this->checker->schematicError();

		if ($errors !== null) {
			$this->error(print_r($errors, true));
			return;
		}

		$this->info('Checking for Refferential Errors...');
		$errors = $this->checker->refferentialError();

		if ($errors !== null) {
			$this->error(print_r($errors, true));
			return;
		}
		
		$this->info('--------------------------');
		$this->info('Looks OK! :D');
		$this->info('--------------------------');
	}
}