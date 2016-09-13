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
		$tables = $this->db->table($this->app['config']['jables.table'])->orderBy('id', 'desc')->where('type', '=', 'table')->get();

		foreach ($tables as $raw) {
			$defs = json_decode($raw->data);

			foreach ($defs as $table => &$x) {
				$this->tables[] = $table;
			}
		}

		$foreigns = $this->db->table($this->app['config']['jables.table'])->orderBy('id', 'desc')->where('type', '=', 'foreign')->get();

		foreach ($foreigns as $raw) {
			$tabs = json_decode($raw->data, true);

			$this->foreigns = array_merge_recursive($this->foreigns, $tabs);
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

		if (!$builder->hasTable($this->app['config']['jables.table'])) {
			return false;
		}

		$this->buildLists();

		foreach ($this->foreigns as $table_name => $foreigns) {
			if (count($foreigns) === 0) {
				continue;
			}

			$builder->table($table_name, function(Blueprint $table) use ($table_name, $foreigns) {
				foreach ($foreigns as $foreign) {
					$table->dropForeign($foreign);
				}
			});
		}

		foreach ($this->tables as $table) {
			$builder->dropIfExists($table);
		}

		return true;
	}
}
