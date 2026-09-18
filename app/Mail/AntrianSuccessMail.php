<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AntrianSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftaran; // Variabel untuk menyimpan data pendaftar

    public function __construct($pendaftaran)
    {
        $this->pendaftaran = $pendaftaran;
    }

    public function build()
    {
        return $this->subject('Pendaftaran Berhasil! Nomor Antrian: ' . $this->pendaftaran->nomor_antrian)
                    ->view('emails.antrian_success');
    }
}