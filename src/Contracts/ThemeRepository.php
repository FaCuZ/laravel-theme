<?php 

namespace Facuz\Theme\Contracts;

interface ThemeRepository 
{
    /**
     * Register the theme to repository
     *
     * @param string $themeName
     * @param array $options
     * @return void
     */
    public function register(string $themeName, array $options);
}