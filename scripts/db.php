#!/usr/local/bin/php
<?php

error_reporting(-1);
set_include_path(get_include_path() . __DIR__ . DIRECTORY_SEPARATOR . 'db_private');

include_once 'internals.php';

function launch($argc, $argv)
{
	setup($argc, $argv);
	$command = null;

	switch (configGet('args')[0])
	{
		case "create":
			$command = new DbCreate;
			break ;
		default:
			echo 'Unknown command';
			return ;
	}
	$command->run();
}

launch($argc, $argv);

?>
