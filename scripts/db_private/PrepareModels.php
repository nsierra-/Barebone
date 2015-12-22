<?php

class PrepareModels
{
	public function run()
	{
		$appConfig = configGet('appConfig');
		$modelsDir = $appConfig['modelsDir'] ?? realpath(__DIR__ . str_repeat(DIRECTORY_SEPARATOR . '..', 3) . DIRECTORY_SEPARATOR . 'models');

		foreach (glob($modelsDir . DIRECTORY_SEPARATOR . '*.php') as $file)
		{
			$generated = PHP_EOL;
			$contents = file_get_contents($file);
			$attributes = $this->_retrieveAttributes($contents);

			if ($attributes === false)
				continue ;

			foreach ($attributes as $attribute)
			{
				if ($this->_isGetterPresent($contents, $attribute) === false)
					$this->_addGetter($generated, $attribute);
				if ($this->_isSetterPresent($contents, $attribute) === false)
					$this->_addSetter($generated, $attribute);
			}
			$this->_addGeneratedToFile($contents, $generated, $file);
			echo "Getters and setters successfully added to $file !" . PHP_EOL;
		}
	}

	private function _retrieveAttributes(&$contents)
	{
		$matches = [];
		$pattern = '~(?:private|protected|public)\s+\$(\w+)~';

		if (preg_match_all($pattern, $contents, $matches) === false)
			die('An error occured retrieving attributes.');
		if (!isset($matches[1]) || empty($matches[1]))
			return false;
		return $matches[1];
	}

	private function _isSetterPresent(&$contents, $attribute)
	{
		$pattern = '~public\s+function\s+set' . ucfirst($attribute) . '~';

		if (preg_match($pattern, $contents) === 1)
			return true;
		return false;
	}

	private function _isGetterPresent(&$contents, $attribute)
	{
		$pattern = '~public\s+function\s+get' . ucfirst($attribute) . '~';

		if (preg_match($pattern, $contents) === 1)
			return true;
		return false;
	}

	private function _addSetter(&$generated, $attribute)
	{
		$content =
			"\tpublic function set" . ucfirst($attribute) . '($' . $attribute . ')' . PHP_EOL
			. "\t{" . PHP_EOL
			. "\t\t" . '$this->' . $attribute . ' = $' . $attribute . ';' . PHP_EOL
			. "\t}" . PHP_EOL . PHP_EOL
		;
		$generated .= $content;
	}

	private function _addGetter(&$generated, $attribute)
	{
		$content =
			"\tpublic function get" . ucfirst($attribute) . '()' . PHP_EOL
			. "\t{" . PHP_EOL
			. "\t\t" . 'return $this->' . $attribute . ';' . PHP_EOL
			. "\t}" . PHP_EOL . PHP_EOL
		;
		$generated .= $content;
	}

	private function _addGeneratedToFile(&$contents, &$generated, $file)
	{
		if ($generated === PHP_EOL)
			return ;
		$classClosingBrace = strrpos($contents, '}');
		$remains = substr($contents, $classClosingBrace);
		$newContent = substr_replace($contents, $generated . $remains, $classClosingBrace);
		file_put_contents($file, $newContent);
	}
}
