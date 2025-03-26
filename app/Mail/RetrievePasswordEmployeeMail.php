<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Employee;

class RetrievePasswordEmployeeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($employee, $password)
    {
        $this->employee = $employee;
        $this->employee->newpassword = str_replace('/', '(slash)', $this->employee->newpassword);
        $this->password = $password;
    }

    public function build()
    {
        if(env('MAIL_USERNAME'))
            return $this->view('mails.mailretrievepasswordemployee')->subject('Recuperacion de Credenciales de Ingreso a la plataforma de empleados '.env("APP_NAME"))
                                                                    ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'));
//                                                                    ->from(env('MAIL_USERNAME'), env('APP_NAME'));
        else
            return $this->view('mails.mailretrievepasswordemployee')->subject('Recuperacion de Credenciales de Ingreso a la plataforma de empleados '.env("APP_NAME"))
                                                                    ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'));
//                                                                    ->from(config('mail.username'), env('APP_NAME'));
    }
}
