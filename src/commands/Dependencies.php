<?php
namespace hedronium\Jables\commands;

use Illuminate\Database\DatabaseManager;
use hedronium\Jables\Destroyer;
use hedronium\Jables\Command;
use hedronium\Jables\DependencyResolver;

class Dependencies extends Command
{
	protected $signature = 'jables:dependencies {table_name} {--relations} {--list}';
	protected $description = 'List all dependencies of a particular table.';

  protected $dependency = null;

	public function __construct($app, DependencyResolver $dependency)
	{
		parent::__construct();

    $this->app = $app;
		$this->dependency = $dependency;
	}

  protected function listDeps()
  {
    $deps = $this->dependency->resolveDependencyList($this->argument('table_name'));

    $name_lengths = [];
    $from_lengths = [];
    $to_lengths = [];

    foreach ($deps as $dep) {
      $from_table = $dep['from_table'];
      $to_table = $dep['to_table'];

      $from_field = $dep['from_field'];
      $to_field = $dep['to_field'];

      $name_lengths[] = strlen($to_table);
      $from_lengths[] = strlen($from_table) + strlen($from_field) + 1;
      $to_lengths[] = strlen($to_table) + strlen($to_field) + 1;
    }

    $max_name = max($name_lengths);
    $max_from = max($from_lengths);
    $max_to = max($to_lengths);

    foreach ($deps as $dep) {
      $from_table = $dep['from_table'];
      $to_table = $dep['to_table'];

      $from_field = $dep['from_field'];
      $to_field = $dep['to_field'];

      $table = str_pad($to_table, $max_name);
      $from = str_pad("$from_table.$from_field", $max_from, ' ', STR_PAD_LEFT);
      $to = str_pad("$to_table.$to_field", $max_to);

      if ($this->option('relations')) {
        $this->line("- $table  |  $from  -->  $to");
      } else {
        $this->line("- $table");
      }
    }
  }

  protected function recurse($subtree, $relation, $depth, $last)
  {
    $padding = '';
    if ($depth > 1) {
      for ($i = 0; $i < $depth-1; $i++) {
        $padding .= ($last ? ' ' : '│').'  ';
      }
    }

    if ($depth === 0) {
      $line = "";
    } else {
      $line = ($last ? '└─' : '├─')." ";
    }

    $table_name = $subtree['name'];
    $relations = $subtree['relations'];

    $rel = '';
    if ($this->option('relations') && $relation) {
      $from_field = $relation['from_field'];
      $to_field = $relation['to_field'];
      $rel = " [$from_field --> $to_field]";
    }

    $this->line("$padding$line$table_name$rel");

    foreach ($relations as $i => $relation) {
      $this->recurse($relation['table'], $relation, $depth + 1, $i === count($relations) - 1);
    }
  }

  protected function treeDeps()
  {
    $deps = $this->dependency->resolveDependencyTree($this->argument('table_name'));
    $this->recurse($deps['table'], null, 0, true);
  }

	public function handle()
	{
    $this->callSilent('jables:check');
    $this->comment('Dependencies for "'.$this->argument('table_name').'" table');

    if (!$this->option('list')) {
      $this->treeDeps();
    } else {
      $this->listDeps();
    }
	}
}
