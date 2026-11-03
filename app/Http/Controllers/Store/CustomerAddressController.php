<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerAddressController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $addresses = $customer->addresses()
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();

        return view('store.account.addresses.index', compact('addresses'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $validated = $this->validateAddress($request);
        $isDefault = ! $customer->addresses()->exists() || $validated['is_default'];

        DB::transaction(function () use ($customer, $validated, $isDefault): void {
            if ($isDefault) {
                $customer->addresses()->update(['is_default' => false]);
            }

            $customer->addresses()->create([
                ...$validated,
                'is_default' => $isDefault,
            ]);
        });

        return redirect()->route('account.addresses.index')
            ->with('success', 'Alamat berhasil disimpan.');
    }

    public function update(Request $request, CustomerAddress $address): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $address = $customer->addresses()->whereKey($address->getKey())->firstOrFail();
        $validated = $this->validateAddress($request);
        $isDefault = $address->is_default || $validated['is_default'];

        DB::transaction(function () use ($customer, $address, $validated, $isDefault): void {
            if ($isDefault) {
                $customer->addresses()
                    ->where('id', '!=', $address->getKey())
                    ->update(['is_default' => false]);
            }

            $address->fill([
                ...$validated,
                'is_default' => $isDefault,
            ])->save();
        });

        return redirect()->route('account.addresses.index')
            ->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(Request $request, CustomerAddress $address): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $address = $customer->addresses()->whereKey($address->getKey())->firstOrFail();

        DB::transaction(function () use ($customer, $address): void {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $replacementAddress = $customer->addresses()->oldest('id')->first();
                $replacementAddress?->update(['is_default' => true]);
            }
        });

        return redirect()->route('account.addresses.index')
            ->with('success', 'Alamat berhasil dihapus.');
    }

    public function setDefault(Request $request, CustomerAddress $address): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');
        $address = $customer->addresses()->whereKey($address->getKey())->firstOrFail();

        DB::transaction(function () use ($customer, $address): void {
            $customer->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return redirect()->route('account.addresses.index')
            ->with('success', 'Alamat utama berhasil diperbarui.');
    }

    /**
     * @return array{
     *     label: string,
     *     recipient_name: string,
     *     recipient_phone: string,
     *     province: string,
     *     rajaongkir_province_id: string,
     *     city: string,
     *     rajaongkir_city_id: string,
     *     district: string,
     *     rajaongkir_district_id: string,
     *     subdistrict: string|null,
     *     rajaongkir_subdistrict_id: string|null,
     *     postal_code: string,
     *     address_line: string,
     *     notes: string|null,
     *     latitude: mixed,
     *     longitude: mixed,
     *     is_default: bool|int|string
     * }
     */
    private function validateAddress(Request $request): array
    {
        return array_merge([
            'subdistrict' => null,
            'rajaongkir_subdistrict_id' => null,
            'notes' => null,
            'latitude' => null,
            'longitude' => null,
            'is_default' => false,
        ], $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+().\\s-]{8,30}$/'],
            'province' => ['required', 'string', 'max:100'],
            'rajaongkir_province_id' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'rajaongkir_city_id' => ['required', 'string', 'max:30'],
            'district' => ['required', 'string', 'max:100'],
            'rajaongkir_district_id' => ['required', 'string', 'max:30'],
            'subdistrict' => ['nullable', 'required_with:rajaongkir_subdistrict_id', 'string', 'max:100'],
            'rajaongkir_subdistrict_id' => ['nullable', 'required_with:subdistrict', 'string', 'max:30'],
            'postal_code' => ['required', 'string', 'regex:/^\\d{5}$/'],
            'address_line' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'is_default' => ['sometimes', 'boolean'],
        ], [
            'recipient_phone.regex' => 'Nomor penerima harus berupa nomor telepon yang valid.',
            'postal_code.regex' => 'Kode pos harus terdiri dari 5 angka.',
        ]));
    }
}
