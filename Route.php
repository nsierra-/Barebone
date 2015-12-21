<?php

class Route
{
	protected $pattern;
	protected $handlerClasses;
	protected $handlerMethods;
	protected $allowedMethods;

	public function __construct(string $pattern, array $params)
	{
		$tmp = '~^/' . trim($pattern, '/') . '/?$~';

		if (preg_match($tmp, null) === false)
			Tools::errorAndDie('Route pattern given is not a regular expression : ' . $pattern);
		else if (isset($params['handler']) === false)
			Tools::errorAndDie('Route wasn\'t given a handler');

		$this->pattern = $tmp;
		$this->_setHandlers($params);
		$this->allowedMethods = $params['allowedMethods'] ?? [];
	}

	public function handleRequest(Request $request)
	{
		$matches = [];

		if ($this->_parseUrl($matches, $request) && $this->_isMethodAllowed($request))
		{
			return [
				'matches' 		=> $matches,
				'handlerClass'	=> $this->_getHandlerClassForRequest($request),
				'handlerMethod'	=> $this->_getHandlerMethodForRequest($request),
			];
		}
		return false;
	}

	private function _parseUrl(array &$matches, Request $request)
	{
		return preg_match($this->pattern, $request->path, $matches) == 1;
	}

	private function _isMethodAllowed(Request $request)
	{
		return
			!empty($this->allowedMethods)
				&& in_array($request->method, $this->allowedMethods)
			|| empty($this->allowedMethods)
		;
	}

	private function _setHandlers(array $params)
	{
		if (is_string($params['handler']))
		{
			$tmp = $this->_extractHandlerString($params['handler']);
			$this->handlerClasses['default'] = $tmp[0];
			$this->handlerMethods['default'] = $tmp[1];
		}

		if (is_array($params['handler']))
		{
			foreach ($params['handler'] as $key => $value)
			{
				$tmp = $this->_extractHandlerString($value);
				$this->handlerClasses[strtoupper($key)] = $tmp[0];
				$this->handlerMethods[strtoupper($key)] = $tmp[1];
			}
		}
	}

	private function _extractHandlerString(string $str)
	{
		$tmp = explode('.', $str);

		if (count($tmp) != 2)
			Tools::errorAndDie('Handler parameter is invalid. You should only specify class and method : ' . $str);
		return $tmp;
	}

	private function _getHandlerClassForRequest($request)
	{
		$method = strtoupper($request->method);
		$classes = $this->handlerClasses;

		if (isset($classes[$method]))
			return $classes[$method];
		else if (isset($classes['default']))
			return $classes['default'];
		Tools::errorAndDie('Missing default handler for route ' . $this->pattern);
	}

	private function _getHandlerMethodForRequest($request)
	{
		$method = strtoupper($request->method);
		$methods = $this->handlerMethods;

		if (isset($methods[$method]))
			return $methods[$method];
		else if (isset($methods['default']))
			return $methods['default'];
		Tools::errorAndDie('Missing default handler for route ' . $this->pattern);
	}
}
