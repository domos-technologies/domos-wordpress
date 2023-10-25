<?php

namespace Domos\Core\Exceptions;
class InvalidInquiry extends \Exception
{
    public array $errorData;

	public function __construct(array $errorData)
	{
		$this->errorData = $errorData;

		parent::__construct($errorData['message']);
	}
}
