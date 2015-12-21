<?php

class Prompter
{
	public $steps = [];
	public $results = [];

	public function addStep(
		string $msg,
		string $validationPattern,
		array $params = []
	)
	{
		if (preg_match($validationPattern, null) === false)
			die('Prompter fatal error. ' . $validationPattern . ' is not a regex.');

		$step = [
			'msg'               => trim($msg),
			'validationPattern' => $validationPattern,
			'retryMsg'          => $params['retryMsg']        ?? 'Try again.',
			'invalidRegexMsg'   => $params['invalidRegexMsg'] ?? 'This input is invalid.',
			'maxTries'          => $params['maxTries']        ?? 30,
			'defaultValue'      => $params['defaultValue']    ?? '',
			'resultIndex'       => $params['resultIndex']     ?? '',
			'yesOrNo'           => $params['yesOrNo']         ?? false,
		];

		if ($step['yesOrNo'])
			$step['validationPattern'] = '~^y|n$~';

		$this->steps[] = $step;
		return $this;
	}

	public function run()
	{
		foreach ($this->steps as $step)
		{
			$validated         = false;
			$matches           = [];
			$defaultValue      = $step['defaultValue'];
			$resultIndex       = $step['resultIndex'];

			$result = $this->_askLoop($step);

			if (is_string($result))
				$this->_addResult($result, $resultIndex);
			else if (!empty($defaultValue))
				$this->_addResult($defaultValue, $resultIndex);
			else
				return false;
		}
		return $this->getResults();
	}

	public function getResults()
	{
		return $this->results;
	}

	private function _askLoop($step)
	{
		$matches           = [];
		$validated         = false;
		$validationPattern = $step['validationPattern'];
		$invalidRegexMsg   = $step['invalidRegexMsg'];
		$maxTries          = $step['maxTries'];
		$defaultValue      = $step['defaultValue'];

		for ($i = 0; $i < $maxTries; $i++)
		{
				$this->_printMessage($step, $i);

				$usrInput = trim(fgets(STDIN));

				if (!empty($defaultValue) && empty($usrInput))
					return $defaultValue;
				if (preg_match($validationPattern, $usrInput, $matches) == 1)
				{
					$validated = true;
					break ;
				}
				else
					echo $invalidRegexMsg . PHP_EOL;
		}

		if ($validated)
			return $matches[0];
		return false;
	}

	private function _printMessage($step, $i)
	{
		$msg          = $step['msg'];
		$retryMsg     = $step['retryMsg'];
		$yesOrNo      = $step['yesOrNo'];
		$defaultValue = $step['defaultValue'];

		if ($i == 0)
		{
			if ($yesOrNo)
				$msg .= ' (y or n)';

			if (!empty($defaultValue))
				$msg .= ' (default : "' . $defaultValue . '")';
			echo $msg . PHP_EOL;
		}
		else
			echo $retryMsg . PHP_EOL;
	}

	private function _addResult($result, $index)
	{
		$this->results[] = $result;

		if (!empty($index))
			$this->results[$index] = $result;
	}
}
