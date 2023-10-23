<?php

namespace Domos\Core\Exceptions;
class CouldNotSync extends \Exception
{
    public $errorCode;

    public function __construct(string $message = "", $errorCode = 'unknown') {
        parent::__construct($message, 0, null);

        $this->errorCode = $errorCode;
    }
}
