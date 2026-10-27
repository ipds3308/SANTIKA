@extends('layouts.main')

@section('title', 'Manajemen Pengguna - SANTIKA BPS')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom border-2 border-primary d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="fw-bold m-0" style="color: #002060;"><i class="fa fa-users-cog me-2"></i> Manajemen Akun Petugas & Admin</h5>
            <p class="text-muted small m-0 mt-1">Kelola hak akses akun login untuk Admin dan Petugas CS.</p>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
            <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahUser" style="background-color: #002060;">
                <i class="fa fa-user-plus me-1"></i> Tambah Akun Baru
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- Notifikasi -->
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="fa fa-exclamation-triangle me-2"></i> {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tabel Daftar User -->
        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-dark" style="background-color: #002060;">
                    <tr>
                        <th class="py-3 px-3">No</th>
                        <th class="py-3">Nama Lengkap</th>
                        <th class="py-3">Email (Login)</th>
                        <th class="py-3 text-center">Role / Hak Akses</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $u)
                        <tr>
                            <td class="px-3">{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $u->name }} @if($u->id == auth()->id()) <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">(Akun Anda)</span> @endif</td>
                            <td>{{ $u->email }}</td>
                            <td class="text-center">
                                @if($u->role == 'admin')
                                    <span class="badge bg-danger px-3 py-2">Administrator</span>
                                @else
                                    <span class="badge bg-info text-dark px-3 py-2">Petugas CS</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <!-- Tombol Ubah Role (Memicu Modal Kustom) -->
                                    @if($u->role == 'admin')
                                        <button type="button" class="btn btn-outline-warning btn-sm py-1 px-2" title="Jadikan CS" onclick="bukaModalRole('{{ route('admin.users.updateRole', $u->id) }}', '{{ $u->name }}', 'cs')">
                                            <i class="fa fa-user-slash me-1"></i> Demote ke CS
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-success btn-sm py-1 px-2" title="Jadikan Admin" onclick="bukaModalRole('{{ route('admin.users.updateRole', $u->id) }}', '{{ $u->name }}', 'admin')">
                                            <i class="fa fa-user-shield me-1"></i> Jadikan Admin
                                        </button>
                                    @endif

                                    <!-- Tombol Hapus User (Memicu Modal Kustom) -->
                                    @if($u->id !== auth()->id())
                                        <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" onclick="bukaModalHapusUser('{{ route('admin.users.destroy', $u->id) }}', '{{ $u->name }}')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH USER -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold m-0" id="modalTambahUserLabel" style="color: #002060;">Tambah Akun Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Petugas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Alamat Email (Untuk Login)</label>
                        <input type="email" name="email" class="form-control" placeholder="petugas@bps.go.id" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="background-color: #002060;">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI UBAH ROLE -->
<div class="modal fade" id="modalKonfirmasiRole" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; background-color: #f8fafc; border: 1px solid #cbd5e1 !important;">
            <form id="formRoleAction" method="POST" class="m-0">
                @csrf
                @method('PUT')
                <input type="hidden" name="role" id="targetRoleInput">
                
                <div class="modal-header border-0 pb-0 pt-4 px-4 bg-transparent">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 42px; height: 42px; background-color: rgba(2, 132, 199, 0.15); color: #0284c7;">
                            <i class="fa fa-user-cog fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.1rem;" id="modalRoleTitle">Konfirmasi Perubahan Role</h5>
                        </div>
                    </div>
                </div>

                <div class="modal-body px-4 py-3">
                    <p class="small text-muted mb-0" id="modalRoleMessage" style="line-height: 1.6; font-size: 0.95rem;"></p>
                </div>

                <div class="modal-footer border-0 bg-transparent justify-content-end pb-4 px-4 pt-2 gap-2">
                    <button type="button" class="btn fw-bold px-4 py-2 text-secondary bg-white border" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 0.9rem;">
                        Batal
                    </button>
                    <button type="submit" class="btn fw-bold px-4 py-2 text-white" id="modalRoleBtnSubmit" style="border-radius: 8px; font-size: 0.9rem;">
                        Ya, Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI HAPUS AKUN -->
<div class="modal fade" id="modalKonfirmasiHapusUser" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; background-color: #fffbeb; border: 1px solid #fde68a !important;">
            <form id="formHapusUserAction" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                
                <div class="modal-header border-0 pb-0 pt-4 px-4 bg-transparent">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 42px; height: 42px; background-color: rgba(245, 158, 11, 0.15); color: #d97706;">
                            <i class="fa fa-triangle-exclamation fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-0" style="color: #92400e; font-size: 1rem;">Konfirmasi Hapus Akun</h5>
                        </div>
                    </div>
                </div>

                <div class="modal-body px-4 py-3">
                    <p class="small mb-2" style="color: #b45309 !important; line-height: 1.6; font-size: 0.95rem;">
                        Apakah Anda yakin ingin menghapus akun milik <strong id="namaUserHapus" class="text-dark"></strong>?
                    </p>
                    <p class="small text-muted mb-0" style="font-size: 0.85rem;">
                        Tindakan ini permanen dan pengguna tersebut tidak akan bisa login kembali.
                    </p>
                </div>

                <div class="modal-footer border-0 bg-transparent justify-content-end pb-4 px-4 pt-2 gap-2">
                    <button type="button" class="btn fw-bold px-4 py-2 text-secondary bg-white border" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 0.9rem;">
                        Batal
                    </button>
                    <button type="submit" class="btn fw-bold px-4 py-2 text-white" style="background-color: #dc3545; border-radius: 8px; font-size: 0.9rem;">
                        <i class="fa fa-trash me-1"></i> Ya, Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JAVASCRIPT UNTUK MEMICU MODAL KUSTOM -->
<script>
    function bukaModalRole(urlAction, userName, newRole) {
        let title = document.getElementById('modalRoleTitle');
        let message = document.getElementById('modalRoleMessage');
        let btnSubmit = document.getElementById('modalRoleBtnSubmit');
        
        document.getElementById('formRoleAction').setAttribute('action', urlAction);
        document.getElementById('targetRoleInput').value = newRole;

        if(newRole === 'admin') {
            title.innerText = 'Konfirmasi Jadikan Administrator';
            message.innerHTML = `Apakah Anda yakin ingin memberikan hak akses penuh (Administrator) kepada akun <strong>${userName}</strong>?`;
            btnSubmit.style.backgroundColor = '#16a34a'; // Warna hijau
            btnSubmit.innerText = 'Ya, Jadikan Admin';
        } else {
            title.innerText = 'Konfirmasi Ubah ke Petugas CS';
            message.innerHTML = `Apakah Anda yakin ingin mengubah hak akses akun <strong>${userName}</strong> menjadi Petugas CS biasa?`;
            btnSubmit.style.backgroundColor = '#d97706'; // Warna kuning/oranye
            btnSubmit.innerText = 'Ya, Ubah ke CS';
        }

        var modal = new bootstrap.Modal(document.getElementById('modalKonfirmasiRole'));
        modal.show();
    }

    function bukaModalHapusUser(urlAction, userName) {
        document.getElementById('namaUserHapus').innerText = userName;
        document.getElementById('formHapusUserAction').setAttribute('action', urlAction);

        var modal = new bootstrap.Modal(document.getElementById('modalKonfirmasiHapusUser'));
        modal.show();
    }
</script>
@endsection