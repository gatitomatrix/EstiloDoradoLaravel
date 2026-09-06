<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    /** @param array<int,array<string,mixed>> $detalles */
    public function __construct(public array $detalles = [])
    {
        parent::__construct('No hay suficiente stock para algunos productos.');
    }
}
