<?php

class Update
{
	const CREATE = 1;
	const ALTER = 2;

	private $typesMapping = [
		'string' => 'VARCHAR',
		'int'    => 'INTEGER',
		'short'  => 'SMALLINT',
		'long'   => 'BIGINT',
		'float'  => 'FLOAT',
		'bool'   => 'TINYINT',
		'date'   => 'DATETIME',
		'text'   => 'TEXT',
		'object' => 'BLOB',
		'array'  => 'BLOB'
	];

	private $dbh;

	public function run()
	{
		$modelsDir = $appConfig['modelsDir'] ?? realpath(__DIR__ . str_repeat(DIRECTORY_SEPARATOR . '..', 3) . DIRECTORY_SEPARATOR . 'models');
		$appConfig = configGet('appConfig');
		$dbConfig  = configGet('dbConfig');
		$dsn       = $dbConfig['dsn'] . ';dbname=' . $dbConfig['dbName'];
		$this->dbh = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		foreach (glob($modelsDir . DIRECTORY_SEPARATOR . '*.php') as $file)
		{
			$mdFile = $this->_retrieveMetadataFile($file);
			$md = $this->_retrieveMetadata($mdFile, $file);

			if ($md === false)
				continue ;
			$tableName = $md['table'] ?? pathinfo($file, PATHINFO_FILENAME);
			unset($md['table']);

			if ($this->_alterOrCreate($tableName) == self::CREATE)
				$this->_createTable($tableName, $md);
			else
				$this->_alterTable($tableName, $md);
			echo "Successfully updated $tableName into database !" . PHP_EOL;
		}
		$this->dbh = null;
	}

	private function _alterTable($tableName, $md)
	{
		$query = "ALTER TABLE $tableName ";
		$tableColumns = $this->_retrieveColumnNames($tableName);
		$this->_deleteSuperfluousColumns($md, $query, $tableColumns);
		$this->_addNewColumns($md, $query, $tableColumns);
		$this->_updateRemainingColumns($md, $query);
		$query = rtrim($query, ', ');
		$this->dbh->query($query);
	}

	private function _updateRemainingColumns($md, &$query)
	{
		foreach ($md as $fieldName => $infos)
		{
			$this->_addFieldToQuery($query, $fieldName, $infos, true);
			$query = trim($query) . ', ';
		}
	}

	private function _retrieveColumnNames($tableName)
	{
		$sth = $this->dbh->prepare("DESCRIBE $tableName");
		$sth->execute();
		return $sth->fetchAll(PDO::FETCH_COLUMN);
	}

	private function _addNewColumns($md, &$query, $columns)
	{
		$toAdd = [];

		foreach ($md as $columnName => $foo)
		{
			if ($columnName != 'table' && !in_array($columnName, $columns))
				$toAdd[] = $columnName;
		}

		foreach ($toAdd as $columnName)
		{
			$query .= " ADD ";
			$this->_addFieldToQuery($query, $columnName, $md[$columnName]);
			$query .= ",";
			unset($md[$columnName]);
		}
	}

	private function _deleteSuperfluousColumns($md, &$query, $columns)
	{
		$toDelete = [];

		foreach ($columns as $columnName)
		{
			if ($columnName != 'id' && !isset($md[$columnName]))
				$toDelete[] = $columnName;
		}

		foreach ($toDelete as $columnName)
			$query .= " DROP $columnName,";
	}

	private function _addFieldToQuery(&$query, $fieldName, $infos, $alter = false)
	{
		$fieldType     = is_array($infos) ? $this->typesMapping[$infos['type']] : $this->typesMapping[$infos];
		$fieldLength   = $infos['length']        ?? false;
		$null          = $infos['null']          ?? true;
		$defaultValue  = $infos['default']       ?? [];
		$autoIncrement = $infos['autoIncrement'] ?? false;
		$unique        = $infos['unique']        ?? false;

		if (is_string($defaultValue))
			$defaultValue = "'$defaultValue'";

		if (!is_int($fieldLength) && $fieldType == 'VARCHAR')
			$fieldLength = 24;

		if ($alter)
			$query .= "CHANGE $fieldName $fieldName ";
		else
			$query .=  $fieldName . ' ';
		$query .= $fieldType;
		$query .= is_int($fieldLength)     ? '(' . $fieldLength . ') ' : ' ';
		$query .= $null === true           ? 'NULL '                   : 'NOT NULL ';
		$query .= !is_array($defaultValue) ? "DEFAULT $defaultValue "  : '';
		$query .= $autoIncrement === true  ? 'AUTO_INCREMENT '         : '';
		$query .= $unique === true         ? 'UNIQUE '                 : '';
		$query = rtrim($query);
	}

	private function _createTable($tableName, $md)
	{
		$query = "CREATE TABLE $tableName (" . 'id INTEGER AUTO_INCREMENT NOT NULL, ';

		foreach ($md as $fieldName => $value)
		{
			$this->_addFieldToQuery($query, $fieldName, $value);
			$query = trim($query) . ', ';
		}
		$query .= 'PRIMARY KEY(id));';
		$this->dbh->query($query);
	}

	private function _alterOrCreate($tableName)
	{
		try
		{
			$this->dbh->query("SELECT 1 FROM $tableName LIMIT 1");
			return self::ALTER;
		}
		catch (PDOException $e)
		{
			if ($e->getCode() === "42S02")
				return self::CREATE;
			throw $e;
		}
	}

	private function _retrieveMetadataFile($file)
	{
		$tmp = pathinfo($file);
		return $tmp['dirname'] . DIRECTORY_SEPARATOR . $tmp['filename'] . '.json';
	}

	private function _retrieveMetadata($mdFile, $file)
	{
		if (!is_file($mdFile))
		{
			echo 'WARNING: ' . basename($mdFile) . ' not found. Not updating ' . basename($file) . ' model.' . PHP_EOL;
			return false;
		}
		$asJson = json_decode(file_get_contents($mdFile), true);

		if ($asJson === null)
		{
			echo 'An error occured retrieving ' . $mdFile . PHP_EOL;
			return false;
		}
		if (empty($asJson))
		{
			echo 'WARNING: ' . basename($mdFile) . ' is empty.' . PHP_EOL;
			return false;
		}
		return $asJson;
	}
}
