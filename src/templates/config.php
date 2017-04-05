<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Inherit from another theme
	|--------------------------------------------------------------------------
	|
	| Set up inherit from another if the file is not exists, this 
	| is work with "layouts", "partials", "views" and "widgets"
	|
	| [Notice] assets cannot inherit.
	|
	*/

	'inherit' => null, //default

	/*
	|--------------------------------------------------------------------------
	| Listener from events
	|--------------------------------------------------------------------------
	|
	| You can hook a theme when event fired on activities this is cool 
	| feature to set up a title, meta, default styles and scripts.
	|
	| [Notice] these event can be override by package config.
	|
	*/

	'events' => array(

		'before' => function($theme)
		{
			$theme->setTitle('Title example');
			$theme->setAuthor('Jonh Doe');
		},

		'beforeRenderTheme' => function($theme)
		{
			$theme->asset()->usePath()->add('styles', 'css/style.css');
			$theme->asset()->usePath()->add('scripts', 'js/script.js');


			// You may use elixir to concat styles and scripts.
			/*
			$theme->asset()->usePath()->add('styles', 'dist/css/styles.css');
			$theme->asset()->usePath()->add('scripts', 'dist/js/scripts.js');
			*/


			// Or you may use this event to set up your assets.
			/*
			$theme->asset()->usePath()->add('core', 'core.js');
			$theme->asset()->add('jquery', 'vendor/jquery/jquery.min.js');
			$theme->asset()->add('jquery-ui', 'vendor/jqueryui/jquery-ui.min.js', array('jquery'));
			*/
		},

		'beforeRenderLayout' => array(

			'default' => function($theme)
			{
				// $theme->asset()->usePath()->add('ipad', 'css/layouts/ipad.css');
			}

		)

	)

);