<?php

namespace SchemaImmo\Exceptions;

class InvalidDataException extends \Exception
{
    public function __construct(public string $key, ?string $message = null)
    {
        parent::__construct($message ?? "Invalid data for key: $key");
    }
}