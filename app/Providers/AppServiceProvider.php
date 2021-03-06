<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Request;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
        view()->composer('*', function($view)
        {
            if (Auth::check()){
                $home_flock = Auth::user()->getFlock();
            }
            else{
                $home_flock = 'false';
            }
            /* *********** logic from app.blade *********** */
            $url = Request::path();
            $elements = explode('/', $url);
            if(Request::path() === ('home')){
                $help_page = $elements[0];
            }
            else{
                $help_page = $elements[1];
            }
            /* ************ */
            /* ******** logic from sheeplist page********** */
            $string = array_slice($elements,0,-1);
            $print = implode("/",$string).'/print';
            $filtered_pages = array('ewes','tups');
            /* ********* */
            $view->with([
                'home_flock'    => $home_flock,
                'help_page'     => $help_page,
                'filtered_pages'=> $filtered_pages,
                'print'         => $print,
                'elements'      => $elements
            ]);
        });

	}
	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'App\Services\Registrar'
		);
        if ($this->app->environment() == ('local')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
	}

}
