<?php
/**
 * Created by PhpStorm.
 * User: rex
 * Email: caoliang@simpleedu.com.cn
 * Date: 2018/9/25
 * Time: 4:04 PM
 */

namespace Libs\Common;

/**
 * 简单粗暴的容器服务类。
 *
 * Class Container
 * @package Libs\common
 */
class Container
{
    private static $_instances = [];

    /**
     * 获取实例。
     *
     * @param $key
     * @return mixed|null
     */
    public static function get($key) {
        return self::$_instances[$key] ?? null;
    }

    /**
     * 设置实例。
     *
     * @param $key
     * @param $instance
     */
    public static function set($key, $instance) {
        self::$_instances[$key] = $instance;
    }
}