<?php

namespace App\Services\AI;

use App\Support\AI\AdminAIDomainRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EntityResolverService
{
    public function __construct(private readonly AdminAIDomainRegistry $registry)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function resolve(string $message): ?array
    {
        $normalized = Str::lower(trim($message));

        foreach ($this->registry->entities() as $type => $entityConfig) {
            $aliases = array_map('strtolower', $entityConfig['aliases'] ?? []);
            $mentionsType = empty($aliases) || $this->containsAny($normalized, $aliases);
            $match = $this->findBestMatch($normalized, $type, $entityConfig, $mentionsType);

            if ($match !== null) {
                return array_merge($match, [
                    'type' => $type,
                    'label' => (string) ($entityConfig['label'] ?? Str::headline($type)),
                ]);
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $entityConfig
     * @return array<string, mixed>|null
     */
    private function findBestMatch(string $message, string $type, array $entityConfig, bool $preferStrict): ?array
    {
        $table = (string) ($entityConfig['table'] ?? '');
        $key = (string) ($entityConfig['key'] ?? '');
        $nameColumn = (string) ($entityConfig['name_column'] ?? '');

        if ($table === '' || $key === '' || $nameColumn === '') {
            return null;
        }

        $rows = DB::table($table)
            ->select([$key . ' as entity_id', $nameColumn . ' as entity_name'])
            ->limit(80)
            ->get();

        $best = null;
        $bestScore = 0;

        foreach ($rows as $row) {
            $name = Str::lower((string) $row->entity_name);
            $id = Str::lower((string) $row->entity_id);
            $score = 0;

            if ($name !== '' && str_contains($message, $name)) {
                $score += 100;
            }

            if ($id !== '' && str_contains($message, $id)) {
                $score += 120;
            }

            foreach (preg_split('/\s+/u', $name) ?: [] as $token) {
                if ($token !== '' && mb_strlen($token) >= 3 && str_contains($message, $token)) {
                    $score += 8;
                }
            }

            if ($preferStrict && $score > 0) {
                $score += 10;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = [
                    'entity_id' => (string) $row->entity_id,
                    'entity_name' => (string) $row->entity_name,
                    'score' => $score,
                ];
            }
        }

        if ($bestScore < 18) {
            return null;
        }

        return $best;
    }

    /**
     * @param array<int, string> $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
