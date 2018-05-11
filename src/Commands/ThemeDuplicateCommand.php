<?php namespace Facuz\Theme\Commands;

use Illuminate\Console\Command;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem as File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ThemeDuplicateCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'theme:duplicate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Duplicate theme structure from other theme.';

	/**
	 * Repository config.
	 *
	 * @var Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Filesystem
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new command instance.
	 *
	 * @param \Illuminate\Config\Repository     $config
	 * @param \Illuminate\Filesystem\Filesystem $files
	 * @return \Facuz\Theme\Commands\ThemeDuplicateCommand
	 */
	public function __construct(Repository $config, File $files)
	{
		$this->config = $config;

		$this->files = $files;

		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$theme = strtolower($this->argument('name'));
		$new_theme = strtolower($this->argument('new-name'));

		$theme_path = $this->getPath($theme);
		$new_theme_path = $this->getPath($new_theme);

		if(!$this->files->isDirectory($theme_path)){
			return $this->error('Theme "'.$theme.'" does not exist.');
		}

		if($this->files->isDirectory($new_theme_path)){
			return $this->error('Theme "'.$new_theme.'" is already exists.');
		}

		$this->files->copyDirectory($theme_path, $new_theme_path);

		$this->info('Theme "'.$new_theme.'" has been created.');
	}


	/**
	 * Get root writable path.
	 *
	 * @param  string $path
	 * @return string
	 */
	protected function getPath($theme, $file = null)
	{
		$rootPath = $this->option('path');

		return $rootPath.'/'.$theme.'/' . $file;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'Name of the theme to duplicate.'),
			array('new-name', InputArgument::REQUIRED, 'Name of the new theme.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$path = base_path($this->config->get('theme.themeDir'));

		return array(
			array('path', null, InputOption::VALUE_OPTIONAL, 'Path to theme directory.', $path),
		);
	}

}