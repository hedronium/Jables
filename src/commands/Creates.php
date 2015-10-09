<?php
namespace hedronium\Jables\commands;

trait Creates {
	public function create()
	{
		$database = $this->option('database');
		$this->runner->connection($database);

		if ($this->createTable() === false) {
			return false;
		}

		$this->info('Creating Database Tables...');
		$this->runner->up();

		$this->info('Creating foreign Key Constraints...');
		$this->runner->foreigns();

		$this->info('DONE.');

		return true;
	}
}