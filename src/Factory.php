<?php


namespace Cranux\Larakeaimao;

/**
 * Class Factory
 * @package Cranux\Larakeaimao
 * @method static \Cranux\Larakeaimao\LovelyCat LovelyCat(array $config)
 */
class Factory
{
    /**
     * @param $name
     * @param array $config
     * @return mixed
     */
    public static function make($name, array $config)
    {
        $name = Kernel\Support\Str::studly($name);

        $className = "\\Cranux\\Larakeaimao\\{$name}";

        return new $className($config);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }
}