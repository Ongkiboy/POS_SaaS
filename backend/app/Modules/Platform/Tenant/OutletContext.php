<?php

namespace App\Modules\Platform\Tenant;

use App\Modules\Platform\Outlet\Models\Outlet;

/**
 * Outlet aktif request ini (dari header X-Outlet-Id). Scoped binding.
 */
final class OutletContext
{
    private ?Outlet $outlet = null;

    public function set(?Outlet $outlet): void
    {
        $this->outlet = $outlet;
    }

    public function outlet(): ?Outlet
    {
        return $this->outlet;
    }

    public function id(): ?int
    {
        return $this->outlet === null ? null : (int) $this->outlet->getKey();
    }
}
