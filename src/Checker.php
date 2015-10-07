<?php
namespace hedronium\Jables;

use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Seld\JsonLint\JsonParser;

class Checker
{
	protected $fs = null;
	protected $app = null;
	protected $db = null;

	protected $schema_retriever = null;
	protected $schema_validator = null;
	protected $schema_resolver = null;

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

	public function __construct (Filesystem $fs, DatabaseManager $db)
	{
		$this->fs = $fs;
		$this->db = $db;

		$this->schema_retriever = new \JsonSchema\Uri\UriRetriever;
		$this->schema_resolver = new \JsonSchema\RefResolver($this->schema_retriever);
		$this->schema_validator = new \JsonSchema\Validator;

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

	protected function fieldSchematicLimitError($table_name, $field_name, $field_schema, $field_data)
	{
		$errors = [];

		if (!isset($field_schema->allOf)) {
			return null;
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
				'property' => "$table_name.fields.$field_name",
				'message' => "The property - $property - is not defined and the definition does not allow additional properties"
			];
		}

		if (count($diff)) {
			return $errors;
		}

		return null;
	}

	protected function fieldSchematicError($table_name, $table_data)
	{
		$errors = [];

		$retriever = $this->schema_retriever;
		$validator = $this->schema_validator;
		$refResolver = $this->schema_resolver;


		$resolve_path = 'file://'.__DIR__.'/schemas/';

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

			if ($fieldSchematicLimitErrors = $this->fieldSchematicLimitError($table_name, $name, $field_schema, $field_data)) {
				return $fieldSchematicLimitErrors;
			}
		}

		return null;
	}

	public function schematicError()
	{
		$errors = [];

		$retriever = $this->schema_retriever;
		$validator = $this->schema_validator;
		$refResolver = $this->schema_resolver;


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

			if ($field_errors = $this->fieldSchematicError($table_name, $table_data)) {
				return $field_errors;
			}
		}

		return null;
	}

	public function refferentialError()
	{
		return null;
	}
}