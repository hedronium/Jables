<?php
namespace hedronium\Jables\commands\traits;

/*
 All kind's of checks methods are implemeted in this class
*/

trait Checks {

	/**
	 *  When a check method is called .. it start checking by calling methods from checker class in root of
	 *  the folder
	 */


	public function check()
	{
		$this->info('Checking for JSON syntax Errors...');

		// Calling to json syntax error 
		$errors = $this->checker->syntaxError();

		// if find any error it will jump to next valiation check
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