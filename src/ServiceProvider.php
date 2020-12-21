<?php


namespace Cranux\Larakeaimao;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(LovelyCat::class, function(){
            return new LovelyCat(config('lovelycat'));
        });

        $this->app->alias(LovelyCat::class, 'lovelycat');
    }

    public function provides()
    {
        return [LovelyCat::class, 'lovelycat'];
    }
}