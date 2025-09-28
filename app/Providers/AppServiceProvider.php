<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Blade::directive('money', function ($amount) {
            return "<?php echo '$' . number_format($amount, 2); ?>";
        });

        if(Schema::hasTable('configs')) {
            $config = Config::get();
            foreach ($config as $value) {
                $result[$value['config_title']] = $value['value'];
            }
            if(!empty($result)){
                View::share('setting', $result);
            }
            date_default_timezone_set("Asia/Dhaka");
        }
        

    }

    /**
     * Register any application services.
     *
     * @return void
     */

    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
        // ...
    }
}
