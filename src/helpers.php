<?php

if (!function_exists('theme')){
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

if (!function_exists('protectEmail')){
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


if (!function_exists('meta_init')){
	/**
	 * Returns common metadata
	 *
	 * @return string
	 */
	function meta_init() {
		return '<meta charset="utf-8">'.
		'<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">'.
		'<meta name="viewport" content="width=device-width, initial-scale=1">';
	}
}