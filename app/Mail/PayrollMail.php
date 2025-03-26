<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Traits\DocumentTrait;
use App\DocumentPayroll;
use App\Employee;
use App\Company;
use App\User;

class PayrollMail extends Mailable
{
    use DocumentTrait, Queueable, SerializesModels;

    public $payroll;
    public $employee;
    public $company;
    public $PDFAlternativo;
    public $filename;
    public $request_in;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payroll,  $employee,  $company, $PDFAlternativo = FALSE, $filename = FALSE, $request_in = FALSE)
    {
        $this->payroll = $payroll;
        $this->employee = $employee;
        $this->company  = $company;
        $this->filename = $filename;
        $this->request_in = $request_in;

        $this->user = User::where('id', $company->user_id)->firstOrFail();
        if($PDFAlternativo){
            $this->PDFAlternativo = TRUE;
            $file = fopen(storage_path("app/public/{$this->company->identification_number}/PDF-{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive}.pdf"), "w");
            fwrite($file, base64_decode($PDFAlternativo));
            fclose($file);
        }
        else
            $this->PDFAlternativo = FALSE;
    }

    public function build()
    {
        if($this->PDFAlternativo)
            if(env('MAIL_USERNAME') and $this->user->validate_mail_server() == false){
                if($this->filename)
                    $nameZIP = $this->zipEmailPayroll(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/PDF-{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive}.pdf"));
                else
                    $nameZIP = $this->zipEmailPayroll(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/PDF-{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive}.pdf"));
                return $this->view('mails.mail_employee')->subject("Nomina Electronica: {$this->company->identification_number};{$this->company->user->name};{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive};{$this->payroll[0]->type_document->code};{$this->company->user->name}")
                                                  ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                ->from(env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                ->from(env('MAIL_USERNAME'))
                                                ->attach($nameZIP);
            }
            else{
                if($this->filename)
                    $nameZIP = $this->zipEmailPayroll(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/PDF-{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive}.pdf"));
                else
                    $nameZIP = $this->zipEmailPayroll(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/PDF-{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive}.pdf"));
                return $this->view('mails.mail_employee')->subject("Nomina Electronica: {$this->company->identification_number};{$this->company->user->name};{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive};{$this->payroll[0]->type_document->code};{$this->company->user->name}")
                                                  ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                ->from(env('MAIL_FROM_ADDRESS', config('mail.username')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                ->from(config('mail.username'))
                                                ->attach($nameZIP);
            }
        else{
            if(env('MAIL_USERNAME') and $this->user->validate_mail_server() == false){
                if($this->filename)
                    $nameZIP = $this->zipEmailPayroll(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/{$this->payroll[0]->pdf}"));
                else
                    $nameZIP = $this->zipEmailPayroll(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/{$this->payroll[0]->pdf}"));
                return $this->view('mails.mail_employee')->subject("Nomina Electronica: {$this->company->identification_number};{$this->company->user->name};{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive};{$this->payroll[0]->type_document->code};{$this->company->user->name}")
                                                  ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                ->from(env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                ->from(env('MAIL_USERNAME'))
                                                ->attach($nameZIP);
            }
            else{
                if($this->filename)
                    $nameZIP = $this->zipEmailPayroll(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/FES-{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive}.pdf"));
                else
                    $nameZIP = $this->zipEmailPayroll(storage_path("app/public/{$this->company->identification_number}/{$this->filename}.xml"), storage_path("app/public/{$this->company->identification_number}/FES-{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive}.pdf"));
                return $this->view('mails.mail_employee')->subject("Nomina Electronica: {$this->company->identification_number};{$this->company->user->name};{$this->payroll[0]->prefix}{$this->payroll[0]->consecutive};{$this->payroll[0]->type_document->code};{$this->company->user->name}")
                                                  ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'))
//                                                ->from(env('MAIL_FROM_ADDRESS', config('mail.username')), env('MAIL_FROM_NAME', env('APP_NAME')))
//                                                ->from(config('mail.username'))
                                                ->attach($nameZIP);
            }
        }
    }
}
