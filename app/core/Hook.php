<?php
namespace App\Core;

class Hook
{
    private static array $filters = [];
    private static array $actions = [];

    /**
     * Add a filter hook
     */
    public static function addFilter(string $name, callable $callback, int $priority = 10): void
    {
        self::$filters[$name][$priority][] = $callback;
    }

    /**
     * Apply a filter hook (modifies a value)
     */
    public static function apply(string $name, mixed $value, ...$args): mixed
    {
        if (!isset(self::$filters[$name])) return $value;
        
        ksort(self::$filters[$name]);
        foreach (self::$filters[$name] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $value = call_user_func($callback, $value, ...$args);
            }
        }
        return $value;
    }

    /**
     * Add an action hook
     */
    public static function addAction(string $name, callable $callback, int $priority = 10): void
    {
        self::$actions[$name][$priority][] = $callback;
    }

    /**
     * Execute an action hook (does not modify value, just runs code)
     */
    public static function do(string $name, ...$args): void
    {
        if (!isset(self::$actions[$name])) return;
        
        ksort(self::$actions[$name]);
        foreach (self::$actions[$name] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func($callback, ...$args);
            }
        }
    }
}
