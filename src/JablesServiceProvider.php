<?php
namespace hedronium\Jables;

use Illuminate\Support\ServiceProvider;

class JablesServiceProvider extends ServiceProvider
{
	public $defer = true;

	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__.'/config/jables.php', 'jables'
		);

		$this->app->singleton('jables.checker', function($app){
			return new Checker($app, $app['files']);
		});

		$this->app->singleton('jables.runner', function($app){
			return new Runner($app, $app['files'], $app['db']);
		});

		$this->app->singleton('jables.destroyer', function($app){
			return new Destroyer($app, $app['db']);
		});

		$this->app['jables.commands.jables'] = $this->app->share(function($app){
			return new commands\Jables($app['jables.runner'], $app['jables.checker']);
		});

		$this->app['jables.commands.check'] = $this->app->share(function($app){
			return new commands\Check($app['jables.checker']);
		});

		$this->app['jables.commands.refresh'] = $this->app->share(function($app){
			return new commands\Refresh(
				$app,
				$app['jables.checker'],
				$app['jables.destroyer'],
				$app['jables.runner']
			);
		});

		$this->app['jables.commands.destroy'] = $this->app->share(function($app){
			return new commands\Destroy($app, $app['jables.destroyer']);
		});

		$this->app['jables.commands.diff'] = $this->app->share(function($app){
			return new commands\Diff();
		});

		$this->app['jables.commands.create-table'] = $this->app->share(function($app){
			return new commands\CreateTable($app['jables.runner']);
		});

		$this->app['jables.commands.create-folder'] = $this->app->share(function($app){
			return new commands\CreateFolder($app, $app['files']);
		});

		$this->app['jables.commands.prettify'] = $this->app->share(function($app){
			return new commands\Prettify();
		});
	}

	public function boot()
	{
		$this->publishes([
			__DIR__.'/config/jables.php' => config_path('jables.php'),
		]);

		$this->commands([
			'jables.commands.jables',
			'jables.commands.check',
			'jables.commands.refresh',
			'jables.commands.destroy',
			// 'jables.commands.diff',
			'jables.commands.create-table',
			'jables.commands.create-folder',
			// 'jables.commands.prettify',
		]);
	}

	public function provides()
	{
		return [
			'jables.runner',
			'jables.checker',
			'jables.commands.jables',
			'jables.commands.check',
			'jables.commands.refresh',
			'jables.commands.destroy',
			'jables.commands.diff',
			'jables.commands.create-table',
			'jables.commands.create-folder',
			'jables.commands.prettify',
		];
	}
}