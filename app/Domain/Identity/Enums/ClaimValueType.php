<?php

namespace App\Domain\Identity\Enums;

enum ClaimValueType: string
{
    case String = 'string';
    case Boolean = 'boolean';
    case Array = 'array';
    case Json = 'json';
}
