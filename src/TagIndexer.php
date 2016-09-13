<?php
namespace hedronium\Jables;

use exceptions\TagNotFoundException;

class TagIndexer
{
	protected $app = null;
	protected $loader = null;

	protected $indexed = false;

	protected $tags = [];
	protected $untagged = [];

	public function __construct($app, Loader $loader)
	{
		$this->app = $app;
		$this->loader = $loader;
	}

	public function indexTags()
	{
		foreach ($this->loader->names() as $table_name) {
			$definition = $this->loader->get($table_name);

			if (!isset($definition->tags)) {
				$this->untagged[] = $table_name;
				continue;
			}

			foreach ($definition->tags as $tag) {
				if (!isset($this->tags[$tag])) {
					$this->tags[$tag] = [];
				}

				$this->tags[$tag][] = $table_name;
			}
		}

		$this->indexed = true;
	}

	public function tags()
	{
		if (!$this->indexed) {
			$this->indexTags();
		}

		return array_keys($this->tags);
	}

	public function untagged()
	{
		if (!$this->indexed) {
			$this->indexTags();
		}

		return $this->untagged;
	}

	public function get($tag)
	{
		if (!$this->indexed) {
			$this->indexTags();
		}

		if (isset($this->tags[$tag])) {
			return $this->tags[$tag];
		} else {
			throw new TagNotFoundException($tag);
		}
	}
}
