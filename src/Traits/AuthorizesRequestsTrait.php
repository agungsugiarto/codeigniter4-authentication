<?php

namespace Fluent\Auth\Traits;

use Fluent\Auth\Config\Services;

trait AuthorizesRequestsTrait
{
    /**
     * Authorize a given action for the current user.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return \Fluent\Auth\Authorization\Response
     *
     * @throws \Fluent\Auth\Exceptions\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);

        return Services::gate()->authorize($ability, $arguments);
    }

    /**
     * Authorize a given action for a user.
     *
     * @param  \Fluent\Auth\Contracts\AuthenticatorInterface $user
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return \Fluent\Auth\Authorization\Response
     *
     * @throws \Fluent\Auth\Exceptions\AuthorizationException
     */
    public function authorizeForUser($user, $ability, $arguments = [])
    {
        [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);

        return Services::gate()->forUser($user)->authorize($ability, $arguments);
    }

    /**
     * Guesses the ability's name if it wasn't provided.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return array
     */
    protected function parseAbilityAndArguments($ability, $arguments)
    {
        if (is_string($ability) && strpos($ability, '\\') === false) {
            return [$ability, $arguments];
        }

        $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];

        return [$this->normalizeGuessedAbilityName($method), $ability];
    }

    /**
     * Normalize the ability name that has been guessed from the method name.
     *
     * @param  string  $ability
     * @return string
     */
    protected function normalizeGuessedAbilityName($ability)
    {
        $map = $this->resourceAbilityMap();

        return $map[$ability] ?? $ability;
    }

    /**
     * Authorize a resource action based on the incoming request.
     *
     * @param  string|array  $model
     * @param  string|array|null  $parameter
     * @param  array  $options
     * @param  \CodeIgniter\HTTP\RequestInterface|null  $request
     * @return void
     */
    public function authorizeResource($model, $parameter = null, array $options = [], $request = null)
    {
        helper('url');

        $model = is_array($model) ? implode(',', $model) : $model;

        $parameter = is_array($parameter) ? implode(',', $parameter) : $parameter;

        $parameter = $parameter ?: url_title(class_basename($model), '_', true);

        $middleware = [];

        foreach ($this->resourceAbilityMap() as $method => $ability) {
            $modelName = in_array($method, $this->resourceMethodsWithoutModels()) ? $model : $parameter;

            $middleware["can:{$ability},{$modelName}"][] = $method;
        }

        foreach ($middleware as $middlewareName => $methods) {
            $this->middleware($middlewareName, $options)->only($methods);
        }
    }

    /**
     * Get the map of resource methods to ability names.
     *
     * @return array
     */
    protected function resourceAbilityMap()
    {
        return [
            'index' => 'viewAny',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'destroy' => 'delete',
        ];
    }

    /**
     * Get the list of resource methods which do not have model parameters.
     *
     * @return array
     */
    protected function resourceMethodsWithoutModels()
    {
        return ['index', 'create', 'store'];
    }
}