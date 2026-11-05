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

        /** @var array<array{keywords: list<string>, reply: string}> $faqResponses */
        $faqResponses = [
            [
                'keywords' => ['status pesanan', 'pesanan saya', 'lacak pesanan', 'cek pesanan', 'nomor resi', 'resi'],
                'reply' => 'Status pesanan dan informasi resi bisa dilihat di menu Pesanan Saya setelah masuk ke akun.',
            ],
            [
                'keywords' => ['ongkir', 'ongkos kirim', 'biaya kirim', 'ekspedisi', 'kurir'],
                'reply' => 'Ongkir belum termasuk saat pesanan dibuat. Admin akan menghitung ongkir berdasarkan alamat tujuan, lalu total pembayaran tampil di detail pesanan sebelum kamu membayar.',
            ],
            [
                'keywords' => ['bukti pembayaran', 'bukti transfer', 'cara bayar', 'pembayaran', 'transfer', 'bayar'],
                'reply' => 'Setelah admin menetapkan ongkir, buka detail pesanan di menu Pesanan Saya. Ikuti informasi pembayaran yang tercantum di sana, lalu unggah bukti pembayaran sebelum batas waktunya.',
            ],
            [
                'keywords' => ['stok', 'di hold', 'ditahan', 'persediaan'],
                'reply' => 'Stok ditahan selama 24 jam setelah admin menetapkan ongkir dan pesanan menunggu pembayaran. Stok fisik dikurangi setelah pembayaran dikonfirmasi admin.',
            ],
            [
                'keywords' => ['buat akun', 'daftar', 'registrasi', 'login', 'masuk akun', 'akun'],
                'reply' => 'Kamu perlu masuk atau membuat akun pelanggan untuk memesan. Akun juga dipakai untuk melihat pesanan, mengelola alamat, dan mengunggah bukti pembayaran.',
            ],
            [
                'keywords' => ['pesan produk', 'cara memesan', 'cara pesan', 'buat pesanan', 'pemesanan', 'checkout', 'beli produk'],
                'reply' => 'Masuk ke akun, pilih produk dan alamat tujuan, lalu buat pesanan. Admin akan meninjau pesanan dan menetapkan ongkir. Setelah total pembayaran tampil, kamu bisa melanjutkan pembayaran dari detail pesanan.',
            ],
            [
                'keywords' => ['ukuran', 'warna', 'varian', 'promo', 'diskon', 'detail produk', 'ketersediaan produk'],
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
