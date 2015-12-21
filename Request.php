<?php

class Request
{
	protected $query;
	protected $postRequest;
	protected $attributes;
	protected $cookies;
	protected $files;
	protected $server;
	protected $content;
	protected $path;

	public function __construct(
		array $query       = [],
		array $postRequest = [],
		array $attributes  = [],
		array $cookies     = [],
		array $files       = [],
		array $server      = [],
		$content           = null
	)
	{
		$this->initialize(
			$query,
			$postRequest,
			$attributes,
			$cookies,
			$files,
			$server,
			$content
		);
	}

	public function __get(string $what)
	{
		if (property_exists($this, $what))
			return $this->{$what};

		switch ($what)
		{
			case 'method': return $this->server['REQUEST_METHOD'];

			default:
				error_log("Application class has no $what attribute.");
				return null;
		}

	}

	public function initialize(
		array $query       = [],
		array $postRequest = [],
		array $attributes  = [],
		array $cookies     = [],
		array $files       = [],
		array $server      = [],
		$content           = null
	)
	{
		$this->query       = $query;
		$this->postRequest = $postRequest;
		$this->attributes  = $attributes;
		$this->cookies     = $cookies;
		$this->files       = $files;
		$this->server      = $server;
		$this->content     = $content;
		$this->path        = parse_url($server['REQUEST_URI'])['path'];
	}

	public static function createFromGlobals()
	{
		return new Request(
			$_GET,
			$_POST,
			[],
			$_COOKIE,
			$_FILES,
			$_SERVER
		);
	}
}
