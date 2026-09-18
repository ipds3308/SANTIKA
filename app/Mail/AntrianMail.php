<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class AntrianMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftaran;
    public bool $isPerubahan;

    public function __construct($pendaftaran, bool $isPerubahan = false)
    {
        $this->pendaftaran = $pendaftaran;
        $this->isPerubahan = $isPerubahan;
    }

    public function build()
    {
        // Generate PDF langsung dari view cetak tiket yang sudah ada
        $pdf = Pdf::loadView('pages.cetak', ['data' => $this->pendaftaran]);

        $subject = $this->isPerubahan
            ? '[Perubahan Tiket] E-Tiket SANTIKA BPS Kabupaten Magelang - ' . $this->pendaftaran->nomor_antrian
            : 'E-Tiket Resmi Nomor Antrian SANTIKA BPS - ' . $this->pendaftaran->nomor_antrian;

        return $this->subject($subject)
                    ->view('emails.antrian_notif') // Body email teks biasa yang bersih
                    ->attachData($pdf->output(), 'Tiket-Antrian-' . $this->pendaftaran->nomor_antrian . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}