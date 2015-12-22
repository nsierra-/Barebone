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
		case "prepareModels":
			$command = new PrepareModels;
			break ;
		case "update":
			$command = new Update;
			break ;
		default:
			die('Unknown command' . PHP_EOL);
	}
	try
	{
		$command->run();
	}
	catch (PDOException $e)
	{
		die('An error occured ! ' . $e->getMessage());
	}
}

launch($argc, $argv);

?>
