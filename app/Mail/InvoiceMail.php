<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Traits\DocumentTrait;
use App\Document;
use App\Customer;
use App\Company;
use App\User;

class InvoiceMail extends Mailable
{
    use DocumentTrait, Queueable, SerializesModels;

    public $invoice;
    public $customer;
    public $company;
    public $GuardarEn;
    public $PDFAlternativo;
    public $filename;
    public $showAcceptRejectButtons;
    public $request_in;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice,  $customer,  $company, $GuardarEn = FALSE, $PDFAlternativo = FALSE, $filename = FALSE, $showAcceptRejectButtons = FALSE, $request_in = FALSE)
    {
        $this->invoice = $invoice;
        $this->customer = $customer;
        $this->company  = $company;
        $this->filename = $filename;
        $this->showAcceptRejectButtons = $showAcceptRejectButtons;
        $this->request_in = $request_in;

        $this->user = User::where('id', $company->user_id)->firstOrFail();
        if($PDFAlternativo){
            $this->PDFAlternativo = TRUE;
            if($GuardarEn)
                $file = fopen($GuardarEn."\\PDF-{$this->invoice[0]->prefix}{$this->invoice[0]->number}.pdf", "w");
            else
                $file = fopen(storage_path("app/public/{$this->company->identification_number}/PDF-{$this->invoice[0]->prefix}{$this->invoice[0]->number}.pdf"), "w");
            fwrite($file, base64_decode($PDFAlternativo));
            fclose($file);
        }
        else
            $this->PDFAlternativo = FALSE;
    }

    public function build()
    {
        if($this->GuardarEn)
            if($this->PDFAlternativo)
                if(env('MAIL_USERNAME') and $this->user->validate_mail_server() == false){
                    if($this->filename)
                        $nameZIP = $this->zipEmail($this->GuardarEn."\\{$this->filename}.xml", $this->GuardarEn."\\PDF-{$this->invoice[0]->prefix}{$this->invoice[0]->number}.pdf");
                    else
                        $nameZIP = $this->zipEmail($this->GuardarEn."\\{$this->filename}.xml", $this->GuardarEn."\\PDF-{$this->invoice[0]->prefix}{$this->invoice[0]->number}.pdf");
                    return $this->view('mails.mail')->subject("{$this->company->identification_number};{$this->company->user->name};{$this->invoice[0]->prefix}{$this->invoice[0]->number};{$this->invoice[0]->type_document->code};{$this->company->user->name}")
                                                    ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                    ->from(env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                    ->from(env('MAIL_USERNAME'))
                                                    ->attach($nameZIP);
                }
                else{
                    if($this->filename)
                        $nameZIP = $this->zipEmail($this->GuardarEn."\\{$this->filename}.xml", $this->GuardarEn."\\PDF-{$this->invoice[0]->prefix}{$this->invoice[0]->number}.pdf");
                    else
                        $nameZIP = $this->zipEmail($this->GuardarEn."\\{$this->filename}.xml", $this->GuardarEn."\\PDF-{$this->invoice[0]->prefix}{$this->invoice[0]->number}.pdf");
                    return $this->view('mails.mail')->subject("{$this->company->identification_number};{$this->company->user->name};{$this->invoice[0]->prefix}{$this->invoice[0]->number};{$this->invoice[0]->type_document->code};{$this->company->user->name}")
                                                    ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                    ->from(env('MAIL_FROM_ADDRESS', config('mail.username')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                    ->from(config('mail.username'))
                                                    ->attach($nameZIP);
                }
            else
                if(env('MAIL_USERNAME') and $this->user->validate_mail_server() == false){
                    if($this->filename)
                        $nameZIP = $this->zipEmail($this->GuardarEn."\\{$this->filename}.xml", $this->GuardarEn."\\".$this->invoice[0]->pdf);
                    else
                        $nameZIP = $this->zipEmail($this->GuardarEn."\\{$this->filename}.xml", $this->GuardarEn."\\".$this->invoice[0]->pdf);
                    return $this->view('mails.mail')->subject("{$this->company->identification_number};{$this->company->user->name};{$this->invoice[0]->prefix}{$this->invoice[0]->number};{$this->invoice[0]->type_document->code};{$this->company->user->name}")
                                                    ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                    ->from(env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                    ->from(env('MAIL_USERNAME'))
                                                    ->attach($nameZIP);
                }
                else{
                    if($this->filename)
                        $nameZIP = $this->zipEmail($this->GuardarEn."\\{$this->filename}.xml", $this->GuardarEn."\\{$this->invoice[0]->pdf}");
                    else
                        $nameZIP = $this->zipEmail($this->GuardarEn."\\{$this->filename}.xml", $this->GuardarEn."\\{$this->invoice[0]->pdf}");
                    return $this->view('mails.mail')->subject("{$this->company->identification_number};{$this->company->user->name};{$this->invoice[0]->prefix}{$this->invoice[0]->number};{$this->invoice[0]->type_document->code};{$this->company->user->name}")
                                                    ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                    ->from(env('MAIL_FROM_ADDRESS', config('mail.username')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                    ->from(config('mail.username'))
                                                    ->attach($nameZIP);
                }
        else
            if($this->PDFAlternativo)
                if(env('MAIL_USERNAME') and $this->user->validate_mail_server() == false){
                    if($this->filename)
                        $nameZIP = $this->zipEmail(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/PDF-{$this->invoice[0]->prefix}{$this->invoice[0]->number}.pdf"));
                    else
                        $nameZIP = $this->zipEmail(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/PDF-{$this->invoice[0]->prefix}{$this->invoice[0]->number}.pdf"));
                    return $this->view('mails.mail')->subject("{$this->company->identification_number};{$this->company->user->name};{$this->invoice[0]->prefix}{$this->invoice[0]->number};{$this->invoice[0]->type_document->code};{$this->company->user->name}")
                                                    ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                    ->from(env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                    ->from(env('MAIL_USERNAME'))
                                                    ->attach($nameZIP);
                }
                else{
                    if($this->filename)
                        $nameZIP = $this->zipEmail(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/PDF-{$this->invoice[0]->prefix}{$this->invoice[0]->number}.pdf"));
                    else
                        $nameZIP = $this->zipEmail(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/PDF-{$this->invoice[0]->prefix}{$this->invoice[0]->number}.pdf"));
                    return $this->view('mails.mail')->subject("{$this->company->identification_number};{$this->company->user->name};{$this->invoice[0]->prefix}{$this->invoice[0]->number};{$this->invoice[0]->type_document->code};{$this->company->user->name}")
                                                    ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                    ->from(env('MAIL_FROM_ADDRESS', config('mail.username')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                    ->from(config('mail.username'))
                                                    ->attach($nameZIP);
                }
            else{
                if(env('MAIL_USERNAME') and $this->user->validate_mail_server() == false){
                    if($this->filename)
                        $nameZIP = $this->zipEmail(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/{$this->invoice[0]->pdf}"));
                    else
                        $nameZIP = $this->zipEmail(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/{$this->invoice[0]->pdf}"));
                    return $this->view('mails.mail')->subject("{$this->company->identification_number};{$this->company->user->name};{$this->invoice[0]->prefix}{$this->invoice[0]->number};{$this->invoice[0]->type_document->code};{$this->company->user->name}")
                                                    ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                    ->from(env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                    ->from(env('MAIL_USERNAME'))
                                                    ->attach($nameZIP);
                }
                else{
//                    \Log::debug(storage_path("app/public/{$this->company->identification_number}/{$this->invoice[0]->pdf}"));
                    if($this->filename)
                        $nameZIP = $this->zipEmail(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/{$this->invoice[0]->pdf}"));
                    else
                        $nameZIP = $this->zipEmail(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/{$this->invoice[0]->pdf}"));
                    return $this->view('mails.mail')->subject("{$this->company->identification_number};{$this->company->user->name};{$this->invoice[0]->prefix}{$this->invoice[0]->number};{$this->invoice[0]->type_document->code};{$this->company->user->name}")
                                                    ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                    ->from(env('MAIL_FROM_ADDRESS', config('mail.username')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                    ->from(config('mail.username'))
                                                    ->attach($nameZIP);
                }
            }
    }
}
