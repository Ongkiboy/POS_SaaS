<?php

namespace App\Modules\Platform\Audit\Jobs;

use App\Modules\Platform\Audit\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class WriteAuditLogJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $payload  Sudah berisi business_id eksplisit.
     */
    public function __construct(public readonly array $payload) {}

    public function handle(): void
    {
        AuditLog::query()->create($this->payload);
    }
}
