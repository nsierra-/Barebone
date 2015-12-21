<?php

class DbCreate
{
	private $prompter;

	public function __construct()
	{
		$this->prompter = new Prompter;
		$this->_setupPrompter();
	}

	public function run()
	{
		$prompter = $this->prompter;
		$results = $prompter->run();

		if ($results === false)
			die('An error occured.');

		$this->_updateConfig();
		$this->_createDb();
		$this->_saveConfig();
	}

	private function _setupPrompter()
	{
		$this->prompter
			->addStep(
				'How would you like your database to be named ?',
				'~^[A-Za-z]+$~',
				[
					'invalidRegexMsg' => 'Your database name should be only characters.',
					'resultIndex'     => 'name',
					'defaultValue'    => $this->_retrieveProjectName()
				]
			)
			->addStep(
				'Which charset do you wand to use ?',
				'~^\w+$~',
				[
					'resultIndex'  => 'charset',
					'defaultValue' => 'utf8mb4'
				]
			)
			->addStep(
				'Overwrite config file ?',
				'~^$~',
				[
					'resultIndex'  => 'overwrite',
					'yesOrNo'      => true,
					'defaultValue' => 'y'
				]
			)
			->addStep(
				'Are all the parameters correct ?',
				'~^$~',
				[
					'resultIndex'  => 'confirmation',
					'yesOrNo'      => true,
					'defaultValue' => 'y'
				]
			)
		;
	}

	private function _createDb()
	{
		$dbConfig = configGet('dbConfig');

		try
		{
			$dbh = new PDO($dbConfig['dsn'], $dbConfig['user'], $dbConfig['password']);
			$dbh->query(
				'CREATE DATABASE IF NOT EXISTS ' . $dbConfig['dbName']
				. ' CHARACTER SET ' . $dbConfig['charset']
			);
			$dbh = null;
		}
		catch (PDOException $e)
		{
			error_log('PDO Error : ' . $e->getMessage());
			die();
		}
	}

	private function _saveConfig()
	{
		$result = $this->prompter->getResults();

		if ($result['overwrite'] == 'y')
			file_put_contents(configGet('configFile'), jsonEncodeNicely(configGet('dbConfig')));
	}

	private function _updateConfig()
	{
		$result = $this->prompter->getResults();

		$dbConfig = configGet('dbConfig');
		$dbConfig['dbName'] = $result['name'];
		$dbConfig['charset'] = $result['charset'];
		configSet('dbConfig', $dbConfig);
	}

	private function _retrieveProjectName()
	{
		$path = realpath(__DIR__ . str_repeat(DIRECTORY_SEPARATOR . '..', 3));
		return basename($path);
	}
}
