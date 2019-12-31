<?php

/**
 * @see       https://github.com/mezzio/mezzio for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio;

use Closure;
use ReflectionFunction;
use ReflectionMethod;

trait IsCallableInteropMiddlewareTrait
{
    /**
     * Is the provided $middleware argument callable?
     *
     * Runs the argument against is_callable(). If that returns true, and the
     * value is an array with two elements, tests to ensure that the second
     * element is a method of the first.
     *
     * @param mixed $middleware
     * @return bool
     */
    private function isCallable($middleware)
    {
        if (! is_callable($middleware)) {
            return false;
        }

        if (! is_array($middleware)) {
            return true;
        }

        $classOrObject = array_shift($middleware);
        if (! is_object($classOrObject) && ! class_exists($classOrObject)) {
            return false;
        }

        $method = array_shift($middleware);
        return method_exists($classOrObject, $method);
    }

    /**
     * Is callable middleware interop middleware?
     *
     * @param mixed $middleware
     * @return bool
     */
    private function isCallableInteropMiddleware($middleware)
    {
        if (! $this->isCallable($middleware)) {
            return false;
        }

        $r = $this->reflectMiddleware($middleware);
        $paramsCount = $r->getNumberOfParameters();

        return $paramsCount === 2;
    }

    /**
     * Reflect a callable middleware.
     *
     * Duplicates MiddlewarePipe::getReflectionFunction, but that method is not
     * callable due to private visibility.
     *
     * @param callable $middleware
     * @return \ReflectionFunctionAbstract
     */
    private function reflectMiddleware(callable $middleware)
    {
        if (is_array($middleware)) {
            $class = array_shift($middleware);
            $method = array_shift($middleware);
            return new ReflectionMethod($class, $method);
        }

        if ($middleware instanceof Closure || ! is_object($middleware)) {
            return new ReflectionFunction($middleware);
        }

        return new ReflectionMethod($middleware, '__invoke');
    }
}
