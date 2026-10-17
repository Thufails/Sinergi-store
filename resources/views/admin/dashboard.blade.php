@extends('layouts.admin')

@section('title', 'Dashboard Admin - Sinergi Store')
@section('heading', 'Dashboard')

@section('content')
    <section class="relative isolate overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-amber-600 p-6 shadow-sm sm:p-8">
        <div class="relative z-10 max-w-2xl">
            <p class="text-sm font-semibold text-amber-950">Selamat datang kembali, {{ auth()->user()->name }}</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Kelola Sinergi Store dengan lebih mudah.</h2>
            <p class="mt-4 max-w-xl text-sm leading-6 text-amber-950/80 sm:text-base">Pantau katalog, stok, kategori, dan promosi produk dari satu ruang kerja yang rapi.</p>
            {{-- <div class="mt-6 flex flex-wrap gap-3 text-xs font-semibold text-amber-950/80"><span class="rounded-full bg-white/25 px-4 py-2">Katalog lebih rapi</span><span class="rounded-full bg-white/25 px-4 py-2">Produk mudah dikelola</span><span class="rounded-full bg-white/25 px-4 py-2">Promosi lebih fleksibel</span></div> --}}
        </div>
        <div class="pointer-events-none absolute -right-10 -top-16 h-64 w-64 rounded-full border-[28px] border-white/15"></div>
        <div class="pointer-events-none absolute -bottom-28 right-24 h-72 w-72 rounded-full border-[42px] border-amber-200/20"></div>
        <div class="pointer-events-none absolute right-12 top-10 hidden h-28 w-44 rotate-6 rounded-2xl border border-white/25 bg-white/10 shadow-xl sm:block"></div>
        <div class="pointer-events-none absolute right-24 top-20 hidden h-28 w-44 -rotate-6 rounded-2xl border border-white/20 bg-white/10 sm:block"></div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="admin-dashboard-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Omzet bulan ini</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Dari pesanan yang sudah dibayar</p>
        </div>
        <div class="admin-dashboard-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Produk terjual</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($productsSoldInPeriod, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Total unit · {{ $periodLabel }}</p>
        </div>
        <div class="admin-dashboard-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pesanan dibayar bulan ini</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($paidOrdersThisMonth, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Jumlah transaksi berstatus dibayar</p>
        </div>
        <div class="admin-dashboard-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Menunggu konfirmasi</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($ordersAwaitingConfirmation, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Pesanan perlu ditinjau admin</p>
        </div>
    </section>

    <section class="space-y-4">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-dashboard-card grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-5 xl:items-end">
            <div>
                <label for="sales-period" class="mb-1.5 block text-xs font-medium text-slate-600">Periode penjualan</label>
                <select id="sales-period" name="period" data-period-select class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    <option value="today" @selected($selectedPeriod === 'today')>Hari ini</option>
                    <option value="last_7_days" @selected($selectedPeriod === 'last_7_days')>7 hari terakhir</option>
                    <option value="last_30_days" @selected($selectedPeriod === 'last_30_days')>30 hari terakhir</option>
                    <option value="custom" @selected($selectedPeriod === 'custom')>Rentang tanggal</option>
                </select>
            </div>
            <div data-period-range class="grid grid-cols-2 gap-3 sm:col-span-2 xl:col-span-2" @if ($selectedPeriod !== 'custom') hidden @endif>
                <div>
                    <label for="sales-date-from" class="mb-1.5 block text-xs font-medium text-slate-600">Dari tanggal</label>
                    <input id="sales-date-from" type="date" name="date_from" value="{{ $dateFrom }}" data-period-date class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                </div>
                <div>
                    <label for="sales-date-to" class="mb-1.5 block text-xs font-medium text-slate-600">Sampai tanggal</label>
                    <input id="sales-date-to" type="date" name="date_to" value="{{ $dateTo }}" data-period-date class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                </div>
            </div>
            <button type="submit" class="h-11 rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800">Terapkan</button>
            <a href="{{ route('admin.dashboard') }}" class="flex h-11 items-center justify-center rounded-xl border border-slate-300 px-5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">Reset</a>
            @error('date_from')<p class="text-xs font-medium text-red-600 sm:col-span-2 xl:col-span-5">{{ $message }}</p>@enderror
            @error('date_to')<p class="text-xs font-medium text-red-600 sm:col-span-2 xl:col-span-5">{{ $message }}</p>@enderror
        </form>

        <div class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
        <div class="admin-dashboard-card rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 px-6 py-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Produk terjual</h2>
                    <p class="mt-1 text-sm text-slate-500">Unit dari pesanan yang dibayar · {{ $periodLabel }}.</p>
                </div>
                <span class="rounded-full bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-800">{{ number_format($dailyProductSales->sum('units_sold'), 0, ',', '.') }} unit</span>
            </div>
            <div class="p-6">
                @if ($dailyProductSales->sum('units_sold') === 0)
                    <div class="flex min-h-48 items-center justify-center rounded-xl bg-slate-50 px-6 text-center text-sm text-slate-500">Belum ada produk terjual pada periode ini.</div>
                @else
                    @php
                        $chartLeft = 48;
                        $chartRight = 880;
                        $chartTop = 18;
                        $chartBottom = 198;
                        $chartPoints = $dailyProductSales->values()->map(function ($day, $index) use ($dailyProductSales, $maxDailyProductSales, $chartLeft, $chartRight, $chartTop, $chartBottom) {
                            $lastIndex = max(1, $dailyProductSales->count() - 1);

                            return [
                                'day' => $day,
                                'x' => round($chartLeft + (($chartRight - $chartLeft) * $index / $lastIndex), 1),
                                'y' => round($chartBottom - (($chartBottom - $chartTop) * $day['units_sold'] / $maxDailyProductSales), 1),
                            ];
                        });
                        $chartLinePoints = $chartPoints->map(fn ($point) => $point['x'] . ',' . $point['y'])->implode(' ');
                        $chartAreaPoints = $chartLeft . ',' . $chartBottom . ' ' . $chartLinePoints . ' ' . $chartRight . ',' . $chartBottom;
                        $chartLabelInterval = max(1, (int) ceil($dailyProductSales->count() / 6));
                        $chartTickSteps = min(4, $maxDailyProductSales);
                    @endphp
                    <svg class="admin-sales-chart block h-auto w-full" viewBox="0 0 900 250" role="img" aria-labelledby="sales-chart-title sales-chart-description">
                        <title id="sales-chart-title">Grafik produk terjual</title>
                        <desc id="sales-chart-description">Jumlah unit terjual per hari untuk periode {{ $periodLabel }}.</desc>

                        @foreach (range(0, $chartTickSteps) as $tickIndex)
                            @php
                                $tick = $tickIndex / $chartTickSteps;
                                $tickY = $chartBottom - (($chartBottom - $chartTop) * $tick);
                                $tickValue = (int) round($maxDailyProductSales * $tick);
                            @endphp
                            <line x1="{{ $chartLeft }}" y1="{{ $tickY }}" x2="{{ $chartRight }}" y2="{{ $tickY }}" stroke="#e2e8f0" stroke-width="1" />
                            <text x="38" y="{{ $tickY + 4 }}" text-anchor="end" fill="#64748b" font-size="11">{{ $tickValue }}</text>
                        @endforeach

                        <polygon points="{{ $chartAreaPoints }}" fill="#f59e0b" fill-opacity="0.12" />
                        <polyline points="{{ $chartLinePoints }}" fill="none" stroke="#f59e0b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                        @foreach ($chartPoints as $index => $point)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3.5" fill="#d97706" stroke="white" stroke-width="2">
                                <title>{{ $point['day']['date'] }}: {{ number_format($point['day']['units_sold'], 0, ',', '.') }} unit</title>
                            </circle>
                            @if ($dailyProductSales->count() <= 7 || $index === 0 || $index === $dailyProductSales->count() - 1 || $index % $chartLabelInterval === 0)
                                <text x="{{ $point['x'] }}" y="230" text-anchor="middle" fill="#64748b" font-size="11">{{ $point['day']['label'] }}</text>
                            @endif
                        @endforeach
                    </svg>
                @endif
            </div>
        </div>

        <div class="admin-dashboard-card rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">5 produk terlaris</h2>
                <p class="mt-1 text-sm text-slate-500">Berdasarkan unit terjual · {{ $periodLabel }}.</p>
            </div>
            <div class="p-6">
                @if ($topSellingProducts->isEmpty())
                    <div class="flex min-h-48 items-center justify-center rounded-xl bg-slate-50 px-6 text-center text-sm text-slate-500">Belum ada penjualan yang dibayar pada periode ini.</div>
                @else
                    @php($topSellingMax = max(1, (int) $topSellingProducts->max('units_sold')))
                    <div class="space-y-5">
                        @foreach ($topSellingProducts as $productSale)
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                    <span class="truncate font-medium text-slate-700" title="{{ $productSale->product_name }}">{{ $productSale->product_name }}</span>
                                    <span class="shrink-0 font-semibold text-slate-900">{{ number_format($productSale->units_sold, 0, ',', '.') }} unit</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="{{ $productSale->product_name }} terjual" aria-valuemin="0" aria-valuemax="{{ $topSellingMax }}" aria-valuenow="{{ $productSale->units_sold }}">
                                    <div class="h-full rounded-full bg-amber-500" style="width: {{ min(100, ($productSale->units_sold / $topSellingMax) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        </div>
    </section>

    <section>
        <div class="admin-dashboard-card rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-semibold text-slate-900">Perhatian stok</h2><p class="mt-1 text-sm text-slate-500">Produk yang perlu segera dicek.</p></div>
            <div class="p-6">
                @if ($outOfStockProducts->isEmpty() && $lowStockProducts->isEmpty())<p class="text-sm text-slate-500">Semua stok masih aman.</p>@else
                    <div class="space-y-3">@foreach ($outOfStockProducts->take(3) as $product)<div class="flex items-center justify-between gap-4 rounded-xl bg-red-50 px-4 py-3"><span class="text-sm font-medium text-slate-800">{{ $product->name }}</span><span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">Habis</span></div>@endforeach @foreach ($lowStockProducts->take(3) as $product)<div class="flex items-center justify-between gap-4 rounded-xl bg-amber-50 px-4 py-3"><span class="text-sm font-medium text-slate-800">{{ $product->name }}</span><span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">Stok {{ $product->stock }}</span></div>@endforeach</div>
                @endif
                <div class="mt-5 flex flex-wrap gap-2"><a href="{{ route('admin.products.index', ['stock' => 'out']) }}" class="rounded-xl border border-red-200 px-4 py-2 text-xs font-medium text-red-700 hover:bg-red-50">Lihat stok habis</a><a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="rounded-xl border border-amber-200 px-4 py-2 text-xs font-medium text-amber-700 hover:bg-amber-50">Lihat stok menipis</a></div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const periodSelect = document.querySelector('[data-period-select]');
            const dateRange = document.querySelector('[data-period-range]');

            if (!periodSelect || !dateRange) return;

            const dateInputs = dateRange.querySelectorAll('[data-period-date]');
            const updateDateRange = () => {
                const isCustom = periodSelect.value === 'custom';
                dateRange.hidden = !isCustom;
                dateInputs.forEach((input) => {
                    input.disabled = !isCustom;
                    input.required = isCustom;
                });
            };

            periodSelect.addEventListener('change', updateDateRange);
            updateDateRange();
        })();
    </script>
@endpush
