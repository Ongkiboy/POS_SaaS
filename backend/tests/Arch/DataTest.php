<?php

use Symfony\Component\Finder\Finder;

/**
 * Semua class di folder Data/ pada submodul (TD §2.2).
 *
 * @return array<int, string>
 */
function archDataClasses(): array
{
    $path = dirname(__DIR__, 2).'/app/Modules';

    if (! is_dir($path)) {
        return [];
    }

    $classes = [];

    foreach ((new Finder)->files()->in($path)->name('*.php') as $file) {
        $relative = str_replace('\\', '/', $file->getRelativePathname());

        if (! str_contains('/'.$relative, '/Data/')) {
            continue;
        }

        $classes[] = 'App\\Modules\\'.str_replace('/', '\\', substr($relative, 0, -4));
    }

    sort($classes);

    return $classes;
}

it('semua class di Data/ adalah final readonly', function () {
    $melanggar = [];

    foreach (archDataClasses() as $class) {
        $ref = new ReflectionClass($class);

        // Enum dan interface di Data/ tidak diperiksa.
        if ($ref->isEnum() || $ref->isInterface()) {
            continue;
        }

        if (! $ref->isFinal() || ! $ref->isReadOnly()) {
            $melanggar[] = $class;
        }
    }

    expect($melanggar)->toBeEmpty();
});
