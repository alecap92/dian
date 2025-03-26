<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\DocumentTrait;
use App\Mail\InvoiceMail;
use App\Mail\PayrollMail;
use App\Mail\PasswordCustomerMail;
use App\User;
use App\Customer;
use App\Employee;
use App\Document;
use App\DocumentPayroll;
use App\Company;
use App\Http\Requests\Api\SendEmailRequest;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Http\Requests\Api\SendPayrollEmailRequest;
use App\Mail\DocumentPayrollEmail;
use Carbon\Carbon;

class SendEmailController extends Controller
{
    use DocumentTrait;

    /**
     * SendEmail.
     *
     *
     * @return array
     */

    public function SendEmail(SendEmailRequest $request, $GuardarEn = FALSE)
    {
        // User
        $ShowAcceptRejectButtons = FALSE;
        $user = auth()->user();
        $smtp_parameters = collect($request->smtp_parameters);
        if(isset($request->smtp_parameters)){
            \Config::set('mail.host', $smtp_parameters->toArray()['host']);
            \Config::set('mail.port', $smtp_parameters->toArray()['port']);
            \Config::set('mail.username', $smtp_parameters->toArray()['username']);
            \Config::set('mail.password', $smtp_parameters->toArray()['password']);
            \Config::set('mail.encryption', $smtp_parameters->toArray()['encryption']);
        }
        else
            if($user->validate_mail_server()){
                \Config::set('mail.host', $user->mail_host);
                \Config::set('mail.port', $user->mail_port);
                \Config::set('mail.username', $user->mail_username);
                \Config::set('mail.password', $user->mail_password);
                \Config::set('mail.encryption', $user->mail_encryption);
            }

        // User company
        $company = $user->company;

        $document = Document::where('identification_number', '=', $company->identification_number)
                            ->where('prefix', '=', $request->prefix)
                            ->where('number', '=', $request->number)
                            ->where('state_document_id', '=', 1)->get();
        if(sizeof($document) == 0)
            return [
                'message' => "Documento {$request->prefix}-{$request->number} no existe en la base de datos.",
                'success' => FALSE,
            ];

        if(isset($request->showacceptrejectbuttons))
            $ShowAcceptRejectButtons = $request->showacceptrejectbuttons;
        else
            $ShowAcceptRejectButtons = FALSE;

        $customer = Customer::findOrFail($document[0]->customer);
        if($request->alternate_email)
            $email = $request->alternate_email;
        else
            $email = $customer->email;

        if($document[0]->type_document_id == 1)
            $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaFE-".$document[0]->prefix.$document[0]->number.".xml"));
        else
            if($document[0]->type_document_id == 4)
                $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaNC-".$document[0]->prefix.$document[0]->number.".xml"));
            else
                if ($document[0]->type_document_id == 11)
                    $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaDS-".$document[0]->prefix.$document[0]->number.".xml"));
                else
                    if ($document[0]->type_document_id == 15)
                        $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaPOS-".$document[0]->prefix.$document[0]->number.".xml"));
                    else
                        if ($document[0]->type_document_id == 19)
                            $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaTTR-".$document[0]->prefix.$document[0]->number.".xml"));
                        else
                            if ($document[0]->type_document_id == 24)
                                $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaSRV-".$document[0]->prefix.$document[0]->number.".xml"));
                            else
                               $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaND-".$document[0]->prefix.$document[0]->number.".xml"));

        $filename = str_replace('pos', 'ad', str_replace('ttr', 'ad', str_replace('srv', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $this->getTag($rptafe, 'XmlFileName')->nodeValue))))));
        try{
            if(!$request->only_send_to_cc_list){
                if ($GuardarEn){
                    if($request->base64graphicrepresentation)
                        Mail::to($email)->send(new InvoiceMail($document, $customer, $company, $GuardarEn, $request->base64graphicrepresentation, $filename, $ShowAcceptRejectButtons, $request));
                    else
                        Mail::to($email)->send(new InvoiceMail($document, $customer, $company, $GuardarEn, FALSE, $filename, $ShowAcceptRejectButtons, $request));
                }
                else{
                    if($request->base64graphicrepresentation)
                        Mail::to($email)->send(new InvoiceMail($document, $customer, $company, FALSE, $request->base64graphicrepresentation, $filename, $ShowAcceptRejectButtons, $request));
                    else
                        Mail::to($email)->send(new InvoiceMail($document, $customer, $company, FALSE, FALSE, $filename, $ShowAcceptRejectButtons, $request));
                }
            }
            if($request->email_cc_list){
                foreach($request->email_cc_list as $email)
                    if($request->base64graphicrepresentation)
                        Mail::to($email)->send(new InvoiceMail($document, $customer, $company, FALSE, $request->base64graphicrepresentation, $filename, FALSE, $request));
                    else
                        Mail::to($email)->send(new InvoiceMail($document, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
            }
            $document[0]->send_email_success = 1;
            if(is_null($document[0]->send_email_date_time))
                $document[0]->send_email_date_time = Carbon::now()->format('Y-m-d H:i');
            $document[0]->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return [
            'message' => 'Envio realizado con éxito',
            'success' => TRUE,
        ];
    }

    /**
     * SendEmail Customer.
     *
     *
     * @return view
     */

    public function SendEmailCustomer(SendEmailRequest $request, $ShowView = 'YES')
    {
        $company = Company::where('identification_number', '=', $request->company_idnumber)->first();
        // User
        $user = User::where('id', $company->user_id)->firstOrFail();
        $smtp_parameters = collect($request->smtp_parameters);
        if(isset($request->smtp_parameters)){
            \Config::set('mail.host', $smtp_parameters->toArray()['host']);
            \Config::set('mail.port', $smtp_parameters->toArray()['port']);
            \Config::set('mail.username', $smtp_parameters->toArray()['username']);
            \Config::set('mail.password', $smtp_parameters->toArray()['password']);
            \Config::set('mail.encryption', $smtp_parameters->toArray()['encryption']);
        }
        else
            if($user->validate_mail_server()){
                \Config::set('mail.host', $user->mail_host);
                \Config::set('mail.port', $user->mail_port);
                \Config::set('mail.username', $user->mail_username);
                \Config::set('mail.password', $user->mail_password);
                \Config::set('mail.encryption', $user->mail_encryption);
            }

        if(empty($company))
            return view('customerloginmensaje', ['titulo' => 'Error al realizar el envio.',
                                                'mensaje' => 'Esta compañia no existe en la base de datos.']);

        $document = Document::where('identification_number', '=', $company->identification_number)
                            ->where('prefix', '=', $request->prefix)
                            ->where('number', '=', $request->number)
                            ->where('state_document_id', '=', 1)->get();
        if(sizeof($document) == 0)
            return view('customerloginmensaje', ['titulo' => 'Error al realizar el envio.',
                                                'mensaje' => 'Este documento no existe en la base de datos.']);

        $customer = Customer::findOrFail($document[0]->customer);
        if($document[0]->type_document_id == 1)
            $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaFE-".$document[0]->prefix.$document[0]->number.".xml"));
        else
            if($document[0]->type_document_id == 4)
                $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaNC-".$document[0]->prefix.$document[0]->number.".xml"));
            else
                if ($document[0]->type_document_id == 11)
                    $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaDS-".$document[0]->prefix.$document[0]->number.".xml"));
                else
                    $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaND-".$document[0]->prefix.$document[0]->number.".xml"));

        if(isset($this->getTag($rptafe, 'ZipKey')->nodeValue))
            $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaZIP-".$this->getTag($rptafe, 'ZipKey')->nodeValue.".xml"));

        $filename = str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $this->getTag($rptafe, 'XmlFileName')->nodeValue)));
