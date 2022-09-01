<?php

namespace Cranux\Larakeaimao;

class IHttpServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(IHttp::class, function(){
            return new IHttp(config('lovelycat'));
        });

        $this->app->alias(IHttp::class, 'ihttp');
    }

    public function provides()
    {
        return [IHttp::class, 'ihttp'];
    }
}