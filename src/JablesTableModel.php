<?php
namespace hedronium\Jables;

use Illuminate\Database\Eloquent\Model;

class JablesTableModel extends Model {
	protected $table = '';

	public function __construct()
	{
		$this->table = config('jables.table');
		parent::__construct();
	}
}