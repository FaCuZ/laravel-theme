# Theme Management for Laravel

Laravel-Theme is a theme management for Laravel 5+, it is the easiest way to organize your skins, layouts and assets.

This package is based on [teepluss\theme](https://github.com/teepluss/laravel-theme/)

>##### Differences with teepluss version:
>- Compatible with laravel 5.4+.
>- Removed twig compatibility (Reduces the package by 94%).
>- Blade directives
>- Better base template.
>- Simplified configuration.
>- More commands and helper functions.
>- Better README file.

## Usage

Theme has many features to help you get started with Laravel

- [Installation](#installation)
- [Create new theme](#create-new-theme)
- [Configuration](#configuration)
- [Basic usage](#basic-usage)
- [Symlink from another view](#symlink-from-another-view)
- [Basic usage of assets](#basic-usage-of-assets)
- [Partials](#partials)
- [Magic methods](#magic-methods)
- [Preparing data to view](#preparing-data-to-view)
- [Breadcrumb](#breadcrumb)
- [Widgets](#widgets)
- [Using theme global](#using-theme-global)
- [Cheatsheet](#cheatsheet)


## Installation

To get the latest version of laravel-themes simply require it in your `composer.json` file.

~~~
"facuz/laravel-themes": "^3.0"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

Once Theme is installed you need to register the service provider with the application. Open up `config/app.php` and find the `providers` key.

~~~
'providers' => [

    Facuz\Theme\ThemeServiceProvider::class,

]
~~~

Theme also ships with a facade which provides the static syntax for creating collections. You can register the facade in the `aliases` key of your `config/app.php` file.

~~~
'aliases' => [

    'Theme' => Facuz\Theme\Facades\Theme::class,

]
~~~
Publish config using artisan CLI.

~~~
php artisan vendor:publish --provider="Facuz\Theme\ThemeServiceProvider"
~~~

It's recommended to add to the .env file the theme that we are going to use
~~~
APP_THEME=default
~~~



## Create new theme

The first time you have to create theme "default" structure, using the artisan command:

~~~
php artisan theme:create default
~~~
> If you change the facade name you can add an option --facade="Alias".

To delete an existing theme, use the command:

~~~
php artisan theme:destroy default
~~~

If you wanna list all installed themes use the command:

~~~
php artisan theme:list
~~~

Create from the applicaton without CLI.

~~~php
Artisan::call('theme:create', ['name' => 'foo']);
~~~

## Configuration

After the config is published, you will see a global config file `/config/theme.php`, all the configuration can be replaced by a config file inside a theme: `/public/themes/[theme]/config.php`

The config is convenient for setting up basic CSS/JS, partial composer, breadcrumb template and also metas.

~~~php
'events' => array(

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

        /*
        Breadcrumb template.
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
         */
    },

	/*
     * Listen on event before render a theme, this event should
     * call to assign some assets, breadcrumb template.
     */
    'beforeRenderTheme' => function($theme)
    {
        // You may use this event to set up your assets.
        /*
        $theme->asset()->usePath()->add('core', 'core.js');
        $theme->asset()->add('jquery', 'vendor/jquery/jquery.min.js');
        $theme->asset()->add('jquery-ui', 'vendor/jqueryui/jquery-ui.min.js', array('jquery'));

        $theme->partialComposer('header', function($view){
            $view->with('auth', Auth::user());
        });
        */
    },

    /*
     * Listen on event before render a layout, this should 
     * call to assign style, script for a layout.
     */
    'beforeRenderLayout' => array(

        'default' => function($theme){
            // $theme->asset()->usePath()->add('ipad', 'css/layouts/ipad.css');
        }

    )

)
~~~

## Basic usage

~~~php
namespace App\Http\Controllers;

use Theme;

class HomeController extends Controller {

    public function getIndex()
    {
        $theme = Theme::uses('default')->layout('mobile');

        $view = array(
            'name' => 'Teepluss'
        );

        // home.index will look up the path 'resources/views/home/index.php'
        return $theme->of('home.index', $view)->render();

        // Specific status code with render.
        return $theme->of('home.index', $view)->render(200);

        // home.index will look up the path 'resources/views/mobile/home/index.php'
        return $theme->ofWithLayout('home.index', $view)->render();

        // home.index will look up the path 'public/themes/default/views/home/index.php'
        return $theme->scope('home.index', $view)->render();

        // home.index will look up the path 'public/themes/default/views/mobile/home/index.php'
        return $theme->scopeWithLayout('home.index', $view)->render();

        // Looking for a custom path.
        return $theme->load('app.somewhere.viewfile', $view)->render();

        // Working with cookie.
        $cookie = Cookie::make('name', 'Tee');
        return $theme->of('home.index', $view)->withCookie($cookie)->render();
    }

}
~~~
> Get only content "$theme->of('home.index')->content();".

Finding from both theme's view and application's view.
~~~php
$theme = Theme::uses('default')->layout('default');

return $theme->watch('home.index')->render();
~~~

To check whether a theme exists.

~~~php
Theme::exists('themename');
~~~
> Returns boolean.

To find the location of a view.

~~~php
$which = $theme->scope('home.index')->location();

echo $which; // themer::views.home.index

$which = $theme->scope('home.index')->location(true);

echo $which; // ./public/themes/name/views/home/index.blade.php
~~~

#### Render from string:

~~~php
return $theme->string('<h1>{{ $name }}</h1>', array('name' => 'Teepluss'), 'blade')->render();
~~~

#### Compile string:

~~~php
$template = '<h1>Name: {{ $name }}</h1><p>{{ Theme::widget("WidgetIntro", array("userId" => 9999, "title" => "Demo Widget"))->render() }}</p>';

echo Theme::blader($template, array('name' => 'Teepluss'));
~~~

#### Symlink from another view

This is a nice feature when you have multiple files that have the same name, but need to be located as a separate one.

~~~php
// Theme A : /public/themes/a/views/welcome.blade.php

// Theme B : /public/themes/b/views/welcome.blade.php

// File welcome.blade.php at Theme B is the same as Theme A, so you can do link below:

// ................

// Location: public/themes/b/views/welcome.blade.php
Theme::symlink('a');

// That's it!
~~~

## Basic usage of assets

Add assets in your route.

~~~php
// path: public/css/style.css
$theme->asset()->add('core-style', 'css/style.css');

// path: public/js/script.css
$theme->asset()->container('footer')->add('core-script', 'js/script.js');

// path: public/themes/[current theme]/assets/css/custom.css
// This case has dependency with "core-style".
$theme->asset()->usePath()->add('custom', 'css/custom.css', array('core-style'));

// path: public/themes/[current theme]/assets/js/custom.js
// This case has dependency with "core-script".
$theme->asset()->container('footer')->usePath()->add('custom', 'js/custom.js', array('core-script'));
~~~
> You can force use theme to look up existing theme by passing parameter to method:
> $theme->asset()->usePath('default')

Writing in-line style or script.

~~~php

// Dependency with.
$dependencies = array();

// Writing an in-line script.
$theme->asset()->writeScript('inline-script', '
    $(function() {
        console.log("Running");
    })
', $dependencies);

// Writing an in-line style.
$theme->asset()->writeStyle('inline-style', '
    h1 { font-size: 0.9em; }
', $dependencies);

// Writing an in-line script, style without tag wrapper.
$theme->asset()->writeContent('custom-inline-script', '
    <script>
        $(function() {
            console.log("Running");
        });
    </script>
', $dependencies);
~~~

Render styles and scripts in your blade layout.

~~~php
// Without container
@styles()

// With "footer" container
@scripts('footer')
~~~

Direct path to theme asset.

~~~php
echo Theme::asset()->url('img/image.png');
~~~

#### Preparing group of assets:

Some assets you don't want to add on a page right now, but you still need them sometimes, so "cook" and "serve" is your magic.

Cook your assets.
~~~php
Theme::asset()->cook('backbone', function($asset)
{
    $asset->add('backbone', '//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js');
    $asset->add('underscorejs', '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js');
});
~~~

You can prepare on a global in package config.

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

Serve theme when you need.
~~~php
// At the controller.
Theme::asset()->serve('backbone');
~~~

Then you can get output:
~~~php
---
<head>
    @styles()
    @styles('YOUR_CONTAINER')
</head>
<body>
	---
	@scripts()
    @scripts('YOUR_CONTAINER')
</body>
---
~~~

## Partials

Render a partial in your layouts or views:
~~~php
@partial('header', ['title' => 'Header']);
~~~

> This will look up to `/public/themes/[theme]/partials/header.php`, and will add a variable `$title` (opcional)

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

The `@section` blade directive simplify the access to `/partials/sections/` path:
~~~php
@section('main');
~~~

It's the same as:
~~~php
@partial('sections.main');
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

Theme::getAnything();

Theme::getFoo();

Theme::get('foo');
~~~

or use place:

~~~php
Theme::place('anything');

Theme::place('foo', 'default-value-if-it-does-not-exist');
~~~

##### Check if the place exists or not:
~~~php
@getIfHas('title')
~~~
It's the same as:
~~~php
@if(Theme::has('title'))
    {{ Theme::place('title') }}
@endif
~~~
~~~php
@if(Theme::hasTitle())
    {{ Theme::getTitle() }}
@endif
~~~

Get argument assigned to content in layout or region.

~~~php
Theme::getContentArguments();
Theme::getContentArgument('name');
~~~
To check if it exists
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

Using bound data on view.

~~~php
echo Theme::bind('something');
~~~

## Breadcrumb

In order to use breadcrumbs, follow the instruction below:

~~~php
$theme->breadcrumb()->add('label', 'http://...')->add('label2', 'http:...');

// or

$theme->breadcrumb()->add(array(
    array(
        'label' => 'label1',
        'url'   => 'http://...'
    ),
    array(
        'label' => 'label2',
        'url'   => 'http://...'
    )
));
~~~

To render breadcrumbs.

~~~php
echo $theme->breadcrumb()->render();

// or

echo Theme::breadcrumb()->render();
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
> Widget tpl is located in "resources/views/widgets/{widget-tpl}.{extension}"

Creating a specific theme name.
~~~
php artisan theme:widget demo default 
~~~
> Widget tpl is located in "public/themes/[theme]/widgets/{widget-tpl}.{extension}"

Now you will see a widget class at /app/Widgets/WidgetDemo.php

~~~html
<h1>User Id: {{ $label }}</h1>
~~~

##### Calling your widget in layout or view:

~~~php
echo Theme::widget('demo', array('label' => 'Demo Widget'))->render();
~~~

## Using theme global
~~~php
use Facuz\Theme\Contracts\Theme;
use App\Http\Controllers\Controller;

class BaseController extends Controller {

    /**
     * Theme instance.
     *
     * @var \Teepluss\Theme\Theme
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


## Helpers
##### Protect emails:
Protect the email address against bots or spiders that index or harvest addresses for sending you spam.
~~~php
{!! protectEmail('email@example.com') !!}
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
`artisan theme:create name` | Generate theme structure
`artisan theme:destroy name` | Remove theme exsisting
`artisan theme:list` | Show a list of all themes.
`artisan theme:widget name` | Generate widget structure

##### Blade Directives:
Blade | Description 
------------ | -------------
`@get('value')` |  Magic method for get. 
`@getIfHas('value')` | Like `@get` but show only if exist
`@partial('value', ['var'=> 'optional'])` | Load the partial from current theme.
`@section('value', ['var'=> 'optional'])` | Like `@partial` but load from sections folder
`@content()` | Load the content of the selected view
`@styles('optional')` | Render styles declared in theme config.
`@scripts('optional')` | Render scripts declared in theme config.

##### Helpers:
Helper | Description 
------------ | -------------
`protectEmail('email')` | Protect the email address against bots or spiders
`meta_init()` | Print meta tags with common metadata.













