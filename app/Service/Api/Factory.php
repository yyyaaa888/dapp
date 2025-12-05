<?php

namespace App\Service\Api;

use Illuminate\Support\Str;

class Factory
{
    # 实例
    protected static $class = [];

    /**
     * @Author   Chen
     * @DateTime 2021-07-02
     * @param    [param]
     * @param    [type]     $name [description]
     */
    public static function make($name)
    {
        $modelName = Str::studly($name);
        $application = "App\\Service\\Api\\{$modelName}";

        if (!isset(self::$class[$application])) {
            self::$class[$application] = new $application();
        }

        return self::$class[$application];
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::make($name, $arguments);
    }
}
