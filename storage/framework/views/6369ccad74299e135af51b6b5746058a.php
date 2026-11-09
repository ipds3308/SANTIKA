<div id="admin-reviews-fragment">
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

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold m-0 text-dark">Daftar Penilaian & Komentar Terbaru</h6>
        </div>
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
                                <td class="text-center"><?php echo e($perPage === 'all' ? $index + 1 : $reviews->firstItem() + $index); ?></td>
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
        <div class="card-footer bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <?php if($perPage === 'all'): ?>
                <small class="text-muted">Menampilkan seluruh <?php echo e($reviews->count()); ?> ulasan.</small>
            <?php else: ?>
                <small class="text-muted">
                    Menampilkan <?php echo e($reviews->firstItem() ?? 0); ?>–<?php echo e($reviews->lastItem() ?? 0); ?> dari <?php echo e($reviews->total()); ?> ulasan.
                </small>
                <?php if($reviews->hasPages()): ?>
                    <?php echo e($reviews->links('pagination::bootstrap-5')); ?>

                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH D:\Kuliah\Magang\SANTIKA\resources\views/pages/partials/admin-reviews.blade.php ENDPATH**/ ?>