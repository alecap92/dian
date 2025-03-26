<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use App\DocumentPayroll;
use Mpdf\Mpdf;
use Storage;
use App\User;
use App\Traits\DocumentTrait;

class DocumentPayrollEmail extends Mailable
{
    use Queueable, SerializesModels, DocumentTrait;

    /**
     * User
     * @var \App\User
     */
    public $company;

    /**
     * DocumentPayroll
     * @var \App\DocumentPayroll
     */
    public $document;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $company, DocumentPayroll $document) {
        $this->company = $company;
        $this->document = $document;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {

        $path_file = storage_path("app/public/{$this->document->identification_number}/{$this->document->pdf}");
        $path_file_xml = storage_path("app/public/{$this->document->identification_number}/{$this->document->xml}");

        return $this->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
                    ->markdown('emails.send.graphicRepresentationPayroll')
                    ->subject("{$this->company->name} - {$this->document->type_document->name} {$this->document->consecutive}")
                    ->attach($path_file_xml, [
                        'as' => $this->document->xml,
                        'mime' => 'application/xml',
                    ])
                    ->attach($path_file, [
                        'as' => $this->document->pdf,
                        'mime' => 'application/pdf',
                    ]);
    }
}
