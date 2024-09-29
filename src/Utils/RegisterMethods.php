<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils;

use InvalidArgumentException;

trait RegisterMethods
{

    /**
     * 注册
     *
     * @param string $key
     * @param mixed $value
     * @return bool|null
     */
    public static function register(string $key, mixed $value): ?bool
    {
        if (!self::$_handlers[$key] ?? null) {
            if ($value = self::registerVerify($value)) {
                self::$_handlers[$key] = $value;
                return true;
            }
            return false;
        }
        return null;
    }

    /**
     * 注册校验
     *
     * @param mixed $value 注册值
     * @return false|object|string false:注册失败 其他:注册成功
     * @throws InvalidArgumentException 如果参数值不合法，请抛出该异常
     */
    abstract public static function registerVerify(mixed $value): mixed;

    /**
     * 注销
     *
     * @param string $key
     * @return bool|null
     */
    public static function unregister(string $key): ?bool
    {
        $res = null;
        if (self::$_handlers[$key] ?? null) {
            $res = self::unregisterExecute($key);
            if ($res) {
                unset(self::$_handlers[$key]);
            }
        }

        return $res;
    }

    /**
     * 注销执行
     *
     * @param string $key
     * @return bool false:注册失败 true:注册成功
     * @throws InvalidArgumentException 如果参数值不合法，请抛出该异常
     */
    abstract public static function unregisterExecute(string $key): bool;


    /**
     * 获取指定handler 或 获取所有
     *
     * @return array
     */
    public static function getHandler(null|string $key): mixed
    {
        return $key === null ? self::$_handlers : self::$_handlers[$key] ?? null;
    }
}