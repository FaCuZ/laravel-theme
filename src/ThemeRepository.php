<?php 

namespace Facuz\Theme;

use Facuz\Theme\Contracts\ThemeRepository as ThemeRepositoryContract;
use Illuminate\Filesystem\Filesystem;


class ThemeRepository implements ThemeRepositoryContract
{

    /**
     * Main variables for storing the registered theme information
     *
     * @var array
     */
    public $repository = [];



    /**
     * Register the theme to repository
     *
     * @param string $themeName
     * @param array $options
     * @return void
     */
    public function register(string $themeName, array $options)
    {
        $this->repository[$themeName] = $options;
    }



    /**
     * Retrieving values using dotted notation
     *
     * @param string $key
     * @param any $default
     * @return void
     */
    public function get(string $key, $default = null) 
    {
        return data_get($this->repository, $key, $default);
    }



    /**
     * Check if theme exists in repository
     *
     * @param string $key
     * @param any $default
     * @return void
     */
    public function has($themeName) 
    {
        return isset($this->repository[$themeName]) && !empty($this->repository[$themeName]);
    }



    /**
     * Retrieving repository as collection
     *
     * @return void
     */
    public function collect()
    {
        return collect($this->repository);
    }


    /**
     * Get all registered theme
     *
     * @return void
     */
    public function all()
    {
        return $this->repository;
    }


    /**
     * Get the theme names
     *
     * @return void
     */
    public function getThemeNames()
    {
        return array_keys($this->repository);
    }


    /**
     * Scan directory for themes
     *
     * @param string $themeDir
     * @return void
     */
    public function scan(string $themeDir = '')
    {
        $files = new Filesystem();
        if (!empty($themeDir) && $files->isDirectory(base_path($themeDir))) {
            $scannedThemes = $files->directories(base_path($themeDir));
            foreach ($scannedThemes as $theme) {
                $themeName = basename($theme);
                $jsonFile = "$theme/theme.json";

                if (file_exists($jsonFile)) {
                    $info = (array) json_decode(file_get_contents($jsonFile));
                    $info['path'] = "$themeDir/$themeName";

                    $this->register($themeName, $info);
                }
            }
        }
    }



    /**
     * Generate the full path for specified theme
     *
     * @param string $theme
     * @param string $path
     * @return string
     */
    public function path(string $theme = '', string $path = ''): string
    {
        return base_path($this->get("$theme.path") . $path);
    }
}
