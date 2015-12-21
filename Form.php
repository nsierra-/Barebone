<?php

class Form
{
	private $formFields = '';
	private $method;
	private $action;

	public function __construct(string $action = '', string $method = 'POST')
	{
		$this->action = $action;
		$this->method = $method;
	}

	public function &input(array $params = [])
	{
		$type = $params['type'] ?? 'text';
		$name = $params['name'] ?? '';
		$value = $params['value'] ?? '';
		$html = '<input ';

		$html .=                      'type="'  . $type  . '" ';
		$html .= empty($name)  ? '' : 'name="'  . $name  . '" ';
		$html .= empty($value) ? '' : 'value="' . $value . '" ';
		$html = trim($html) . '>';

		$this->formFields .= $html;
		return $this;
	}

	public function &submit(string $placeholder = 'Send')
	{
		$this->formFields .= '<button type="submit">' . $placeholder . '</button>';
		return $this;
	}

	public function getHTML()
	{
		return
			'<form action="' . (empty($this->action) ? '#' : $this->action) . '" '
			. 'method="' . strtolower($this->method) . '">'
			. $this->formFields
			. '</form>'
		;
	}

	public function __get(string $what)
	{
		if (property_exists($this, $what))
			return $this->{$what};
	}

	public function __set(string $what, $value)
	{
		if (property_exists($this, $what))
			return $this->{$what} = $value;
	}
}
