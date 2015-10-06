<?php
namespace hedronium\Jables;

use Illuminate\Support\ServiceProvider;

class JablesServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->singleton('jables', function($app){
			return new Jables($app, $app['files'], $app['db']);
		});

		$this->app['jables-command'] = $this->app->share(function($app){
			return new commands\Jables($app['jables']);
		});

		$this->app['jables-check'] = $this->app->share(function($app){
			return new commands\JablesCheck($app['jables']);
		});

		$this->app['jables-refresh'] = $this->app->share(function($app){
			return new commands\JablesRefresh($app['jables']);
		});

		$this->app['jables-down'] = $this->app->share(function($app){
			return new commands\JablesDown($app['jables']);
		});

		$this->app['jables-diff'] = $this->app->share(function($app){
			return new commands\JablesDiff($app['jables']);
		});

		$this->app['jables-create-table'] = $this->app->share(function($app){
			return new commands\JablesCreateTable($app['jables']);
		});

		$this->app['jables-prettify'] = $this->app->share(function($app){
			return new commands\JablesPrettify($app['jables']);
		});
	}

	public function boot()
	{
		$this->commands([
			'jables-command',
			'jables-check',
			'jables-refresh',
			'jables-down',
			'jables-diff',
			'jables-create-table',
			'jables-prettify',
		]);
	}
}