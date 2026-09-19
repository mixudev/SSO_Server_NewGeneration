<?php

namespace App\Domain\Identity\Contracts;

use App\Models\Identity\SigningKey;
use Illuminate\Support\Collection;

interface KeyManagerInterface
{
    public function generate(): SigningKey;

    public function active(): SigningKey;

    /** @return Collection<int, SigningKey> */
    public function verificationKeys(): Collection;

    public function rotate(): SigningKey;
}
