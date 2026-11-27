<?php

namespace App\Support\Contracts;

interface Module
{
    /** Path absolut ke routes.php modul, atau null bila modul tidak punya route. */
    public function routes(): ?string;

    /**
     * @return array<class-string, class-string> Model => Policy
     */
    public function policies(): array;

    /**
     * @return array<class-string, array<int, mixed>> Event => daftar listener
     */
    public function events(): array;
}
