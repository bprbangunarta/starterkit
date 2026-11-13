<?php

namespace Database\Factories;

use App\Enums\AuditExportStatus;
use App\Models\AuditExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditExport> */
class AuditExportFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->superAdmin(),
            'status' => AuditExportStatus::Pending,
            'filters' => [],
            'last_id' => 0,
            'rows' => 0,
            'expires_at' => now()->addDay(),
        ];
    }

    public function ready(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AuditExportStatus::Ready,
            'path' => 'audit-exports/'.fake()->uuid().'.csv',
            'ready_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(['status' => AuditExportStatus::Failed]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => ['expires_at' => now()->subMinute()]);
    }
}
