# Theme Management for Laravel

Laravel-Theme is a theme management for Laravel 5+ (last check 6.3), it is the easiest way to organize your skins, layouts and assets.

This package is based on [teepluss\theme](https://github.com/teepluss/laravel-theme/)

>##### Differences with teepluss version:
>- Compatible with laravel 5+
>- Removed twig compatibility (Reduces the package by 94%).
>- Blade directives
>- Better base template.
>- Simplified configuration.
>- More commands and helper functions.
>- Better README file.
>- Manifest file (Get and set theme info)
>- Middleware to define theme and layout

## Usage

Theme has many features to help you get started with Laravel

- [Installation](#installation)
- [Create new theme](#create-new-theme)
- [Basic usage](#basic-usage)
- [Configuration](#configuration)
- [Basic usage of assets](#basic-usage-of-assets)
- [Partials](#partials)
- [Magic methods](#magic-methods)
- [Preparing data to view](#preparing-data-to-view)
- [Breadcrumb](#breadcrumb)
- [Widgets](#widgets)
- [Using theme global](#using-theme-global)
- [Middleware](#middleware)
- [Helpers](#helpers)
- [Cheatsheet](#cheatsheet)


## Installation

To get the latest version of laravel-themes simply require it in your `composer.json` file.

~~~json
"facuz/laravel-themes": "^3.2"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

Once Theme is installed you need to register the service provider with the application. Open up `config/app.php` and find the `providers` key.

~~~php
'providers' => [
	...
	Facuz\Theme\ThemeServiceProvider::class,

]
~~~

Theme also ships with a facade which provides the static syntax for creating collections. You can register the facade in the `aliases` key of your `config/app.php` file.

~~~php
'aliases' => [
	...
	'Theme' => Facuz\Theme\Facades\Theme::class,

]
~~~
Publish config using artisan CLI.

~~~
php artisan vendor:publish --provider="Facuz\Theme\ThemeServiceProvider"
~~~

It's recommended to add to the `.env` file the theme that we are going to use
~~~
APP_THEME=default
~~~



## Create new theme

The first time you have to create theme "default" structure, using the artisan command:

~~~
php artisan theme:create default
~~~
> If you change the facade name you can add an option --facade="Alias".


This will create the following directory structure:

```
├── public/
    └── themes/
	└── default/
		├── assets
        	|	├── css/
		|	├── img/
            	|	└── js/
            	├── layouts/
            	├── partials/
           	|	└── sections/
            	├── views/
	        └── widgets/
```

To delete an existing theme, use the command:

~~~
php artisan theme:destroy default
~~~

If you want to list all installed themes use the command:

~~~
php artisan theme:list
~~~

You can duplicate an existing theme:
~~~
php artisan theme:duplicate name new-theme
~~~



Create from the application without CLI.

~~~php
Artisan::call('theme:create', ['name' => 'foo']);
~~~

## Basic usage

To display a view from the controller:

~~~php
namespace App\Http\Controllers;

use Theme;

class HomeController extends Controller {

	public function getIndex()
	{
		return Theme::view('index');
	}
	...
}
~~~
>This will use the theme and layout set by default on `.env`

		
You can add data or define the theme and layout:

~~~php
...		
Theme::uses('themename');
        
$data['info'] = 'Hello World'; 

return Theme::view('index', $data);
...
~~~

Or you can do:
~~~php
$cookie = Cookie::make('name', 'Tee');

return Theme::view([
		    'view' => 'index',
		    'theme' => 'default',
		    'layout' => 'layout',
		    'statusCode' => 200,
		    'cookie'  => $cookie,
		    'args' => $data
		   ]);
~~~
>All values except `'view'` are optional

To check whether a theme exists.

~~~php
Theme::exists('themename');
~~~

Each theme must come supplied with a manifest file `theme.json` stored at the root of the theme, which defines supplemental details about the theme. 
~~~json
{
    "slug": "default",
    "name": "Default",
    "author": "John Doe",
    "email": "johndoe@example.com",
    "description": "This is an example theme.",
    "web": "www.example.com",
    "license": "MIT",
    "version": "1.0"
}
~~~

The manifest file can store any property that you'd like. These values can be retrieved and even set through a couple helper methods:

~~~php
// Get all: (array)
Theme::info(); 
// Get:
Theme::info("property"); 
// Set:
Theme::info("property", "new data"); 
~~~

#### Other ways to display a view:
~~~php
$theme = Theme::uses('default')->layout('mobile');

$data = ['info' => 'Hello World'];
~~~

~~~php
// It will look up the path 'resources/views/home/index.php':
return $theme->of('home.index', $data)->render();
~~~

~~~php
// Specific status code with render:
return $theme->of('home.index', $data)->render(200);
~~~

~~~php
// It will look up the path 'resources/views/mobile/home/index.php':
return $theme->ofWithLayout('home.index', $data)->render();
~~~

~~~php
// It will look up the path 'public/themes/default/views/home/index.php':
return $theme->scope('home.index', $data)->render();
~~~

~~~php
// It will look up the path 'public/themes/default/views/mobile/home/index.php':
return $theme->scopeWithLayout('home.index', $data)->render();
~~~

~~~php
// Looking for a custom path:
return $theme->load('app.somewhere.viewfile', $data)->render();
~~~

~~~php
// Working with cookie:
$cookie = Cookie::make('name', 'Tee');
return $theme->of('home.index', $data)->withCookie($cookie)->render();
~~~

~~~php
// Get only content:
return $theme->of('home.index')->content();
~~~

Finding from both theme's view and application's view:
~~~php
$theme = Theme::uses('default')->layout('default');

return $theme->watch('home.index')->render();
~~~

To find the location of a view:

~~~php
$which = $theme->scope('home.index')->location();

echo $which; // theme::views.home.index

$which = $theme->scope('home.index')->location(true);

echo $which; // ./public/themes/name/views/home/index.blade.php
~~~

#### Render from string:

~~~php
return $theme->string('<h1>{{ $name }}</h1>', ['name' => 'Teepluss'], 'blade')->render();
~~~

Compile string:

~~~php
$template = `<h1>Name: {{ $name }}</h1>
		     <p>
		      {{ Theme::widget("WidgetIntro", ["title" => "Demo Widget"])->render() }}
		     </p>`;

echo Theme::blader($template, ['name' => 'Teepluss']);
~~~

#### Symlink from another view

This is a nice feature when you have multiple files that have the same name, but need to be located as a separate one.

~~~php
// Theme A : /public/themes/a/views/welcome.blade.php
// Theme B : /public/themes/b/views/welcome.blade.php

// File welcome.blade.php at Theme B is the same as Theme A, so you can do link below:

Theme::symlink('a'); 
// Location: public/themes/b/views/welcome.blade.php
~~~

## Configuration

After the config is published, you will see a global config file `/config/theme.php`, all the configuration can be replaced by a config file inside a theme: `/public/themes/[theme]/config.php`

The config is convenient for setting up basic CSS/JS, partial composer, breadcrumb template and also metas.

~~~php
'events' => [

	/* 
	 * Before event inherit from package config and the theme that call
	 * before, you can use this event to set meta, breadcrumb
	 * template or anything you want inheriting.
	 */
	'before' => function($theme)
	{
		// You can remove this lines anytime.
		$theme->setTitle('Title Example');
		$theme->setAuthor('John Doe');
		$theme->setKeywords('Example, Web');
	
		// Breadcrumb template.
		$theme->breadcrumb()->setTemplate(`        
			 <ul class="breadcrumb">
			 @foreach($crumbs as $i => $crumb)
				 @if($i != (count($crumbs) - 1))
					<li>
                    	<a href="{{ $crumb["url"] }}">{{ $crumb["label"] }}</a>
                        <span class="divider">/</span>
					</li>
				 @else
					<li class="active">{{ $crumb["label"] }}</li>
				 @endif
			 @endforeach
			 </ul>             
		 `);
	 },
    
    /*
	 * Listen on event before render a theme, this
	 * event should call to assign some assets.
	 */
	'asset' => function($asset)
	{
		$asset->themePath()->add([
					['style', 'css/style.css'],
					['script', 'js/script.js']
					 ]);

		// You may use elixir to concat styles and scripts.
		$asset->themePath()->add([
					['styles', 'dist/css/styles.css'],
					['scripts', 'dist/js/scripts.js']
					 ]);

		// Or you may use this event to set up your assets.
		$asset->themePath()->add('core', 'core.js');			
		$asset->add([
			['jquery', 'vendor/jquery/jquery.min.js'],
			['jquery-ui', 'vendor/jqueryui/jquery-ui.min.js', ['jquery']]
			 ]);
	},
   

	/*
	 * Listen on event before render a theme, this event should
	 * call to assign some partials or breadcrumb template.
	 */
	'beforeRenderTheme' => function($theme)
	{
		$theme->partialComposer('header', function($view){
			$view->with('auth', Auth::user());
		});
	},

	/*
	 * Listen on event before render a layout, this should 
	 * call to assign style, script for a layout.
	 */
	'beforeRenderLayout' => [
		'mobile' => function($theme){
			$theme->asset()->usePath()->add('ipad', 'css/layouts/ipad.css');
		}
	]
];
~~~

## Basic usage of assets

You can add assets on the `asset` method of the config file. If yo want to add assets in your route you can get `$asset` variable from `$theme->asset()`.

~~~php
$asset->add('core-style', 'css/style.css');
// path: public/css/style.css

$asset->container('footer')->add('core-script', 'js/script.js');
// path: public/js/script.css

$asset->themePath()->add('custom', 'css/custom.css', ['core-style']);
// path: public/themes/[current-theme]/assets/css/custom.css
// This case has dependency with "core-style".

$asset->container('footer')->themePath()->add('custom', 'js/custom.js', array('core-script'));
// path: public/themes/[current theme]/assets/js/custom.js
// This case has dependency with "core-script".
~~~
> You can force use theme to look up existing theme by passing parameter to method: `$asset->themePath('default')`

Writing in-line style or script:

~~~php
// Dependency with.
$dependencies = [];

// Writing an in-line script.
$asset->writeScript('inline-script', '
	$(function() {
		console.log("Running");
	})', $dependencies);

// Writing an in-line style.
$asset->writeStyle('inline-style', 'h1{ font-size: 0.9em; }', $dependencies);

// Writing an in-line script, style without tag wrapper.
$asset->writeContent('custom-inline-script', '
	<script>
		$(function() {
			console.log("Running");
		});
	</script>', $dependencies);
~~~

Render styles and scripts in your blade layout:

~~~php
// Without container
@styles()

// With "footer" container
@scripts('footer')

// Get a specific path from the asset folder
@asset('img/image.png')
~~~
> Scripts and Style can be used with or without container

or a more complex way:

~~~php
{!! Theme::asset()->styles() !!}

{!! Theme::asset()->container('footer')->scripts() !!}
~~~

Direct path to theme asset:

~~~php
{!! Theme::asset()->url('img/image.png') !!}
~~~

#### Preparing group of assets:

Some assets you don't want to add on a page right now, but you still need them sometimes, so `cook` and `serve` is your magic.

Cook your assets.
~~~php
Theme::asset()->cook('backbone', function($asset)
{
	$asset->add('backbone', '//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js');
	$asset->add('underscorejs', '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js');
});
~~~

You can prepare on a global in package config:

~~~php
// Location: config/theme/config.php
....
	'events' => array(
		....
		// This event will fire as a global you can add any assets you want here.
		'asset' => function($asset)
		{
			// Preparing asset you need to serve after.
			$asset->cook('backbone', function($asset)
			{
				$asset->add('backbone', '//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js');
				$asset->add('underscorejs', '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js');
			});
		}
	)
....
~~~

Serve theme when you need:
~~~php
// At the controller.
Theme::asset()->serve('backbone');
~~~

Then you can get output:
~~~php
<html>
  <head>
      @styles()
      @styles('your-container')
  </head>
  <body>
      ...
      @scripts()
      @scripts('your-container')
  </body>
<html>
~~~

## Partials

Render a partial in your layouts or views:
~~~php
@partial('header', ['title' => 'Header']);
~~~

> This will look up to `/public/themes/[theme]/partials/header.php`, and will add a variable `$title` (optional)

Partial with current layout specific:
~~~php
Theme::partialWithLayout('header', ['title' => 'Header']);
~~~
> This will look up up to `/public/themes/[theme]/partials/[CURRENT_LAYOUT]/header.php`

Finding from both theme's partial and application's partials:

~~~php
Theme::watchPartial('header', ['title' => 'Header']);
~~~

##### Partial composer:

~~~php
$theme->partialComposer('header', function($view)
{
	$view->with('key', 'value');
});

// Working with partialWithLayout.
$theme->partialComposer('header', function($view)
{
	$view->with('key', 'value');
}, 'layout-name');
~~~

### Sections

The `@sections` blade directive simplify the access to `/partials/sections/` path:
~~~php
@sections('main')
~~~

It's the same as:
~~~php
@partial('sections.main')
~~~


## Magic methods

Magic methods allow you to set, prepend and append anything.

~~~php
$theme->setTitle('Your title');

$theme->appendTitle('Your appended title');

$theme->prependTitle('Hello: ....');

$theme->setAnything('anything');

$theme->setFoo('foo');

$theme->set('foo', 'foo');
~~~

Render in your blade layout or view:

~~~php
@get('foo')

@get('foo', 'Default msj')

Theme::getAnything();

Theme::getFoo();

Theme::get('foo', 'Default msj');
~~~

##### Check if the place exists or not:
~~~php
@getIfHas('title')
~~~
It's the same as:
~~~php
@if(Theme::has('title'))
	{{ Theme::get('title') }}
@endif
~~~
~~~php
@if(Theme::hasTitle())
	{{ Theme::getTitle() }}
@endif
~~~

Get argument assigned to content in layout or region:

~~~php
Theme::getContentArguments();
Theme::getContentArgument('name');
~~~
To check if it exists:
~~~php
Theme::hasContentArgument('name');
~~~

> Theme::place('content') is a reserve region to render sub-view.

## Preparing data to view

Sometimes you don't need to execute heavy processing, so you can prepare and use when you need it.

~~~php
$theme->bind('something', function()
{
	return 'This is bound parameter.';
});
~~~

Using bound data on view:

~~~php
echo Theme::bind('something');
~~~

## Breadcrumb

In order to use breadcrumbs, follow the instruction below:

~~~php
$theme->breadcrumb()->add('label', 'http://...')->add('label2', 'http:...');

// or

$theme->breadcrumb()->add([[
			 'label' => 'label1',
			 'url'   => 'http://...'
			],[
			 'label' => 'label2',
			 'url'   => 'http://...'
			]]);
~~~

To render breadcrumbs:
~~~php
{!! $theme->breadcrumb()->render() !!}
~~~
or
~~~php
{!! Theme::breadcrumb()->render() !!}
~~~

You can set up breadcrumbs template anywhere you want by using a blade template.

~~~php
$theme->breadcrumb()->setTemplate('
	<ul class="breadcrumb">
	  @foreach ($crumbs as $i => $crumb)
		  @if ($i != (count($crumbs) - 1))
			  <li><a href="{{ $crumb["url"] }}">{{ $crumb["label"] }}</a><span class="divider">/</span></li>
		  @else
			  <li class="active">{{ $crumb["label"] }}</li>
		  @endif
	  @endforeach
	</ul>
');
~~~

## Widgets

Theme has many useful features called "widget" that can be anything.
You can create a global widget class using artisan command:

~~~bash
php artisan theme:widget demo --global
~~~
> Widget tpl is located in "resources/views/widgets/{widget-tpl}.blade.php"

Creating a specific theme name.
~~~
php artisan theme:widget demo default 
~~~
> Widget tpl is located in "public/themes/[theme]/widgets/{widget-tpl}.blade.php"

Now you will see a widget class at /app/Widgets/WidgetDemo.php

~~~html
<h1>User Id: {{ $label }}</h1>
~~~

##### Calling your widget in layout or view:

~~~php
@widget('demo', ['label' => 'Hi!'])
~~~

or

~~~php
{!! Theme::widget('demo', ['label' => 'Hi!'])->render() !!}
~~~

## Using theme global
~~~php
use Facuz\Theme\Contracts\Theme;
use App\Http\Controllers\Controller;

class BaseController extends Controller {

	/**
	 * Theme instance.
	 *
	 * @var \Facuz\Theme\Theme
	 */
	protected $theme;

	/**
	 * Construct
	 *
	 * @return void
	 */
	public function __construct(Theme $theme)
	{
		// Using theme as a global.
		$this->theme = $theme->uses('default')->layout('ipad');
	}

}
~~~

To override theme or layout.
~~~php
public function getIndex()
{
	$this->theme->uses('newone');

	// or just override layout
	$this->theme->layout('desktop');

	$this->theme->of('somewhere.index')->render();
}
~~~
## Middleware:

A middleware is included out of the box if you want to define a theme or layout per route. For Laravel 5.4+ the middleware is installed by default.

##### To install it in Laravel before 5.4:

Only register it in `app\Http\Kernel.php`
~~~php
protected $routeMiddleware = [
    ...
    'setTheme' => \Facuz\Theme\Middleware\ThemeLoader::class,
];
~~~

##### Usage:
You can apply the middleware to a route or route-group with the string `'theme:[theme],[layout]'`
~~~php
Route::get('/', function () {
	...
	return Theme::view('index');
})->middleware('theme:default,layout');
~~~

Or using groups:

~~~php
Route::group(['middleware'=>'theme:default,layout'], function() {
    ...
});
~~~

## Helpers
##### Protect emails:
Protect the email address against bots or spiders that index or harvest addresses for sending you spam.
~~~php
{!! protectEmail('email@example.com') !!}
~~~
or shorter
~~~php
@protect('email@example.com')
~~~
##### Metadata init:
Print meta tags with common metadata.
~~~php
{!! meta_init() !!}
~~~
> Returns: `<meta charset="utf-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> <meta name="viewport" content="width=device-width, initial-scale=1">`
  
## Cheatsheet
##### Commands:
Command | Description 
------------ | -------------
`artisan theme:create name` | Generate theme structure.
`artisan theme:destroy name` | Remove a theme.
`artisan theme:list` | Show a list of all themes.
`artisan theme:duplicate name new` | Duplicate theme structure from other theme.
`artisan theme:widget demo default` | Generate widget structure.

##### Blade Directives:
Blade | Description 
------------ | -------------
`@get('value')` |  Magic method for get. 
`@getIfHas('value')` | Like `@get` but show only if exist.
`@partial('value', ['var'=> 'optional'])` | Load the partial from current theme.
`@section('value', ['var'=> 'optional'])` | Like `@partial` but load from sections folder.
`@content()` | Load the content of the selected view.
`@styles('optional')` | Render styles declared in theme config.
`@scripts('optional')` | Render scripts declared in theme config.
`@widget('value', ['var'=> 'optional'])` | Render widget.
`@protect('value')` | Protect the email address against bots or spiders.
`@dd('value')` | Dump and Die. 
`@d('value')` | Only dump.
`@dv()` | Dump, Die and show all defined variables.

##### Helpers:
Helper | Description 
------------ | -------------
`protectEmail('email')` | Protect the email address against bots or spiders.
`meta_init()` | Print meta tags with common metadata.
