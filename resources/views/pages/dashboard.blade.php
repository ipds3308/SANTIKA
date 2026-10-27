@extends('layouts.main')

@section('title', 'Panel Pengelola Antrian - SANTIKA BPS')

@section('content')
<style>
    /* Mencegah halaman utama bergeser/slide ke kanan-kiri */
    body, html {
        overflow-x: hidden;
        max-width: 100%;
    }

    .edit-antrian-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 20px 22px;
        background-color: whitesmoke;
    }

    .edit-antrian-form .uiverse-label {
        display: block;
        margin-bottom: 3px;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .edit-antrian-form .input-custom {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        outline: 0;
        background-color: #ffffff;
        color: #1e293b;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .edit-antrian-form .input-custom:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }

    .edit-antrian-form textarea.input-custom {
        min-height: 45px;
        resize: none;
    }

    .dashboard-date-picker {
        position: relative;
    }

    .dashboard-date-picker-controls {
        display: flex;
        gap: 0.5rem;
    }

    .dashboard-date-picker-display {
        min-width: 0;
        cursor: pointer;
    }

    .dashboard-date-picker-toggle {
        flex: 0 0 42px;
    }

    .dashboard-date-calendar {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        z-index: 1090;
        width: min(100%, 360px);
        padding: 14px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
    }

    .dashboard-date-calendar[hidden] {
        display: none;
    }

    .dashboard-date-calendar-header,
    .dashboard-date-calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 4px;
    }

    .dashboard-date-calendar-header {
        align-items: center;
        margin-bottom: 6px;
    }

    .dashboard-date-calendar-header button {
        border: 0;
        border-radius: 6px;
        padding: 6px 2px;
        color: #334155;
        background: transparent;
        font-weight: 700;
    }

    .dashboard-date-calendar-header button:hover:not(:disabled) {
        background: #e0f2fe;
    }

    .dashboard-date-calendar-header button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .dashboard-date-calendar-weekday,
    .dashboard-date-calendar-day {
        display: flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.84rem;
    }

    .dashboard-date-calendar-weekday {
        color: #64748b;
        font-weight: 700;
    }

    .dashboard-date-calendar-day {
        border: 1px solid transparent;
        color: #1e293b;
        background: transparent;
    }

    .dashboard-date-calendar-day:hover:not(:disabled) {
        border-color: #0284c7;
        background: #e0f2fe;
    }

    .dashboard-date-calendar-day.is-selected {
        color: #fff;
        background: #0284c7;
    }

    .dashboard-date-calendar-day:disabled {
        color: #94a3b8;
        background: #f1f5f9;
        cursor: not-allowed;
        opacity: 0.55;
    }

    .dashboard-date-calendar-day.is-holiday:disabled {
        color: #b91c1c;
        background: #fee2e2;
        text-decoration: line-through;
    }

    .dashboard-date-calendar-clear {
        margin-top: 8px;
        padding: 0;
        border: 0;
        color: #475569;
        background: transparent;
        font-size: 0.8rem;
        text-decoration: underline;
    }

    /* Pastikan dropdown-menu tampil di atas semua elemen lain saat pakai strategy: fixed */
    #tabel-pendaftar-body .dropdown-menu {
        z-index: 1080;
    }
</style>

