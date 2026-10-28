<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\CarbonImmutable;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    private const STATUS_LABELS = [
        Order::STATUS_PENDING_CONFIRMATION => 'Menunggu konfirmasi',
        Order::STATUS_AWAITING_PAYMENT => 'Menunggu pembayaran',
        Order::STATUS_PAID => 'Dibayar',
        Order::STATUS_PROCESSING => 'Sedang dikemas',
        Order::STATUS_SHIPPED => 'Dikirim',
        Order::STATUS_CANCELLED => 'Dibatalkan',
        Order::STATUS_EXPIRED => 'Kedaluwarsa',
    ];

    public function index(Request $request): View
    {
        $orders = $this->filteredOrdersQuery($request)
            ->with('items')
            ->withMax('statusHistories as latest_status_update_at', 'created_at')
            ->orderByDesc('latest_status_update_at')
            ->orderByDesc('orders.created_at')
            ->orderByDesc('orders.id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function exportOrders(Request $request): StreamedResponse
    {
        $orders = $this->filteredOrdersQuery($request)->with('items');
        $filters = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);
        $status = $request->string('status')->toString();
        $statusLabel = self::STATUS_LABELS[$status] ?? 'Semua status';
        $ordersCount = (clone $orders)->count();
        $ordersTotal = (int) (clone $orders)->sum('total_amount');
        $filename = $this->exportFilename('pesanan', $filters, $statusLabel);

        return $this->streamXlsxDownload($filename, function (Worksheet $sheet) use ($orders, $ordersCount, $ordersTotal, $filters): void {
            $this->prepareExportSheet(
                $sheet,
                'Laporan Pesanan Sinergi Store',
                $this->periodLabel($filters),
                $ordersCount,
                $ordersTotal,
                'Data mengikuti filter pesanan yang diterapkan. Nilai pesanan bukan omzet; status setiap pesanan dicantumkan.',
                ['Nomor pesanan', 'Tanggal pesanan', 'Nama pembeli', 'WhatsApp', 'Produk', 'Varian', 'Jumlah', 'Harga satuan (Rp)', 'Subtotal (Rp)', 'Status'],
                'Nilai pesanan',
            );

            $row = 7;
            foreach ($orders->lazyById(250) as $order) {
                $items = $order->items;
                if ($items->isEmpty()) {
                    $this->writeExportRow($sheet, $row++, [
                        $order->order_number,
                        $order->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
                        $order->customer_name,
                        $order->customer_phone,
                        '-', '-', 0, 0, 0,
                        self::STATUS_LABELS[$order->status] ?? $order->status,
                    ]);
                    continue;
                }

                foreach ($items as $item) {
                    $this->writeExportRow($sheet, $row++, [
                        $order->order_number,
                        $order->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
                        $order->customer_name,
                        $order->customer_phone,
                        $item->product_name,
                        $item->variant_name ?? '-',
                        (int) $item->quantity,
                        (int) $item->unit_price,
                        (int) $item->subtotal,
                        self::STATUS_LABELS[$order->status] ?? $order->status,
                    ]);
                }
            }
        });
    }

    public function exportSales(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $orders = Order::query()
            ->where('status', Order::STATUS_PAID)
            ->with([
                'items',
                'statusHistories' => fn ($query) => $query
                    ->where('to_status', Order::STATUS_PAID)
                    ->oldest('created_at')
                    ->oldest('id'),
            ]);

        if (isset($filters['date_from']) || isset($filters['date_to'])) {
            $dateFromUtc = isset($filters['date_from'])
                ? CarbonImmutable::createFromFormat('!Y-m-d', $filters['date_from'], 'Asia/Jakarta')->utc()
                : null;
            $dateToExclusiveUtc = isset($filters['date_to'])
                ? CarbonImmutable::createFromFormat('!Y-m-d', $filters['date_to'], 'Asia/Jakarta')->addDay()->utc()
                : null;

            $orders->whereHas('statusHistories', function (Builder $query) use ($dateFromUtc, $dateToExclusiveUtc): void {
                $query->where('to_status', Order::STATUS_PAID)
                    ->when($dateFromUtc, fn (Builder $query) => $query->where('created_at', '>=', $dateFromUtc))
                    ->when($dateToExclusiveUtc, fn (Builder $query) => $query->where('created_at', '<', $dateToExclusiveUtc));
            });
        }

        $salesCount = (clone $orders)->count();
        $salesTotal = (int) (clone $orders)->sum('total_amount');
        $filename = $this->exportFilename('laporan-penjualan', $filters);

        return $this->streamXlsxDownload($filename, function (Worksheet $sheet) use ($orders, $salesCount, $salesTotal, $filters): void {
            $this->prepareExportSheet(
                $sheet,
                'Laporan Penjualan Sinergi Store',
                $this->periodLabel($filters),
                $salesCount,
                $salesTotal,
                'Hanya pesanan berstatus Dibayar. Tanggal laporan mengikuti waktu pembayaran yang dikonfirmasi admin.',
                ['Nomor pesanan', 'Dikonfirmasi pada', 'Nama pembeli', 'WhatsApp', 'Produk', 'Varian', 'Jumlah', 'Harga satuan (Rp)', 'Subtotal (Rp)', 'Status'],
            );

            $row = 7;
            foreach ($orders->lazyById(250) as $order) {
                $paidAt = $order->statusHistories->first()?->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i');

                foreach ($order->items as $item) {
                    $this->writeExportRow($sheet, $row++, [
                        $order->order_number,
                        $paidAt,
                        $order->customer_name,
                        $order->customer_phone,
                        $item->product_name,
                        $item->variant_name ?? '-',
                        (int) $item->quantity,
                        (int) $item->unit_price,
                        (int) $item->subtotal,
                        self::STATUS_LABELS[$order->status] ?? $order->status,
                    ]);
                }
            }
        });
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'items.variant', 'statusHistories']);

        return view('admin.orders.show', [
            'order' => $order,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    Order::STATUS_AWAITING_PAYMENT,
                    Order::STATUS_PAID,
                    Order::STATUS_PROCESSING,
                    Order::STATUS_CANCELLED,
                    Order::STATUS_EXPIRED,
                ]),
            ],
        ]);

        $admin = $request->user();
        $message = DB::transaction(function () use ($order, $validated, $admin): ?string {
            $lockedOrder = Order::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($order->id);
            $targetStatus = $validated['status'];

            if ($targetStatus === Order::STATUS_AWAITING_PAYMENT) {
                if ($lockedOrder->status !== Order::STATUS_PENDING_CONFIRMATION) {
                    return 'Hanya pesanan yang menunggu konfirmasi yang bisa disetujui.';
                }

                $inventoryError = $this->reserveOrderItems($lockedOrder);
                if ($inventoryError !== null) {
                    return $inventoryError;
                }

                $lockedOrder->update([
                    'status' => Order::STATUS_AWAITING_PAYMENT,
                    'payment_due_at' => now()->addHours(24),
                ]);
                $this->recordStatusChange(
                    $lockedOrder,
                    Order::STATUS_PENDING_CONFIRMATION,
                    Order::STATUS_AWAITING_PAYMENT,
                    (int) $admin->getAuthIdentifier(),
                    (string) $admin->name,
                );

                return null;
            }

            if ($targetStatus === Order::STATUS_PAID) {
                if ($lockedOrder->status !== Order::STATUS_AWAITING_PAYMENT) {
                    return 'Hanya pesanan yang menunggu pembayaran yang bisa dikonfirmasi lunas.';
                }

                if (! $lockedOrder->payment_due_at || $lockedOrder->payment_due_at->isPast()) {
                    return 'Batas pembayaran sudah lewat. Tandai pesanan ini kedaluwarsa terlebih dahulu.';
                }

                $inventoryError = $this->deductPaidOrderItems($lockedOrder);
                if ($inventoryError !== null) {
                    return $inventoryError;
                }

                $lockedOrder->update(['status' => Order::STATUS_PAID]);
                $this->recordStatusChange(
                    $lockedOrder,
                    Order::STATUS_AWAITING_PAYMENT,
                    Order::STATUS_PAID,
                    (int) $admin->getAuthIdentifier(),
                    (string) $admin->name,
                );

                return null;
            }

            if ($targetStatus === Order::STATUS_PROCESSING) {
                if ($lockedOrder->status !== Order::STATUS_PAID) {
                    return 'Hanya pesanan yang sudah dibayar yang bisa mulai diproses.';
                }

                $lockedOrder->update(['status' => Order::STATUS_PROCESSING]);
                $this->recordStatusChange(
                    $lockedOrder,
                    Order::STATUS_PAID,
                    Order::STATUS_PROCESSING,
                    (int) $admin->getAuthIdentifier(),
                    (string) $admin->name,
                );

                return null;
            }

            if ($targetStatus === Order::STATUS_CANCELLED) {
                if (! in_array($lockedOrder->status, [Order::STATUS_PENDING_CONFIRMATION, Order::STATUS_AWAITING_PAYMENT], true)) {
                    return 'Pesanan yang sudah dibayar atau selesai tidak dapat dibatalkan dari alur ini.';
                }

                $previousStatus = $lockedOrder->status;
                $lockedOrder->update(['status' => Order::STATUS_CANCELLED]);
                $this->recordStatusChange(
                    $lockedOrder,
                    $previousStatus,
                    Order::STATUS_CANCELLED,
                    (int) $admin->getAuthIdentifier(),
                    (string) $admin->name,
                );

                return null;
            }

            if ($targetStatus === Order::STATUS_EXPIRED) {
                if (
                    $lockedOrder->status !== Order::STATUS_AWAITING_PAYMENT
                    || ! $lockedOrder->payment_due_at
                    || $lockedOrder->payment_due_at->isFuture()
                ) {
                    return 'Pesanan hanya bisa ditandai kedaluwarsa setelah batas pembayarannya lewat.';
                }

                $lockedOrder->update(['status' => Order::STATUS_EXPIRED]);
                $this->recordStatusChange(
                    $lockedOrder,
                    Order::STATUS_AWAITING_PAYMENT,
                    Order::STATUS_EXPIRED,
                    (int) $admin->getAuthIdentifier(),
                    (string) $admin->name,
                );

                return null;
            }

            return 'Perubahan status pesanan tidak valid.';
        });

        if ($message !== null) {
            return back()->with('error', $message);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function updateShipping(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'courier_name' => ['required', 'string', 'max:100'],
            'tracking_number' => ['required', 'string', 'max:100'],
        ]);

        $admin = $request->user();
        $message = DB::transaction(function () use ($order, $validated, $admin): ?string {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($lockedOrder->status !== Order::STATUS_PROCESSING) {
                return 'Detail pengiriman hanya bisa diisi untuk pesanan yang sedang diproses.';
            }

            $lockedOrder->update([
                'courier_name' => trim($validated['courier_name']),
                'tracking_number' => trim($validated['tracking_number']),
                'shipped_at' => now(),
                'status' => Order::STATUS_SHIPPED,
            ]);

            $this->recordStatusChange(
                $lockedOrder,
                Order::STATUS_PROCESSING,
                Order::STATUS_SHIPPED,
                (int) $admin->getAuthIdentifier(),
                (string) $admin->name,
            );

            return null;
        });

        if ($message !== null) {
            return back()->with('error', $message);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Detail pengiriman tersimpan dan status pesanan menjadi Dikirim.');
    }

    private function filteredOrdersQuery(Request $request): Builder
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $query = Order::query();

        $query->when($request->filled('search'), function (Builder $query) use ($request): void {
            $search = (string) $request->string('search')->trim();

            $query->where(function (Builder $query) use ($search): void {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        });

        $query->when(
            $request->filled('status') && array_key_exists($request->string('status')->toString(), self::STATUS_LABELS),
            fn (Builder $query) => $query->where('status', $request->string('status')->toString()),
        );

        if (isset($filters['date_from'])) {
            $dateFromUtc = CarbonImmutable::createFromFormat('!Y-m-d', $filters['date_from'], 'Asia/Jakarta')->utc();
            $query->where('created_at', '>=', $dateFromUtc);
        }

        if (isset($filters['date_to'])) {
            $dateToExclusiveUtc = CarbonImmutable::createFromFormat('!Y-m-d', $filters['date_to'], 'Asia/Jakarta')
                ->addDay()
                ->utc();
            $query->where('created_at', '<', $dateToExclusiveUtc);
        }

        return $query;
    }

    /** @param array{date_from?: string, date_to?: string} $filters */
    private function exportFilename(string $prefix, array $filters, ?string $statusLabel = null): string
    {
        $parts = [$prefix];
        if ($statusLabel !== null) {
            $parts[] = str($statusLabel)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->toString();
        }

        if (isset($filters['date_from'], $filters['date_to'])) {
            $parts[] = $this->filenameDate($filters['date_from']);
            $parts[] = 'sampai';
            $parts[] = $this->filenameDate($filters['date_to']);
        } elseif (isset($filters['date_from'])) {
            $parts[] = 'sejak';
            $parts[] = $this->filenameDate($filters['date_from']);
        } elseif (isset($filters['date_to'])) {
            $parts[] = 'sampai';
            $parts[] = $this->filenameDate($filters['date_to']);
        } else {
            $parts[] = 'semua-periode';
        }

        return implode('-', $parts) . '.xlsx';
    }

    private function filenameDate(string $date): string
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $date, 'Asia/Jakarta')
            ->locale('id')
            ->translatedFormat('d-F-Y');
    }

    /** @param array{date_from?: string, date_to?: string} $filters */
    private function periodLabel(array $filters): string
    {
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;

        return match (true) {
            $from && $to => 'Periode ' . CarbonImmutable::parse($from)->format('d/m/Y') . ' – ' . CarbonImmutable::parse($to)->format('d/m/Y'),
            $from => 'Mulai ' . CarbonImmutable::parse($from)->format('d/m/Y'),
            $to => 'Sampai ' . CarbonImmutable::parse($to)->format('d/m/Y'),
            default => 'Semua periode',
        };
    }

    /** @param list<string> $headers */
    private function prepareExportSheet(
        Worksheet $sheet,
        string $title,
        string $period,
        int $ordersCount,
        int $total,
        string $note,
        array $headers,
        string $totalLabel = 'Total penjualan',
    ): void {
        foreach (['A1:I1' => 'A1:J1', 'A2:I2' => 'A2:J2', 'A3:I3' => 'A3:J3', 'A5:I5' => 'A5:J5'] as $oldRange => $newRange) {
            if (array_key_exists($oldRange, $sheet->getMergeCells())) {
                $sheet->unmergeCells($oldRange);
                $sheet->mergeCells($newRange);
            }
        }

        $this->setExportText($sheet, 'A1', $title);
        $this->setExportText($sheet, 'A2', $period);
        $this->setExportText($sheet, 'A3', 'Ringkasan transaksi');
        $this->setExportText($sheet, 'A4', 'TOTAL PESANAN');
        $sheet->setCellValue('C4', $ordersCount);
        $this->setExportText($sheet, 'E4', strtoupper($totalLabel));
        $sheet->setCellValue('G4', $total);
        $this->setExportText($sheet, 'A5', $note);

        foreach ($headers as $index => $header) {
            $column = chr(ord('A') + $index);
            if ($column === 'J') {
                $sheet->duplicateStyle($sheet->getStyle('I6'), 'J6');
            }
            $this->setExportText($sheet, $column . '6', $header);
        }

        $sheet->getColumnDimension('J')->setWidth(22);
        $lastTemplateRow = max(9, $sheet->getHighestDataRow());
        for ($row = 7; $row <= $lastTemplateRow; $row++) {
            for ($column = 'A'; $column <= 'J'; $column++) {
                $sheet->setCellValue($column . $row, null);
            }
        }
    }

    /** @param list<mixed> $values */
    private function writeExportRow(Worksheet $sheet, int $row, array $values): void
    {
        if ($row > 9) {
            $sheet->duplicateStyle($sheet->getStyle('A9:I9'), "A{$row}:I{$row}");
            $sheet->duplicateStyle($sheet->getStyle('I9'), "J{$row}");
            $sheet->getRowDimension($row)->setRowHeight($sheet->getRowDimension(9)->getRowHeight());
        } else {
            $sheet->duplicateStyle($sheet->getStyle("I{$row}"), "J{$row}");
        }

        foreach ($values as $index => $value) {
            $column = chr(ord('A') + $index);
            if (is_int($value) || is_float($value)) {
                $sheet->setCellValue($column . $row, $value);
            } else {
                $this->setExportText($sheet, $column . $row, $value === null ? '' : (string) $value);
            }
        }
    }

    private function setExportText(Worksheet $sheet, string $cell, string $value): void
    {
        $sheet->setCellValueExplicit($cell, $value, DataType::TYPE_STRING);
    }

    private function streamXlsxDownload(string $filename, callable $populate): StreamedResponse
    {
        return response()->streamDownload(function () use ($populate): void {
            $temporaryPath = tempnam(sys_get_temp_dir(), 'sinergi-export-');
            if ($temporaryPath === false) {
                throw new \RuntimeException('File sementara untuk ekspor tidak dapat dibuat.');
            }

            try {
                $spreadsheet = IOFactory::load(resource_path('templates/exports/rekap-penjualan.xlsx'));
                $populate($spreadsheet->getActiveSheet());
                (new Xlsx($spreadsheet))->save($temporaryPath);
                readfile($temporaryPath);
                $spreadsheet->disconnectWorksheets();
            } finally {
                if (is_file($temporaryPath)) {
                    unlink($temporaryPath);
                }
            }
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function recordStatusChange(
        Order $order,
        string $fromStatus,
        string $toStatus,
        int $adminId,
        string $adminName,
    ): void {
        $order->statusHistories()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $adminId,
            'actor_name' => $adminName,
        ]);
    }

    private function reserveOrderItems(Order $order): ?string
    {
        if ($order->items->isEmpty()) {
            return 'Pesanan ini belum memiliki item untuk ditahan stoknya.';
        }

        foreach ($this->groupItemsByInventory($order->items) as $inventoryKey => $items) {
            $quantity = $items->sum('quantity');
            $inventory = $this->lockInventory($inventoryKey, $items->first());

            if ($inventory === null) {
                return 'Produk atau varian pada pesanan ini sudah tidak tersedia di katalog.';
            }

            [, $reservedQuantity] = $this->stockAndActiveReservations($inventoryKey, $inventory['stock'], $order->id);
            if ($inventory['stock'] - $reservedQuantity < $quantity) {
                return "Stok tersedia tidak cukup untuk {$items->first()->product_name}.";
            }
        }

        return null;
    }

    private function deductPaidOrderItems(Order $order): ?string
    {
        if ($order->items->isEmpty()) {
            return 'Pesanan ini tidak memiliki item yang bisa diproses.';
        }

        $lockedInventories = [];

        foreach ($this->groupItemsByInventory($order->items) as $inventoryKey => $items) {
            $quantity = $items->sum('quantity');
            $inventory = $this->lockInventory($inventoryKey, $items->first());

            if ($inventory === null || $inventory['stock'] < $quantity) {
                return 'Stok fisik tidak cukup untuk mengonfirmasi pembayaran pesanan ini.';
            }

            $lockedInventories[$inventoryKey] = [
                'inventory' => $inventory,
                'quantity' => $quantity,
            ];
        }

        foreach ($lockedInventories as $lockedInventory) {
            $inventory = $lockedInventory['inventory'];
            $remainingStock = $inventory['stock'] - $lockedInventory['quantity'];

            if ($inventory['variant'] instanceof ProductVariant) {
                $inventory['variant']->update(['stock' => $remainingStock]);
                $inventory['product']->update([
                    'stock' => $inventory['product']->variants()->sum('stock'),
                ]);
            } else {
                $inventory['product']->update(['stock' => $remainingStock]);
            }
        }

        return null;
    }

    /**
     * @param  EloquentCollection<int, OrderItem>  $items
     * @return Collection<string, EloquentCollection<int, OrderItem>>
     */
    private function groupItemsByInventory(EloquentCollection $items): Collection
    {
        return $items
            ->groupBy(fn (OrderItem $item) => $item->product_variant_id
                ? 'variant:' . $item->product_variant_id
                : 'product:' . $item->product_id)
            ->sortKeys();
    }

    /**
     * @return array{product: Product, variant: ProductVariant|null, stock: int}|null
     */
    private function lockInventory(string $inventoryKey, OrderItem $item): ?array
    {
        [$type, $id] = explode(':', $inventoryKey, 2);

        if ($type === 'variant') {
            $product = Product::query()->lockForUpdate()->find($item->product_id);
            if (! $product) {
                return null;
            }

            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->find($id);
            if (! $variant) {
                return null;
            }

            return [
                'product' => $product,
                'variant' => $variant,
                'stock' => (int) $variant->stock,
            ];
        }

        $product = Product::query()->lockForUpdate()->find($id);
        if (! $product || $product->variants()->exists()) {
            return null;
        }

        return [
            'product' => $product,
            'variant' => null,
            'stock' => (int) $product->stock,
        ];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function stockAndActiveReservations(string $inventoryKey, int $stock, int $excludingOrderId): array
    {
        [$type, $id] = explode(':', $inventoryKey, 2);

        $query = OrderItem::query()
            ->whereHas('order', function ($query) use ($excludingOrderId) {
                $query->where('status', Order::STATUS_AWAITING_PAYMENT)
                    ->where('payment_due_at', '>', now())
                    ->where('id', '!=', $excludingOrderId);
            });

        if ($type === 'variant') {
            $query->where('product_variant_id', $id);
        } else {
            $query->where('product_id', $id)->whereNull('product_variant_id');
        }

        return [$stock, (int) $query->sum('quantity')];
    }
}
