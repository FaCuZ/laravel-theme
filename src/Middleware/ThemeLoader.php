<?php namespace Facuz\Theme\Middleware;

use Closure, Theme;

class ThemeLoader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param String $theme
     * @param String $layout
     * @return mixed
     */
    public function handle($request, Closure $next, $theme = null, $layout = null)
    {
        if(isset($theme)) Theme::uses($theme);
        if(isset($layout)) Theme::layout($layout);

        return $next($request);
       
/*
        $response = $next($request);
        
        $originalContent = $response->getOriginalContent();

        if(!is_string($originalContent)) {
            $view_name = $originalContent->getName();
            
            $data = $originalContent->getData();
        } else {
            $view_name = $response->exception->getTrace()[0]['args'][0];
        }
        

        return $theme->scope($view_name, $data)->render();
*/
    }


}