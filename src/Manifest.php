<?php namespace Facuz\Theme;

use Illuminate\Filesystem\Filesystem;

class Manifest
{
     /**
     * Path of all themes.
     *
     * @var array
     */
    protected $themePath;
  
    /**
     * Filesystem.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new theme instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem $files
     * @return \Facuz\Theme\Manifest
     */
	public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }


	/**
	 * Sets the specified themes path.
	 *
	 * @param string $themePath
	 * @return void
	 */
    public function setThemePath($themePath){
    	$this->themePath = $themePath;
    }

	/**
	 * Get path of theme JSON file.
	 *
	 * @return string
	 */
	public function getJsonPath()
	{
		return $this->themePath.'/theme.json';
	}

	/**
	 * Get theme JSON content as an array.
	 *
	 * @return array|mixed
	 */
	public function getJsonContents()
	{
		$default = [];

		$path = $this->getJsonPath();

		if ($this->files->exists($path)) {
			$contents = $this->files->get($path);

			return json_decode($contents, true);
		} else {
			throw new UnknownFileException("The theme must have a valid theme.json manifest file.");
		}
	}

	/**
	 * Set theme manifest JSON content property value.
	 *
	 * @param  array  $content
	 * @return integer
	 */
	protected function setJsonContents(array $content)
	{
		$content = json_encode($content, JSON_PRETTY_PRINT);


		return $this->files->put($this->getJsonPath(), $content);
	}

	/**
	 * Get a theme manifest key value.
	 *
	 * @param  string      $key
	 * @param  null|string $default
	 * @return mixed
	 */
	public function getProperty($key, $default = null)
	{
		return array_get($this->getJsonContents(), $key, $default);
	}

	/**
	 * Set a theme manifest key value.
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 * @return bool
	 */
	public function setProperty($key, $value)
	{

		$content = $this->getJsonContents();

		if (count($content)) {
			if (isset($content[$key])) {
				unset($content[$key]);
			}

			$content[$key] = $value;

			$this->setJsonContents($content);

			return true;
		}

		return false;
	}


}