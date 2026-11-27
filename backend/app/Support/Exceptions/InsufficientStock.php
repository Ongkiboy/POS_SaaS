<?php

namespace App\Support\Exceptions;

use Throwable;

class InsufficientStock extends ApiException
{
    /**
     * @param  array<string, mixed>|null  $details
     */
    public function __construct(
        string $message = 'Stok tidak cukup.',
        ?array $details = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            errorCode: 'INSUFFICIENT_STOCK',
            httpStatus: 422,
            details: $details,
            previous: $previous,
        );
    }

    public static function forIngredient(string $ingredientName): self
    {
        return new self("Stok tidak cukup: {$ingredientName}.", [
            'ingredients' => [$ingredientName],
        ]);
    }

    /**
     * @param  array<int, string>  $ingredientNames
     */
    public static function forIngredients(array $ingredientNames): self
    {
        $list = implode(', ', $ingredientNames);

        return new self("Stok tidak cukup: {$list}.", [
            'ingredients' => $ingredientNames,
        ]);
    }
}
