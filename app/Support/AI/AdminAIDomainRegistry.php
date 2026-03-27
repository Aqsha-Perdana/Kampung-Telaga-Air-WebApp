<?php

namespace App\Support\AI;

class AdminAIDomainRegistry
{
    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $domains = $this->domains();

        return [
            'total_domains' => count($domains),
            'domains' => $domains,
            'safe_tables' => $this->safeTables(),
            'safe_columns' => $this->safeColumns(),
            'entities' => $this->entities(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function domains(): array
    {
        return config('ai.admin.domains', []);
    }

    /**
     * @return array<int, string>
     */
    public function safeTables(): array
    {
        return config('ai.admin.safe_tables', []);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function safeColumns(): array
    {
        return config('ai.admin.safe_columns', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function entities(): array
    {
        return config('ai.admin.entities', []);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function entity(string $type): ?array
    {
        $entities = $this->entities();

        return $entities[$type] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function allBusinessTerms(): array
    {
        $terms = [];

        foreach ($this->domains() as $domain) {
            foreach (($domain['business_terms'] ?? []) as $term) {
                $terms[] = (string) $term;
            }
        }

        return array_values(array_unique($terms));
    }
}
