<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TestimoniController extends Controller
{
    public function index(Request $request): View
    {
        $query = Testimonial::query();

        $query->when($request->filled('search'), function ($query) use ($request) {
            $search = (string) $request->string('search')->trim();
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        });

        $query->when($request->status === 'active', fn ($query) => $query->where('is_active', true));
        $query->when($request->status === 'inactive', fn ($query) => $query
            ->where('is_active', false)
            ->where('is_approved', true));
        $query->when($request->status === 'pending', fn ($query) => $query
            ->where('is_active', false)
            ->where('is_approved', false));

        $testimonials = $query->latest()->paginate(10)->withQueryString();
        $editingTestimonial = $request->filled('edit')
            ? Testimonial::findOrFail($request->integer('edit'))
            : null;

        return view('admin.testimonials.index', compact('testimonials', 'editingTestimonial'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_approved'] = true;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('testimonials', 'public');
        }

        Testimonial::create($validated);

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_approved'] = true;
        $oldPhoto = $testimonial->photo;
        $photoWasChanged = false;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('testimonials', 'public');
            $photoWasChanged = true;
        } elseif ($request->boolean('remove_photo')) {
            $validated['photo'] = null;
            $photoWasChanged = true;
        }

        unset($validated['remove_photo']);
        $testimonial->update($validated);

        if ($photoWasChanged && $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimoni berhasil diperbarui.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        if ($testimonial->photo) {
            Storage::disk('public')->delete($testimonial->photo);
        }

        $testimonial->delete();

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimoni berhasil dihapus.');
    }
}
