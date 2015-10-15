<?php
namespace hedronium\Jables\commands\traits;

trait CreatesTable {
	public function createTable($connection = null)
	{
		$this->info('Creating Jables Tracker table...');
		$this->runner->connection($connection);

		if ($this->runner->createTable() === null) {
			$this->info('Tracker table already created.');
			return;
		}

		$this->info('Tracker table created.');
		return true;
	}
}