<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\Model;
use Fluent\Auth\Config\Services;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthorizeFilter implements FilterInterface
{
    /**
     * The gate instance.
     *
     * @var \Fluent\Auth\Contracts\GateInterface
     */
    protected $gate;

    /**
     * Create a new filter instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->gate = Services::gate();
    }

    /**
     * {@inheritdoc}
     * 
     * @throws \Fluent\Auth\Exceptions\AuthenticationException
     * @throws \Fluent\Auth\Exceptions\AuthorizationException
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        [$ability] = $arguments;

        $this->gate->authorize($ability, $this->getGateArguments(isset($arguments[1]) ? $arguments[1] : null));

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    { 
    }

    /**
     * Get the arguments parameter for the gate.
     *
     * @param  array|null  $models
     * @return \CodeIgniter\Model|array|string
     */
    protected function getGateArguments($models)
    {
        if (is_null($models)) {
            return [];
        }

        return collect($models)->map(function ($model) {
            return $model instanceof Model ? $model : $this->getModel($model);
        })->all();
    }

    /**
     * Get the model to authorize.
     *
     * @param  string  $model
     * @return \CodeIgniter\Model|string
     */
    protected function getModel($model)
    {
        if ($this->isClassName($model)) {
            return trim($model);
        } else {
            return ((preg_match("/^['\"](.*)['\"]$/", trim($model), $matches)) ? $matches[1] : null);
        }
    }

    /**
     * Checks if the given string looks like a fully qualified class name.
     *
     * @param  string  $value
     * @return bool
     */
    protected function isClassName($value)
    {
        return strpos($value, '\\') !== false;
    }
}
