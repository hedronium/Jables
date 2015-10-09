<?php
namespace hedronium\Jables\commands;

trait Checks {
	public function check()
	{
		$this->info('Checking for JSON syntax Errors...');
		$errors = $this->checker->syntaxError();

		if ($errors !== null) {
			$this->error($errors);
			return false;
		}

		$this->info('Checking for Schema Errors...');
		$errors = $this->checker->schemaError();

		if ($errors !== null) {
			$this->error(print_r($errors, true));
			return false;
		}

		$this->info('Checking for Foreign key Constraint Errors...');
		$errors = $this->checker->foreignKeyError();

		if ($errors !== null) {
			$this->error(print_r($errors, true));
			return false;
		}

		$this->info('Checking for Unique Constraint Errors...');
		$errors = $this->checker->uniqueError();

		if ($errors !== null) {
			$this->error(print_r($errors, true));
			return false;
		}
		
		$this->info('--------------------------');
		$this->info('Looks OK! :D');
		echo PHP_EOL;

		return true;
	}
}