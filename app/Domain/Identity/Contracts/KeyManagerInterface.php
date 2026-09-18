<?php

namespace App\Domain\Identity\Contracts;

use App\Models\Identity\SigningKey;

interface KeyManagerInterface
{
    public function generate(): SigningKey;

    public function active(): SigningKey;

    public function rotate(): SigningKey;
}
