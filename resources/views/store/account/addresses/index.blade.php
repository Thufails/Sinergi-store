@extends('layouts.store')

@section('title', ($addressForm ? ($address ? 'Edit Alamat' : 'Tambah Alamat') : 'Alamat Saya').' - Sinergi Store')

@section('content')
    @if (! $addressForm)
    <section class="mx-auto max-w-7xl px-6 py-10 lg:px-8 lg:py-14">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Akun pembeli</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Alamat Saya</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Simpan alamat tujuan agar bisa dipilih saat melakukan pemesanan.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('account.orders') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-800">Pesanan saya</a>
                <a href="{{ route('account.addresses.create') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Tambah alamat</a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
        @endif

        @if ($addresses->isEmpty())
            <div class="rounded-3xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-800" aria-hidden="true">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.1 7-12a7 7 0 1 0-14 0c0 6.9 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                </div>
                <h2 class="mt-5 text-lg font-semibold text-slate-900">Belum ada alamat tersimpan</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Tambahkan alamat penerima supaya detail tujuan pesananmu tersimpan dengan rapi.</p>
                <a href="{{ route('account.addresses.create') }}" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Tambah alamat pertama</a>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($addresses as $address)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-semibold text-slate-900">{{ $address->label }}</h2>
                                    @if ($address->is_default)
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900">Alamat utama</span>
                                    @endif
                                </div>
                                <p class="mt-3 font-medium text-slate-800">{{ $address->recipient_name }}</p>
                                <p class="mt-0.5 text-sm text-slate-600">{{ $address->recipient_phone }}</p>
                            </div>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.1 7-12a7 7 0 1 0-14 0c0 6.9 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                            </span>
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-600">
                            {{ $address->address_line }}<br>
                            {{ collect([$address->subdistrict, $address->district, $address->city, $address->province])->filter()->join(', ') }} {{ $address->postal_code }}
                        </p>
                        @if ($address->notes)
                            <p class="mt-2 text-sm text-slate-500">Catatan: {{ $address->notes }}</p>
                        @endif
                        <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                            <a href="{{ route('account.addresses.edit', $address) }}" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-800">Edit</a>

                            @unless ($address->is_default)
                                <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold text-amber-800 transition hover:bg-amber-50">Jadikan utama</button>
                                </form>
                            @endunless

                            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" class="ml-auto" onsubmit="return confirm('Hapus alamat ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Hapus</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
    @else
    <section class="mx-auto max-w-6xl px-6 py-10 lg:px-8 lg:py-14">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Akun pembeli</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">{{ $address ? 'Edit alamat' : 'Tambah alamat' }}</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Lengkapi detail penerima dan tujuan pengiriman.</p>
            </div>
            <a href="{{ route('account.addresses.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-800">Kembali ke alamat</a>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
        @endif

        <div data-address-form-panel class="rounded-3xl border border-slate-200 bg-white text-slate-900 shadow-sm">
        <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">Buku alamat</p>
                <h2 class="mt-1 text-xl font-semibold">{{ $address ? 'Edit alamat penerima' : 'Alamat penerima baru' }}</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih wilayah, lalu tandai titik alamat di peta bila diperlukan.</p>
            </div>
        </div>

        <form data-address-form method="POST" action="{{ $address ? route('account.addresses.update', $address) : route('account.addresses.store') }}" class="space-y-5 px-5 py-5 sm:px-7">
            @csrf
            @if ($address)
                @method('PUT')
            @endif
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="address-label" class="mb-2 block text-sm font-medium text-slate-700">Label alamat</label>
                    <input id="address-label" name="label" type="text" maxlength="60" required placeholder="Rumah, kantor, dan lainnya" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                    @error('label')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="recipient-name" class="mb-2 block text-sm font-medium text-slate-700">Nama penerima</label>
                    <input id="recipient-name" name="recipient_name" type="text" maxlength="150" required autocomplete="name" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                    @error('recipient_name')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="recipient-phone" class="mb-2 block text-sm font-medium text-slate-700">Nomor WhatsApp penerima</label>
                    <input id="recipient-phone" name="recipient_phone" type="tel" maxlength="30" required autocomplete="tel" inputmode="tel" placeholder="Contoh: 081234567890" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                    @error('recipient_phone')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="address-province" class="mb-2 block text-sm font-medium text-slate-700">Provinsi</label>
                    <select id="address-province" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="">Pilih provinsi</option></select>
                    <input data-address-region-name="province" type="hidden" name="province">
                    <input data-address-region-code="province_code" type="hidden" name="province_code">
                    @error('province')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                    @error('province_code')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="address-regency" class="mb-2 block text-sm font-medium text-slate-700">Kabupaten / kota</label>
                    <select id="address-regency" required disabled class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="">Pilih kabupaten / kota</option></select>
                    <input data-address-region-name="city" type="hidden" name="city">
                    <input data-address-region-code="regency_code" type="hidden" name="regency_code">
                    @error('city')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                    @error('regency_code')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="address-district" class="mb-2 block text-sm font-medium text-slate-700">Kecamatan</label>
                    <select id="address-district" required disabled class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="">Pilih kecamatan</option></select>
                    <input data-address-region-name="district" type="hidden" name="district">
                    <input data-address-region-code="district_code" type="hidden" name="district_code">
                    @error('district')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                    @error('district_code')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="address-village" class="mb-2 block text-sm font-medium text-slate-700">Desa / kelurahan <span class="font-normal text-slate-500">(opsional)</span></label>
                    <select id="address-village" disabled class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"><option value="">Pilih desa / kelurahan</option></select>
                    <input data-address-region-name="subdistrict" type="hidden" name="subdistrict">
                    <input data-address-region-code="village_code" type="hidden" name="village_code">
                    @error('subdistrict')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                    @error('village_code')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="address-postal-code" class="mb-2 block text-sm font-medium text-slate-700">Kode pos</label>
                    <input id="address-postal-code" name="postal_code" type="text" inputmode="numeric" maxlength="5" pattern="\d{5}" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                    @error('postal_code')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="address-line" class="mb-2 block text-sm font-medium text-slate-700">Alamat lengkap</label>
                <textarea id="address-line" name="address_line" rows="3" maxlength="2000" required placeholder="Nama jalan, nomor rumah, RT/RW, patokan..." class="w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-200"></textarea>
                @error('address_line')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Titik lokasi di peta <span class="font-normal text-slate-500">(opsional)</span></h3>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Pilih wilayah untuk melihat batasnya, lalu klik atau geser pin ke titik rumah.</p>
                    </div>
                    <span data-map-coordinates class="text-xs text-slate-500">Belum memilih titik</span>
                </div>
                <div data-address-map class="isolate z-0 h-64 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 sm:h-80" aria-label="Peta alamat"></div>
                <p data-address-map-message class="mt-2 text-xs text-slate-500" role="status">Peta memakai OpenStreetMap. Titik pin tidak menggantikan alamat tertulis.</p>
                <input data-address-latitude type="hidden" name="latitude">
                <input data-address-longitude type="hidden" name="longitude">
                @error('latitude')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
                @error('longitude')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="address-notes" class="mb-2 block text-sm font-medium text-slate-700">Catatan untuk kurir <span class="font-normal text-slate-500">(opsional)</span></label>
                <textarea id="address-notes" name="notes" rows="2" maxlength="1000" placeholder="Contoh: rumah pagar hitam, dekat minimarket" class="w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-200"></textarea>
                @error('notes')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>

            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700">
                <input data-address-default type="checkbox" name="is_default" value="1" class="mt-0.5 rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                <span><span class="font-semibold">Jadikan alamat utama</span><span class="mt-0.5 block text-xs text-slate-500">Alamat ini akan diprioritaskan untuk pemesanan berikutnya.</span></span>
            </label>

            <div data-address-form-error class="hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert"></div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                <a href="{{ route('account.addresses.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</a>
                <button type="submit" data-address-submit class="inline-flex min-h-11 items-center justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">{{ $address ? 'Simpan perubahan' : 'Simpan alamat' }}</button>
            </div>
        </form>
        </div>
    </section>
    @endif
@endsection

@if ($addressForm)
@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
@endpush

@push('scripts')
    @php
        $initialAddress = [
            'label' => old('label', $address?->label),
            'recipient_name' => old('recipient_name', $address?->recipient_name),
            'recipient_phone' => old('recipient_phone', $address?->recipient_phone),
            'province' => old('province', $address?->province),
            'province_code' => old('province_code', $address?->province_code),
            'city' => old('city', $address?->city),
            'regency_code' => old('regency_code', $address?->regency_code),
            'district' => old('district', $address?->district),
            'district_code' => old('district_code', $address?->district_code),
            'subdistrict' => old('subdistrict', $address?->subdistrict),
            'village_code' => old('village_code', $address?->village_code),
            'postal_code' => old('postal_code', $address?->postal_code),
            'address_line' => old('address_line', $address?->address_line),
            'notes' => old('notes', $address?->notes),
            'latitude' => old('latitude', $address?->latitude),
            'longitude' => old('longitude', $address?->longitude),
            'is_default' => old('is_default', $address?->is_default),
        ];
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
    <script>
        (() => {
            const apiBaseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/v2';
            const addressPanel = document.querySelector('[data-address-form-panel]');
            const form = document.querySelector('[data-address-form]');

            if (!(addressPanel instanceof HTMLElement) || !(form instanceof HTMLFormElement)) {
                return;
            }

            const selects = {
                province: document.getElementById('address-province'),
                regency: document.getElementById('address-regency'),
                district: document.getElementById('address-district'),
                village: document.getElementById('address-village'),
            };
            const regionNames = Object.fromEntries([...form.querySelectorAll('[data-address-region-name]')].map((field) => [field.dataset.addressRegionName, field]));
            const regionCodes = Object.fromEntries([...form.querySelectorAll('[data-address-region-code]')].map((field) => [field.dataset.addressRegionCode, field]));
            const formError = form.querySelector('[data-address-form-error]');
            const mapMessage = form.querySelector('[data-address-map-message]');
            const mapCoordinates = form.querySelector('[data-map-coordinates]');
            const latitudeField = form.querySelector('[data-address-latitude]');
            const longitudeField = form.querySelector('[data-address-longitude]');
            const dataCache = new Map();
            let map;
            let marker;
            let boundaryLayer;
            let selectedRegionLayer;

            const get = (selector) => form.querySelector(selector);
            const setError = (message = '') => {
                formError.textContent = message;
                formError.classList.toggle('hidden', !message);
            };
            const clearRegion = (key) => {
                if (key === 'province') {
                    regionNames.province.value = '';
                    regionCodes.province_code.value = '';
                }
                if (key === 'regency') {
                    regionNames.city.value = '';
                    regionCodes.regency_code.value = '';
                }
                if (key === 'district') {
                    regionNames.district.value = '';
                    regionCodes.district_code.value = '';
                }
                if (key === 'village') {
                    regionNames.subdistrict.value = '';
                    regionCodes.village_code.value = '';
                }
            };
            const resetSelect = (select, placeholder, disabled = true) => {
                select.replaceChildren(new Option(placeholder, ''));
                select.disabled = disabled;
            };
            const fetchData = async (path) => {
                if (dataCache.has(path)) {
                    return dataCache.get(path);
                }

                const response = await fetch(`${apiBaseUrl}/${path}.json`, { headers: { Accept: 'application/json' } });

                if (!response.ok) {
                    throw new Error('Data wilayah tidak dapat dimuat. Periksa koneksi internet lalu coba lagi.');
                }

                const responseBody = await response.json();
                const items = Array.isArray(responseBody.data) ? responseBody.data : [];
                dataCache.set(path, items);

                return items;
            };
            const fillSelect = async (select, path, placeholder, selectedCode = '') => {
                resetSelect(select, 'Memuat wilayah...', true);
                const items = await fetchData(path);
                select.replaceChildren(new Option(placeholder, ''));

                items.forEach((item) => {
                    const option = new Option(item.name, item.id);
                    option.dataset.latitude = item.lat ?? '';
                    option.dataset.longitude = item.lng ?? '';
                    option.dataset.hasPath = String(Boolean(item.has_path));
                    option.dataset.postalCode = item.postal_code ?? '';
                    select.add(option);
                });

                select.disabled = false;
                select.value = selectedCode;

                return items;
            };
            const ensureMap = () => {
                if (map || !window.L) {
                    return;
                }

                map = L.map(form.querySelector('[data-address-map]'), { scrollWheelZoom: true }).setView([-2.5, 118], 4);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors',
                }).addTo(map);

                map.on('click', (event) => setMarker(event.latlng, true));
            };
            const setMarker = (point, isManual = false) => {
                latitudeField.value = Number(point.lat).toFixed(7);
                longitudeField.value = Number(point.lng).toFixed(7);
                mapCoordinates.textContent = `${latitudeField.value}, ${longitudeField.value}`;

                if (!marker) {
                    marker = L.marker(point, { draggable: true }).addTo(map);
                    marker.on('dragend', (event) => setMarker(event.target.getLatLng(), true));
                } else {
                    marker.setLatLng(point);
                }

                if (isManual) {
                    mapMessage.textContent = 'Titik pin tersimpan. Kamu masih bisa menggeser pin untuk menyesuaikannya.';
                }
            };
            const focusRegion = async (region, code) => {
                ensureMap();

                if (!map) {
                    mapMessage.textContent = 'Peta belum dapat dimuat. Alamat tetap bisa disimpan tanpa titik lokasi.';
                    return;
                }

                const lat = Number(region?.lat);
                const lng = Number(region?.lng);

                if (Number.isFinite(lat) && Number.isFinite(lng)) {
                    map.setView([lat, lng], 11);
                    mapMessage.textContent = 'Peta diarahkan ke wilayah pilihan. Klik peta untuk menandai titik rumah yang lebih tepat.';
                }

                if (selectedRegionLayer) {
                    map.removeLayer(selectedRegionLayer);
                    selectedRegionLayer = null;
                }
                if (boundaryLayer) {
                    map.removeLayer(boundaryLayer);
                    boundaryLayer = null;
                }

                if (!region?.has_path || !code) {
                    mapMessage.textContent = 'Batas peta wilayah ini belum tersedia. Kamu tetap bisa memilih titik dengan klik pada peta.';
                    return;
                }

                try {
                    const response = await fetch(`${apiBaseUrl}/paths/${encodeURIComponent(code)}.json`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) {
                        throw new Error('Batas wilayah tidak tersedia saat ini.');
                    }
                    const body = await response.json();
                    const rings = [];
                    const collectRings = (node) => {
                        if (!Array.isArray(node)) {
                            return;
                        }
                        if (node.length > 2 && Array.isArray(node[0]) && typeof node[0][0] === 'number') {
                            rings.push(node);
                            return;
                        }
                        node.forEach(collectRings);
                    };
                    collectRings(body.data?.path);

                    if (rings.length === 0) {
                        throw new Error('Batas wilayah belum tersedia.');
                    }

                    boundaryLayer = L.featureGroup(rings.map((ring) => L.polygon(ring, {
                        color: '#d97706',
                        weight: 2,
                        fillColor: '#f59e0b',
                        fillOpacity: 0.13,
                    }))).addTo(map);
                    map.fitBounds(boundaryLayer.getBounds(), { padding: [18, 18], maxZoom: 13 });
                    selectedRegionLayer = boundaryLayer;
                    mapMessage.textContent = 'Batas wilayah ditampilkan. Klik peta atau geser pin untuk memilih titik rumah.';
                } catch (error) {
                    mapMessage.textContent = error.message || 'Batas wilayah gagal dimuat.';
                }
            };
            const hydrateAddress = async (address) => {
                try {
                    get('#address-label').value = address.label ?? '';
                    get('#recipient-name').value = address.recipient_name ?? '';
                    get('#recipient-phone').value = address.recipient_phone ?? '';
                    get('#address-postal-code').value = address.postal_code ?? '';
                    get('#address-line').value = address.address_line ?? '';
                    get('#address-notes').value = address.notes ?? '';
                    get('[data-address-default]').checked = Boolean(Number(address.is_default ?? 0));
                    latitudeField.value = address.latitude ?? '';
                    longitudeField.value = address.longitude ?? '';
                    if (latitudeField.value && longitudeField.value) {
                        mapCoordinates.textContent = `${latitudeField.value}, ${longitudeField.value}`;
                    }

                    const provinces = await fillSelect(selects.province, 'provinces', 'Pilih provinsi', address.province_code ?? '');
                    const province = provinces.find((item) => item.id === address.province_code);
                    if (province) {
                        regionNames.province.value = province.name;
                        regionCodes.province_code.value = province.id;
                        const regencies = await fillSelect(selects.regency, `regencies/${province.id}`, 'Pilih kabupaten / kota', address.regency_code ?? '');
                        const regency = regencies.find((item) => item.id === address.regency_code);
                        if (regency) {
                            regionNames.city.value = regency.name;
                            regionCodes.regency_code.value = regency.id;
                            const districts = await fillSelect(selects.district, `districts/${regency.id}`, 'Pilih kecamatan', address.district_code ?? '');
                            const district = districts.find((item) => item.id === address.district_code);
                            if (district) {
                                regionNames.district.value = district.name;
                                regionCodes.district_code.value = district.id;
                                const villages = await fillSelect(selects.village, `villages/${district.id}`, 'Pilih desa / kelurahan', address.village_code ?? '');
                                const village = villages.find((item) => item.id === address.village_code);
                                if (village) {
                                    regionNames.subdistrict.value = village.name;
                                    regionCodes.village_code.value = village.id;
                                    get('#address-postal-code').value = address.postal_code || village.postal_code || '';
                                    await focusRegion(village, village.id);
                                } else {
                                    await focusRegion(district, district.id);
                                }
                            } else {
                                await focusRegion(regency, regency.id);
                            }
                        }
                    }

                    if (latitudeField.value && longitudeField.value) {
                        ensureMap();
                        const point = { lat: Number(latitudeField.value), lng: Number(longitudeField.value) };
                        if (map && Number.isFinite(point.lat) && Number.isFinite(point.lng)) {
                            setMarker(point);
                            map.setView(point, Math.max(map.getZoom(), 13));
                        }
                    }
                } catch (error) {
                    setError(error.message || 'Data wilayah gagal dimuat. Coba buka kembali form saat koneksi tersedia.');
                }
            };
            selects.province.addEventListener('change', async () => {
                clearRegion('province');
                clearRegion('regency');
                clearRegion('district');
                clearRegion('village');
                resetSelect(selects.regency, 'Pilih kabupaten / kota');
                resetSelect(selects.district, 'Pilih kecamatan');
                resetSelect(selects.village, 'Pilih desa / kelurahan');
                const selected = selects.province.selectedOptions[0];
                if (!selected?.value) return;
                regionNames.province.value = selected.textContent;
                regionCodes.province_code.value = selected.value;
                try {
                    await fillSelect(selects.regency, `regencies/${selected.value}`, 'Pilih kabupaten / kota');
                    await focusRegion({ lat: selected.dataset.latitude, lng: selected.dataset.longitude, has_path: selected.dataset.hasPath === 'true' }, selected.value);
                    setError('');
                } catch (error) {
                    setError(error.message);
                }
            });

            selects.regency.addEventListener('change', async () => {
                clearRegion('regency');
                clearRegion('district');
                clearRegion('village');
                resetSelect(selects.district, 'Pilih kecamatan');
                resetSelect(selects.village, 'Pilih desa / kelurahan');
                const selected = selects.regency.selectedOptions[0];
                if (!selected?.value) return;
                regionNames.city.value = selected.textContent;
                regionCodes.regency_code.value = selected.value;
                try {
                    await fillSelect(selects.district, `districts/${selected.value}`, 'Pilih kecamatan');
                    await focusRegion({ lat: selected.dataset.latitude, lng: selected.dataset.longitude, has_path: selected.dataset.hasPath === 'true' }, selected.value);
                    setError('');
                } catch (error) {
                    setError(error.message);
                }
            });

            selects.district.addEventListener('change', async () => {
                clearRegion('district');
                clearRegion('village');
                resetSelect(selects.village, 'Pilih desa / kelurahan');
                const selected = selects.district.selectedOptions[0];
                if (!selected?.value) return;
                regionNames.district.value = selected.textContent;
                regionCodes.district_code.value = selected.value;
                try {
                    await fillSelect(selects.village, `villages/${selected.value}`, 'Pilih desa / kelurahan');
                    await focusRegion({ lat: selected.dataset.latitude, lng: selected.dataset.longitude, has_path: selected.dataset.hasPath === 'true' }, selected.value);
                    setError('');
                } catch (error) {
                    setError(error.message);
                }
            });

            selects.village.addEventListener('change', async () => {
                clearRegion('village');
                const selected = selects.village.selectedOptions[0];
                if (!selected?.value) return;
                regionNames.subdistrict.value = selected.textContent;
                regionCodes.village_code.value = selected.value;
                get('#address-postal-code').value = selected.dataset.postalCode ?? '';
                try {
                    await focusRegion({ lat: selected.dataset.latitude, lng: selected.dataset.longitude, has_path: selected.dataset.hasPath === 'true' }, selected.value);
                } catch (error) {
                    setError(error.message);
                }
            });

            const initialAddress = {{ Illuminate\Support\Js::from($initialAddress) }};
            ensureMap();
            window.setTimeout(() => map?.invalidateSize(), 80);
            hydrateAddress(initialAddress);
        })();
    </script>
@endpush
@endif
