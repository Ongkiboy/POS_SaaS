<?php

namespace App\Support\Module;

use App\Support\Contracts\Module;

abstract class BaseModule implements Module
{
    public function routes(): ?string
    {
        return null;
    }

    public function policies(): array
    {
        return [];
    }

    public function events(): array
    {
        return [];
    }
}
