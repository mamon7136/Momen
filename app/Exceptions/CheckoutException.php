<?php

namespace App\Exceptions;

class CheckoutException extends \RuntimeException
{
    public function __construct(public array $errors)
    {
        parent::__construct(implode(' | ', $errors));
    }
}
