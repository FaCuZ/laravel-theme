<?php

if (!function_exists('theme'))
{
	/**
	 * Get the theme instance.
	 *
	 * @param  string  $themeName
	 * @param  string  $layoutName
	 * @return \Facuz\Theme\Theme
	 */
	function theme($themeName = null, $layoutName = null){
		$theme = app('theme');

		if ($themeName){
			$theme->theme($themeName);
		}

		if ($layoutName){
			$theme->layout($layoutName);
		}

		return $theme;
	}
}

if (!function_exists('protectEmail'))
{
	/**
	 * Protect the Email address against bots or spiders that 
	 * index or harvest addresses for sending you spam.
	 *
	 * @param  string  $email
	 * @return string
	 */
	function protectEmail($email) {
		$p = str_split(trim($email));
		$new_mail = '';

		foreach ($p as $val) {
			$new_mail .= '&#'.ord($val).';';
		}

		return $new_mail;
	}
}