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

		$retriever = new \JsonSchema\Uri\UriRetriever;
		$validator = new \JsonSchema\Validator;
		$refResolver = new \JsonSchema\RefResolver($retriever);


		$resolve_path = 'file://'.__DIR__.'/schemas/';

		$table_schema = $retriever->retrieve('file://'.__DIR__.'/schemas/table.json');
		$refResolver->resolve($table_schema, $resolve_path);

		foreach ($this->files as $i => $file) {
			$table_name = $this->fs->name($file);
			$table_data = json_decode($this->fs->get($file));
			$validator->check($table_data, $table_schema);

			if (!$validator->isValid()) {
				foreach ($validator->getErrors() as $error) {
					$errors[] = [
						'table' => $table_name,
						'proterty' => $error['property'],
						'message' => $error['message']
					];
				}

				return $errors;
			}

			$fields = $table_data->fields;

			foreach ($fields as $name => $field) {
				if ($name === 'timestamps') {
					continue;
				}

				$schema_file = $field->type.'.json';

				$field_schema = $retriever->retrieve('file://'.__DIR__.'/schemas/'.$schema_file);
				$refResolver->resolve($field_schema, $resolve_path);

				$field_data = $field;
				$validator->check($field_data, $field_schema);

				if (!$validator->isValid()) {
					foreach ($validator->getErrors() as $error) {
						$errors[] = [
							'table' => $table_name,
							'proterty' => $error['property'],
							'message' => $error['message']
						];
					}

					return $errors;
				}

				if (!isset($field_schema->allOf)) {
					continue;
				}

				$permitted = [];
				$available = [];

				foreach($field_schema->allOf as $subschema) {
					foreach($subschema->properties as $attr_name => $property) {
						$permitted[] = $attr_name;
					}
				}

				foreach($field_data as $attr_name => $value) {
					$available[] = $attr_name;
				}

				$diff = array_diff($available, $permitted);

				foreach ($diff as $property) {
					$errors[] = [
						'table' => $table_name,
						'property' => "$table_name.fields.$name",
						'message' => "The property - $property - is not defined and the definition does not allow additional properties"
					];
				}

				if (count($diff)) {
					return $errors;
				}
			}
		}

		return null;
	}

	public function refferentialError()
	{
		return null;
	}
}