<?php
namespace hedronium\Jables;

use Seld\JsonLint\JsonParser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\DatabaseManager;

class DependencyResolver
{
  protected $app = null;
	protected $loader = null;

	public function __construct($app, Loader $loader)
	{
		$this->app = $app;
		$this->loader = $loader;
	}

  protected function resolve($table)
  {
    $definition = $this->loader->get($table);

    $sub_tree = [
      'name' => $table,
      'relations' => []
    ];

    $foreign_keys = isset($definition->foreign) ? $definition->foreign : [];

    foreach ($definition->fields as $field_name => $field_def) {
      if (isset($field_def->foreign)) {
        $foreign_keys[$field_name] = $field_def->foreign;
      }
    }

    foreach ($foreign_keys as $field_name => $foreign) {
      list($foreign_table, $foreign_field) = explode('.', $foreign);

      $sub_tree['relations'][] = [
        'from_table' => $table,
        'to_table' => $foreign_table,
        'from_field' => $field_name,
        'to_field' => $foreign_field,
        'table' => $this->resolve($foreign_table)
      ];
    }

    return $sub_tree;
  }

  public function resolveDependencyTree($table)
  {
    $tree = [
      'table' => $this->resolve($table)
    ];

    return $tree;
  }

  protected function resolveList($relations, &$list)
  {
    foreach ($relations as $relation) {
      $temp = $relation;
      unset($temp['table']);

      $list[] = $temp;

      $this->resolveList($relation['table']['relations'], $list);
    }
  }

  public function resolveDependencyList($table)
  {
    $tree = $this->resolveDependencyTree($table);
    $list = [];

    $this->resolveList($tree['table']['relations'], $list);

    return $list;
  }
}
