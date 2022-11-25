<?php

namespace lowcold\ClosureTable;

use Illuminate\Support\ServiceProvider;

class ClosureTableServiceProvider extends ServiceProvider
{
    /**
     * 服务提供者加是否延迟加载.
     */

    protected $defer = true; // 延迟加载服务

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton('closure-table', function () {
            return new ClosureTable;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()

    {
        // 因为延迟加载 所以要定义 provides 函数 具体参考laravel 文档
        return ['closure-table'];
    }
}
