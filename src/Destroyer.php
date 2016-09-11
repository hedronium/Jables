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
	protected $foreigns = [];



	public function buildLists()
	{
		$tables = JablesTableModel::orderBy('id', 'desc')->where('type', '=', 'table')->get();

		foreach ($tables as $raw) {
			$defs = json_decode($raw->data);

			foreach ($defs as $table => &$x) {
				$this->tables[] = $table;
			}
		}

		$foreigns = JablesTableModel::orderBy('id', 'desc')->where('type', '=', 'foreign')->get();

		foreach ($foreigns as $raw) {
			$defs = json_decode($raw->data);

			foreach ($defs as $def) {
				$this->foreigns[] = $def;
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

		if (!$builder->hasTable(config('jables.table'))) {
			return false;
		}

		$this->buildLists();

		foreach ($this->foreigns as $foreign) {
			list($table_name) = explode('_', $foreign);

			$builder->table($table_name, function(Blueprint $table) use ($table_name, $foreign) {
				$table->dropForeign($foreign);
			});

		}

		foreach ($this->tables as $table) {
			$builder->dropIfExists($table);
		}

		return true;
	}
}
