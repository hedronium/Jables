<?php
namespace hedronium\Jables;

use Seld\JsonLint\JsonParser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\DatabaseManager;

class Destroyer
{
	protected $app = null;
	protected $db_manager = null;
	protected $parser = null;
	protected $db = null;

	protected $model = null;

	protected $tables = [];



	public function buildLists()
	{
		$model = JablesTableModel::orderBy('id', 'desc')->first();

		if (!$model) {
			return false;
		}

		$data = json_decode($model->data);

		foreach ($data as $table_name => $table_definition) {

			$this->tables[$table_name] = [];

			if (isset($table_definition->foreign)) {
				$this->tables[$table_name] = $table_definition->foreign;
			}

			foreach ($table_definition->fields as $field_name => $field_definition) {
				if (isset($field_definition->foreign)) {
					$this->tables[$table_name][$field_name] = $field_definition->foreign;
				}
			}
		}

		return true;
	}

	public function __construct($app, DatabaseManager $db)
	{
		$this->app = $app;
		$this->db_manager = $db;
		$this->parser = new JsonParser;
	}

	public function connection($connection = null)
	{
		$this->db = $this->db_manager->connection($connection);
	}

	public function destroyJablesTable()
	{
		$builder = $this->db->getSchemaBuilder();
		$builder->dropIfExists(config('jables.table'));
	}

	public function destroyUserTables()
	{
		$builder = $this->db->getSchemaBuilder();

		if (!$builder->hasTable(config('jables.table')) || !$this->buildLists()) {
			return false;
		}

		foreach ($this->tables as $table_name => $foreigns) {

			$builder->table($table_name, function(Blueprint $table) use ($table_name, $foreigns) {
				foreach ($foreigns as $field => $foreign) {
					$table->dropForeign($table_name.'_'.$field.'_foreign');
				}
			});

		}

		foreach ($this->tables as $table_name => $foreigns) {
			$builder->dropIfExists($table_name);
		}

		return true;
	}
}