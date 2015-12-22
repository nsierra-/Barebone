<?php

require_once 'Tools.php';

class Application
{
	private	$router;
	private	$rootDir;
	private	$frameworkDir;
	private	$viewsDir;
	private	$modelsDir;
	private	$modelsMetadataDir;
	private	$controlersDir;
	private $configDir;

	private $controlers = [];

	public function __construct($configDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config')
	{
		$configDir = realpath($configDir);
		$config = $this->_retrieveConfig($configDir);
		$this->_setDirectories($config);
		$this->_configureAutoload();
		$this->router = new Router($this);
	}

	public function run()
	{
		$request = Request::createFromGlobals();
		ob_start();
		$controlerResponse = $this->router->handleRequest($request);
		$response = $this->_getResponse($controlerResponse);

		$buf = ob_get_flush();
		$response->content = $buf . $response->content;
		$response->send();
	}

	public function __get(string $what)
	{
		if (property_exists($this, $what))
			return $this->{$what};
		error_log("Application class has no $what attribute.");
		return null;
	}

	private function _retrieveConfig($configDir)
	{
		$configFile = $configDir . DIRECTORY_SEPARATOR . 'application.json';
		$config = [];

		if (!is_file($configFile))
			Tools::errorAndDie('Expected application.json at ' . $configDir . ' not found.');
		else if (($config = json_decode(file_get_contents($configFile), true)) === null)
			Tools::errorAndDie('An error occured loading configuration. Please make sure your config file is correct.');
		return $config;
	}

	private function _getResponse($controlerResponse)
	{
		if (is_string($controlerResponse))
			return new Response(Response::SUCCESS, 'text/html', $controlerResponse);
		else if ($controlerResponse instanceof Response)
			return $controlerResponse;
		else if ($controlerResponse instanceof Template)
		{
			$controlerResponse->viewsPath = $this->viewsDir;
			return new Response(Response::SUCCESS, 'text/html', $controlerResponse->getContents());
		}
		else
			return new Response(Response::INTERNAL_SERVER_ERROR, 'text/plain', 'Internal Error');
	}

	private function _configureAutoload()
	{
		spl_autoload_register(
			function ($class)
			{
				if (is_file($this->frameworkDir . DIRECTORY_SEPARATOR . $class . '.php'))
					include_once $this->frameworkDir . DIRECTORY_SEPARATOR . $class . '.php';
				else if (is_file($this->controlersDir . DIRECTORY_SEPARATOR . $class . '.php'))
					include_once $this->controlersDir . DIRECTORY_SEPARATOR . $class . '.php';
				else if (is_file($this->modelsDir . DIRECTORY_SEPARATOR . $class . '.php'))
					include_once $this->modelsDir . DIRECTORY_SEPARATOR . $class . '.php';
			}
		);
	}

	private function _setDirectories(array $config = [])
	{
		$this->frameworkDir      = __DIR__;
		$this->rootDir           = $config['rootDir']           ?? realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
		$this->viewsDir          = $config['viewsDir']          ?? $this->rootDir . DIRECTORY_SEPARATOR . 'views';
		$this->modelsDir         = $config['modelsDir']         ?? $this->rootDir . DIRECTORY_SEPARATOR . 'models';
		$this->modelsMetadataDir = $config['modelsMetadataDir'] ?? $this->rootDir . DIRECTORY_SEPARATOR . 'models';
		$this->controlersDir     = $config['controlersDir']     ?? $this->rootDir . DIRECTORY_SEPARATOR . 'controlers';
		$this->configDir         = $config['configDir']         ?? $this->rootDir . DIRECTORY_SEPARATOR . 'config';
		$this->_resetIncludePath($config['keepOldIncludePath'] ?? true);
	}

	private function _resetIncludePath(bool $keepOld = true)
	{
		$newIncludePath = $keepOld ? get_include_path() : '.';
		$newIncludePath .=
			PATH_SEPARATOR . __DIR__ . PATH_SEPARATOR
			. $this->modelsDir . PATH_SEPARATOR
			. $this->controlersDir . PATH_SEPARATOR
		;
		set_include_path($newIncludePath);
	}
}
