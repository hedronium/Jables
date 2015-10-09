<?php
namespace hedronium\Jables\commands;

trait Destroys {
	public function destroy()
	{
		$this->info('Removing User Defined Tables...');
		$this->destroyer->connection($this->option('database'));

		if (!$this->destroyer->destroyUserTables()) {
			$this->error('Jables have not been run. Nothing to destroy.');
			return false;
		}

		$this->info('Removing Jables Tracking table...');
		$this->destroyer->destroyJablesTable();

		return true;
	}
}