<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductAvailabilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqBotController extends Controller
{
    public function respond(Request $request, ProductAvailabilityService $productAvailability): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $message = mb_strtolower(trim($validated['message']));
        $greeting = $this->extractGreeting($message);
        $message = $this->removeGreeting($message, $greeting);

        if ($message === '') {
            return response()->json([
                'matched' => true,
                'reply' => ($greeting ? $greeting.'! ' : '').'Ada yang ingin kamu tanyakan tentang produk, pesanan, ongkir, atau jasa kami?',
            ]);
        }

        /** @var list<string> $serviceKeywords */
        $serviceKeywords = [
            'jasa kreatif',
            'jasa foto',
            'jasa video',
            'jasa desain',
            'harga jasa',
            'pesan jasa',
            'jasa kami',
            'foto produk',
            'video produk',
            'desain produk',
            'layanan kreatif',
            'layanan foto',
            'layanan video',
            'layanan desain',
            'sinergi creative',
        ];

        $shippingServiceKeywords = ['pengiriman', 'ongkir', 'ongkos kirim', 'ongkos pengiriman', 'ekspedisi', 'kurir', 'jasa kirim'];
        $mentionsShippingService = false;

        foreach ($shippingServiceKeywords as $keyword) {
            if ($this->containsKeyword($message, $keyword)) {
                $mentionsShippingService = true;
                break;
            }
        }

        if (! $mentionsShippingService && $this->containsKeyword($message, 'jasa')) {
            $serviceKeywords[] = 'jasa';
        }

        foreach ($serviceKeywords as $keyword) {
            if ($this->containsKeyword($message, $keyword)) {
                return response()->json([
                    'matched' => false,
                    'reply' => $this->withGreeting($greeting, 'Untuk pertanyaan atau pemesanan jasa foto produk, video, dan desain, silakan konsultasi langsung dengan admin kami melalui WhatsApp.'),
                    'contact_url' => 'https://wa.me/6282142612382',
                ]);
            }
        }

        $stockHoldingKeywords = ['hold stok', 'stok di-hold', 'dihold', 'di hold', 'stok ditahan', 'stok pesanan ditahan', 'ditahan'];

        foreach ($stockHoldingKeywords as $keyword) {
            if ($this->containsKeyword($message, $keyword)) {
                return response()->json([
                    'matched' => true,
                    'reply' => $this->withGreeting($greeting, 'Stok ditahan selama 24 jam setelah admin menetapkan ongkir dan pesanan menunggu pembayaran. Stok fisik dikurangi setelah pembayaran dikonfirmasi admin.'),
                ]);
            }
        }

        $stockQuestionKeywords = ['total stok produk', 'total stok', 'stok total', 'jumlah stok produk', 'jumlah stok', 'stok produk', 'stok habis', 'kehabisan stok', 'stok tersedia', 'persediaan', 'ready stock', 'ready stok', 'masih ready', 'masih ada stok', 'stok'];
        $asksAboutStock = collect($stockQuestionKeywords)
            ->contains(fn (string $keyword): bool => $this->containsKeyword($message, $keyword));

        if ($asksAboutStock) {
            $product = $this->findMentionedActiveProduct($message);

            if (! $product) {
                return response()->json([
                    'matched' => true,
                    'reply' => $this->withGreeting($greeting, 'Produk mana yang ingin kamu cek stoknya? Sebutkan nama produknya, nanti aku tampilkan jumlah stok tersedia dan rinciannya per ukuran atau warna jika ada.'),
                ]);
            }

            $productAvailability->refresh($product);

            if ($product->variants->isEmpty()) {
                $availableStock = (int) $product->getAttribute('available_stock');
                $stockReply = 'Stok tersedia untuk '.$product->name.': '.$availableStock.' unit.';
            } else {
                $availableStock = (int) $product->variants->sum('available_stock');
                $variantStockSummary = $product->variants
                    ->map(function ($variant): string {
                        $variantName = collect([$variant->size, $variant->color])
                            ->filter()
                            ->implode(' · ');

                        return ($variantName !== '' ? $variantName : 'Varian')
                            .': '.(int) $variant->available_stock.' unit';
                    })
                    ->implode(', ');
                $stockReply = 'Total stok tersedia untuk '.$product->name.': '.$availableStock.' unit. Rincian: '.$variantStockSummary.'.';
            }

            return response()->json([
                'matched' => true,
                'reply' => $this->withGreeting($greeting, $stockReply.' Stok yang sedang ditahan untuk pesanan belum dibayar tidak dihitung sebagai stok tersedia.'),
            ]);
        }

        /** @var array<array{keywords: list<string>, reply: string}> $faqResponses */
        $faqResponses = [
            [
                'keywords' => ['status pesanan', 'status order', 'pesanan saya', 'pesenan saya', 'cek status', 'cek pesanan', 'cek pesenan', 'lacak pesanan', 'lacak order', 'tracking pesanan', 'nomor resi', 'no resi', 'no.resi', 'resi', 'pesanan udah sampai', 'pesanan sudah sampai'],
                'reply' => 'Status pesanan dan informasi resi bisa dilihat di menu Pesanan Saya setelah masuk ke akun.',
            ],
            [
                'keywords' => ['ongkir', 'ongkirnya', 'ongkr', 'ongkos', 'ongkos kirim', 'ongkir kirim', 'ongkos pengiriman', 'biaya kirim', 'biaya pengiriman', 'tarif kirim', 'tarif ongkir', 'ekspedisi', 'kurir', 'berapa ongkir', 'brp ongkir', 'ongkir brp', 'ongkir berapa', 'ongkir ke', 'jasa kirim'],
                'reply' => 'Ongkir belum termasuk saat pesanan dibuat. Admin akan menghitung ongkir berdasarkan alamat tujuan, lalu total pembayaran tampil di detail pesanan sebelum kamu membayar.',
            ],
            [
                'keywords' => ['bukti pembayaran', 'bukti bayar', 'bukti transfer', 'bukti tf', 'upload bukti', 'unggah bukti', 'upload transfer', 'unggah transfer', 'cara bayar', 'cara pembayaran', 'pembayaran', 'transfer bank', 'transfer', 'tf', 'nomor rekening', 'norek', 'rekening', 'bayar pesanan', 'bayarnya gimana', 'bayar pake apa', 'bayar pakai apa', 'cara tf', 'bayar'],
                'reply' => 'Setelah admin menetapkan ongkir, buka detail pesanan di menu Pesanan Saya. Ikuti informasi pembayaran yang tercantum di sana, lalu unggah bukti pembayaran sebelum batas waktunya.',
            ],
            [
                'keywords' => ['buat akun', 'bikin akun', 'bkin akun', 'daftar akun', 'daftar', 'registrasi', 'register', 'sign up', 'sign in', 'masuk akun', 'login', 'log in', 'akun'],
                'reply' => 'Kamu perlu masuk atau membuat akun pelanggan untuk memesan. Akun juga dipakai untuk melihat pesanan, mengelola alamat, dan mengunggah bukti pembayaran.',
            ],
            [
                'keywords' => ['pesan produk', 'pesan barang', 'pesen produk', 'pesen barang', 'cara memesan', 'cara pesan', 'gimana pesan', 'gimana cara pesan', 'gmn cara pesan', 'gmna cara pesan', 'cara order', 'gimana order', 'gmn order', 'buat pesanan', 'pemesanan', 'checkout', 'beli produk', 'beli barang', 'cara beli', 'cara pembelian'],
                'reply' => 'Masuk ke akun, pilih produk dan alamat tujuan, lalu buat pesanan. Admin akan meninjau pesanan dan menetapkan ongkir. Setelah total pembayaran tampil, kamu bisa melanjutkan pembayaran dari detail pesanan.',
            ],
            [
                'keywords' => ['ukuran', 'warna', 'varian', 'bahan produk', 'material produk', 'motif', 'promo', 'promosi', 'diskon', 'detail produk', 'tanya produk', 'tanya soal produk', 'ketersediaan produk', 'produk tersedia', 'produk ini ready', 'masih jual'],
                'reply' => 'Untuk pertanyaan detail tentang ukuran, warna, promo, atau produk tertentu, silakan tanyakan langsung kepada admin melalui WhatsApp.',
            ],
        ];

        /** @var list<string> $productCategoryKeywords */
        $productCategoryKeywords = [
            'ada produk apa',
            'ada produk apa saja',
            'produk apa saja',
            'produk apa aja',
            'produk apa',
            'ada produk apa aja',
            'jual produk apa',
            'jual apa saja',
            'jual apa aja',
            'menjual apa',
            'barang apa saja',
            'barang apa aja',
            'jenis produk',
            'katalog produk',
            'katalog',
        ];

        foreach ($productCategoryKeywords as $keyword) {
            if (! $this->containsKeyword($message, 'kategori') && $this->containsKeyword($message, $keyword)) {
                $productCategories = Category::query()
                    ->where('is_active', true)
                    ->whereHas('products', fn (Builder $query) => $query->where('is_active', true))
                    ->inRandomOrder()
                    ->limit(3)
                    ->pluck('name');

                if ($productCategories->isEmpty()) {
                    return response()->json([
                        'matched' => true,
                        'reply' => $this->withGreeting($greeting, 'Saat ini belum ada kategori produk aktif yang tersedia di katalog. Silakan coba lagi nanti atau hubungi admin melalui WhatsApp.'),
                    ]);
                }

                $categoryNames = $productCategories->take(2)->implode(' dan ');
                $moreCategories = $productCategories->count() > 2 ? ', dan lainnya' : '';

                return response()->json([
                    'matched' => true,
                    'reply' => $this->withGreeting($greeting, 'Produk yang tersedia antara lain kategori '.$categoryNames.$moreCategories.'. Lihat katalog pada menu Produk untuk daftar lengkapnya.'),
                ]);
            }
        }

        $asksForCategoryProducts = $this->containsKeyword($message, 'kategori')
            && collect(['produk', 'barang', 'apa', 'ada', 'jual', 'tersedia', 'isi'])
                ->contains(fn (string $keyword): bool => $this->containsKeyword($message, $keyword));

        if ($asksForCategoryProducts) {
            $category = $this->findMentionedActiveCategory($message);

            if (! $category) {
                $activeCategoryNames = Category::query()
                    ->where('is_active', true)
                    ->whereHas('products', fn (Builder $query) => $query->where('is_active', true))
                    ->orderBy('name')
                    ->pluck('name');

                $availableCategories = $activeCategoryNames->isNotEmpty()
                    ? ' Kategori yang tersedia: '.$activeCategoryNames->implode(', ').'.'
                    : ' Saat ini belum ada kategori aktif di katalog.';

                return response()->json([
                    'matched' => true,
                    'reply' => $this->withGreeting($greeting, 'Aku belum menemukan kategori yang kamu maksud.'.$availableCategories),
                ]);
            }

            $products = $category->products()
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name');

            if ($products->isEmpty()) {
                return response()->json([
                    'matched' => true,
                    'reply' => $this->withGreeting($greeting, 'Saat ini belum ada produk aktif di kategori '.$category->name.'.'),
                ]);
            }

            $listedProducts = $products->take(8)->implode(', ');
            $moreProducts = $products->count() > 8
                ? ' dan '.($products->count() - 8).' produk lainnya'
                : '';

            return response()->json([
                'matched' => true,
                'reply' => $this->withGreeting($greeting, 'Di kategori '.$category->name.', produk yang tersedia: '.$listedProducts.$moreProducts.'.'),
            ]);
        }

        foreach ($faqResponses as $faqResponse) {
            foreach ($faqResponse['keywords'] as $keyword) {
                if ($this->containsKeyword($message, $keyword)) {
                    return response()->json([
                        'matched' => true,
                        'reply' => $this->withGreeting($greeting, $faqResponse['reply']),
                    ]);
                }
            }
        }

        /** @var array<array{keywords: list<string>, reply: string}> $conversationalResponses */
        $conversationalResponses = [
            [
                'keywords' => ['terima kasih', 'terimakasih', 'makasih', 'makasi', 'thanks', 'thank you', 'okeoke jos'],
                'reply' => 'Sama-sama! Senang bisa membantu. Kalau ada pertanyaan lain soal produk, pesanan, atau jasa, langsung tanya aja ya.',
            ],
        ];

        foreach ($conversationalResponses as $conversationalResponse) {
            foreach ($conversationalResponse['keywords'] as $keyword) {
                if ($this->containsKeyword($message, $keyword)) {
                    return response()->json([
                        'matched' => true,
                        'reply' => $this->withGreeting($greeting, $conversationalResponse['reply']),
                    ]);
                }
            }
        }

        return response()->json([
            'matched' => false,
            'reply' => $this->withGreeting($greeting, 'Maaf, aku belum menemukan jawaban yang sesuai. Untuk pertanyaan khusus atau jawaban yang belum tersedia, chat admin kami melalui WhatsApp lewat tombol di bawah ini.'),
            'contact_url' => 'https://wa.me/6282142612382',
        ]);
    }

    private function extractGreeting(string $message): ?string
    {
        /** @var array<string, string> $greetings */
        $greetings = [
            'selamat pagi' => 'Selamat pagi',
            'selamat siang' => 'Selamat siang',
            'selamat sore' => 'Selamat sore',
            'selamat malam' => 'Selamat malam',
            'pagi' => 'Pagi',
            'siang' => 'Siang',
            'sore' => 'Sore',
            'malam' => 'Malam',
            'halo' => 'Halo',
            'hai' => 'Hai',
            'hi' => 'Hi',
        ];

        foreach ($greetings as $keyword => $greeting) {
            if (preg_match('/^'.preg_quote($keyword, '/').'(?![\p{L}\p{N}_])/u', $message) === 1) {
                return $greeting;
            }
        }

        return null;
    }

    private function removeGreeting(string $message, ?string $greeting): string
    {
        if ($greeting !== null) {
            $message = preg_replace('/^'.preg_quote(mb_strtolower($greeting), '/').'(?![\p{L}\p{N}_])/u', '', $message, 1) ?? $message;
        }

        $message = preg_replace('/^[\s,!.?;:]+/u', '', $message) ?? $message;
        $message = preg_replace('/^(?:(?:mau|ingin|pengen)\s+)?(?:bertanya|tanya|nanya)\b[\s,!.?;:]+/u', '', $message) ?? $message;

        return trim($message);
    }

    private function withGreeting(?string $greeting, string $reply): string
    {
        return $greeting === null ? $reply : $greeting.'! '.$reply;
    }

    private function containsKeyword(string $message, string $keyword): bool
    {
        $pattern = '/(?<![\p{L}\p{N}_])'.preg_quote($keyword, '/').'(?![\p{L}\p{N}_])/u';

        return preg_match($pattern, $message) === 1;
    }

    private function findMentionedActiveProduct(string $message): ?Product
    {
        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->get(['id', 'name', 'slug', 'stock']);

        $product = $products
            ->filter(function (Product $product) use ($message): bool {
                $productName = mb_strtolower(trim($product->name));
                $productSlug = mb_strtolower(str_replace('-', ' ', trim($product->slug)));

                return $this->containsKeyword($message, $productName)
                    || $this->containsKeyword($message, $productSlug);
            })
            ->sortByDesc(fn (Product $product): int => mb_strlen($product->name))
            ->first();

        return $product?->load('variants');
    }

    private function findMentionedActiveCategory(string $message): ?Category
    {
        return Category::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'slug'])
            ->filter(function (Category $category) use ($message): bool {
                $categoryName = mb_strtolower(trim($category->name));
                $categorySlug = mb_strtolower(str_replace('-', ' ', trim($category->slug)));

                return $this->containsKeyword($message, $categoryName)
                    || $this->containsKeyword($message, $categorySlug);
            })
            ->sortByDesc(fn (Category $category): int => mb_strlen($category->name))
            ->first();
    }
}
