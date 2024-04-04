<?php

namespace Framework\Routing\Generator;

use Framework\Http\Request;
use Framework\Routing\RouteCollection;
use Framework\Support\Helpers\Str;

/**
 * The UrlGenerator class generates URLs for routes and resources within the application.
 *
 * This class provides methods to generate full URLs based on the current request and route configuration.
 *
 * @package Framework\Routing\Generator
 */
class UrlGenerator
{
    /**
     * Collection of registered routes.
     *
     * @var RouteCollection
     */
    protected RouteCollection $routes;

    /**
     * Request instance representing a HTTP request.
     *
     * @var Request
     */
    private Request $request;

    /**
     * UrlGenerator constructor.
     *
     * @param RouteCollection $routes The route collection.
     * @param Request $request The Request instance.
     */
    public function __construct(RouteCollection $routes, Request $request)
    {
        $this->routes = $routes;
        $this->request = $request;
    }

    /**
     * Get the Route URL generator instance.
     *
     * @return RouteUrlGenerator
     */
    protected function route_url(): RouteUrlGenerator
    {
        if (!isset($this->route_generator)) {
            $this->route_generator = new RouteUrlGenerator($this);
        }

        return $this->route_generator;
    }

    /**
     * Generate a URL for the given route name.
     *
     * @param string $name The name of the route.
     * @param array $parameters [optional] Parameters to substitute into the route URI.
     * @param bool $absolute [optional] Whether to generate an absolute URL (including scheme and host).
     * @return string The generated URL.
     */
    public function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $route_path = $this->routes->get($name, $parameters);

        if ($absolute) {
            $route_path = $this->request->root() . ltrim($route_path, '/');
        }

        return $route_path;
    }

    /**
     * Generate an absolute URL for the given path with route parameters, optionally excluding the host.
     *
     * @param string $path The path to the resource.
     * @param array $parameters [optional] Route parameters to include in the URL.
     * @param bool $absolute [optional] Whether to exclude the host from the generated URL.
     * @return string The generated absolute URL.
     */
    public function to(string $path, array $parameters = [], bool $absolute = false): string
    {
        return $this->route_url()->to($path, $parameters, $absolute);
    }

    /**
     * Get the full base URL for the application.
     *
     * @return string|null The full base URL for the application. Returns null if 'app.url' is not set.
     */
    public function full(): ?string
    {
        return config('app.url') ?: $this->request->root() . $this->get_relative_path();
    }

    /**
     * Get the relative path from the document root to the project directory.
     *
     * @return string The relative path from the document root to the project directory.
     */
    private function get_relative_path(): string
    {
        return str_replace(Str::finish($this->request->server('DOCUMENT_ROOT'), '/'), '', base_path());
    }

    /**
     * Get the current URL.
     *
     * @return string The current URL.
     */
    public function current(): string
    {
        return $this->request->root() . ltrim($this->request->path(), '/');
    }
}
