<?php
namespace hedronium\Jables\commands;

use hedronium\Jables\Command;
use hedronium\Jables\TagIndexer;

class Tags extends Command
{
	protected $signature = 'jables:tags {tag_name?} {--tables}';
	protected $description = 'List all dependencies of a particular table.';

  protected $tags = null;

	public function __construct($app, TagIndexer $tags)
	{
		parent::__construct();

    $this->app = $app;
		$this->tags = $tags;
	}

	public function handle()
	{
    if ($this->argument('tag_name')) {
			$this->comment('Tables tagged "'.$this->argument('tag_name').'"');

			foreach ($this->tags->get($this->argument('tag_name')) as $table) {
				$this->line($table);
			}
		} else {
			$this->comment('All Tags');

			foreach ($this->tags->tags() as $tag) {
				if ($this->option('tables')) {
					$tables = implode(', ', $this->tags->get($tag));
					$this->line("$tag: $tables");
				} else {
					$this->line($tag);
				}
			}
		}
	}
}
