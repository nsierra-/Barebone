<?php

$scriptConfig = array(
	'commands' => [
		'create',
		'prepareModels',
		'update'
	],
	'configDir' => realpath(
		__DIR__ . DIRECTORY_SEPARATOR
		. '..' . DIRECTORY_SEPARATOR
		. '..' . DIRECTORY_SEPARATOR
		. '..' . DIRECTORY_SEPARATOR
		. 'config'
	),
	'options' => [
		'help',
		'configDir:'
	]
);

function    configGet($param)
{
    global  $scriptConfig;

    return $scriptConfig[$param];
}

function    configSet($param, $value)
{
    global  $scriptConfig;

    $scriptConfig[$param] = $value;
}

function	printHelp()
{
	die('Usage ./db.php [command]' . PHP_EOL);
}

function	loadConfigFiles()
{
	$configDir = configGet('configDir');
	$dbConfigFile = $configDir . DIRECTORY_SEPARATOR . 'database.json';
	$appConfigFile = $configDir . DIRECTORY_SEPARATOR . 'application.json';

	loadConfigFile($dbConfigFile, 'dbConfig');
	loadConfigFile($appConfigFile, 'appConfig');
}

function	retrieveArgs($argv)
{
	$commands = configGet('commands');
	$args = [];

	foreach ($commands as $command)
	{
		$pos = array_search($command, $argv);

		if ($pos === false)
			continue ;
		$args = array_slice($argv, $pos);
		break ;
	}
	configSet('args', $args);
}

function	loadConfigFile($configFile, $index)
{
	if (!is_file($configFile))
		die('Configuration file ' . $configFile . ' not found.');
	$asJson = json_decode(file_get_contents($configFile), true);

	if ($asJson === null)
		die('Configuration file ' . $configFile . ' is invalid.');
	configSet($index, $asJson);
	configSet($index . 'File', $configFile);
}

function	loadOptions($argv)
{
	$options = getopt('', configGet('options'));

	if (isset($options['help']))
		return false;

	if (isset($options['configDir']))
		configSet('configDir', $options['configDir']);
}

function	setupAutoload()
{
	$dir = __DIR__;

	spl_autoload_register(
			function ($class) use ($dir)
			{
				if (is_file($dir . DIRECTORY_SEPARATOR . $class . '.php'))
					include_once $dir . DIRECTORY_SEPARATOR . $class . '.php';
			}
	);
}

function	setup($argc, $argv)
{
	if ($argc == 1 || loadOptions($argv) === false)
		printHelp();
	retrieveArgs($argv);
	loadConfigFiles();
	setupAutoload();
}

function	jsonEncodeNicely($in, $indent = 0)
{
    $_myself = __FUNCTION__;
    $_escape = function ($str)
    {
        return preg_replace("!([\b\t\n\r\f\"\\'])!", "\\\\\\1", $str);
    };

    $out = '';

    foreach ($in as $key => $value)
    {
        $out .= str_repeat("\t", $indent + 1);
        $out .= "\"".$_escape((string)$key)."\": ";

        if (is_object($value) || is_array($value))
        {
            $out .= "\n";
            $out .= $_myself($value, $indent + 1);
        }
        else if (is_bool($value))
            $out .= $value ? 'true' : 'false';
        else if (is_null($value))
            $out .= 'null';
        else if (is_string($value))
            $out .= "\"" . $_escape($value) ."\"";
        else
            $out .= $value;

        $out .= ",\n";
    }

    if (!empty($out))
        $out = substr($out, 0, -2);

    $out = str_repeat("\t", $indent) . "{\n" . $out;
    $out .= "\n" . str_repeat("\t", $indent) . "}";

    return $out;
}

?>
