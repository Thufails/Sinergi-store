<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RajaOngkirShippingService
{
    public function courierOptions(): array
    {
        return [
            'anteraja' => 'AnterAja',
            'dse' => 'DSE (21 Express)',
            'ide' => 'IDE (ID Express)',
            'jnt' => 'J&T (J&T Express)',
            'jne' => 'JNE (Jalur Nugraha Ekakurir)',
            'lion' => 'Lion (Lion Parcel)',
            'ninja' => 'Ninja (Ninja Xpress)',
            'ncs' => 'NCS (Nusantara Card Semesta)',
            'pos' => 'POS (Pos Indonesia)',
            'rex' => 'REX (REX Kiriman Express)',
            'rpx' => 'RPX (RPX Holding)',
            'sap' => 'SAP (SAP Express)',
            'sentral' => 'SC (Sentral Cargo)',
            'sicepat' => 'SiCepat (SiCepat Express)',
            'star' => 'STAR (Star Cargo)',
            'tiki' => 'TiKi (Citra Van Titipan Kilat)',
            'wahana' => 'Wahana (Wahana Prestasi Logistik)',
        ];
    }

    public function calculateRates(Order $order, string $courierCode): array
    {
        if (! filled(config('services.rajaongkir.api_key'))) {
            throw new RuntimeException('API key RajaOngkir belum dikonfigurasi di .env.');
        }

        $weight = (int) $order->shipping_weight_grams;
        if ($weight < 1) {
            throw new RuntimeException('Berat produk belum diatur. Lengkapi berat produk atau variannya.');
        }

        $originId = $this->resolveOriginId();
        $destinationId = $this->resolveDestinationId($order);
        $cacheKey = 'rajaongkir:rates:' . hash('sha256', implode('|', [$originId, $destinationId, $weight, $courierCode]));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($originId, $destinationId, $weight, $courierCode): array {
            try {
                $response = $this->client()->asForm()->post('/calculate/domestic-cost', [
                    'origin' => $originId,
                    'destination' => $destinationId,
                    'weight' => $weight,
                    'courier' => $courierCode,
                    'price' => 'lowest',
                ]);
            } catch (ConnectionException) {
                throw new RuntimeException('Tidak dapat tersambung ke RajaOngkir. Periksa koneksi internet lalu coba lagi.');
            }

            if (! $response->successful()) {
                throw new RuntimeException('RajaOngkir belum dapat menghitung ongkir untuk rute ini. Coba lagi nanti.');
            }

            $rates = $response->json('data');
            if (! is_array($rates) || $rates === []) {
                throw new RuntimeException('Tidak ada layanan pengiriman untuk rute dan ekspedisi ini.');
            }

            return collect($rates)
                ->filter(fn (mixed $rate): bool => is_array($rate) && isset($rate['cost']) && is_numeric($rate['cost']))
                ->map(fn (array $rate): array => [
                    'name' => (string) ($rate['name'] ?? $this->courierOptions()[$courierCode]),
                    'code' => (string) ($rate['code'] ?? $courierCode),
                    'service' => (string) ($rate['service'] ?? ''),
                    'description' => (string) ($rate['description'] ?? ''),
                    'cost' => (int) $rate['cost'],
                    'etd' => (string) ($rate['etd'] ?? ''),
                ])
                ->values()
                ->all();
        });
    }

    private function resolveOriginId(): int
    {
        $origin = config('services.rajaongkir.origin');

        return $this->findDestinationId(
            [(string) $origin['search']],
            [
                'province_name' => (string) $origin['province'],
                'city_name' => (string) $origin['city'],
                'district_name' => (string) $origin['district'],
                'subdistrict_name' => (string) $origin['subdistrict'],
                'zip_code' => (string) $origin['postal_code'],
            ],
            'alamat asal toko',
        );
    }

    private function resolveDestinationId(Order $order): int
    {
        $searchTerms = array_values(array_unique(array_filter([
            $order->shipping_village,
            $order->shipping_district,
            $order->shipping_postal_code,
        ], fn (mixed $term): bool => filled($term))));

        return $this->findDestinationId($searchTerms, [
            'province_name' => (string) $order->shipping_province,
            'city_name' => (string) $order->shipping_city,
            'district_name' => (string) $order->shipping_district,
            'subdistrict_name' => (string) $order->shipping_village,
        ], 'alamat tujuan pesanan');
    }

    private function findDestinationId(array $searchTerms, array $expected, string $label): int
    {
        $cacheKey = 'rajaongkir:destination:' . hash('sha256', mb_strtolower(implode('|', $searchTerms) . '|' . implode('|', $expected)));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($searchTerms, $expected, $label): int {
            foreach ($searchTerms as $search) {
                try {
                    $response = $this->client()->get('/destination/domestic-destination', [
                        'search' => $search,
                        'limit' => 100,
                        'offset' => 0,
                    ]);
                } catch (ConnectionException) {
                    throw new RuntimeException('Tidak dapat tersambung ke RajaOngkir. Periksa koneksi internet lalu coba lagi.');
                }

                if (! $response->successful()) {
                    throw new RuntimeException('RajaOngkir gagal mencari ' . $label . '. Periksa koneksi atau konfigurasi API.');
                }

                $locations = $response->json('data');
                if (! is_array($locations)) {
                    continue;
                }

                $matchedLocation = collect($locations)
                    ->filter(fn (mixed $location): bool => is_array($location))
                    ->first(fn (array $location): bool => $this->matchesLocation($location, $expected));

                if (is_array($matchedLocation) && isset($matchedLocation['id'])) {
                    return (int) $matchedLocation['id'];
                }
            }

            throw new RuntimeException('Wilayah pada ' . $label . ' belum cocok dengan data RajaOngkir. Periksa provinsi, kabupaten/kota, kecamatan, dan desa.');
        });
    }

    private function matchesLocation(array $location, array $expected): bool
    {
        foreach (['province_name', 'city_name', 'district_name'] as $field) {
            $expectedValue = (string) ($expected[$field] ?? '');
            $actualValue = (string) ($location[$field] ?? '');

            if ($expectedValue === '' || $actualValue === '' || $this->normalize($actualValue) !== $this->normalize($expectedValue)) {
                return false;
            }
        }

        $expectedVillage = (string) ($expected['subdistrict_name'] ?? '');
        $actualVillage = (string) ($location['subdistrict_name'] ?? '');

        return $expectedVillage === '' || $actualVillage === '' || $this->normalize($actualVillage) === $this->normalize($expectedVillage);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.rajaongkir.base_url'), '/'))
            ->withHeaders(['key' => (string) config('services.rajaongkir.api_key')])
            ->acceptJson()
            ->timeout(15)
            ->retry(1, 250);
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/^(kabupaten|kab\.|kota administrasi|kota)\s+/u', '', $value) ?? $value;
        return preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? $value;
    }
}
