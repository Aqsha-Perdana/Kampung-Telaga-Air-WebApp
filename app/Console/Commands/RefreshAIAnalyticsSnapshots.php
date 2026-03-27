<?php

namespace App\Console\Commands;

use App\Services\AI\AnalyticsSnapshotService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RefreshAIAnalyticsSnapshots extends Command
{
    protected $signature = 'ai:snapshots:refresh {--date=} {--days=1}';

    protected $description = 'Refresh daily analytics snapshots for Admin AI Chat';

    public function __construct(private readonly AnalyticsSnapshotService $snapshotService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $dateOption = $this->option('date');
        $endDate = $dateOption ? Carbon::parse((string) $dateOption) : Carbon::today();

        if (!$this->snapshotService->hasSnapshotTables()) {
            $this->error('Snapshot tables belum tersedia. Jalankan migration terlebih dahulu.');
            return self::FAILURE;
        }

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $date = $endDate->copy()->subDays($offset)->toDateString();
            $result = $this->snapshotService->refreshForDate($date);

            $this->line(sprintf(
                '[%s] finance ok, resources %d rows, packages %d rows',
                $date,
                count($result['resources'] ?? []),
                count($result['packages'] ?? [])
            ));
        }

        $this->info('AI analytics snapshots refreshed successfully.');

        return self::SUCCESS;
    }
}
