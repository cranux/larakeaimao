<?php


namespace Cranux\Larakeaimao;


class YaAiServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(YaAiHttp::class, function(){
            return new YaAiHttp(config('lovelycat'));
        });

        $this->app->alias(YaAiHttp::class, 'yaaihttp');
    }

    public function provides()
    {
        return [YaAiHttp::class, 'yaaihttp'];
    }
}