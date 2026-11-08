<?php

namespace App\Modules\Platform\Rbac;

/**
 * Matriks permission final SRS v3.0 §2.1. Satu-satunya sumber di kode.
 */
final class PermissionMatrix
{
    public const ROLES = ['OWNER', 'MANAGER', 'CASHIER', 'INVENTORY', 'KITCHEN'];

    public const PERMISSIONS = [
        'sales.view', 'sales.create', 'sales.update', 'sales.cancel', 'sales.pay', 'sales.refund',
        'discounts.apply',
        'shifts.open', 'shifts.close', 'shifts.view_all',
        'cash.manage',
        'orders.kitchen_view', 'orders.kitchen_update',
        'products.view', 'products.create', 'products.update', 'products.delete',
        'recipes.view', 'recipes.manage',
        'inventory.view', 'inventory.adjust', 'inventory.count', 'inventory.waste',
        'reports.view', 'reports.export',
        'dashboard.view',
        'users.view', 'users.create', 'users.update', 'users.delete',
        'settings.view', 'settings.update',
        'payment_config.manage',
        'outlets.view', 'outlets.manage',
        'audit.view',
        'purchases.view', 'purchases.create', 'purchases.receive', 'purchases.cancel',
        'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete',
    ];

    /**
     * @return array<string, list<string>>
     */
    public static function rolePermissions(): array
    {
        return [
            'OWNER' => self::PERMISSIONS,
            'MANAGER' => array_values(array_diff(self::PERMISSIONS, ['payment_config.manage', 'outlets.manage'])),
            'CASHIER' => [
                'sales.view', 'sales.create', 'sales.update', 'sales.cancel', 'sales.pay',
                'discounts.apply', 'shifts.open', 'shifts.close', 'cash.manage',
                'products.view', 'reports.view', 'dashboard.view', 'outlets.view',
            ],
            'INVENTORY' => [
                'products.view', 'recipes.view',
                'inventory.view', 'inventory.adjust', 'inventory.count', 'inventory.waste',
                'reports.view', 'dashboard.view', 'outlets.view',
                'purchases.view', 'purchases.create', 'purchases.receive', 'purchases.cancel',
                'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete',
            ],
            'KITCHEN' => [
                'orders.kitchen_view', 'orders.kitchen_update',
                'products.view', 'recipes.view', 'inventory.view', 'inventory.waste', 'outlets.view',
            ],
        ];
    }
}
