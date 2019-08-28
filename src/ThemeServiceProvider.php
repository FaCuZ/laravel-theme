<?php namespace Facuz\Theme;

use Illuminate\Support\Facades\Blade;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @param  \Illuminate\Routing\Router $router
	 * @return void
	 */
	public function boot(Router $router)
	{
		$configPath = __DIR__.'/../config/theme.php';

		// Publish config.
		$this->publishes([$configPath => config_path('theme.php')], 'config');
	
	    $router->aliasMiddleware('theme', Middleware\ThemeLoader::class);

	    // Register blade directives:
		$this->addToBlade(['dd', 'dd(%s);']);
		$this->addToBlade(['dv', 'dd(get_defined_vars()[%s]);', 'dd(get_defined_vars()["__data"]);']);
		$this->addToBlade(['d', 'dump(%s);']);


		$this->addToBlade(['get', 'Theme::get(%s);']);
		$this->addToBlade(['getIfHas', 'Theme::has(%1$s) ? Theme::get(%1$s) : ""']);

		$this->addToBlade(['partial', 'Theme::partial(%s, get_defined_vars()["__data"]);']);
		$this->addToBlade(['sections', 'Theme::partial("sections.".%s, get_defined_vars()["__data"]);']);
		$this->addToBlade(['content', null, 'Theme::content();']);

		$this->addToBlade(['asset', 'Theme::asset()->absUrl(%s);']);

		$this->addToBlade(['protect', 'protectEmail(%s);']);

		$this->addToBlade(['styles', 'Theme::asset()->container(%s)->styles();', 'Theme::asset()->styles();']);
		$this->addToBlade(['scripts', 'Theme::asset()->container(%s)->scripts();', 'Theme::asset()->scripts();']);

		$this->addToBlade(['widget', 'Theme::widget(%s)->render();']);
	}

	/**
	 * Register service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$configPath = __DIR__.'/../config/theme.php';

		// Merge config to allow user overwrite.
		$this->mergeConfigFrom($configPath, 'theme');

		$app = $this->app;

		// Register providers:
		$this->registerAsset();
		$this->registerTheme();
		$this->registerWidget();
		$this->registerBreadcrumb();
		$this->registerManifest();

		// Register commands:
		$this->registerThemeGenerator();
		$this->registerWidgetGenerator();
		$this->registerThemeList();
		$this->registerThemeDuplicate();
		$this->registerThemeDestroy();

		// Assign commands:
		$this->commands(
						'theme.create',
						'theme.widget',
						'theme.list',
						'theme.duplicate',
						'theme.destroy'
						);

	}

	/**
	 * Set a blade directive
	 *
	 * @return void
	 */
	protected function addToBlade($array){
		Blade::directive($array[0], function ($data) use ($array) {	
			if(!$data) return '<?php echo '.$array[2].' ?>';

			return sprintf('<?php echo '.$array[1].' ?>',
				null !== $data ? $data : "get_defined_vars()['__data']"
			);
		});  
	}


	/**
	 * Register asset provider.
	 *
	 * @return void
	 */
	public function registerAsset()
	{
		$this->app->singleton('asset', function($app)
		{
			return new Asset();
		});
	}

	/**
	 * Register theme provider.
	 *
	 * @return void
	 */
	public function registerTheme()
	{
		$this->app->singleton('theme', function($app)
		{
			return new Theme($app['config'], $app['events'], $app['view'], $app['asset'], $app['files'], $app['breadcrumb'], $app['manifest']);
		});

		$this->app->alias('theme', 'Facuz\Theme\Contracts\Theme');
	}

	/**
	 * Register widget provider.
	 *
	 * @return void
	 */
	public function registerWidget()
	{
		$this->app->singleton('widget', function($app)
		{
			return new Widget($app['view']);
		});
	}

	/**
	 * Register breadcrumb provider.
	 *
	 * @return void
	 */
	public function registerBreadcrumb()
	{
		$this->app->singleton('breadcrumb', function($app)
		{
			return new Breadcrumb($app['files']);
		});
	}

	/**
	 * Register manifest provider.
	 *
	 * @return void
	 */
	public function registerManifest()
	{
		$this->app->singleton('manifest', function($app)
		{
			return new Manifest($app['files']);
		});
	}

	/**
	 * Register generator of theme.
	 *
	 * @return void
	 */
	public function registerThemeGenerator()
	{
		$this->app->singleton('theme.create', function($app)
		{
			return new Commands\ThemeGeneratorCommand($app['config'], $app['files']);
		});
	}

	/**
	 * Register duplicate of theme.
	 *
	 * @return void
	 */
	public function registerThemeDuplicate()
	{
		$this->app->singleton('theme.duplicate', function($app)
		{
			return new Commands\ThemeDuplicateCommand($app['config'], $app['files']);
		});
	}

	/**
	 * Register generator of widget.
	 *
	 * @return void
	 */
	public function registerWidgetGenerator()
	{
		$this->app->singleton('theme.widget', function($app)
		{
			return new Commands\WidgetGeneratorCommand($app['config'], $app['files']);
		});
	}

	/**
	 * Register theme destroy.
	 *
	 * @return void
	 */
	public function registerThemeDestroy()
	{
		$this->app->singleton('theme.destroy', function($app)
		{
			return new Commands\ThemeDestroyCommand($app['config'], $app['files']);
		});
	} 

	/**
	 * Register list themes.
	 *
	 * @return void
	 */
	public function registerThemeList()
	{
		$this->app->singleton('theme.list', function($app)
		{
			return new Commands\ThemeListCommand($app['config'], $app['files']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('asset', 'theme', 'widget', 'breadcrumb');
	}

}
