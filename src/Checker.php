<?php
namespace hedronium\Jables;

use Illuminate\Filesystem\Filesystem;
use Seld\JsonLint\JsonParser;

use \hedronium\Jables\exceptions\ParseException;
use \hedronium\Jables\exceptions\SchemaException;

class Checker
{
	protected $fs = null;
	protected $app = null;
	protected $parser = null;
	protected $loader = null;

	protected $schema_retriever = null;
	protected $schema_validator = null;
	protected $schema_resolver = null;

	protected $files = [];

	protected $schemas = [];
	protected $datas = [];

	protected $refference_checks = [
		'integer' => ['attributes'],
		'big-integer' => 'integer',
		'medium-integer' => 'integer',
		'small-integer' => 'integer',
		'tiny-integer' => 'integer',
		'string' => ['length'],
		'char'   => 'string',
		'decimal' => ['digits', 'prescision'],
		'double' => 'decimal',
		'enum' => ['values']
	];

	public function getFileList()
	{
		return $this->files;
	}

	public function __construct ($app, Filesystem $fs, Loader $loader)
	{
		$this->fs = $fs;
		$this->app = $app;
		$this->loader = $loader;

		$this->files = array_values($loader->paths());

		$this->parser = new JsonParser();
		$this->schema_retriever = new \JsonSchema\Uri\UriRetriever;
		$this->schema_resolver = new \JsonSchema\RefResolver($this->schema_retriever);
		$this->schema_validator = new \JsonSchema\Validator;
	}

	protected function loadSchema($file)
	{
		if (isset($this->schemas[$file])) {
			return $this->schemas[$file];
		}

		$retriever = $this->schema_retriever;
		$refResolver = $this->schema_resolver;


		$resolve_path = 'file://'.__DIR__.'/schemas/';

		$schema = $retriever->retrieve('file://'.__DIR__.'/schemas/'.$file);
		$refResolver->resolve($schema, $resolve_path);

		$this->schemas[$file] = $schema;

		return $schema;
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

		$validator = $this->schema_validator;

		$fields = $table_data->fields;

		foreach ($fields as $name => $field) {
			if ($name === 'timestamps' || $name === 'soft-deletes') {
				continue;
			}

			$schema_file = $field->type.'.json';

			$field_schema = $this->loadSchema($schema_file);

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

	public function schemaError()
	{
		$errors = [];

		$validator = $this->schema_validator;
		$table_schema = $this->loadSchema('table.json');

		foreach ($this->loader->names() as $table_name) {
			$table_data = $this->loader->get($table_name);
			$validator->check($table_data, $table_schema);

			if (!$validator->isValid()) {
				foreach ($validator->getErrors() as $error) {
					$errors[] = [
						'table' => $table_name,
						'path' => $this->loader->path($table_name),
						'property' => $error['property'],
						'message' => $error['message']
					];
				}

				throw new SchemaException($errors);
			}

			if ($field_errors = $this->fieldSchematicError($table_name, $table_data)) {
				return $field_errors;
			}
		}

		return null;
	}

	public function resolveRefferenceChecks()
	{
		$checks = &$this->refference_checks;

		foreach ($checks as &$check) {
			if (is_string($check)) {
				$check = $checks[$check];
			} else {
				array_push($check, 'type');
			}
		}
	}

	public function foreignKeyError()
	{
		$this->resolveRefferencechecks();

		$fields = [];
		$foreigns = [];

		foreach ($this->datas as $file => $table) {
			$table_name = $this->getName($file);

			foreach ($table->fields as $field_name => $field) {
				$fields[$table_name.'.'.$field_name] = $field;

				if (isset($field->foreign)) {
					$foreigns[$table_name.'.'.$field_name] = $field->foreign;
				}
			}

			if (isset($table->foreign)) {
				$table_foreigns = (array) $table->foreign;
				$table_new_foreigns = [];

				foreach ($table_foreigns as $field=>$parent) {
					if (!isset($table->fields->$field)) {
						return "The field $field does not exist in table $table_name";
					}

					$table_new_foreigns[$table_name.'.'.$field] = $parent;
				}

				$foreigns = array_merge(
					$foreigns,
					$table_new_foreigns
				);
			}
		}

		foreach ($foreigns as $child=>$parent) {
			if (!isset($fields[$parent])) {
				return "$parent does not exist, in $child";
			}


			$fields[$child];

			if (isset($this->refference_checks[$fields[$child]->type])) {
				$checks = $this->refference_checks[$fields[$child]->type];
			} else {
				$checks = ['type'];
			}

			foreach ($checks as $check) {
				if (!(isset($fields[$child]->$check) && isset($fields[$parent]->$check))) {
					return "The fields definitions $child & $parent don't match. ($check missing)";
				}

				if (is_array($fields[$child]->$check) && is_array($fields[$parent]->$check)) {
					if (!empty(array_diff($fields[$child]->$check, $fields[$parent]->$check)) || !empty(array_diff($fields[$parent]->$check, $fields[$child]->$check))) {
						return "The fields definitions $child & $parent don't match. ($check)";
					}
				} elseif ($fields[$child]->$check !== $fields[$parent]->$check) {
					return "The fields definitions $child & $parent don't match. ($check)";
				}
			}
		}
	}

	public function uniqueError()
	{
		foreach ($this->datas as $name => $table) {
			$name = $this->getName($name);

			if (isset($table->unique)) {
				foreach ($table->unique as $compound) {
					foreach ($compound as $field) {
						if (!isset($table->fields->$field)) {
							return "The $field field is missing in table $name. (unique constraints)";
						}
					}
				}
			}
		}
	}
}
