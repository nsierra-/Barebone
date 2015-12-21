<?php

class Router
{
	private $routes = [];
	private $app;

	public function __construct(Application &$app)
	{
		$this->app = $app;
	}

	public function addRoute(string $name, string $pattern, array $params)
	{
		if (isset($this->routes[$name]))
			Tools::errorAndDie('Some route already has this name : ' . $name);
		$this->routes[$name] = new Route($pattern, $params);
	}

	public function handleRequest(Request $request)
	{
		foreach ($this->routes as $route)
		{
			$result = [];

			if (($result = $route->handleRequest($request)) !== false)
			{
				$app = &$this->app;
				$controlers = $app->controlers;
				$handlerClass = $result['handlerClass'];
				$handlerMethod = $result['handlerMethod'];

				if (isset($controlers[$handlerClass]) === false)
					$controlers[$handlerClass] = new $handlerClass;
				return $controlers[$handlerClass]->{$handlerMethod}($request, $result['matches']);
			}
		}
		return new Response(Response::NOT_FOUND, 'text/html', '404 Bi4tch');
	}
}
