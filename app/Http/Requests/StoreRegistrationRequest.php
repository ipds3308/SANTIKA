<?php

namespace App\Http\Requests;

use App\Services\IndonesianPublicHolidayCalendar;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationRequest extends FormRequest
{
    /** @var list<string> */
    private array $fieldsWithMarkup = [];

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        foreach ($input as $field => &$value) {
            $this->sanitizeValue($value, (string) $field);
        }
        unset($value);

        $this->merge($input);
    }

    private function sanitizeValue(mixed &$value, string $field): void
    {
        if (is_string($value)) {
            $sanitizedValue = strip_tags($value);

            if ($sanitizedValue !== $value) {
                $this->fieldsWithMarkup[] = $field;
                $value = $sanitizedValue;
            }

            return;
        }

        if (is_array($value)) {
            foreach ($value as &$nestedValue) {
                $this->sanitizeValue($nestedValue, $field);
            }
            unset($nestedValue);
        }
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach (array_unique($this->fieldsWithMarkup) as $field) {
                $validator->errors()->add($field, 'Tag HTML tidak diperbolehkan.');
            }
        }];
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(IndonesianPublicHolidayCalendar $holidayCalendar): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{M}\s.,\']+$/u'],
            'email' => ['required', 'email'],
            'no_wa' => ['required', 'string', 'regex:/^(08|628)[0-9]{7,13}$/'],
            'alamat' => ['required', 'string', 'regex:/^[\p{L}\p{M}\d\s.,\'\/#()-]+$/u'],
            'jenis_layanan' => ['required', 'string', Rule::in([
                'Konsultasi Statistik',
                'Konsultasi DTSEN',
                'Permintaan Data',
                'Rekomendasi Kegiatan Statistik',
                'Pengaduan',
                'Lainnya',
            ])],
            'keterangan_lainnya' => ['nullable', 'string', 'max:50', 'regex:/^[\p{L}\p{M}\d\s.,\'\/():-]*$/u', 'required_if:jenis_layanan,Lainnya'],
            'tanggal_kunjungan' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                function (string $attribute, mixed $value, \Closure $fail) use ($holidayCalendar): void {
                    try {
                        $visitDate = Carbon::createFromFormat('Y-m-d', $value);
                    } catch (\Throwable) {
                        return;
                    }

                    if ($visitDate->isWeekend()) {
                        $fail('Tanggal kunjungan hanya tersedia pada hari Senin sampai Jumat.');

                        return;
                    }

                    if ($holidayName = $holidayCalendar->holidayName($value)) {
                        $fail("Tanggal kunjungan merupakan hari libur nasional: {$holidayName}.");
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, koma, dan apostrof.',
            'alamat.regex' => 'Alamat mengandung karakter yang tidak diizinkan.',
            'no_wa.regex' => 'Nomor WhatsApp harus diawali dengan 08 atau 628, dan panjang 10 s.d. 15 digit.',
            'jenis_layanan.required' => 'Silakan pilih jenis layanan atau keperluan terlebih dahulu.',
            'keterangan_lainnya.regex' => 'Keterangan layanan mengandung karakter yang tidak diizinkan.',
            'keterangan_lainnya.required_if' => 'Silakan isi keterangan untuk layanan Lainnya.',
            'tanggal_kunjungan.after_or_equal' => 'Tanggal kunjungan tidak boleh kurang dari hari ini.',
        ];
    }
}