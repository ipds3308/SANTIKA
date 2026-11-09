<div id="admin-queue-table" class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive mb-0" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="table table-hover align-middle border mb-0" style="min-width: 850px;">
            <thead class="table-dark" style="background-color: #002060;">
                <tr>
                    <th class="py-3 px-3">No. Antrian</th>
                    <th class="py-3">Nama Pemohon</th>
                    <th class="py-3">Jenis Layanan</th>
                    <th class="py-3">Info Kontak & Alamat</th>
                    <th class="py-3 text-center">Status Layanan</th>
                    <th class="py-2 text-center" style="width: 160px;">Aksi Petugas</th>
                </tr>
            </thead>
            <tbody id="tabel-pendaftar-body">
                <?php $__empty_1 = true; $__currentLoopData = $pendaftar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="fw-bold text-primary px-3" style="font-size: 1.1rem;"><?php echo e($data->nomor_antrian); ?></td>
                        <td class="fw-bold"><?php echo e($data->nama); ?></td>
                        <td>
                            <?php
                                $serviceMeta = \App\Models\Registration::serviceBadgeMeta($data->jenis_layanan);
                            ?>
                            <span class="badge px-2 py-1" style="<?php echo e($serviceMeta['inlineStyle']); ?>; font-size: 0.8rem; font-weight: 600;">
                                <?php if(str_starts_with((string) $data->jenis_layanan, 'Lainnya:')): ?>
                                    Lainnya:<?php echo e(Str::after($data->jenis_layanan, 'Lainnya:')); ?>

                                <?php else: ?>
                                    <?php echo e($data->jenis_layanan ?? '-'); ?>

                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div><i class="fa fa-envelope text-muted me-1" style="width: 15px;"></i> <?php echo e($data->email); ?></div>
                            <?php
                                $phoneWa = preg_match('/^08/', $data->no_wa) ? '62' . substr($data->no_wa, 1) : $data->no_wa;
                            ?>
                            <div class="mt-1">
                                <a href="https://wa.me/<?php echo e($phoneWa); ?>" target="_blank" class="text-decoration-none text-dark fw-semibold" title="Chat WhatsApp">
                                    <i class="fa-brands fa-whatsapp text-success me-1" style="width: 15px; font-size: 1rem;"></i>
                                    <?php echo e($data->no_wa); ?>

                                    <i class="fa fa-external-link-alt fa-xs text-muted ms-1"></i>
                                </a>
                            </div>
                            <div class="small text-muted mt-1 border-top pt-1"><i class="fa fa-map-marker-alt me-1" style="width: 15px;"></i> <?php echo e($data->alamat ?? 'Alamat belum diisi'); ?></div>
                        </td>
                        <td class="text-center">
                            <?php if($data->status == 'Menunggu'): ?>
                                <span class="badge bg-warning text-dark px-3 py-2">Menunggu</span>
                            <?php elseif($data->status == 'Dipanggil'): ?>
                                <span class="badge bg-info text-white px-3 py-2">Dipanggil</span>
                            <?php elseif($data->status == 'Tidak Hadir'): ?>
                                <span class="badge bg-secondary px-3 py-2">Tidak Hadir</span>
                            <?php else: ?>
                                <span class="badge bg-success px-3 py-2">Selesai</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center px-1 py-2 align-middle" style="width: 160px; white-space: nowrap;">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <?php if($data->status === 'Selesai'): ?>
                                    <button type="button" class="btn btn-secondary btn-sm fw-bold px-2 py-1" disabled title="Antrian selesai dan terkunci">
                                        <i class="fa fa-lock me-1"></i> Terkunci
                                    </button>
                                <?php else: ?>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-primary btn-sm fw-bold px-2 py-1 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.78rem; background-color: #0d6efd; border-color: #0d6efd;">
                                            <i class="fa fa-chevron-down me-1"></i> Ubah Status
                                        </button>
                                        <ul class="dropdown-menu shadow-lg border py-1 bg-white" style="min-width: 170px;">
                                            <?php if($data->status === 'Tidak Hadir'): ?>
                                                <li>
                                                    <button type="button" class="dropdown-item px-3 py-2 text-muted" disabled>
                                                        <i class="fa fa-bullhorn me-2"></i> Panggil (Terkunci)
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item px-3 py-2 text-muted" disabled>
                                                        <i class="fa fa-check me-2"></i> Selesai (Terkunci)
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item px-3 py-2 text-warning fw-bold" data-status-action="true" data-id="<?php echo e($data->id); ?>" data-status="Menunggu">
                                                        <i class="fa fa-undo me-2"></i> Jadikan Menunggu Lagi
                                                    </button>
                                                </li>
                                            <?php else: ?>
                                                <li>
                                                    <button type="button" class="dropdown-item px-3 py-2" data-status-action="true" data-id="<?php echo e($data->id); ?>" data-status="Dipanggil" data-nomor-antrian="<?php echo e($data->nomor_antrian); ?>">
                                                        <i class="fa fa-bullhorn me-2 text-info"></i> Panggil
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item px-3 py-2" data-status-action="true" data-id="<?php echo e($data->id); ?>" data-status="Selesai" data-nomor-antrian="<?php echo e($data->nomor_antrian); ?>" data-nama="<?php echo e($data->nama); ?>">
                                                        <i class="fa fa-check me-2 text-success"></i> Selesai
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item px-3 py-2" data-status-action="true" data-id="<?php echo e($data->id); ?>" data-status="Tidak Hadir">
                                                        <i class="fa fa-user-slash me-2 text-secondary"></i> Tidak Hadir
                                                    </button>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                <?php if(in_array(strtolower((string) auth()->user()->role), ['admin', 'cs'], true)): ?>
                                    <button type="button" class="btn btn-outline-warning btn-sm fw-bold px-2 py-1" style="font-size: 0.78rem;" onclick="bukaModalEdit(<?php echo e(json_encode(['id' => $data->id, 'nama' => $data->nama, 'email' => $data->email, 'no_wa' => $data->no_wa, 'alamat' => $data->alamat, 'jenis_layanan' => $data->jenis_layanan, 'tanggal_kunjungan' => $data->tanggal_kunjungan, 'nomor_antrian' => $data->nomor_antrian, 'status' => $data->status], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)); ?>)">
                                        <i class="fa fa-pen me-1"></i> Edit
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fa fa-info-circle me-1"></i> Tidak ada data antrian untuk filter yang dipilih.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <?php if($perPage === 'all'): ?>
            <small class="text-muted">Menampilkan seluruh <?php echo e($pendaftar->count()); ?> data.</small>
        <?php else: ?>
            <small class="text-muted">
                Menampilkan <?php echo e($pendaftar->firstItem() ?? 0); ?>–<?php echo e($pendaftar->lastItem() ?? 0); ?> dari <?php echo e($pendaftar->total()); ?> data.
            </small>
            <?php if($pendaftar->hasPages()): ?>
                <?php echo e($pendaftar->links('pagination::bootstrap-5')); ?>

            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH D:\Kuliah\Magang\SANTIKA\resources\views/pages/partials/admin-queue-table.blade.php ENDPATH**/ ?>