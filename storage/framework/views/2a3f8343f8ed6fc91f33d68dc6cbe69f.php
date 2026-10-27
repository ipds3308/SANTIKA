

<?php $__env->startSection('title', 'Rekap Ulasan & Saran - SANTIKA BPS'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- Header Bagian Atas -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="fa fa-star text-warning me-2"></i> Rekap Ulasan & Saran Pengguna</h4>
            <p class="text-muted small mb-0">Kelola dan pantau penilaian kepuasan layanan dari para pemohon antrian.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.reviews.exportPdf', ['bulan' => $bulan ?? ''])); ?>" target="_blank" class="btn btn-danger btn-sm fw-bold flex-fill flex-md-grow-0">
                <i class="fa fa-file-pdf me-1"></i> Export PDF
            </a>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-outline-secondary btn-sm fw-bold flex-fill flex-md-grow-0">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Form Filter Bulanan -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body py-3">
            <form action="<?php echo e(route('admin.reviews')); ?>" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label for="bulan" class="fw-bold text-secondary small mb-1"><i class="fa fa-calendar-week me-1"></i> Filter Bulan:</label>
                    <input type="month" id="bulan" name="bulan" value="<?php echo e($bulan ?? ''); ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-auto col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-4" style="background-color: #002060; border-color: #002060;">
                        <i class="fa fa-filter me-1"></i> Terapkan
                    </button>
                    <a href="<?php echo e(route('admin.reviews')); ?>" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="fa fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Kotak Ringkasan Statistik Rating (Responsif: Jejer di HP & Web, tapi bentuk berubah otomatis) -->
    <div id="ulasan-stats-container" class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 border-start border-3 border-warning h-100">
                <div class="d-flex flex-column flex-sm-row align-items-center text-center text-sm-start px-md-2">
                    <div class="fs-1 text-warning mb-2 mb-sm-0 me-sm-3">⭐</div>
                    <div>
                        <span class="text-muted small d-block">Rata-rata Rating</span>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo e(number_format($averageRating ?? 0, 1)); ?> <span class="fs-6 text-muted fw-normal">/ 5.0</span></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 border-start border-3 border-primary h-100">
                <div class="d-flex flex-column flex-sm-row align-items-center text-center text-sm-start px-md-2">
                    <div class="fs-1 text-primary mb-2 mb-sm-0 me-sm-3">💬</div>
                    <div>
                        <span class="text-muted small d-block">Total Ulasan Masuk</span>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo e($totalReview); ?> <span class="fs-6 text-muted fw-normal">Responden</span></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Ulasan & Pagination dibungkus satu kontainer real-time -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold m-0 text-dark">Daftar Penilaian & Komentar Terbaru</h6>
        </div>
        <div id="ulasan-realtime-container">
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table class="table table-hover align-middle mb-0" style="min-width: 700px;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">NO</th>
                                <th class="text-center" style="width: 130px;">TANGGAL WAKTU</th>
                                <th style="width: 150px;">NAMA PEMOHON</th>
                                <th class="text-center" style="width: 110px;">NO. ANTRIAN</th>
                                <th class="text-center" style="width: 130px;">RATING</th>
                                <th style="width: 200px;">KRITIK & SARAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="text-center"><?php echo e($reviews->firstItem() + $index); ?></td>
                                    <td class="text-center text-muted small"><?php echo e($review->created_at->format('d-m-Y H:i')); ?></td>
                                    <td class="fw-semibold text-dark"><?php echo e($review->registration->nama ?? 'Pemohon Umum'); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1"><?php echo e($review->registration->nomor_antrian ?? '-'); ?></span>
                                    </td>
                                    <td class="text-center text-warning">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if($i <= $review->rating): ?>
                                                ★
                                            <?php else: ?>
                                                <span class="text-muted" style="opacity: 0.3;">★</span>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <span class="text-secondary small ms-1">(<?php echo e($review->rating); ?>/5)</span>
                                    </td>
                                    <td><?php echo e($review->kritik_saran ?: '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada ulasan atau penilaian yang masuk.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if($reviews->hasPages()): ?>
                <div class="card-footer bg-white py-3">
                    <?php echo e($reviews->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bulanInput = document.getElementById('bulan');
        if (bulanInput) {
            bulanInput.addEventListener('click', function () {
                if (typeof this.showPicker === 'function') {
                    try {
                        this.showPicker();
                    } catch (e) {
                        // Diamkan error jika showPicker dipanggil terlalu cepat/berulang
                    }
                }
            });
        }
    });

    function autoRefreshReviews() {
        if (window.getSelection().toString().length > 0) {
            return;
        }

        let currentUrl = window.location.href;

        fetch(currentUrl)
            .then(response => response.text())
            .then(html => {
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');
                
                let newContainer = doc.querySelector('#ulasan-realtime-container');
                let currentContainer = document.querySelector('#ulasan-realtime-container');
                
                let newStats = doc.querySelector('#ulasan-stats-container');
                let currentStats = document.querySelector('#ulasan-stats-container');

                if (newContainer && currentContainer) {
                    currentContainer.innerHTML = newContainer.innerHTML;
                }

                if (newStats && currentStats) {
                    currentStats.innerHTML = newStats.innerHTML;
                }
            })
            .catch(error => console.error('Gagal memperbarui ulasan secara real-time:', error));
    }

    // Perbarui data ulasan otomatis setiap 5 detik
    setInterval(autoRefreshReviews, 5000);
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Kuliah\Magang\SANTIKA\resources\views\pages\admin_reviews.blade.php ENDPATH**/ ?>