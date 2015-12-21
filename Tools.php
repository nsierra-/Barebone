<?php

class Tools
{
	public static function dump($var, $label = null, $echo = true)
	{
		$label = ($label === null) ? '' : rtrim($label) . '=';
		ob_start();
		var_dump($var);
		$output = ob_get_clean();
		$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);

		if (PHP_SAPI == 'cli')
			$output = PHP_EOL . $label . $output . PHP_EOL;
		else
		{
			$output = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
			$output = '<pre>' . PHP_EOL . $label . $output . '</pre>'. PHP_EOL;
		}

		if ($echo)
			echo $output;
		return $output;
	}

	public static function errorAndDie(string $msg)
	{
		error_log($msg . PHP_EOL);
		die();
	}
}
