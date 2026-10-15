<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $nowJakarta = now('Asia/Jakarta');
        $monthStartUtc = $nowJakarta->copy()->startOfMonth()->utc();
        $monthEndUtc = $nowJakarta->copy()->endOfMonth()->utc();

        $filters = $request->validate([
            'period' => ['nullable', 'in:today,last_7_days,last_30_days,custom'],
            'date_from' => ['required_if:period,custom', 'nullable', 'date_format:Y-m-d'],
            'date_to' => ['required_if:period,custom', 'nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $selectedPeriod = $filters['period'] ?? 'last_7_days';
        $periodStart = match ($selectedPeriod) {
            'today' => $nowJakarta->copy()->startOfDay(),
            'last_30_days' => $nowJakarta->copy()->startOfDay()->subDays(29),
            'custom' => Carbon::createFromFormat('!Y-m-d', $filters['date_from'], 'Asia/Jakarta'),
            default => $nowJakarta->copy()->startOfDay()->subDays(6),
        };
        $periodEnd = $selectedPeriod === 'custom'
            ? Carbon::createFromFormat('!Y-m-d', $filters['date_to'], 'Asia/Jakarta')->endOfDay()
            : $nowJakarta->copy()->endOfDay();

        if ($selectedPeriod === 'custom' && $periodStart->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) > 30) {
            throw ValidationException::withMessages([
                'date_to' => 'Rentang tanggal maksimal 31 hari.',
            ]);
        }

        $periodStartUtc = $periodStart->copy()->utc();
        $periodEndUtc = $periodEnd->copy()->utc();
        $periodLabel = $selectedPeriod === 'custom'
            ? ($periodStart->isSameDay($periodEnd)
                ? $periodStart->locale('id')->translatedFormat('d M Y')
                : $periodStart->locale('id')->translatedFormat('d M Y') . ' – ' . $periodEnd->locale('id')->translatedFormat('d M Y'))
            : match ($selectedPeriod) {
                'today' => 'Hari ini · ' . $nowJakarta->locale('id')->translatedFormat('d M Y'),
                'last_30_days' => '30 hari terakhir',
                default => '7 hari terakhir',
            };
        $dateFrom = $periodStart->toDateString();
        $dateTo = $periodEnd->toDateString();

        $monthlyPaidOrders = fn () => Order::query()
            ->where('status', Order::STATUS_PAID)
            ->whereBetween('updated_at', [$monthStartUtc, $monthEndUtc]);

        $monthlyRevenue = (float) $monthlyPaidOrders()->sum('total_amount');
        $paidOrdersThisMonth = $monthlyPaidOrders()->count();
        $productsSoldInPeriod = (int) OrderItem::query()
            ->whereHas('order', fn ($query) => $query
                ->where('status', Order::STATUS_PAID)
                ->whereBetween('updated_at', [$periodStartUtc, $periodEndUtc]))
            ->sum('quantity');
        $ordersAwaitingConfirmation = Order::query()
            ->where('status', Order::STATUS_PENDING_CONFIRMATION)
            ->count();

        $topSellingProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', Order::STATUS_PAID)
            ->whereBetween('orders.updated_at', [$periodStartUtc, $periodEndUtc])
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as units_sold')
            ->groupBy('order_items.product_name')
            ->orderByDesc('units_sold')
            ->orderBy('order_items.product_name')
            ->limit(5)
            ->get();

        $daysInPeriod = $periodStart->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) + 1;
        $dailyPaidItems = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', Order::STATUS_PAID)
            ->whereBetween('orders.updated_at', [$periodStartUtc, $periodEndUtc])
            ->selectRaw('orders.updated_at as paid_at, order_items.quantity')
            ->get();
        $dailySalesByDate = $dailyPaidItems
            ->groupBy(fn ($item) => Carbon::parse($item->paid_at, 'UTC')->timezone('Asia/Jakarta')->format('Y-m-d'))
            ->map(fn ($items) => (int) $items->sum('quantity'));
        $dailyProductSales = collect(range(0, $daysInPeriod - 1))->map(function (int $daysAfterStart) use ($periodStart, $dailySalesByDate) {
            $date = $periodStart->copy()->startOfDay()->addDays($daysAfterStart);
            $key = $date->format('Y-m-d');

            return [
                'label' => $date->format('d/m'),
                'date' => $date->locale('id')->translatedFormat('l, d F Y'),
                'units_sold' => (int) $dailySalesByDate->get($key, 0),
            ];
        });
        $maxDailyProductSales = max(1, (int) ($dailyProductSales->max('units_sold') ?? 0));

        $lowStockProducts = Product::whereBetween('stock', [1, 5])->orderBy('stock')->orderBy('name')->get();
        $outOfStockProducts = Product::where('stock', 0)->orderBy('name')->get();
        return view('admin.dashboard', compact(
            'monthlyRevenue',
            'paidOrdersThisMonth',
            'productsSoldInPeriod',
            'ordersAwaitingConfirmation',
            'selectedPeriod',
            'dateFrom',
            'dateTo',
            'periodLabel',
            'topSellingProducts',
            'dailyProductSales',
            'maxDailyProductSales',
            'lowStockProducts',
            'outOfStockProducts',
        ));
    }

    public function profile(): View
    {
        return view('admin.profile');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        return back()->with('success', 'Profil admin berhasil diperbarui.');
    }
}
