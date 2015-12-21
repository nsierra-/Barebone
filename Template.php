<?php

class Template
{
	protected $path;
	protected $vars;
	protected $extension;
	protected $params;
	protected $viewsPath = '';

	public function __construct($path, array $vars = [], array $params = [])
	{
		$this->path = $path;
		$this->vars = $vars;
		$this->extension = $params['extension'] ?? 'stpl';
	}

	public function __set(string $what, $value)
	{
		if ($what == 'viewsPath')
			$this->viewsPath = $value;
	}

	public function getContents()
	{
		$fullPath = $this->getFullPath();

		if (!is_file($fullPath))
			return "Template error, file not foud : $fullPath";
		$template = file_get_contents($fullPath);
		return $this->_fillTemplate($template);
	}

	public function getFullPath()
	{
		return
			rtrim($this->viewsPath, DIRECTORY_SEPARATOR)
			. DIRECTORY_SEPARATOR
			. $this->path
			. '.'
			. $this->extension
		;
	}

	private function _fillTemplate(string &$template)
	{
		$matches = [];

		if (preg_match_all('/\{(\w+)\}/', $template, $matches) != 0)
		{
			foreach ($matches[1] as $token)
			{
				if (!array_key_exists($token, $this->vars))
				{
					error_log(
						'Variable '.$token.' requested by template at '
						.$fullPath.' not found. Have you passed it '
						.'correctly ?'
					);
				}
				else
				{
					$template = preg_replace(
						'/\{'.$token.'\}/',
						$this->vars[$token],
						$template
					);
				}
			}
		}
		return $template;
	}
}
