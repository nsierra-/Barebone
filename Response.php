<?php

class Response
{
	const SUCCESS               = 200;
	const MOVED_PERMANENTLY     = 301;
	const MOVED_TEMPORARILY     = 302;
	const FORBIDDEN             = 403;
	const NOT_FOUND             = 404;
	const INTERNAL_SERVER_ERROR = 500;
	const SERVICE_UNAVAILABLE   = 503;

	public static $statusTexts = [
		self::SUCCESS 				=> 'Ok',
		self::MOVED_PERMANENTLY 	=> 'Moved Permanently',
		self::MOVED_TEMPORARILY 	=> 'Found',
		self::FORBIDDEN				=> 'Forbidden',
		self::NOT_FOUND				=> 'Not Found',
		self::INTERNAL_SERVER_ERROR	=> 'Internal Server Error',
		self::SERVICE_UNAVAILABLE	=> 'Service Unavailable'

	];

	protected $status;
	protected $content;
	protected $contentType;

	public function __construct(int $status = self::SUCCESS, string $contentType = 'text/html', string $content = '')
	{
		$this->status = $status;
		$this->content = $content;
		$this->contentType = $contentType;
	}

	public function send()
	{
		header($_SERVER['SERVER_PROTOCOL'] . ' ' . $this->status . ' ' . self::$statusTexts[$this->status]);
		header('Content-Type: ' . $this->contentType);

		if (!empty($this->content))
			echo $this->content;
	}

	public function sendContent()
	{

	}

	public function __get(string $what)
	{
		if (property_exists($this, $what))
			return $this->{$what};
	}

	public function __set(string $what, $value)
	{
		if (property_exists($this, $what))
			$this->{$what} = $value;
	}
}