//        return $user->email;
        if($filename <> ''){
            if(isset($request->customerEmail))
                Mail::to($request->customerEmail)->send(new InvoiceMail($document, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
            else
                Mail::to($customer->email)->send(new InvoiceMail($document, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
            Mail::to($user->email)->send(new InvoiceMail($document, $customer, $company, FALSE, FALSE, $filename, FALSE, $request));
        }
        if($ShowView == 'YES')
//            return redirect('/homecustomers');
            if($filename <> '')
                return view('customerloginmensaje', ['titulo' => 'Envio realizado con exito.',
                                                     'mensaje' => 'El Documento se envio satisfactoriamente.']);
            else
                return view('customerloginmensaje', ['titulo' => 'Error al realizar el envio.',
                                                     'mensaje' => 'El Documento no se pudo enviar.']);
        else
            if($filename <> '')
                return [
                    'message' => 'Envio realizado con éxito',
                    'success' => TRUE,
                ];
            else
                return [
                    'message' => 'Error al realizar el envio',
                    'success' => FALSE,
                ];

    }

    /**
     * SendEmail Employee.
     *
     *
     * @return view
     */

    public function SendEmailEmployee(SendEmailRequest $request, $ShowView = 'YES')
    {
        $company = Company::where('identification_number', '=', $request->company_idnumber)->first();
        // User
        $user = User::where('id', $company->user_id)->firstOrFail();
        $smtp_parameters = collect($request->smtp_parameters);
        if(isset($request->smtp_parameters)){
            \Config::set('mail.host', $smtp_parameters->toArray()['host']);
            \Config::set('mail.port', $smtp_parameters->toArray()['port']);
            \Config::set('mail.username', $smtp_parameters->toArray()['username']);
            \Config::set('mail.password', $smtp_parameters->toArray()['password']);
            \Config::set('mail.encryption', $smtp_parameters->toArray()['encryption']);
        }
        else
            if($user->validate_mail_server()){
                \Config::set('mail.host', $user->mail_host);
                \Config::set('mail.port', $user->mail_port);
                \Config::set('mail.username', $user->mail_username);
                \Config::set('mail.password', $user->mail_password);
                \Config::set('mail.encryption', $user->mail_encryption);
            }

        if(empty($company))
            return view('customerloginmensaje', ['titulo' => 'Error al realizar el envio.',
                                                'mensaje' => 'Esta compañia no existe en la base de datos.']);

        $document = DocumentPayroll::where('identification_number', '=', $company->identification_number)
                                   ->where('prefix', '=', $request->prefix)
                                   ->where('consecutive', '=', $request->number)
                                   ->where('state_document_id', '=', 1)->get();
        if(sizeof($document) == 0)
            return view('customerloginmensaje', ['titulo' => 'Error al realizar el envio.',
                                                'mensaje' => 'Este documento no existe en la base de datos.']);

        $employee = Employee::findOrFail($document[0]->employee_id);
        if($document[0]->type_document_id == 9)
            $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaNI-".$document[0]->prefix.$document[0]->consecutive.".xml"));
        else
            if($document[0]->type_document_id == 10)
                $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaNA-".$document[0]->prefix.$document[0]->consecutive.".xml"));

        if(isset($this->getTag($rptafe, 'ZipKey')->nodeValue))
            $rptafe = file_get_contents(storage_path("app/public/{$company->identification_number}/"."RptaZIP-".$this->getTag($rptafe, 'ZipKey')->nodeValue.".xml"));

        $filename = str_replace('na', 'ad', (str_replace('ni', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $this->getTag($rptafe, 'XmlFileName')->nodeValue))))));
//        return $user->email;
        if($filename <> ''){
            if(!$request->only_send_to_cc_list)
                Mail::to($employee->email)->send(new PayrollMail($document, $employee, $company, FALSE, $filename, $request));
            Mail::to($user->email)->send(new PayrollMail($document, $employee, $company, FALSE, $filename, $request));
            if($request->email_cc_list){
                foreach($request->email_cc_list as $email)
                    Mail::to($email)->send(new PayrollMail($document, $employee, $company, FALSE, $filename, $request));
            }
        }
        if($ShowView == 'YES')
//            return redirect('/homecustomers');
            if($filename <> '')
                return view('customerloginmensaje', ['titulo' => 'Envio realizado con exito.',
                                                     'mensaje' => 'El Documento se envio satisfactoriamente.']);
            else
                return view('customerloginmensaje', ['titulo' => 'Error al realizar el envio.',
                                                     'mensaje' => 'El Documento no se pudo enviar.']);
        else
            if($filename <> '')
                return [
                    'message' => 'Envio realizado con éxito',
                    'success' => TRUE,
                ];
            else
                return [
                    'message' => 'Error al realizar el envio',
                    'success' => FALSE,
                ];
    }

    /**
     * SendEmail Password Customer.
     *
     *
     * @return view
     */

    public function SendEmailPasswordCustomer(Request $request)
    {
        $customer = Customer::findOrFail($request->identification_number);
        Mail::to($customer->email)->send(new PasswordCustomerMail($customer));
        return [
            'message' => 'Correo electronico para credenciales, enviado con exito.',
            'success' => 'true',
        ];
//        return view('customerloginmensaje', ['titulo' => 'Envio realizado con exito.',
//                                            'mensaje' => 'El Documento se envio satisfactoriamente.']);
    }


    /**
     * Envio de correo nomina
     *
     * @param  SendPayrollEmailRequest $request
     * @return void
    */
    public function sendEmailDocumentPayroll(SendPayrollEmailRequest $request)
    {

        try {
            $user = auth()->user();
            $smtp_parameters = collect($request->smtp_parameters);
            if(isset($request->smtp_parameters)){
                \Config::set('mail.host', $smtp_parameters->toArray()['host']);
                \Config::set('mail.port', $smtp_parameters->toArray()['port']);
                \Config::set('mail.username', $smtp_parameters->toArray()['username']);
                \Config::set('mail.password', $smtp_parameters->toArray()['password']);
                \Config::set('mail.encryption', $smtp_parameters->toArray()['encryption']);
            }
            else
                if($user->validate_mail_server()){
                    \Config::set('mail.host', $user->mail_host);
                    \Config::set('mail.port', $user->mail_port);
                    \Config::set('mail.username', $user->mail_username);
                    \Config::set('mail.password', $user->mail_password);
                    \Config::set('mail.encryption', $user->mail_encryption);
                }

            $company = $user->company;
            $email = $request->email;
            $document = DocumentPayroll::whereExistRecord($company->identification_number, $request->prefix, $request->consecutive)->first();

            if(!$document)
            {
                return [
                    'success' => false,
                    'message' => "Nómina {$request->prefix}-{$request->consecutive} no existe en la base de datos.",
                ];
            }

            Mail::to($email)->send(new DocumentPayrollEmail($user, $document));

            return [
                'success' => true,
                'message' => 'El correo fue enviado satisfactoriamente',
            ];

        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => "Ocurrió un error inesperado: {$e->getCode()} - {$e->getMessage()} - {$e->getFile()}  - {$e->getLine()}"
            ];

        }

    }

}
