<?php

namespace App\Http\Controllers;

use App\Company;
use App\Customer;
use App\Document;
use App\User;
use App\Mail\InvoiceMail;
use App\Mail\PasswordCustomerMail;
use App\Mail\RetrievePasswordCustomerMail;
use App\Mail\RetrievePasswordSellerMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\DocumentTrait;

class CustomerLoginController extends Controller
{
    use DocumentTrait;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the customer login form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function ShowLoginForm($company_idnumber, $customer_idnumber)
    {
        $company = Company::where('identification_number', '=', $company_idnumber)->get();
        $customer = Customer::where('identification_number', '=', $customer_idnumber)->get();
        if (count($company) > 0 && count($customer) > 0)
            return view('customerlogin', compact('company_idnumber', 'customer_idnumber'));
        else
            return view('customerloginmensaje', ['titulo' => 'Error en el ingreso para adquirientes', 'mensaje' => 'Los datos de emisor y/o adquiriente no se encuentran registrados.']);
    }

    protected function CustomerPassword(Request $request, $company_idnumber, $customer_idnumber)
    {
        return view('resetcustomerpassword', compact('request', 'company_idnumber', 'customer_idnumber'));
    }

    protected function ResetCustomerPassword(Request $request, $company_idnumber, $customer_idnumber)
    {

        $rules = [
            'password' => [
                'required',
                'min:5',
            ],
            'password_confirmation' => [
                'required',
                'min:5',
                'igual_a:'.$request->password,
            ],
        ];
        $this->validate($request, $rules);

        $customer = Customer::where('identification_number', '=', $customer_idnumber)->get()->first();
        $customer->password = bcrypt($request->password);
        $customer->save();
        Mail::to($customer->email)->send(new PasswordCustomerMail($customer, $request->password));
        return view('customerloginmensaje', ['titulo' => 'Actualizacion de password', 'mensaje' => 'El password ha sido actualizado satisfactoriamente.']);
    }

    protected function RetrievePassword($customer_idnumber, $mostrarvista = 'YES')
    {
        $customer = Customer::where('identification_number', '=', $customer_idnumber)->get()->first();
        $password = \Str::random(6);
        $customer->newpassword = bcrypt($password);
        $customer->save();
        Mail::to($customer->email)->send(new RetrievePasswordCustomerMail($customer, $password));
        if($mostrarvista == 'YES')
            return view('customerloginmensaje', ['titulo' => 'Solicitud de nuevo password', 'mensaje' => 'Se ha enviado un mensaje de correo electronico a la direccion '.$customer->email.', debe confirmar este mensaje para que el cambio de contraseña se haga efectivo.']);
        else
            return [
                'success' => true,
                'password' => $password,
        ];
    }

    protected function AcceptRetrievePassword($customer_idnumber, $hash)
    {
        $customer = Customer::where('identification_number', '=', $customer_idnumber)->get()->first();
        $hash = str_replace('(slash)', '/', substr($hash, 0, strlen($hash) - 1));
        if($hash == $customer->newpassword)
        {
            $customer->password = $customer->newpassword;
            $customer->newpassword = NULL;
            $customer->save();
            return view('customerloginmensaje', ['titulo' => 'Confirmacion de nuevo password', 'mensaje' => 'Se ha actualizado satisfactoriamente su password de ingreso a la plataforma.']);
        }
        return view('customerloginmensaje', ['titulo' => 'Confirmacion de nuevo password', 'mensaje' => 'No ha sido posible realizar la operacion, consulte con su administrador de plataforma.']);
    }

    protected function PasswordVerify(Request $request, $company_idnumber, $customer_idnumber)
    {
//        $rules = [
//            'password' => 'required|min:5',
//        ];

//        Validator::make($request, [
//            'password' => [
//                'required',
//                'min:5',
//                Rule::exists('customers, password')->where(function ($query) {
//                    $query->where('identification_number', $customer_idnumber);
//                }),
//            ],
//        ]);

//        $customer = Customer::where('identification_number', '=', $customer_idnumber)->get();
//        if(count($customer) > 0)
//            if (password_verify($request->password, $customer[0]->password))
//                return "Password Valido";
//            else
//                return "Password No Valido";

        $rules = [
            'password' => [
                'required',
                'min:5',
                'passwordcustomer_verify:'.$customer_idnumber,
//                            Rule::exists('customers', 'password')
//                                ->where(function ($query) use ($customer_idnumber) {
//                                            $query->where('identification_number', $customer_idnumber);
//                                        }),
            ],
        ];
        if($request->verificar <> "FALSE")
            $this->validate($request, $rules);
        $documents = Document::where('identification_number','=',$company_idnumber)->where('customer', '=', $customer_idnumber)->where('state_document_id', '=', 1)->get();
        $documents = $documents->load('type_document');
//        $documents = Document::with('type_document')->get();
//        return view('homecustomers')->with('documents', $documents);
        return view('homecustomers', compact('documents', 'customer_idnumber', 'company_idnumber'));
    }
}
