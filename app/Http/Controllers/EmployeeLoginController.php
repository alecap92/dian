<?php

namespace App\Http\Controllers;

use App\Company;
use App\Employee;
use App\Document;
use App\DocumentPayroll;
use App\User;
use App\Mail\InvoiceMail;
use App\Mail\PasswordEmployeeMail;
use App\Mail\RetrievePasswordEmployeeMail;
use App\Mail\RetrievePasswordSellerMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\DocumentTrait;

class EmployeeLoginController extends Controller
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
     * Show the employee login form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function ShowEmployeeLoginForm($company_idnumber, $employee_idnumber)
    {
        $company = Company::where('identification_number', '=', $company_idnumber)->get();
        $employee = Employee::where('identification_number', '=', $employee_idnumber)->get();
        if (count($company) > 0 && count($employee) > 0)
            return view('employeelogin', compact('company_idnumber', 'employee_idnumber'));
        else
            return view('customerloginmensaje', ['titulo' => 'Error en el ingreso para empleados', 'mensaje' => 'Los datos de emisor y/o empleado no se encuentran registrados.']);
    }

    protected function EmployeePassword(Request $request, $company_idnumber, $employee_idnumber)
    {
        return view('resetemployeepassword', compact('request', 'company_idnumber', 'employee_idnumber'));
    }

    protected function ResetEmployeePassword(Request $request, $company_idnumber, $employee_idnumber)
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

        $employee = Employee::where('identification_number', '=', $employee_idnumber)->get()->first();
        $employee->password = bcrypt($request->password);
        $employee->save();
        Mail::to($employee->email)->send(new PasswordEmployeeMail($employee, $request->password));
        return view('customerloginmensaje', ['titulo' => 'Actualizacion de password', 'mensaje' => 'El password ha sido actualizado satisfactoriamente.']);
    }

    protected function RetrievePasswordEmployee($employee_idnumber, $mostrarvista = 'YES')
    {
        $employee = Employee::where('identification_number', '=', $employee_idnumber)->get()->first();
        $password = \Str::random(6);
        $employee->newpassword = bcrypt($password);
        $employee->save();
        Mail::to($employee->email)->send(new RetrievePasswordEmployeeMail($employee, $password));
        if($mostrarvista == 'YES')
            return view('customerloginmensaje', ['titulo' => 'Solicitud de nuevo password', 'mensaje' => 'Se ha enviado un mensaje de correo electronico a la direccion '.$employee->email.', debe confirmar este mensaje para que el cambio de contraseña se haga efectivo.']);
        else
            return [
                'success' => true,
                'password' => $password,
        ];
    }

    protected function AcceptRetrievePasswordEmployee($employee_idnumber, $hash)
    {
        $employee = Employee::where('identification_number', '=', $employee_idnumber)->get()->first();
        $hash = str_replace('(slash)', '/', substr($hash, 0, strlen($hash) - 1));
        if($hash == $employee->newpassword)
        {
            $employee->password = $employee->newpassword;
            $employee->newpassword = NULL;
            $employee->save();
            return view('customerloginmensaje', ['titulo' => 'Confirmacion de nuevo password', 'mensaje' => 'Se ha actualizado satisfactoriamente su password de ingreso a la plataforma.']);
        }
        return view('customerloginmensaje', ['titulo' => 'Confirmacion de nuevo password', 'mensaje' => 'No ha sido posible realizar la operacion, consulte con su administrador de plataforma.']);
    }

    protected function PasswordEmployeeVerify(Request $request, $company_idnumber, $employee_idnumber)
    {
//        $rules = [
//            'password' => 'required|min:5',
//        ];

//        Validator::make($request, [
//            'password' => [
//                'required',
//                'min:5',
//                Rule::exists('customers, password')->where(function ($query) {
//                    $query->where('identification_number', $employee_idnumber);
//                }),
//            ],
//        ]);

//        $employee = Employee::where('identification_number', '=', $employee_idnumber)->get();
//        if(count($employee) > 0)
//            if (password_verify($request->password, $employee[0]->password))
//                return "Password Valido";
//            else
//                return "Password No Valido";

        $rules = [
            'password' => [
                'required',
                'min:5',
                'passwordemployee_verify:'.$employee_idnumber,
//                            Rule::exists('customers', 'password')
//                                ->where(function ($query) use ($employee_idnumber) {
//                                            $query->where('identification_number', $employee_idnumber);
//                                        }),
            ],
        ];
        if($request->verificar <> "FALSE")
            $this->validate($request, $rules);
        $documents = DocumentPayroll::where('identification_number','=',$company_idnumber)->where('employee_id', '=', $employee_idnumber)->where('state_document_id', '=', 1)->get();
        $documents = $documents->load('type_document');
//        $documents = Document::with('type_document')->get();
//        return view('homecustomers')->with('documents', $documents);
        return view('homeemployees', compact('documents', 'employee_idnumber', 'company_idnumber'));
    }
}
