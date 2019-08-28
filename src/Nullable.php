<?php

namespace Imanghafoori\Helpers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class Nullable
{
    private $result;

    private $predicate = null;

    private $message = '';

    /**
     * Nullable constructor.
     *
     * @param mixed $value
     * @param array $message
     * @param callable $predicate
     */
    public function __construct($value, array $message = [], $predicate = null)
    {
        $this->result = $value;
        $this->message = $message;
        $this->predicate = $predicate;
    }

    public function getOr($default)
    {
        $p = $this->getPredicate();

        if (!$p($this->result)) {
            return $this->result;
        }

        if (is_callable($default)) {
            return call_user_func($default);
        }

        return $default;
    }

    public function getOrAbort($code, $message = '', array $headers = [])
    {
        if (!is_null($this->result)) {
            return $this->result;
        }

        abort($code, $message, $headers);
    }

    public function getOrSend($callable)
    {
        if (!is_null($this->result)) {
            return $this->result;
        }

        if (is_callable($callable)) {
            $callable = call_user_func_array($callable, $this->message);
        }

        if (is_a($callable, Response::class)) {
            $response = $callable;
        }

        if (isset($response)) {
            throw new HttpResponseException($response);
        }

        throw new \InvalidArgumentException('You must provide a valid http response or a callable.');
    }

    public function getOrThrow($exception, ...$parameters)
    {

        if (!is_null($this->result)) {
            return $this->result;
        }

        throw_if(true, $exception, ...$parameters);
    }

    private function getPredicate()
    {
        if (is_callable($this->predicate)) {
            $p = $this->predicate;
        } else {
            $p = function ($r) {
                return is_null($r);
            };
        }

        return $p;
    }
}
