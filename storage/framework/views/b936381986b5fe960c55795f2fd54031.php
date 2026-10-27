<?php $__env->startSection('title', 'Pengelolaan Berita BPS - SANTIKA'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #002060;"><i class="fa fa-newspaper me-2 text-warning"></i>Pengelolaan Berita BPS</h4>
        <p class="text-muted small mb-0">Maksimal tiga berita terbaru ditampilkan pada halaman pendaftaran.</p>
    </div>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-outline-primary btn-sm fw-bold">
        <i class="fa fa-arrow-left me-1"></i> Kembali ke Panel Monitoring
    </a>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success border-0 shadow-sm">
        <i class="fa fa-check-circle me-2"></i><?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0 ps-3">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom border-2 border-warning py-3">
        <h5 class="fw-bold mb-0" style="color: #002060;"><i class="fa fa-plus-circle me-2"></i>Tambah Berita Baru</h5>
    </div>
    <div class="card-body">
        <form action="<?php echo e(route('admin.news.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="row g-3">
                <div class="col-lg-4">
                    <label for="title" class="form-label fw-semibold">Judul Berita</label>
                    <input type="text" name="title" id="title" value="<?php echo e(old('title')); ?>" class="form-control" required maxlength="255">
                </div>
                <div class="col-lg-4">
                    <label for="url" class="form-label fw-semibold">Link Berita / Artikel</label>
                    <input type="url" name="url" id="url" value="<?php echo e(old('url')); ?>" class="form-control" placeholder="https://" required maxlength="2048">
                </div>
                <div class="col-lg-4">
                    <label for="image_url" class="form-label fw-semibold">Link Gambar / Image URL</label>
                    <input type="url" name="image_url" id="image_url" value="<?php echo e(old('image_url')); ?>" class="form-control" placeholder="https://" required maxlength="2048">
                </div>
            </div>
            <button type="submit" class="btn btn-primary fw-bold mt-3">
                <i class="fa fa-plus me-1"></i> Tambah Berita
            </button>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0" style="color: #002060;">Berita Aktif</h5>
    <span class="badge bg-primary"><?php echo e($berita->count()); ?> / 3 berita</span>
</div>

<div class="row g-4">
    <?php $__empty_1 = true; $__currentLoopData = $berita; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-6 col-xl-4">
            <article class="card h-100 border-0 shadow-sm overflow-hidden news-card" data-news-id="<?php echo e($item->id); ?>">
                <img src="<?php echo e($item->image_url); ?>" alt="<?php echo e($item->title); ?>" class="card-img-top" style="height: 180px; object-fit: cover;" onerror="this.src='https://placehold.co/640x360/e2e8f0/475569?text=BPS';">
                <div class="card-body d-flex flex-column">
                    <h6 class="fw-bold text-dark news-card-title"><?php echo e($item->title); ?></h6>
                    <a href="<?php echo e($item->url); ?>" target="_blank" rel="noopener noreferrer" class="small text-primary text-break mb-3 news-card-url"><?php echo e($item->url); ?></a>
                    <small class="text-muted mt-auto"><i class="fa fa-clock me-1"></i>Ditambahkan <?php echo e($item->created_at->format('d/m/Y H:i')); ?></small>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2 align-self-start edit-news-button" data-news-id="<?php echo e($item->id); ?>" data-title="<?php echo e($item->title); ?>" data-url="<?php echo e($item->url); ?>" data-image-url="<?php echo e($item->image_url); ?>">
                        <i class="fa fa-pen me-1"></i> Edit Berita
                    </button>
                </div>
            </article>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="alert alert-light border text-muted">Belum ada berita aktif.</div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="editNewsModal" tabindex="-1" aria-labelledby="editNewsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="editNewsModalLabel"><i class="fa fa-pen me-2"></i>Edit Berita</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="editNewsForm">
                <div class="modal-body">
                    <div id="editNewsError" class="alert alert-danger d-none"></div>
                    <input type="hidden" id="editNewsId">
                    <div class="mb-3">
                        <label for="editNewsTitle" class="form-label fw-semibold">Judul Berita</label>
                        <input type="text" id="editNewsTitle" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="editNewsUrl" class="form-label fw-semibold">Link Berita / Artikel</label>
                        <input type="url" id="editNewsUrl" class="form-control" required maxlength="2048">
                    </div>
                    <div>
                        <label for="editNewsImage" class="form-label fw-semibold">Link Gambar / Image URL</label>
                        <input type="url" id="editNewsImage" class="form-control" required maxlength="2048">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="saveNewsChanges"><i class="fa fa-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="newsToast" class="toast position-fixed bottom-0 end-0 m-4" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header bg-success text-white"><i class="fa fa-check-circle me-2"></i><strong class="me-auto">Berhasil</strong><button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button></div>
    <div class="toast-body">Berita berhasil diperbarui.</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editNewsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editNewsModal'));
        const editNewsForm = document.getElementById('editNewsForm');
        const editNewsError = document.getElementById('editNewsError');
        const editNewsId = document.getElementById('editNewsId');

        function clearEditNewsState() {
            editNewsId.value = '';
            editNewsForm.reset();
            editNewsError.textContent = '';
            editNewsError.classList.add('d-none');
        }

        document.getElementById('editNewsModal').addEventListener('hidden.bs.modal', clearEditNewsState);

        document.querySelectorAll('.edit-news-button').forEach(button => {
            button.addEventListener('click', function () {
                editNewsId.value = this.dataset.newsId;
                document.getElementById('editNewsTitle').value = this.dataset.title;
                document.getElementById('editNewsUrl').value = this.dataset.url;
                document.getElementById('editNewsImage').value = this.dataset.imageUrl;
                editNewsError.classList.add('d-none');
                editNewsModal.show();
            });
        });

        editNewsForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const button = document.getElementById('saveNewsChanges');
            const id = document.getElementById('editNewsId').value;
            button.disabled = true;

            fetch(`/admin/berita/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    title: document.getElementById('editNewsTitle').value,
                    url: document.getElementById('editNewsUrl').value,
                    image_url: document.getElementById('editNewsImage').value
                })
            })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(result => {
                    if (result.status >= 200 && result.status < 300 && result.body.success) {
                        const item = result.body.data;
                        const card = document.querySelector(`.news-card[data-news-id="${item.id}"]`);
                        card.querySelector('img').src = item.image_url;
                        card.querySelector('.news-card-title').innerText = item.title;
                        card.querySelector('.news-card-url').innerText = item.url;
                        card.querySelector('.news-card-url').href = item.url;
                        const editButton = card.querySelector('.edit-news-button');
                        editButton.dataset.title = item.title;
                        editButton.dataset.url = item.url;
                        editButton.dataset.imageUrl = item.image_url;
                        editNewsModal.hide();
                        bootstrap.Toast.getOrCreateInstance(document.getElementById('newsToast')).show();
                        return;
                    }
                    throw new Error(result.body.message || 'Gagal memperbarui berita.');
                })
                .catch(error => {
                    editNewsError.innerText = error.message;
                    editNewsError.classList.remove('d-none');
                })
                .finally(() => { button.disabled = false; });
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Kuliah\Magang\SANTIKA\resources\views\pages\news.blade.php ENDPATH**/ ?>