<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    private $apiKey;
    private $baseUrl;
    private $cacheDuration;

    public function __construct()
    {
        $this->apiKey = config('services.exchange_rate.api_key');
        $this->baseUrl = config('services.exchange_rate.base_url');
        $this->cacheDuration = config('services.exchange_rate.cache_duration', 3600);
    }

    /**
     * Get exchange rate from MYR to target currency
     * Returns: 1 MYR = X target_currency
     */
    public function getRate(string $targetCurrency): float
{
    if ($targetCurrency === 'MYR') {
        return 1.0;
    }

    $cacheKey = "exchange_rate_myr_to_{$targetCurrency}";

    return Cache::remember($cacheKey, $this->cacheDuration, function () use ($targetCurrency) {
        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get("{$this->baseUrl}/{$this->apiKey}/latest/MYR");

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['conversion_rates'][$targetCurrency])) {
                    $rawRate = (float) $data['conversion_rates'][$targetCurrency];

                    /**
                     * INTERNAL SPREAD (PROTEKSI FLUKTUASI)
                     * 1%–3% itu normal di industri
                     */
                    $spread = match ($targetCurrency) {
                        'IDR', 'THB' => 1.02, // volatile
                        'JPY'        => 1.01,
                        default      => 1.015,
                    };

                    $finalRate = $rawRate * $spread;

                    Log::info('Exchange rate fetched', [
                        'base'        => 'MYR',
                        'target'      => $targetCurrency,
                        'raw_rate'    => $rawRate,
                        'final_rate'  => $finalRate,
                        'spread_used' => $spread,
                    ]);

                    return $finalRate;
                }
            }

            Log::warning("Exchange rate API failed, using fallback", [
                'currency' => $targetCurrency
            ]);

            return $this->getFallbackRate($targetCurrency);

        } catch (\Throwable $e) {
            Log::error("Exchange rate exception", [
                'currency' => $targetCurrency,
                'error'    => $e->getMessage()
            ]);

            return $this->getFallbackRate($targetCurrency);
        }
    });
}

    /**
     * Get all supported currencies with their rates
     */
    public function getAllRates()
    {
        $cacheKey = "exchange_rates_all_from_myr";

        return Cache::remember($cacheKey, $this->cacheDuration, function () {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/{$this->apiKey}/latest/MYR");

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['conversion_rates'])) {
                        return $data['conversion_rates'];
                    }
                }

                return $this->getAllFallbackRates();
                
            } catch (\Exception $e) {
                Log::error("Exchange rate API error: " . $e->getMessage());
                return $this->getAllFallbackRates();
            }
        });
    }

    /**
     * Convert amount from MYR to target currency
     */
    public function convert(float $amountMYR, string $currency): float
{
    $rate = $this->getRate($currency);
    $raw = $amountMYR * $rate;

    return match ($currency) {
        'IDR' => ceil($raw / 100) * 100, // wajib
        'JPY' => ceil($raw),
        default => round($raw, 2),
    };
}


    /**
     * Get currency symbol
     */
    public function getSymbol($currency)
    {
        $symbols = [
            'MYR' => 'RM',
            'USD' => '$',
            'SGD' => 'S$',
            'IDR' => 'Rp',
            'EUR' => '€',
            'GBP' => '£',
            'AUD' => 'A$',
            'JPY' => '¥',
            'CNY' => '¥',
            'THB' => '฿',
        ];

        return $symbols[$currency] ?? $currency;
    }

    /**
     * Get currency name
     */
    public function getName($currency)
    {
        $names = [
            'MYR' => 'Ringgit Malaysia',
            'USD' => 'US Dollar',
            'SGD' => 'Singapore Dollar',
            'IDR' => 'Indonesian Rupiah',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'AUD' => 'Australian Dollar',
            'JPY' => 'Japanese Yen',
            'CNY' => 'Chinese Yuan',
            'THB' => 'Thai Baht',
        ];

        return $names[$currency] ?? $currency;
    }

    /**
     * Fallback rates if API fails
     */
    private function getFallbackRate($currency)
    {
        $fallbackRates = [
            'USD' => 0.22,
            'SGD' => 0.30,
            'IDR' => 4151.68,
            'EUR' => 0.21,
            'GBP' => 0.18,
            'AUD' => 0.35,
            'JPY' => 32.50,
            'CNY' => 1.60,
            'THB' => 7.50,
        ];

        Log::warning("Using fallback rate for {$currency}");
        return $fallbackRates[$currency] ?? 1.0;
    }

    /**
     * Get all fallback rates
     */
    private function getAllFallbackRates()
    {
        return [
            'MYR' => 1.0,
            'USD' => 0.22,
            'SGD' => 0.30,
            'IDR' => 4151.68,
            'EUR' => 0.21,
            'GBP' => 0.18,
            'AUD' => 0.35,
            'JPY' => 32.50,
            'CNY' => 1.60,
            'THB' => 7.50,
        ];
    }

    /**
     * Clear cached rates
     */
    public function clearCache()
    {
        Cache::forget('exchange_rates_all_from_myr');
        
        $currencies = ['USD', 'SGD', 'IDR', 'EUR', 'GBP', 'AUD', 'JPY', 'CNY', 'THB'];
        foreach ($currencies as $currency) {
            Cache::forget("exchange_rate_myr_to_{$currency}");
        }
        
        Log::info("Exchange rate cache cleared");
    }
}