<?php
namespace hedronium\Jables\commands;

class JablesCheck extends Command
{
	protected $signature = 'jables:check';
	protected $description = 'Checks the Schema files for inconsistencies.';

	public function handle()
	{
		$this->info('Checking for Structural Errors...');
		$errors = $this->jables->structuralError();

		if ($errors === null) {
			$this->info('Checking for Schematic Errors...');
			$errors = $this->jables->schematicError();
		} else {
			$this->error($error);
		}

		if ($errors === null) {
			$this->info('Checking for Refferential Errors...');
			$errors = $this->jables->refferentialError();

			if ($errors === null) {
				$this->info('Looks OK.');
			}
		} else {
			$this->error(print_r($errors, true));
		}
	}
}