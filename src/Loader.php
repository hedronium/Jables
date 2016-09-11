<?php
namespace hedronium\Jables;

use Seld\JsonLint\JsonParser;
use Symfony\Component\Yaml\Yaml;

use Illuminate\Filesystem\Filesystem;

use \hedronium\Jables\exceptions\ParseException;
use \hedronium\Jables\exceptions\NameCollisionException;

class Loader
{
	protected $app = null;
	protected $fs = null;
	protected $extensions = [];

	protected $paths = [];
	protected $names = [];

	protected $parsed = [];

	public function __construct($app, Filesystem $fs)
	{
		$this->app = $app;
		$this->fs = $fs;

		$json_parser = new JsonParser();

		$this->extensions = [
			'json' => function ($raw) use ($json_parser) {
				$parsed = $json_parser->parse($raw);
				return $parsed;
			},
			'yml' => function ($raw) {
				$parsed = Yaml::parse($raw, false, false, true);
				return $parsed;
			}
		];

		$this->index();
		$this->parse();
	}

	public function names()
	{
		return $this->names;
	}

	public function paths()
	{
		return $this->paths;
	}

	public function path($name)
	{
		return $this->paths[$name];
	}

	public function get($name)
	{
		return isset($this->parsed[$name]) ? $this->parsed[$name] : false;
	}

	public function exists($name)
	{
		return isset($this->parsed[$name]);
	}

	public function parse()
	{
		foreach ($this->paths as $name => $path) {
			$ext = $this->fs->extension($path);
			$raw = $this->fs->get($path);

			try {
				$this->parsed[$name] = $this->extensions[$ext]($raw);
			} catch (\Seld\JsonLint\ParsingException $e) {
				throw new ParseException($name, $path, $e->getMessage());
			}
		}
	}

	public function index($dir = 'jables')
	{
		$files = $this->fs->allFiles($this->app->databasePath().'/'.$dir);

		$paths = [];
		$names = [];

		foreach ($files as $file) {
			if (!isset($this->extensions[$file->getExtension()])) {
				continue;
			}

			$table_name = $this->fs->name($file->getRealPath());

			if (isset($paths[$table_name])) {

				throw new NameCollisionException(
					$paths[$table_name],
					$file->getRealPath()
				);


			} else {
				$paths[$table_name] = $file->getRealPath();
				$names[] = $table_name;
			}
		}

		$this->paths = $paths;
		$this->names = $names;
	}
}
