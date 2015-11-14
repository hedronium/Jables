<?php
namespace hedronium\Jables;

use Illuminate\Filesystem\Filesystem;
use Seld\JsonLint\JsonParser;

class Prettifyer
{
	protected $app = null;
	protected $fs = null;
	protected $loader = null;

	protected $files= [];

	public function __construct($app, Filesystem $fs, Loader $loader)
	{
		$this->app = $app;
		$this->fs = $fs;
		$this->loader = $loader;

		$path = $this->app->databasePath().'/'.config('jables.folder');

		if (!$this->fs->isWritable($path)) {
			throw new \Exception($path.' isn\' writable.');
		}

		$this->buildFileList();
	}

	protected function buildFileList()
	{
		$files = $this->fs->files($this->app->databasePath().'/'.config('jables.folder'));

		foreach ($files as $file) {
			if ($this->fs->extension($file) == 'json') {
				$this->files[] = $file; 
			}
		}
	}

	public function prettify()
	{
		$parser = new JsonParser();

		foreach ($this->files as $file) {
			$raw = $this->fs->get($file);
			$data = $parser->parse($raw);
			$pretty = json_encode($data, JSON_PRETTY_PRINT);

			$tmp_name = $file.'.tmp';

			if ($this->fs->put($tmp_name, $pretty) === false) {
				throw new \Exception('Couldn\'t write to '.$tmp_name);
			}

			if (!$this->fs->delete($file)) {
				throw new \Exception('Couldn\'t delete '.$file);
			}

			if (!$this->fs->move($tmp_name, $file)) {
				throw new \Exception('Couldn\'t rename '.$tmp_name);
			}
		}
	}
}