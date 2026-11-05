<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqBotController extends Controller
{
    public function respond(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $message = mb_strtolower(trim($validated['message']));

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

        $shippingServiceKeywords = ['pengiriman', 'ongkir', 'ongkos kirim', 'ekspedisi', 'kurir'];
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
                    'reply' => 'Untuk pertanyaan atau pemesanan jasa foto produk, video, dan desain, silakan konsultasi langsung dengan admin kami melalui WhatsApp.',
                    'contact_url' => 'https://wa.me/6282142612382',
                ]);
            }
        }

        /** @var array<array{keywords: list<string>, reply: string}> $faqResponses */
        $faqResponses = [
            [
                'keywords' => ['status pesanan', 'status order', 'pesanan saya', 'cek status', 'lacak pesanan', 'lacak order', 'cek pesanan', 'tracking pesanan', 'nomor resi', 'no resi', 'resi'],
                'reply' => 'Status pesanan dan informasi resi bisa dilihat di menu Pesanan Saya setelah masuk ke akun.',
            ],
            [
                'keywords' => ['ongkir', 'ongkos kirim', 'ongkos pengiriman', 'biaya kirim', 'biaya pengiriman', 'tarif kirim', 'tarif ongkir', 'ekspedisi', 'kurir'],
                'reply' => 'Ongkir belum termasuk saat pesanan dibuat. Admin akan menghitung ongkir berdasarkan alamat tujuan, lalu total pembayaran tampil di detail pesanan sebelum kamu membayar.',
            ],
            [
                'keywords' => ['bukti pembayaran', 'bukti bayar', 'bukti transfer', 'upload bukti', 'unggah bukti', 'upload transfer', 'unggah transfer', 'cara bayar', 'cara pembayaran', 'pembayaran', 'transfer bank', 'transfer', 'nomor rekening', 'rekening', 'bayar pesanan', 'bayar'],
                'reply' => 'Setelah admin menetapkan ongkir, buka detail pesanan di menu Pesanan Saya. Ikuti informasi pembayaran yang tercantum di sana, lalu unggah bukti pembayaran sebelum batas waktunya.',
            ],
            [
                'keywords' => ['stok produk', 'stok habis', 'kehabisan stok', 'stok tersedia', 'hold stok', 'di-hold', 'dihold', 'di hold', 'ditahan', 'persediaan', 'stok'],
                'reply' => 'Stok ditahan selama 24 jam setelah admin menetapkan ongkir dan pesanan menunggu pembayaran. Stok fisik dikurangi setelah pembayaran dikonfirmasi admin.',
            ],
            [
                'keywords' => ['buat akun', 'bikin akun', 'daftar akun', 'daftar', 'registrasi', 'sign up', 'sign in', 'masuk akun', 'login', 'akun'],
                'reply' => 'Kamu perlu masuk atau membuat akun pelanggan untuk memesan. Akun juga dipakai untuk melihat pesanan, mengelola alamat, dan mengunggah bukti pembayaran.',
            ],
            [
                'keywords' => ['pesan produk', 'pesan barang', 'cara memesan', 'cara pesan', 'cara order', 'buat pesanan', 'pemesanan', 'checkout', 'beli produk', 'beli barang', 'cara beli', 'cara pembelian'],
                'reply' => 'Masuk ke akun, pilih produk dan alamat tujuan, lalu buat pesanan. Admin akan meninjau pesanan dan menetapkan ongkir. Setelah total pembayaran tampil, kamu bisa melanjutkan pembayaran dari detail pesanan.',
            ],
            [
                'keywords' => ['ukuran', 'warna', 'varian', 'bahan produk', 'material produk', 'motif', 'promo', 'promosi', 'diskon', 'detail produk', 'tanya produk', 'ketersediaan produk', 'produk tersedia'],
                'reply' => 'Untuk pertanyaan detail tentang ukuran, warna, promo, atau produk tertentu, silakan tanyakan langsung kepada admin melalui WhatsApp.',
            ],
        ];

        foreach ($faqResponses as $faqResponse) {
            foreach ($faqResponse['keywords'] as $keyword) {
                if ($this->containsKeyword($message, $keyword)) {
                    return response()->json([
                        'matched' => true,
                        'reply' => $faqResponse['reply'],
                    ]);
                }
            }
        }

        return response()->json([
            'matched' => false,
            'reply' => 'Maaf, aku belum menemukan jawaban yang sesuai. Untuk pertanyaan lebih lanjut, silakan hubungi admin melalui WhatsApp.',
            'contact_url' => 'https://wa.me/6282142612382',
        ]);
    }

    private function containsKeyword(string $message, string $keyword): bool
    {
        $pattern = '/(^|\s)'.preg_quote($keyword, '/').'(?=\s|$)/u';

        return preg_match($pattern, $message) === 1;
    }
}
