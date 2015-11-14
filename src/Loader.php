<?php
namespace hedronium\Jables;

use Illuminate\Filesystem\Filesystem;

class Loader
{
	protected $app = null;
	protected $fs = null;

	protected $datas = [];

	public function __construct($app, Filesystem $fs)
	{
		$this->app = $app;
		$this->fs = $fs;
	}

	protected function sanitize($data)
	{
		// Match all the "description" keys with string values
		$matches = [];
		$pattern = '/"description"\:\s*"/';
		preg_match_all($pattern, $data, $matches, PREG_OFFSET_CAPTURE);

		$total_len = strlen($data);

		// stores descriptions
		$descs = [];

		$new_str = "";
		$pos = 0;

		foreach ($matches[0] as $i => list($match, $offset)) {
			$length = strlen($match);
			$ex = $pos;
			$pos = $offset+$length;

			// While position does not exceed the total length
			while ($pos < $total_len) {
				$pos = strpos($data, '"', $pos+1);
				
				if ($pos === false) {
					throw new \Exception('Something Horibly wrong with the JSON.');
				}

				$backcheck = $pos;
				$slashes = 0;

				while ($backcheck > 0) {
					$backcheck--;

					if ($data[$backcheck] === "\\") {
						$slashes++;
					} else {
						break;
					}
				}

				// If there are an odd number of slashes
				// meaning the quote was escaped.
				if ($slashes&1 === 1) {
					continue;
				} else {
					// set position 1 past the following comma
					// to cancel it out
					// or to the next closing curly brace.
					$next_comma = strpos($data, ',', $pos);
					$next_brace = strpos($data, '}', $pos);

					if ($next_comma === false) {
						$pos = $next_brace;
					} elseif ($next_brace === false) {
						$pos = $next_comma;
					} elseif ($next_comma < $next_brace) {
						$pos = $next_comma;
					} else {
						$pos = $next_brace;
					}

					break;
				}
			}

			$new_str.= substr($data, $ex, $offset-$ex);
			$new_str.= '"description": '.$i;

			$descs[] = preg_replace('/ {2,}/', ' ', trim(
				substr(
					$data, $offset+$length, $pos-$offset-$length
				)
			));
		}

		return $new_str.substr($data, $pos);
	}

	public function get($file)
	{
		if (isset($datas[$file])) {
			return $datas[$file];
		}

		$data = $this->fs->get($file);

		return $datas[$file] = $this->sanitize($data);
	}
}