<div class="bg-white border rounded shadow-sm p-3 mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div class="fw-bold" style="color: #002060;">
        <i class="fa fa-user-circle me-2" style="color: #ffb800;"></i>
        Login sebagai: {{ auth()->user()->role === 'admin' ? 'Admin' : 'CS SANTIKA' }}
    </div>

    <div class="d-grid d-md-flex gap-2" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        @if(in_array(strtolower((string) auth()->user()->role), ['admin', 'cs'], true))
            <a href="{{ route('news.index') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="fa fa-newspaper me-1"></i> Kelola Berita
            </a>
        @endif

        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm fw-bold px-3 w-100">
                <i class="fa fa-sign-out-alt me-1"></i> Keluar Sistem
            </button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom border-2 border-primary d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="fw-bold m-0" style="color: #002060;"><i class="fa fa-tachometer-alt me-2"></i> Panel Monitoring Antrian & Rekapan Layanan</h5>
            <p class="text-muted small m-0 mt-1">Periode Aktif: <strong>
                @if(isset($bulan) && $bulan)
                    Bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}
                @elseif(isset($tanggal) && $tanggal)
                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                @else
                    {{ \Carbon\Carbon::now()->format('d F Y') }}
                @endif
            </strong></p>
        </div>
        
        <!-- Tombol Aksi Header Membawa Filter -->
        <div class="d-grid d-md-flex gap-2 align-items-center" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
            <!-- TOMBOL LIHAT ULASAN & SARAN -->
            <a href="{{ route('admin.reviews') }}" class="btn btn-warning btn-sm fw-bold text-dark shadow-sm">
                <i class="fa fa-star me-1"></i> Lihat Ulasan & Saran
            </a>

            <!-- TOMBOL LINK RUANG TUNGGU TV -->
            <a href="{{ route('ruang.tunggu') }}" target="_blank" class="btn btn-warning btn-sm fw-bold">
                <i class="fa fa-tv me-1"></i> Buka Layar Ruang Tunggu
            </a>

            <!-- TOMBOL DOWNLOAD LAPORAN DENGAN PARAMETER FILTER -->
            <a href="{{ route('admin.export', ['tanggal' => $tanggal ?? '', 'bulan' => $bulan ?? '', 'jenis_layanan' => $jenisLayanan ?? '']) }}" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </a>

            <a href="{{ route('admin.exportWord', ['tanggal' => $tanggal ?? '', 'bulan' => $bulan ?? '', 'jenis_layanan' => $jenisLayanan ?? '']) }}" class="btn btn-primary btn-sm">
                <i class="fa fa-file-word me-1"></i> Export Word
            </a>

            <a href="{{ route('admin.exportPdf', ['tanggal' => $tanggal ?? '', 'bulan' => $bulan ?? '', 'jenis_layanan' => $jenisLayanan ?? '']) }}" target="_blank" class="btn btn-danger btn-sm">
                <i class="fa fa-file-pdf me-1"></i> Export PDF
            </a>
 
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

        <!-- Form Filter Lengkap (Tanggal, Bulan, & Jenis Layanan) -->
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body py-3">
                <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-3 align-items-end">
                    
                    <!-- Filter Harian -->
                    <div class="col-md-3">
                        <label for="tanggalPickerDisplay" class="fw-bold text-secondary small mb-1"><i class="fa fa-calendar-alt me-1"></i> Filter Tanggal:</label>
                        <div class="dashboard-date-picker" data-dashboard-date-picker data-allow-past="true" data-clearable="true">
                            <div class="dashboard-date-picker-controls">
                                <input type="text" id="tanggalPickerDisplay" class="form-control form-control-sm dashboard-date-picker-display" placeholder="Pilih tanggal" aria-haspopup="dialog" aria-expanded="false" autocomplete="off" readonly>
                                <button type="button" class="btn btn-outline-secondary btn-sm dashboard-date-picker-toggle" aria-label="Buka kalender filter tanggal" aria-expanded="false">
                                    <i class="fa fa-calendar-days" aria-hidden="true"></i>
                                </button>
                            </div>
                            <input type="hidden" id="tanggal" name="tanggal" value="{{ $tanggal ?? '' }}" data-dashboard-date-value>
                        </div>
                        @unless($holidayCalendarAvailable)
                            <small class="text-danger d-block mt-1" style="font-size: 0.7rem;">Kalender hari libur nasional tidak dapat dimuat saat ini. Akhir pekan tetap tidak tersedia.</small>
                        @endunless
                    </div>

                    <!-- Filter Bulanan (Rekapan) -->
                    <div class="col-md-3">
                        <label for="bulan" class="fw-bold text-secondary small mb-1"><i class="fa fa-calendar-week me-1"></i> Filter Bulanan (Rekapan):</label>
                        <input type="month" id="bulan" name="bulan" value="{{ $bulan ?? '' }}" class="form-control form-control-sm">
                    </div>

                    <!-- Sort Data -->
                    <div class="col-md-3">
                        <label for="sort" class="fw-bold text-secondary small mb-1"><i class="fa fa-sort me-1"></i> Sort By / Urutkan:</label>
                        <select name="sort" id="sort" class="form-select form-select-sm">
                            <option value="nomor_antrian" {{ ($sort ?? '') === 'nomor_antrian' ? 'selected' : '' }}>Berdasarkan Tiket</option>
                            <option value="created_at" {{ ($sort ?? 'created_at') === 'created_at' ? 'selected' : '' }}>Berdasarkan Terbaru</option>
                            <option value="nama" {{ ($sort ?? '') === 'nama' ? 'selected' : '' }}>Berdasarkan Nama Pemohon</option>
                        </select>
                    </div>

                    <input type="hidden" name="jenis_layanan" value="{{ $jenisLayanan ?? '' }}">

                    <!-- Tombol Aksi Filter & Reset -->
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm fw-bold w-100" style="background-color: #002060; border-color: #002060;">
                            <i class="fa fa-filter me-1"></i> Terapkan
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                            <i class="fa fa-sync-alt"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const dashboardPublicHolidays = @js($publicHolidays);
            const dashboardMinimumDate = @js(now('Asia/Jakarta')->format('Y-m-d'));

            function formatDashboardDate(value) {
                if (!value) {
                    return '';
                }

                return new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                }).format(new Date(`${value}T00:00:00`));
            }

            function setDashboardDatePickerValue(input, value) {
                const picker = input.closest('[data-dashboard-date-picker]');
                const display = picker?.querySelector('.dashboard-date-picker-display');

                input.value = value || '';

                if (display) {
                    display.value = formatDashboardDate(input.value);
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const bulanInput = document.getElementById('bulan');
                const datePickers = Array.from(document.querySelectorAll('[data-dashboard-date-picker]'));

                datePickers.forEach(function (picker) {
                    const display = picker.querySelector('.dashboard-date-picker-display');
                    const valueInput = picker.querySelector('[data-dashboard-date-value]');
                    const toggle = picker.querySelector('.dashboard-date-picker-toggle');
                    const allowPast = picker.dataset.allowPast === 'true';
                    const clearable = picker.dataset.clearable === 'true';
                    const calendar = document.createElement('div');
                    const monthLabel = document.createElement('strong');
                    const days = document.createElement('div');
                    const previousMonth = document.createElement('button');
                    const nextMonth = document.createElement('button');
                    const monthFormatter = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
                    const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                    });
                    const weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                    const selectedDate = valueInput.value
                        ? new Date(`${valueInput.value}T00:00:00`)
                        : new Date(`${dashboardMinimumDate}T00:00:00`);
                    let visibleMonth = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);

                    calendar.className = 'dashboard-date-calendar';
                    calendar.setAttribute('role', 'dialog');
                    calendar.setAttribute('aria-label', 'Pilih tanggal');
                    calendar.hidden = true;

                    const header = document.createElement('div');
                    header.className = 'dashboard-date-calendar-header';
                    previousMonth.type = 'button';
                    previousMonth.setAttribute('aria-label', 'Bulan sebelumnya');
                    previousMonth.innerHTML = '<i class="fa fa-chevron-left" aria-hidden="true"></i>';
                    nextMonth.type = 'button';
                    nextMonth.setAttribute('aria-label', 'Bulan berikutnya');
                    nextMonth.innerHTML = '<i class="fa fa-chevron-right" aria-hidden="true"></i>';
                    monthLabel.className = 'text-center';
                    monthLabel.style.gridColumn = 'span 5';
                    monthLabel.setAttribute('aria-live', 'polite');
                    header.append(previousMonth, monthLabel, nextMonth);

                    const weekdayGrid = document.createElement('div');
                    weekdayGrid.className = 'dashboard-date-calendar-grid mb-1';
                    weekdayGrid.setAttribute('aria-hidden', 'true');
                    weekdays.forEach(function (weekday) {
                        const weekdayLabel = document.createElement('span');
                        weekdayLabel.className = 'dashboard-date-calendar-weekday';
                        weekdayLabel.textContent = weekday;
                        weekdayGrid.appendChild(weekdayLabel);
                    });

                    days.className = 'dashboard-date-calendar-grid';
                    days.setAttribute('role', 'grid');
                    days.setAttribute('aria-label', 'Tanggal dalam bulan');
                    calendar.append(header, weekdayGrid, days);

                    if (clearable) {
                        const clearButton = document.createElement('button');
                        clearButton.type = 'button';
                        clearButton.className = 'dashboard-date-calendar-clear';
                        clearButton.textContent = 'Hapus pilihan tanggal';
                        clearButton.addEventListener('click', function () {
                            setDashboardDatePickerValue(valueInput, '');
                            calendar.hidden = true;
                            display.setAttribute('aria-expanded', 'false');
                            toggle.setAttribute('aria-expanded', 'false');
                        });
                        calendar.appendChild(clearButton);
                    }

                    picker.appendChild(calendar);
                    setDashboardDatePickerValue(valueInput, valueInput.value);

                    function renderCalendar() {
                        const firstDay = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1);
                        const firstGridDate = new Date(firstDay);
                        const mondayOffset = (firstDay.getDay() + 6) % 7;
                        firstGridDate.setDate(firstGridDate.getDate() - mondayOffset);
                        const earliestMonth = new Date(`${dashboardMinimumDate.slice(0, 7)}-01T00:00:00`);

                        monthLabel.textContent = monthFormatter.format(visibleMonth);
                        previousMonth.disabled = !allowPast && visibleMonth <= earliestMonth;
                        days.replaceChildren();

                        for (let dayIndex = 0; dayIndex < 42; dayIndex++) {
                            const date = new Date(firstGridDate);
                            date.setDate(firstGridDate.getDate() + dayIndex);

                            const dateValue = [
                                date.getFullYear(),
                                String(date.getMonth() + 1).padStart(2, '0'),
                                String(date.getDate()).padStart(2, '0'),
                            ].join('-');
                            const holidayName = dashboardPublicHolidays[dateValue];
                            const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                            const isOutsideMonth = date.getMonth() !== visibleMonth.getMonth();
                            const isPast = dateValue < dashboardMinimumDate;
                            const dayButton = document.createElement('button');

                            dayButton.type = 'button';
                            dayButton.className = 'dashboard-date-calendar-day';
                            dayButton.textContent = String(date.getDate());
                            dayButton.dataset.date = dateValue;
                            dayButton.setAttribute('role', 'gridcell');
                            dayButton.setAttribute('aria-label', dateFormatter.format(date));
                            dayButton.disabled = isWeekend || Boolean(holidayName) || (!allowPast && isPast) || isOutsideMonth;
                            dayButton.setAttribute('aria-pressed', dateValue === valueInput.value ? 'true' : 'false');

                            if (dateValue === valueInput.value) {
                                dayButton.classList.add('is-selected');
                            }

                            if (holidayName) {
                                dayButton.classList.add('is-holiday');
                                dayButton.title = `${holidayName} - hari libur nasional`;
                            } else if (isWeekend) {
                                dayButton.title = 'Akhir pekan - tanggal tidak tersedia';
                            } else if (!allowPast && isPast) {
                                dayButton.title = 'Tanggal yang sudah lewat tidak tersedia';
                            }

                            days.appendChild(dayButton);
                        }
                    }

                    function closeCalendar() {
                        calendar.hidden = true;
                        display.setAttribute('aria-expanded', 'false');
                        toggle.setAttribute('aria-expanded', 'false');
                    }

                    function openCalendar() {
                        datePickers.forEach(function (otherPicker) {
                            const otherCalendar = otherPicker.querySelector('.dashboard-date-calendar');
                            const otherDisplay = otherPicker.querySelector('.dashboard-date-picker-display');
                            const otherToggle = otherPicker.querySelector('.dashboard-date-picker-toggle');

                            if (otherCalendar) {
                                otherCalendar.hidden = true;
                                otherDisplay.setAttribute('aria-expanded', 'false');
                                otherToggle.setAttribute('aria-expanded', 'false');
                            }
                        });
                        if (valueInput.value) {
                            const selectedDate = new Date(`${valueInput.value}T00:00:00`);
                            visibleMonth = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
                        }
                        calendar.hidden = false;
                        display.setAttribute('aria-expanded', 'true');
                        toggle.setAttribute('aria-expanded', 'true');
                        renderCalendar();
                    }

                    display.addEventListener('click', openCalendar);
                    toggle.addEventListener('click', function () {
                        if (calendar.hidden) {
                            openCalendar();
                        } else {
                            closeCalendar();
                        }
                    });
                    previousMonth.addEventListener('click', function () {
                        visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() - 1, 1);
                        renderCalendar();
                    });
                    nextMonth.addEventListener('click', function () {
                        visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1);
                        renderCalendar();
                    });
                    days.addEventListener('click', function (event) {
                        const dayButton = event.target.closest('button[data-date]');

                        if (!dayButton || dayButton.disabled) {
                            return;
                        }

                        setDashboardDatePickerValue(valueInput, dayButton.dataset.date);
                        closeCalendar();

                        if (valueInput.id === 'tanggal') {
                            bulanInput.value = '';
                        }
                    });

                    document.addEventListener('click', function (event) {
                        if (!calendar.hidden && !picker.contains(event.target)) {
                            closeCalendar();
                        }
                    });

                    renderCalendar();
                });

                if (bulanInput) {
                    bulanInput.addEventListener('change', function () {
                        if (this.value) {
                            datePickers.forEach(function (picker) {
                                const valueInput = picker.querySelector('[data-dashboard-date-value]');

                                if (valueInput.id === 'tanggal') {
                                    setDashboardDatePickerValue(valueInput, '');
                                }
                            });
                        }
                    });
                }
            });
        </script>

        @php
            $quickFilters = [
                [
                    'label' => 'Semua Layanan',
                    'value' => '',
                    'activeStyle' => 'background-color: #334155; color: #ffffff; border: 1px solid #334155; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.18);',
                    'inactiveStyle' => 'background-color: rgba(255,255,255,0.9); color: #334155; border: 1px solid #cbd5e1;'
                ],
                [
                    'label' => 'Konsultasi Statistik (A)',
                    'value' => 'Konsultasi Statistik',
                    'activeStyle' => 'background-color: #2563eb; color: #ffffff; border: 1px solid #2563eb; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);',
                    'inactiveStyle' => 'background-color: rgba(255,255,255,0.9); color: #1d4ed8; border: 1px solid #93c5fd;'
                ],
                [
                    'label' => 'Konsultasi DTSEN (B)',
                    'value' => 'Konsultasi DTSEN',
                    'activeStyle' => 'background-color: #059669; color: #ffffff; border: 1px solid #059669; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.22);',
                    'inactiveStyle' => 'background-color: rgba(255,255,255,0.9); color: #047857; border: 1px solid #a7f3d0;'
                ],
                [
                    'label' => 'Permintaan Data (C)',
                    'value' => 'Permintaan Data',
                    'activeStyle' => 'background-color: #0891b2; color: #ffffff; border: 1px solid #0891b2; box-shadow: 0 4px 12px rgba(8, 145, 178, 0.22);',
                    'inactiveStyle' => 'background-color: rgba(255,255,255,0.9); color: #0f766e; border: 1px solid #67e8f9;'
                ],
                [
                    'label' => 'Rekomendasi Kegiatan Statistik (D)',
                    'value' => 'Rekomendasi Kegiatan Statistik',
                    'activeStyle' => 'background-color: #d97706; color: #ffffff; border: 1px solid #d97706; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);',
                    'inactiveStyle' => 'background-color: rgba(255,255,255,0.9); color: #b45309; border: 1px solid #fcd34d;'
                ],
                [
                    'label' => 'Pengaduan (E)',
                    'value' => 'Pengaduan',
                    'activeStyle' => 'background-color: #dc2626; color: #ffffff; border: 1px solid #dc2626; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.22);',
                    'inactiveStyle' => 'background-color: rgba(255,255,255,0.9); color: #b91c1c; border: 1px solid #fca5a5;'
                ],
                [
                    'label' => 'Lainnya (F)',
                    'value' => 'Lainnya',
                    'activeStyle' => 'background-color: #64748b; color: #ffffff; border: 1px solid #64748b; box-shadow: 0 4px 12px rgba(100, 116, 139, 0.22);',
                    'inactiveStyle' => 'background-color: rgba(255,255,255,0.9); color: #475569; border: 1px solid #cbd5e1;'
                ],
            ];
            $currentJenisLayanan = $jenisLayanan ?? '';
            $currentSort = $sort ?? 'created_at';
        @endphp

        <div class="d-flex flex-wrap gap-2 my-4">
            @foreach($quickFilters as $filter)
                @php
                    $isActive = ($filter['value'] === '') ? empty($currentJenisLayanan) : $currentJenisLayanan === $filter['value'];
                    $queryParams = request()->query();
                    $queryParams['sort'] = $currentSort;

                    if ($tanggal ?? false) {
                        $queryParams['tanggal'] = $tanggal;
                    }

                    if ($bulan ?? false) {
                        $queryParams['bulan'] = $bulan;
                    }

                    if ($filter['value'] === '') {
                        unset($queryParams['jenis_layanan']);
                    } elseif ($isActive) {
                        unset($queryParams['jenis_layanan']);
                    } else {
                        $queryParams['jenis_layanan'] = $filter['value'];
                    }
                @endphp
                <a
                    href="{{ route('admin.dashboard', $queryParams) }}"
                    class="btn btn-sm fw-bold rounded-pill px-3 py-2 text-nowrap"
                    style="{{ $isActive ? $filter['activeStyle'] : $filter['inactiveStyle'] }}; font-size: 0.78rem; min-height: 34px;"
                >
                    {{ $filter['label'] }}
                </a>
            @endforeach
        </div>

        <!-- Tabel Data Pendaftar dibungkus card dan table-responsive agar rapi di HP -->
        <div class="card border-0 shadow-sm overflow-hidden">
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
                        @forelse($pendaftar as $data)
                            <tr>
                                <td class="fw-bold text-primary px-3" style="font-size: 1.1rem;">{{ $data->nomor_antrian }}</td>
                                <td class="fw-bold">{{ $data->nama }}</td>
                                
                                <td>
                                    @php
                                        $serviceMeta = \App\Models\Registration::serviceBadgeMeta($data->jenis_layanan);
                                    @endphp
                                    <span class="badge px-2 py-1" style="{{ $serviceMeta['inlineStyle'] }}; font-size: 0.8rem; font-weight: 600;">
                                        @if(str_starts_with((string) $data->jenis_layanan, 'Lainnya:'))
                                            Lainnya:{{ Str::after($data->jenis_layanan, 'Lainnya:') }}
                                        @else
                                            {{ $data->jenis_layanan ?? '-' }}
                                        @endif
                                    </span>
                                </td>

                                <td>
                                    <div><i class="fa fa-envelope text-muted me-1" style="width: 15px;"></i> {{ $data->email }}</div>
                                    
                                    <!-- Nomor WhatsApp Klikabel ke wa.me -->
                                    @php
                                        $phoneWa = preg_match('/^08/', $data->no_wa) ? '62' . substr($data->no_wa, 1) : $data->no_wa;
                                    @endphp
                                    <div class="mt-1">
                                        <a href="https://wa.me/{{ $phoneWa }}" target="_blank" class="text-decoration-none text-dark fw-semibold" title="Chat WhatsApp">
                                            <i class="fa-brands fa-whatsapp text-success me-1" style="width: 15px; font-size: 1rem;"></i> 
                                            {{ $data->no_wa }} 
                                            <i class="fa fa-external-link-alt fa-xs text-muted ms-1"></i>
                                        </a>
                                    </div>

                                    <div class="small text-muted mt-1 border-top pt-1"><i class="fa fa-map-marker-alt me-1" style="width: 15px;"></i> {{ $data->alamat ?? 'Alamat belum diisi' }}</div>
                                </td>

                                <td class="text-center">
                                    @if($data->status == 'Menunggu')
                                        <span class="badge bg-warning text-dark px-3 py-2">Menunggu</span>
                                    @elseif($data->status == 'Dipanggil')
                                        <span class="badge bg-info text-white px-3 py-2">Dipanggil</span>
                                    @elseif($data->status == 'Tidak Hadir')
                                        <span class="badge bg-secondary px-3 py-2">Tidak Hadir</span>
                                    @else
                                        <span class="badge bg-success px-3 py-2">Selesai</span>
                                    @endif
                                </td>
                                
                                <td class="text-center px-1 py-2 align-middle" style="width: 160px; white-space: nowrap;">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        @if($data->status === 'Selesai')
                                            <button type="button" class="btn btn-secondary btn-sm fw-bold px-2 py-1" disabled title="Antrian selesai dan terkunci">
                                                <i class="fa fa-lock me-1"></i> Terkunci
                                            </button>
                                        @else
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-primary btn-sm fw-bold px-2 py-1 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.78rem; background-color: #0d6efd; border-color: #0d6efd;">
                                                    <i class="fa fa-chevron-down me-1"></i> Ubah Status
                                                </button>
                                                <ul class="dropdown-menu shadow-lg border py-1 bg-white" style="min-width: 170px;">
                                                    @if($data->status === 'Tidak Hadir')
                                                        <!-- Jika status Tidak Hadir, tombol Panggil & Selesai terkunci, tapi bisa dikembalikan ke Menunggu -->
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
                                                            <button type="button" class="dropdown-item px-3 py-2 text-warning fw-bold" data-status-action="true" data-id="{{ $data->id }}" data-status="Menunggu">
                                                                <i class="fa fa-undo me-2"></i> Jadikan Menunggu Lagi
                                                            </button>
                                                        </li>
                                                    @else
                                                        <!-- Menu normal untuk status Menunggu / Dipanggil -->
                                                        <li>
                                                            <button type="button" class="dropdown-item px-3 py-2" data-status-action="true" data-id="{{ $data->id }}" data-status="Dipanggil" data-nomor-antrian="{{ $data->nomor_antrian }}">
                                                                <i class="fa fa-bullhorn me-2 text-info"></i> Panggil
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item px-3 py-2" data-status-action="true" data-id="{{ $data->id }}" data-status="Selesai" data-nomor-antrian="{{ $data->nomor_antrian }}" data-nama="{{ $data->nama }}">
                                                                <i class="fa fa-check me-2 text-success"></i> Selesai
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item px-3 py-2" data-status-action="true" data-id="{{ $data->id }}" data-status="Tidak Hadir">
                                                                <i class="fa fa-user-slash me-2 text-secondary"></i> Tidak Hadir
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif

                                        @if(in_array(strtolower((string) auth()->user()->role), ['admin', 'cs'], true))
                                            <button type="button" class="btn btn-outline-warning btn-sm fw-bold px-2 py-1" style="font-size: 0.78rem;" onclick="bukaModalEdit({{ json_encode(['id' => $data->id, 'nama' => $data->nama, 'email' => $data->email, 'no_wa' => $data->no_wa, 'alamat' => $data->alamat, 'jenis_layanan' => $data->jenis_layanan, 'tanggal_kunjungan' => $data->tanggal_kunjungan, 'nomor_antrian' => $data->nomor_antrian, 'status' => $data->status], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }})">
                                                <i class="fa fa-pen me-1"></i> Edit
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fa fa-info-circle me-1"></i> Tidak ada data antrian untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(in_array(strtolower((string) auth()->user()->role), ['admin', 'cs'], true))
<!-- MODAL EDIT DATA ANTRIAN -->
<div class="modal fade" id="modalEditAntrian" tabindex="-1" aria-labelledby="modalEditAntrianLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: visible;">
            <div class="modal-header text-white" style="background-color: #002b6a;">
                <h5 class="modal-title fw-bold" id="modalEditAntrianLabel"><i class="fa fa-pen me-2"></i>Edit Data Antrian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="formEditAntrian" class="edit-antrian-form">
                <div class="modal-body p-0">
                    <div class="alert alert-light border mb-3"><strong id="editNomorAntrian"></strong></div>
                    <div id="editAntrianLockedNotice" class="alert alert-secondary border mb-3 d-none">
                        <i class="fa fa-lock me-1"></i> Antrian ini sudah <strong>Selesai</strong> dan datanya tidak dapat diubah lagi. Anda hanya dapat melihat data ini.
                    </div>
                    <div class="mb-2">
                        <label for="editNama" class="uiverse-label">Nama Lengkap (Sesuai KTP)</label>
                        <input type="text" id="editNama" name="nama" class="input-custom" placeholder="Masukkan nama lengkap Anda" required>
                    </div>
                    <div class="mb-2">
                        <label for="editJenisLayanan" class="uiverse-label">Pilih Jenis Layanan / Keperluan</label>
                        <select id="editJenisLayanan" name="jenis_layanan" class="input-custom" required>
                                <option value="Konsultasi Statistik">Konsultasi Statistik</option>
                                <option value="Konsultasi DTSEN">Konsultasi DTSEN</option>
                                <option value="Permintaan Data">Permintaan Data</option>
                                <option value="Rekomendasi Kegiatan Statistik">Rekomendasi Kegiatan Statistik</option>
                                <option value="Pengaduan">Pengaduan</option>
                                <option value="Lainnya">Lainnya</option>
                        </select>
                        <input type="text" id="editJenisLayananLainnya" class="input-custom mt-2" placeholder="Sebutkan jenis layanan / keperluan Anda..." hidden>
                    </div>
                    <div class="mb-2">
                            <label for="editTanggalKunjunganDisplay" class="uiverse-label">Tanggal Kunjungan</label>
                            <div class="dashboard-date-picker" data-dashboard-date-picker data-allow-past="false">
                                <div class="dashboard-date-picker-controls">
                                    <input type="text" id="editTanggalKunjunganDisplay" class="input-custom dashboard-date-picker-display" placeholder="Pilih tanggal kunjungan" aria-haspopup="dialog" aria-expanded="false" autocomplete="off" readonly required>
                                    <button type="button" class="btn btn-outline-secondary dashboard-date-picker-toggle" aria-label="Buka kalender tanggal kunjungan" aria-expanded="false">
                                        <i class="fa fa-calendar-days" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="editTanggalKunjungan" name="tanggal_kunjungan" data-dashboard-date-value>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem; margin-top: 2px; display: block;">Pilih hari Senin–Jumat. Akhir pekan dan hari libur nasional tidak dapat dipilih.</small>
                            @unless($holidayCalendarAvailable)
                                <small class="text-danger d-block mt-1" style="font-size: 0.7rem;">Kalender hari libur nasional tidak dapat dimuat saat ini. Akhir pekan tetap tidak tersedia.</small>
                            @endunless
                    </div>
                    <div class="mb-2">
                        <label for="editEmail" class="uiverse-label">Alamat Email Aktif</label>
                        <input type="email" id="editEmail" name="email" class="input-custom" placeholder="Contoh: nama@domain.com" required>
                    </div>
                    <div class="mb-2">
                        <label for="editNoWa" class="uiverse-label">Nomor WhatsApp Aktif</label>
                        <input type="text" id="editNoWa" name="no_wa" class="input-custom" placeholder="Contoh: 08123456789 atau 628123456789" maxlength="15" required>
                    </div>
                    <div class="mb-0">
                        <label for="editAlamat" class="uiverse-label">Alamat Lengkap (Domisili)</label>
                        <textarea id="editAlamat" name="alamat" class="input-custom" placeholder="Contoh: Jl. Merdeka No. 1, Kota Magelang" rows="2" required></textarea>
                    </div>
                    <div id="editAntrianError" class="alert alert-danger mt-3 mb-0 d-none"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    @if(strtolower((string) auth()->user()->role) === 'admin')
                        <button type="button" class="btn btn-danger fw-bold" id="btnHapusDariEdit"><i class="fa fa-trash me-1"></i>Hapus Antrian</button>
                    @else
                        <div></div>
                    @endif
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btnSimpanEdit"><i class="fa fa-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI PERUBAHAN TIKET -->
<div class="modal fade" id="modalKonfirmasiEditAntrian" tabindex="-1" aria-labelledby="modalKonfirmasiEditAntrianLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: #002b6a;">
                <h5 class="modal-title fw-bold" id="modalKonfirmasiEditAntrianLabel"><i class="fa fa-circle-check me-2"></i>Konfirmasi Perubahan Tiket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-0 text-dark" style="line-height: 1.6;">Apakah Anda yakin ingin menyimpan perubahan data tiket ini? Email e-tiket terbaru akan otomatis dikirimkan ulang ke pemohon dengan penanda [Perubahan Tiket].</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary fw-bold" id="btnKonfirmasiEditAntrian"><i class="fa fa-paper-plane me-1"></i> Ya, Perbarui &amp; Kirim Email</button>
            </div>
        </div>
    </div>
