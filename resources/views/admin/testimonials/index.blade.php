@extends('layouts.admin')

@section('title', 'Testimoni - Sinergi Store')
@section('heading', 'Testimoni')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Daftar testimoni</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola cerita pelanggan yang akan ditampilkan di website.</p>
            </div>
            <button type="button" data-open-testimonial-modal class="relative z-10 touch-manipulation rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">
                + Tambah testimoni
            </button>
        </div>

        <form method="GET" action="{{ route('admin.testimonials.index') }}" class="grid gap-3 border-b border-slate-200 bg-slate-50 p-5 sm:grid-cols-2 lg:grid-cols-3">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama atau isi testimoni..." class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
            <select name="status" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                <option value="">Semua status</option>
                <option value="active" @selected(request('status') === 'active')>Ditampilkan</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Disembunyikan</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-700">Terapkan</button>
                <a href="{{ route('admin.testimonials.index') }}" class="rounded-xl px-5 py-3 text-sm font-medium text-slate-500 hover:bg-slate-200">Reset</a>
            </div>
        </form>

        @if ($testimonials->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-700">{{ request()->hasAny(['search', 'status']) ? 'Testimoni tidak ditemukan.' : 'Belum ada testimoni.' }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ request()->hasAny(['search', 'status']) ? 'Coba ubah kata kunci atau filter.' : 'Klik tombol tambah testimoni untuk memulai.' }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Foto</th>
                            <th class="px-6 py-4 font-semibold">Nama</th>
                            <th class="px-6 py-4 font-semibold">Testimoni</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($testimonials as $testimonial)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    @if ($testimonial->photo)
                                        <img src="{{ asset('storage/' . $testimonial->photo) }}" alt="Foto {{ $testimonial->name }}" class="h-12 w-12 rounded-full object-cover">
                                    @else
                                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-500">{{ mb_strtoupper(mb_substr($testimonial->name, 0, 1)) }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">{{ $testimonial->name }}</td>
                                <td class="max-w-xl px-6 py-4 text-slate-600">{{ Illuminate\Support\Str::limit($testimonial->message, 180) }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($testimonial->is_active)
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">Ditampilkan</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500">Disembunyikan</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a href="{{ route('admin.testimonials.index', ['edit' => $testimonial->id]) }}" class="mr-3 font-medium text-amber-600 hover:text-amber-700">Edit</a>
                                    <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" class="inline" onsubmit="return confirm('Testimoni ini akan dihapus permanen. Lanjutkan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 hover:text-red-700">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-6 py-4">{{ $testimonials->links() }}</div>
        @endif
    </div>

    <div data-testimonial-backdrop style="position:fixed;inset:0;z-index:9998;width:100vw;height:100vh;background:rgba(15,23,42,.42);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px)" class="hidden"></div>
    <aside data-testimonial-modal style="left:50%;top:50%;height:90vh;transform:translate(-50%,-45%);z-index:9999" class="pointer-events-none fixed flex max-h-[90vh] w-[calc(100%-2rem)] max-w-2xl flex-col overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl transition-all duration-300 ease-out">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-6 py-5">
            <div>
                <p class="text-sm font-medium text-amber-600">Cerita pelanggan</p>
                <h2 class="text-xl font-semibold text-slate-900">{{ $editingTestimonial ? 'Edit testimoni' : 'Tambah testimoni' }}</h2>
            </div>
            <button type="button" data-close-testimonial-modal aria-label="Tutup form" class="rounded-lg p-2 text-2xl leading-none text-slate-400 hover:bg-slate-100 hover:text-slate-700">&times;</button>
        </div>

        <div class="relative flex-1 overflow-y-auto p-6">
            <form id="testimonial-form" method="POST" action="{{ $editingTestimonial ? route('admin.testimonials.update', $editingTestimonial) : route('admin.testimonials.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @if ($editingTestimonial)
                    @method('PUT')
                @endif

                <div>
                    <label for="testimonial-name" class="mb-2 block text-sm font-medium text-slate-700">Nama</label>
                    <input id="testimonial-name" name="name" type="text" value="{{ old('name', $editingTestimonial?->name) }}" required maxlength="255" placeholder="Nama pelanggan" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                </div>

                <div>
                    <label for="testimonial-message" class="mb-2 block text-sm font-medium text-slate-700">Kata-kata / testimoni</label>
                    <textarea id="testimonial-message" name="message" rows="6" required maxlength="2000" placeholder="Tuliskan pengalaman pelanggan..." class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200">{{ old('message', $editingTestimonial?->message) }}</textarea>
                </div>

                <div>
                    <label for="testimonial-photo" class="mb-2 block text-sm font-medium text-slate-700">Foto <span class="font-normal text-slate-400">(opsional, maks. 2 MB)</span></label>
                    <div class="flex flex-wrap items-center gap-4">
                        <img data-testimonial-preview data-default-src="{{ $editingTestimonial?->photo ? asset('storage/' . $editingTestimonial->photo) : '' }}" src="{{ $editingTestimonial?->photo ? asset('storage/' . $editingTestimonial->photo) : '' }}" alt="Preview foto testimoni" class="{{ $editingTestimonial?->photo ? '' : 'hidden' }} h-20 w-20 rounded-full border border-slate-200 object-cover">
                        <span data-photo-placeholder class="{{ $editingTestimonial?->photo ? 'hidden' : '' }} flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-xs font-medium text-slate-400">Tanpa foto</span>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" data-photo-select class="rounded-lg bg-amber-100 px-4 py-2 text-sm font-medium text-amber-800 transition hover:bg-amber-200">{{ $editingTestimonial?->photo ? 'Ganti foto' : 'Pilih foto' }}</button>
                        <button type="button" data-photo-recrop class="{{ $editingTestimonial?->photo ? '' : 'hidden' }} rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Atur ulang crop</button>
                        <button type="button" data-photo-remove class="{{ $editingTestimonial?->photo ? '' : 'hidden' }} rounded-lg px-4 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50">Hapus foto</button>
                    </div>
                    <input id="testimonial-photo" name="photo" type="file" accept="image/*" data-testimonial-photo class="sr-only">
                    <input type="hidden" name="remove_photo" value="0" data-photo-remove-input>
                    <p data-photo-help class="mt-2 text-xs text-slate-500">{{ $editingTestimonial?->photo ? 'Foto saat ini digunakan. Pilih Ganti foto untuk menggantinya.' : 'Pilih foto pelanggan (opsional, maksimal 2 MB).' }}</p>
                </div>

                <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                    <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $editingTestimonial ? $editingTestimonial->is_active : true)) class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                    <span>Tampilkan di halaman publik</span>
                </label>
            </form>
        </div>

        <div class="flex shrink-0 gap-3 border-t border-slate-200 bg-white px-6 py-4">
            <button type="submit" form="testimonial-form" class="flex-1 rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">{{ $editingTestimonial ? 'Simpan perubahan' : 'Simpan testimoni' }}</button>
            @if ($editingTestimonial)
                <button type="button" data-close-testimonial-modal class="rounded-xl px-5 py-3 text-sm font-medium text-slate-500 hover:bg-slate-100">Batal</button>
            @endif
        </div>
    </aside>

    <div data-photo-cropper class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-950/70 p-4" role="dialog" aria-modal="true" aria-labelledby="photo-cropper-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="photo-cropper-title" class="text-lg font-semibold text-slate-900">Atur foto</h2>
                    <p class="mt-1 text-sm text-slate-500">Geser foto untuk mengatur posisi, lalu sesuaikan zoom.</p>
                </div>
                <button type="button" data-crop-cancel aria-label="Batal crop foto" class="rounded-lg p-1 text-2xl leading-none text-slate-400 hover:bg-slate-100 hover:text-slate-700">&times;</button>
            </div>

            <div data-crop-viewport class="relative mx-auto mt-5 h-72 w-72 max-w-full touch-none overflow-hidden rounded-2xl bg-slate-950">
                <canvas data-crop-canvas width="320" height="320" class="relative z-10 block h-full w-full"></canvas>
                <img data-crop-image alt="" class="hidden" draggable="false">
            </div>

            <div class="mt-5">
                <div class="mb-2 flex items-center justify-between text-sm font-medium text-slate-700">
                    <label for="testimonial-photo-zoom">Zoom</label>
                    <span data-crop-zoom-label>1.0x</span>
                </div>
                <input id="testimonial-photo-zoom" data-crop-zoom type="range" min="100" max="300" step="1" value="100" class="w-full accent-amber-500">
            </div>

            <div class="mt-6 flex gap-3">
                <button type="button" data-crop-cancel class="flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Batal</button>
                <button type="button" data-crop-confirm class="flex-1 rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">Gunakan foto</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const testimonialModal = document.querySelector('[data-testimonial-modal]');
        const testimonialBackdrop = document.querySelector('[data-testimonial-backdrop]');
        const testimonialsUrl = @json(route('admin.testimonials.index'));
        const hasEditingTestimonial = @json((bool) $editingTestimonial);
        const testimonialPhotoInput = document.querySelector('[data-testimonial-photo]');
        const testimonialPhotoPreview = document.querySelector('[data-testimonial-preview]');
        const photoPlaceholder = document.querySelector('[data-photo-placeholder]');
        const photoSelectButton = document.querySelector('[data-photo-select]');
        const photoRecropButton = document.querySelector('[data-photo-recrop]');
        const photoRemoveButton = document.querySelector('[data-photo-remove]');
        const photoRemoveInput = document.querySelector('[data-photo-remove-input]');
        const photoHelp = document.querySelector('[data-photo-help]');
        const photoCropper = document.querySelector('[data-photo-cropper]');
        const cropViewport = document.querySelector('[data-crop-viewport]');
        const cropCanvas = document.querySelector('[data-crop-canvas]');
        const cropContext = cropCanvas.getContext('2d');
        const cropImage = document.querySelector('[data-crop-image]');
        const cropZoom = document.querySelector('[data-crop-zoom]');
        const cropZoomLabel = document.querySelector('[data-crop-zoom-label]');
        let cropImageUrl = null;
        let cropPreviewUrl = null;
        let cropFileName = 'foto-testimoni.jpg';
        let acceptedCroppedFile = null;
        let cropScale = 1;
        let cropMinimumScale = 1;
        let cropLeft = 0;
        let cropTop = 0;
        let cropDragStart = null;

        const openTestimonialModal = () => {
            testimonialModal.style.transform = 'translate(-50%, -50%)';
            testimonialModal.classList.remove('pointer-events-none', 'opacity-0');
            testimonialBackdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        const closeTestimonialModal = () => {
            if (hasEditingTestimonial) {
                window.location.href = testimonialsUrl;
                return;
            }

            testimonialModal.style.transform = 'translate(-50%, -45%)';
            testimonialModal.classList.add('pointer-events-none', 'opacity-0');
            testimonialBackdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelector('[data-open-testimonial-modal]')?.addEventListener('click', openTestimonialModal);
        document.querySelectorAll('[data-close-testimonial-modal]').forEach((button) => {
            button.addEventListener('click', closeTestimonialModal);
        });
        testimonialBackdrop?.addEventListener('click', closeTestimonialModal);

        const updatePhotoControls = () => {
            const hasPhoto = !testimonialPhotoPreview.classList.contains('hidden');
            photoPlaceholder.classList.toggle('hidden', hasPhoto);
            photoSelectButton.textContent = hasPhoto ? 'Ganti foto' : 'Pilih foto';
            photoRecropButton.classList.toggle('hidden', !hasPhoto);
            photoRemoveButton.classList.toggle('hidden', !hasPhoto);
        };

        photoSelectButton.addEventListener('click', () => testimonialPhotoInput.click());

        photoRecropButton.addEventListener('click', () => {
            if (acceptedCroppedFile) {
                startPhotoCropper(acceptedCroppedFile);
                return;
            }

            if (testimonialPhotoPreview.dataset.defaultSrc) {
                startPhotoCropper(testimonialPhotoPreview.dataset.defaultSrc);
            }
        });

        photoRemoveButton.addEventListener('click', () => {
            testimonialPhotoInput.value = '';
            testimonialPhotoInput.files = new DataTransfer().files;
            photoRemoveInput.value = testimonialPhotoPreview.dataset.defaultSrc ? '1' : '0';
            acceptedCroppedFile = null;

            if (cropPreviewUrl) {
                URL.revokeObjectURL(cropPreviewUrl);
                cropPreviewUrl = null;
            }

            testimonialPhotoPreview.removeAttribute('src');
            testimonialPhotoPreview.classList.add('hidden');
            photoHelp.textContent = photoRemoveInput.value === '1'
                ? 'Foto ini akan dihapus setelah perubahan disimpan.'
                : 'Foto dihapus dari formulir. Kamu bisa memilih foto lain sebelum menyimpan.';
            updatePhotoControls();
        });

        const constrainCropPosition = () => {
            const viewportWidth = cropViewport.clientWidth;
            const viewportHeight = cropViewport.clientHeight;

            if (viewportWidth === 0 || viewportHeight === 0 || cropScale <= 0 || !Number.isFinite(cropScale)) {
                return;
            }

            const imageWidth = cropImage.naturalWidth * cropScale;
            const imageHeight = cropImage.naturalHeight * cropScale;

            cropLeft = Math.min(0, Math.max(viewportWidth - imageWidth, cropLeft));
            cropTop = Math.min(0, Math.max(viewportHeight - imageHeight, cropTop));

            if (cropImage.complete && cropImage.naturalWidth > 0) {
                const sourceX = -cropLeft / cropScale;
                const sourceY = -cropTop / cropScale;
                const sourceSize = Math.min(viewportWidth / cropScale, viewportHeight / cropScale);

                cropContext.clearRect(0, 0, cropCanvas.width, cropCanvas.height);
                cropContext.drawImage(cropImage, sourceX, sourceY, sourceSize, sourceSize, 0, 0, cropCanvas.width, cropCanvas.height);
            }
        };

        const closePhotoCropper = (clearSelection = true) => {
            photoCropper.classList.add('hidden');
            photoCropper.classList.remove('flex');
            cropDragStart = null;

            if (cropImageUrl) {
                URL.revokeObjectURL(cropImageUrl);
                cropImageUrl = null;
            }

            cropImage.removeAttribute('src');

            if (clearSelection) {
                const transfer = new DataTransfer();
                if (acceptedCroppedFile) {
                    transfer.items.add(acceptedCroppedFile);
                }
                testimonialPhotoInput.files = transfer.files;

                const defaultPhoto = acceptedCroppedFile ? cropPreviewUrl : testimonialPhotoPreview.dataset.defaultSrc;
                if (defaultPhoto) {
                    testimonialPhotoPreview.src = defaultPhoto;
                    testimonialPhotoPreview.classList.remove('hidden');
                } else {
                    testimonialPhotoPreview.removeAttribute('src');
                    testimonialPhotoPreview.classList.add('hidden');
                }

                updatePhotoControls();
            }
        };

        function startPhotoCropper(source) {
            if (cropImageUrl) {
                URL.revokeObjectURL(cropImageUrl);
                cropImageUrl = null;
            }

            const isFile = source instanceof File;
            cropFileName = isFile ? source.name : 'foto-testimoni.jpg';
            const imageSource = isFile ? URL.createObjectURL(source) : source;
            cropImageUrl = isFile ? imageSource : null;

            cropImage.onload = () => {
                photoCropper.classList.remove('hidden');
                photoCropper.classList.add('flex');

                window.requestAnimationFrame(() => {
                    const viewportWidth = cropViewport.clientWidth;
                    const viewportHeight = cropViewport.clientHeight;

                    if (viewportWidth === 0 || viewportHeight === 0 || cropImage.naturalWidth === 0 || cropImage.naturalHeight === 0) {
                        closePhotoCropper();
                        return;
                    }

                    cropMinimumScale = Math.max(viewportWidth / cropImage.naturalWidth, viewportHeight / cropImage.naturalHeight);
                    cropScale = cropMinimumScale;
                    cropLeft = (viewportWidth - cropImage.naturalWidth * cropScale) / 2;
                    cropTop = (viewportHeight - cropImage.naturalHeight * cropScale) / 2;
                    cropZoom.value = '100';
                    cropZoomLabel.textContent = '1.0x';
                    constrainCropPosition();
                    document.querySelector('[data-crop-cancel]')?.focus();
                });
            };

            cropImage.onerror = () => {
                closePhotoCropper();
                window.alert('Foto gagal dibuka. Coba pilih file foto lain.');
            };

            cropImage.src = imageSource;
        }

        testimonialPhotoInput?.addEventListener('change', () => {
            const file = testimonialPhotoInput.files?.[0];

            if (!file) {
                return;
            }

            if (!file.type.startsWith('image/')) {
                testimonialPhotoInput.value = '';
                window.alert('Pilih file gambar seperti JPG, PNG, atau WebP.');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                testimonialPhotoInput.value = '';
                window.alert('Ukuran foto maksimal 2 MB.');
                return;
            }

            photoRemoveInput.value = '0';
            photoHelp.textContent = 'Geser dan zoom foto, lalu tekan Gunakan foto untuk menyimpan hasil crop.';
            startPhotoCropper(file);
        });

        cropViewport.addEventListener('pointerdown', (event) => {
            cropDragStart = { x: event.clientX, y: event.clientY, left: cropLeft, top: cropTop };
            cropViewport.setPointerCapture(event.pointerId);
        });

        cropViewport.addEventListener('pointermove', (event) => {
            if (!cropDragStart) {
                return;
            }

            cropLeft = cropDragStart.left + event.clientX - cropDragStart.x;
            cropTop = cropDragStart.top + event.clientY - cropDragStart.y;
            constrainCropPosition();
        });

        cropViewport.addEventListener('pointerup', () => {
            cropDragStart = null;
        });
        cropViewport.addEventListener('pointercancel', () => {
            cropDragStart = null;
        });

        cropZoom.addEventListener('input', () => {
            const viewportWidth = cropViewport.clientWidth;
            const viewportHeight = cropViewport.clientHeight;
            const sourceCenterX = (viewportWidth / 2 - cropLeft) / cropScale;
            const sourceCenterY = (viewportHeight / 2 - cropTop) / cropScale;
            const zoomFactor = Number(cropZoom.value) / 100;

            cropScale = cropMinimumScale * zoomFactor;
            cropLeft = viewportWidth / 2 - sourceCenterX * cropScale;
            cropTop = viewportHeight / 2 - sourceCenterY * cropScale;
            cropZoomLabel.textContent = `${zoomFactor.toFixed(1)}x`;
            constrainCropPosition();
        });

        document.querySelectorAll('[data-crop-cancel]').forEach((button) => {
            button.addEventListener('click', () => closePhotoCropper());
        });

        photoCropper.addEventListener('click', (event) => {
            if (event.target === photoCropper) {
                closePhotoCropper();
            }
        });

        photoCropper.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePhotoCropper();
            }
        });

        document.querySelector('[data-crop-confirm]').addEventListener('click', () => {
            const outputSize = 512;
            const viewportWidth = cropViewport.clientWidth;
            const viewportHeight = cropViewport.clientHeight;
            const sourceX = Math.max(0, -cropLeft / cropScale);
            const sourceY = Math.max(0, -cropTop / cropScale);
            const sourceSize = Math.min(viewportWidth / cropScale, viewportHeight / cropScale);
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            canvas.width = outputSize;
            canvas.height = outputSize;
            context.drawImage(cropImage, sourceX, sourceY, sourceSize, sourceSize, 0, 0, outputSize, outputSize);
            canvas.toBlob((blob) => {
                if (!blob) {
                    return;
                }

                const croppedFile = new File([blob], `${cropFileName.replace(/\.[^.]+$/, '')}-crop.jpg`, {
                    type: 'image/jpeg',
                    lastModified: Date.now(),
                });
                const transfer = new DataTransfer();
                transfer.items.add(croppedFile);
                testimonialPhotoInput.files = transfer.files;

                if (cropPreviewUrl) {
                    URL.revokeObjectURL(cropPreviewUrl);
                }

                cropPreviewUrl = URL.createObjectURL(croppedFile);
                acceptedCroppedFile = croppedFile;
                testimonialPhotoPreview.src = cropPreviewUrl;
                testimonialPhotoPreview.classList.remove('hidden');
                photoRemoveInput.value = '0';
                photoHelp.textContent = 'Foto siap digunakan. Kamu masih bisa mengatur ulang crop atau menggantinya.';
                updatePhotoControls();
                closePhotoCropper(false);
            }, 'image/jpeg', 0.9);
        });

        @if ($editingTestimonial || $errors->any())
            openTestimonialModal();
        @endif
    </script>
@endpush
