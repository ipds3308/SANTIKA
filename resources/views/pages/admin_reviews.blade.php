@extends('layouts.main')

@section('title', 'Rekap Ulasan & Saran - SANTIKA BPS')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Bagian Atas -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="fa fa-star text-warning me-2"></i> Rekap Ulasan & Saran Pengguna</h4>
            <p class="text-muted small mb-0">Kelola dan pantau penilaian kepuasan layanan dari para pemohon antrian.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reviews.exportPdf', ['bulan' => $bulan ?? '']) }}" target="_blank" class="btn btn-danger btn-sm fw-bold flex-fill flex-md-grow-0">
                <i class="fa fa-file-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm fw-bold flex-fill flex-md-grow-0">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Form Filter Bulanan -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body py-3">
            <form action="{{ route('admin.reviews') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label for="bulan" class="fw-bold text-secondary small mb-1"><i class="fa fa-calendar-week me-1"></i> Filter Bulan:</label>
                    <input type="month" id="bulan" name="bulan" value="{{ $bulan ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label for="per_page" class="fw-bold text-secondary small mb-1"><i class="fa fa-list-ol me-1"></i> Data per halaman:</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm">
                        <option value="15" {{ ($perPage ?? '15') === '15' ? 'selected' : '' }}>15</option>
                        <option value="30" {{ ($perPage ?? '15') === '30' ? 'selected' : '' }}>30</option>
                        <option value="50" {{ ($perPage ?? '15') === '50' ? 'selected' : '' }}>50</option>
                        <option value="all" {{ ($perPage ?? '15') === 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
                <div class="col-md-auto col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-4" style="background-color: #002060; border-color: #002060;">
                        <i class="fa fa-filter me-1"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.reviews') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="fa fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('pages.partials.admin-reviews')
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

</script>
@vite('resources/js/admin-dashboard-realtime.js')
@endsection