<?php
namespace hedronium\Jables;

use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Seld\JsonLint\JsonParser;

class Jables
{
	protected $fs = null;
	protected $app = null;
	protected $db = null;

	protected $files = [];

	protected $tables = [];
	protected $tables_ex = [];

	protected function buildFileList()
	{
		$files = $this->fs->files('database/jables');

		foreach ($files as $file) {
			if ($this->fs->extension($file) == 'json') {
				$this->files[] = $file; 
			}
		}
	}

	public function getFileList()
	{
		return $this->files;
	}

	public function __construct ($app, Filesystem $fs, DatabaseManager $db)
	{
		$this->app = $app;
		$this->fs = $fs;
		$this->db = $db;

		$this->buildFileList();
	}

	protected function errorLess()
	{
		if(!$this->structuralError() && !$this->schematicError() && !$this->refferentialError()){
			return false;
		}

		return true;
	}

	public function structuralError()
	{
		$parser = new JsonParser();

		foreach ($this->files as $i => $file) {
			try {
				$parser->parse($this->fs->get($file));
			} catch (\Exception $e) {
				$message = $this->fs->name($file).'.json, '.$e->getMessage();

				return $message;
			}
		}

		return null;
	}

	public function schematicError()
	{
		$errors = [];
		$table_schema = $this->fs->get(__DIR__.'/schemas/table.json');
		$table_schema = json_decode($table_schema);

		$validator = new \JsonSchema\Validator();

		foreach ($this->files as $i => $file) {
			$table_data = json_decode($this->fs->get($file));
			$validator->check($table_data, $table_schema);

			if ($validator->isValid()) {
				$fields = $table_data->fields;

				// foreach ($fields as $field) {
				// 	$schema_file = $field->type;
				// 	$column_schema = $this->fs->get(__DIR__.'/schemas/'.$schema_file.'.json');
				// 	$column_schema = json_decode($column_schema);
				// }

			} else {
				foreach ($validator->getErrors() as $error) {
					var_dump($file);
					print_r($table_data);
					$errors[] = [
						'table' => $this->fs->name($file),
						'proterty' => $error['property'],
						'message' => $error['message']
					];
				}

				break;	
			}
		}

		if (count($errors)) {
			return $errors;
		}

		return null;
	}
}