</div>

<div id="editAntrianSuccessToast" class="toast position-fixed top-0 end-0 m-4 border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1090;" data-bs-delay="5000">
    <div class="toast-header bg-success text-white border-0">
        <i class="fa fa-check-circle me-2"></i>
        <strong class="me-auto">Berhasil</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Tutup"></button>
    </div>
    <div class="toast-body">Data tiket berhasil diperbarui dan email perubahan telah dikirim ke pemohon.</div>
</div>
@endif

<!-- Script AJAX & Auto-Refresh Real-Time Dashboard -->
<script>
    let isAnyDropdownOpen = false;

    // Lacak status dropdown secara akurat via event Bootstrap
    document.addEventListener('shown.bs.dropdown', function (e) {
        if (e.target.closest('#tabel-pendaftar-body')) {
            isAnyDropdownOpen = true;
        }
    });

    document.addEventListener('hidden.bs.dropdown', function (e) {
        if (e.target.closest('#tabel-pendaftar-body')) {
            isAnyDropdownOpen = false;
        }
    });

    // Inisialisasi dropdown dengan strategy 'fixed' agar tidak ke-clip oleh overflow container
    function initDropdownsFixedStrategy(root) {
        root.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
            const existing = bootstrap.Dropdown.getInstance(el);
            if (existing) existing.dispose();
            new bootstrap.Dropdown(el, {
                popperConfig: function (defaultBsPopperConfig) {
                    return Object.assign({}, defaultBsPopperConfig, { strategy: 'fixed' });
                }
            });
        });
    }

    function panggilCustomer(id, nomorAntrian) {
        fetch(`/admin/panggil/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal memanggil antrian.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi.');
        });
    }

    function autoRefreshDashboard() {
        // Cek 1: sebelum fetch dikirim
        if (isAnyDropdownOpen) {
            return;
        }

        let currentUrl = window.location.href;

        fetch(currentUrl)
            .then(response => response.text())
            .then(html => {
                // Cek 2: sebelum HTML diterapkan — menutup celah race condition
                if (isAnyDropdownOpen) {
                    return;
                }

                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');
                
                let newTableBody = doc.querySelector('#tabel-pendaftar-body');
                let currentTableBody = document.querySelector('#tabel-pendaftar-body');

                if (newTableBody && currentTableBody) {
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    initDropdownsFixedStrategy(currentTableBody);
                }
            })
            .catch(error => console.error('Gagal memperbarui data real-time:', error));
    }

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-status-action]');
        if (!trigger) return;

        const id = trigger.dataset.id;
        const status = trigger.dataset.status;
        const nomorAntrian = trigger.dataset.nomorAntrian || '';

        if (status === 'Dipanggil') {
            panggilCustomer(id, nomorAntrian);
            return;
        }

        if (status === 'Selesai') {
            bukaModalSelesai(id, nomorAntrian, trigger.dataset.nama || '');
            return;
        }

        fetch(`/admin/status/${id}/${encodeURIComponent(status)}`)
            .then(response => {
                if (response.ok || response.redirected) {
                    window.location.reload();
                    return;
                }
                throw new Error('Gagal mengubah status');
            })
            .catch(error => {
                console.error(error);
                alert('Gagal mengubah status antrian.');
            });
    });

    // Inisialisasi awal saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function () {
        initDropdownsFixedStrategy(document.querySelector('#tabel-pendaftar-body'));
    });

    setInterval(autoRefreshDashboard, 3000);
</script>

@if(in_array(strtolower((string) auth()->user()->role), ['admin', 'cs'], true))
<script>
    let dataAntrianEdit = null;
    let editUpdatePending = false;

    function bukaModalEdit(data) {
        dataAntrianEdit = data;
        document.getElementById('editNomorAntrian').innerText = `Nomor Antrian: ${data.nomor_antrian}`;
        document.getElementById('editNama').value = data.nama || '';
        document.getElementById('editEmail').value = data.email || '';
        document.getElementById('editNoWa').value = data.no_wa || '';
        document.getElementById('editAlamat').value = data.alamat || '';
        setDashboardDatePickerValue(document.getElementById('editTanggalKunjungan'), data.tanggal_kunjungan || '');

        const layananStandar = [
            'Konsultasi Statistik',
            'Konsultasi DTSEN',
            'Permintaan Data',
            'Rekomendasi Kegiatan Statistik',
            'Pengaduan',
        ];
        const selectLayanan = document.getElementById('editJenisLayanan');
        const inputLainnya = document.getElementById('editJenisLayananLainnya');

        if (layananStandar.includes(data.jenis_layanan)) {
            selectLayanan.value = data.jenis_layanan;
            inputLainnya.value = '';
            inputLainnya.hidden = true;
            inputLainnya.required = false;
        } else {
            selectLayanan.value = 'Lainnya';
            inputLainnya.value = data.jenis_layanan || '';
            inputLainnya.hidden = false;
            inputLainnya.required = true;
        }

        document.getElementById('editAntrianError').classList.add('d-none');

        // Kunci form jika antrian sudah Selesai atau Tidak Hadir
        const isLocked = data.status === 'Selesai' || data.status === 'Tidak Hadir';
        const form = document.getElementById('formEditAntrian');
        const lockedNotice = document.getElementById('editAntrianLockedNotice');
        const btnSimpan = document.getElementById('btnSimpanEdit');

        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.disabled = isLocked;
        });
        form.querySelectorAll('.dashboard-date-picker-toggle').forEach(function (button) {
            button.disabled = isLocked;
        });

        // Sesuaikan teks pemberitahuan terkunci berdasarkan statusnya
        if (data.status === 'Selesai') {
            lockedNotice.innerHTML = '<i class="fa fa-lock me-1"></i> Antrian ini sudah <strong>Selesai</strong> dan datanya tidak dapat diubah lagi. Anda hanya dapat melihat data ini.';
        } else if (data.status === 'Tidak Hadir') {
            lockedNotice.innerHTML = '<i class="fa fa-lock me-1"></i> Antrian ini berstatus <strong>Tidak Hadir</strong> dan datanya tidak dapat diubah lagi. Anda hanya dapat melihat data ini.';
        }

        lockedNotice.classList.toggle('d-none', !isLocked);
        btnSimpan.disabled = isLocked;
        btnSimpan.classList.toggle('d-none', isLocked);

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditAntrian')).show();
    }

    document.getElementById('editJenisLayanan').addEventListener('change', function () {
        const inputLainnya = document.getElementById('editJenisLayananLainnya');
        const isLainnya = this.value === 'Lainnya';

        inputLainnya.hidden = !isLainnya;
        inputLainnya.required = isLainnya;
        if (!isLainnya) {
            inputLainnya.value = '';
        }
    });

    document.getElementById('formEditAntrian').addEventListener('submit', function (event) {
        event.preventDefault();

        // Pengaman: tolak submit jika data yang sedang diedit berstatus Selesai atau Tidak Hadir
        if (dataAntrianEdit && (dataAntrianEdit.status === 'Selesai' || dataAntrianEdit.status === 'Tidak Hadir')) {
            return;
        }

        const form = event.currentTarget;
        const selectLayanan = document.getElementById('editJenisLayanan');
        const inputLainnya = document.getElementById('editJenisLayananLainnya');
        const error = document.getElementById('editAntrianError');

        if (selectLayanan.value === 'Lainnya' && !inputLainnya.value.trim()) {
            error.innerText = 'Silakan isi jenis layanan/keperluan Anda.';
            error.classList.remove('d-none');
            return;
        }

        error.classList.add('d-none');

        editUpdatePending = true;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalKonfirmasiEditAntrian')).show();
    });

    document.getElementById('btnKonfirmasiEditAntrian').addEventListener('click', function () {
        if (!editUpdatePending || !dataAntrianEdit) return;

        const form = document.getElementById('formEditAntrian');
        const selectLayanan = document.getElementById('editJenisLayanan');
        const error = document.getElementById('editAntrianError');
        const button = document.getElementById('btnSimpanEdit');
        const confirmButton = this;
        const formData = new FormData(form);
        formData.set('jenis_layanan', selectLayanan.value);
        formData.append('_method', 'PUT');

        button.disabled = true;
        confirmButton.disabled = true;
        confirmButton.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Memproses...';

        fetch(`/admin/antrian/${dataAntrianEdit.id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(result => {
            if (result.status >= 200 && result.status < 300 && result.body.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasiEditAntrian'))?.hide();
                bootstrap.Modal.getInstance(document.getElementById('modalEditAntrian'))?.hide();
                bootstrap.Toast.getOrCreateInstance(document.getElementById('editAntrianSuccessToast')).show();
                editUpdatePending = false;
                button.disabled = false;
                confirmButton.disabled = false;
                confirmButton.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Ya, Perbarui &amp; Kirim Email';
                return;
            }

            const validationMessage = Object.values(result.body.errors || {}).flat()[0];
            error.innerText = validationMessage || result.body.message || 'Gagal menyimpan perubahan data.';
            error.classList.remove('d-none');
            button.disabled = false;
            confirmButton.disabled = false;
            confirmButton.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Ya, Perbarui &amp; Kirim Email';
        })
        .catch(() => {
            error.innerText = 'Terjadi kesalahan koneksi.';
            error.classList.remove('d-none');
            button.disabled = false;
            confirmButton.disabled = false;
            confirmButton.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Ya, Perbarui &amp; Kirim Email';
        });
    });

    @if(strtolower((string) auth()->user()->role) === 'admin')
        document.getElementById('btnHapusDariEdit').addEventListener('click', function () {
            const modalEdit = bootstrap.Modal.getInstance(document.getElementById('modalEditAntrian'));
            if (modalEdit) {
                modalEdit.hide();
            }

            bukaModalHapus(`{{ url('/admin/hapus') }}/${dataAntrianEdit.id}`, dataAntrianEdit.nomor_antrian, dataAntrianEdit.nama);
        });
    @endif
</script>
@endif

<!-- MODAL KONFIRMASI STATUS SELESAI -->
<div class="modal fade" id="modalKonfirmasiSelesai" tabindex="-1" aria-labelledby="modalSelesaiLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; background-color: #f0fdf4; border: 1px solid #bbf7d0 !important;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 bg-transparent">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 42px; height: 42px; background-color: rgba(34, 197, 94, 0.15); color: #15803d;">
                        <i class="fa fa-check-circle fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-0" style="color: #166534; font-size: 1.1rem;" id="modalSelesaiLabel">Konfirmasi Selesaikan Antrian</h5>
                    </div>
                </div>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="small mb-2 text-dark" style="line-height: 1.6; font-size: 0.95rem;">Apakah antrian ini sudah selesai dilayani?</p>
                <p class="small text-muted mb-0" style="font-size: 0.85rem;">Antrian <strong id="nomorAntrianSelesai" class="text-dark"></strong> akan dikunci dan tidak dapat diubah kembali.</p>
            </div>
            <div class="modal-footer border-0 bg-transparent justify-content-end pb-4 px-4 pt-2 gap-2">
                <button type="button" class="btn fw-bold px-4 py-2 text-secondary bg-white border" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 0.9rem;">Batal</button>
                <button type="button" id="btnKonfirmasiSelesai" class="btn fw-bold px-4 py-2 text-white" style="background-color: #16a34a; border-radius: 8px; font-size: 0.9rem;">
                    <i class="fa fa-check me-1"></i> Ya, Sudah Selesai
                </button>
            </div>
        </div>
    </div>
</div>

@if(strtolower((string) auth()->user()->role) === 'admin')
<!-- MODAL KONFIRMASI HAPUS KUSTOM -->
<div class="modal fade" id="modalKonfirmasiHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; background-color: #fffbeb; border: 1px solid #fde68a !important;">
            
            <div class="modal-header border-0 pb-0 pt-4 px-4 bg-transparent">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 42px; height: 42px; background-color: rgba(245, 158, 11, 0.15); color: #d97706;">
                        <i class="fa fa-triangle-exclamation fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-0" style="color: #92400e; font-size: 1rem;" id="modalHapusLabel">Konfirmasi Hapus Data</h5>
                    </div>
                </div>
            </div>

            <div class="modal-body px-4 py-3">
                <p class="small mb-2" style="color: #b45309 !important; line-height: 1.6; font-size: 0.95rem;">
                    Apakah Anda yakin ingin menghapus antrian ini?
                </p>
                <p class="small text-muted mb-0" style="font-size: 0.85rem;">
                    Antrian atas nama <strong id="namaPemohonHapus" class="text-dark"></strong> dengan nomor <strong id="nomorAntrianHapus" class="text-dark"></strong> akan dihapus permanen dan tidak dapat dikembalikan.
                </p>
            </div>

            <div class="modal-footer border-0 bg-transparent justify-content-end pb-4 px-4 pt-2 gap-2">
                <button type="button" class="btn fw-bold px-4 py-2 text-secondary bg-white border" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 0.9rem;">
                    Batal
                </button>
                <form id="formHapusAction" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn fw-bold px-4 py-2 text-white" style="background-color: #dc3545; border-radius: 8px; font-size: 0.9rem;">
                        <i class="fa fa-trash me-1"></i> Ya, Hapus
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif

<script>
    let statusSelesaiData = null;

    function bukaModalSelesai(id, nomorAntrian, namaPemohon) {
        statusSelesaiData = { id, nomorAntrian, namaPemohon };
        document.getElementById('nomorAntrianSelesai').innerText = nomorAntrian;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalKonfirmasiSelesai')).show();
    }

    document.getElementById('btnKonfirmasiSelesai').addEventListener('click', function () {
        if (!statusSelesaiData) return;

        const button = this;
        button.disabled = true;
        button.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...';

        fetch(`/admin/status/${statusSelesaiData.id}/Selesai`)
            .then(response => {
                if (response.ok || response.redirected) {
                    window.location.reload();
                    return;
                }
                throw new Error('Gagal menyelesaikan antrian');
            })
            .catch(error => {
                console.error(error);
                button.disabled = false;
                button.innerHTML = '<i class="fa fa-check me-1"></i> Ya, Sudah Selesai';
                alert('Gagal mengubah status antrian.');
            });
    });

    @if(strtolower((string) auth()->user()->role) === 'admin')
        function bukaModalHapus(urlDelete, nomorAntrian, namaPemohon) {
            document.getElementById('nomorAntrianHapus').innerText = nomorAntrian;
            document.getElementById('namaPemohonHapus').innerText = namaPemohon;
            document.getElementById('formHapusAction').setAttribute('action', urlDelete);

            var modalHapus = new bootstrap.Modal(document.getElementById('modalKonfirmasiHapus'));
            modalHapus.show();
        }
    @endif
</script>
@endsection