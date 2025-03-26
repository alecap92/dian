<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Employee;

class PasswordEmployeeMail extends Mailable
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
        $this->password = $password;
    }

    public function build()
    {
        if(env('MAIL_USERNAME'))
            return $this->view('mails.mailpasswordemployee')->subject('Credenciales de Ingreso de consulta de empleados a la plataforma '.env("APP_NAME"))
                                                            ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'));
//                                                            ->from(env('MAIL_USERNAME'), env('APP_NAME'));
        else
            return $this->view('mails.mailpasswordemployee')->subject('Credenciales de Ingreso de consulta de empleados a la plataforma '.env("APP_NAME"))
                                                            ->from(\Config::get('mail.from.address'), \Config::get('mail.from.name'));
//                                                            ->from(config('mail.username'), env('APP_NAME'));
    }
}
