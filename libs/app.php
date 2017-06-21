<?php

namespace Yoqeen\Libs;

class App
{
    private static $instance;

    /**
     * Constructor - Define some variables.
     */
    public function __construct() {
        $this->autoload();
    }

    /**
     * Singleton instance.
     *
     * @return $this
     */
    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Magic autoload.
     */
    public function autoload()
    {
        spl_autoload_register(function($className)
        {
            $namespace = strtolower(str_replace("\\", DS, __NAMESPACE__));
            $className = str_replace("\\", DS, $className);
            $classNameOnly = basename($className);
            $className = strtolower(substr($className, 0, -strlen($classNameOnly))) . lcfirst($classNameOnly);

            if (is_file($class = BASE_PATH . (empty($namespace) ? "" : $namespace . "/") . "{$className}.php")) {
                return include_once($class);
            } elseif (is_file($class = BASE_PATH . "{$className}.php")) {
                return include_once($class);
            }
        });
    }

    /**
     * Magic call.
     *
     * @param string   $method
     * @param array    $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return  isset($this->{$method}) && is_callable($this->{$method})
                ? call_user_func_array($this->{$method}, $args) : null;
    }

    /**
     * Set new variables and functions to this class.
     *
     * @param string      $k
     * @param mixed    $v
     */
    public function __set($k, $v)
    {
        $this->{$k} = $v instanceof \Closure ? $v->bindTo($this) : $v;
    }
}
