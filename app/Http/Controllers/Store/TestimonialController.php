<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'message.required' => 'Pesan testimoni wajib diisi.',
            'message.max' => 'Pesan testimoni maksimal 2.000 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->to(route('store.home') . '#testimonial-form')
                ->withErrors($validator)
                ->withInput();
        }

        Testimonial::create([
            ...$validator->validated(),
            'is_active' => false,
            'is_approved' => false,
        ]);

        return redirect()
            ->to(route('store.home') . '#testimonial-form')
            ->with('testimonial_success', 'Terima kasih! Testimoni Anda sudah dikirim dan menunggu persetujuan admin.');
    }
}
