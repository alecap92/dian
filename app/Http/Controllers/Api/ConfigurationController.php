<?php

namespace App\Http\Controllers\Api;

use DB;
use Storage;
use App\User;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PlanRequest;
use App\Http\Requests\Api\ConfigurationRequest;
use App\Http\Requests\Api\ConfigurationSoftwareRequest;
use App\Http\Requests\Api\ConfigurationSoftwarePayrollRequest;
use App\Http\Requests\Api\ConfigurationResolutionRequest;
use App\Http\Requests\Api\ConfigurationCertificateRequest;
use App\Http\Requests\Api\ConfigurationEnvironmentRequest;
use App\Http\Requests\Api\AdministratorRequest;
use App\Http\Requests\Api\ConfigurationLogoRequest;
use App\Http\Requests\Api\ConfigurationInitialDocumentRequest;
use App\Http\Requests\Api\CustomerRequest;
use Carbon\Carbon;
use App\HealthTypeDocumentIdentification;
use App\HealthTypeUser;
use App\HealthCoverage;
use App\HealthContractingPaymentMethod;
use App\Resolution;
use App\Certificate;
use App\Administrator;
use App\TypePlan;
use App\Company;
use App\Software;
use App\Document;
use App\ReceivedDocument;
use App\Log;
use App\Traits\DocumentTrait;

/**
 * @OA\Info(title="Configuracion de empresa", version="ubl2.1")
 *
 * @OA\SecurityScheme(
 *     securityScheme="Bearer",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Token de acceso que se debe incluir en el encabezado Authorization."
 * )
 */

class ConfigurationController extends Controller
{
    use DocumentTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['api.token'])
            ->only('store');
    }

    public function table_health_type_document_identifications(){
        $health_type_document_identifications = HealthTypeDocumentIdentification::all();
        return compact('health_type_document_identifications');
    }

    public function table_health_type_users(){
        $health_type_users = HealthTypeUser::all();
        return compact('health_type_users');
    }

    public function table_health_contracting_payment_methods(){
        $health_contracting_payment_methods = HealthContractingPaymentMethod::all();
        return compact('health_contracting_payment_methods');
    }

    public function table_health_coverages(){
        $health_coverages = HealthCoverage::all();
        return compact('health_coverages');
    }

    public function table_resolutions($identification_number){
        try{
            $resolutions = Resolution::where('company_id', Company::where('identification_number', $identification_number)->firstOrFail()->id)->get();
            return compact('resolutions');
        } catch (Exception $e) {
            $resolutions = [];
            return compact('resolutions');
        }
    }

    public function emailconfig()
    {
        return [
            'message' => 'Configuracion de envios de e-mail',
            'success' => true,
            'MAIL_DRIVER' => env('MAIL_DRIVER'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
        ];
    }

    /**
     * Store Company.
     *
     * @param \App\Http\Requests\Api\ConfigurationRequest $request
     * @param int                                         $nit
     * @param int                                         $dv
     *
     * @return \Illuminate\Http\Response
     *
     * @OA\PathItem(
     *      path="/config/{nit}/{dv}",
     *      summary="Crear empresa",
     *      @OA\Parameter(
     *          name="nit",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer"),
     *          description="NIT de la Empresa"
     *      ),
     *      @OA\Parameter(
     *          name="dv",
     *          in="path",
     *          required=false,
     *          @OA\Schema(type="integer"),
     *          description="Digito de verificacion de la empresa"
     *      )
     * )
     */
    public function store(ConfigurationRequest $request, $nit, $dv = null)
    {
//        if($this->validarDigVerifDIAN($nit) != $dv)
//            return [
//                'message' => 'Error, el digito de verificacion no es valido para este NIT.',
//                'success' => false,
//            ];

        DB::beginTransaction();

        try {
//            $password = Str::random(80);
            $password = $nit;

            if(count(Company::where('identification_number', '=', $nit)->get()) > 0)
            {
                $operacion = "UPDATE";
                $user = User::where('id', '=', Company::where('identification_number', '=', $nit)->get()->first()->user_id)->get()->first();
            }
            else
                $operacion = "CREATE";

            if($operacion == "CREATE"){
                $user = User::create([
                    'name' => $request->business_name,
                    'email' => $request->email,
                    'password' => bcrypt($password),
                    'id_administrador' => $request->id_administrator ?? 1,
                    'mail_host' => $request->mail_host,
                    'mail_port' => $request->mail_port,
                    'mail_username' => $request->mail_username,
                    'mail_password' => $request->mail_password,
                    'mail_encryption' => $request->mail_encryption,
                ]);

                if($user->id_administrator == null && isset($request->id_administrator) && $request->id_administrator != null)
                  $user->id_administrator = $request->id_administrator;

                $user->api_token = hash('sha256', $password);

                if(isset($request->type_plan_id))
                    $start_plan_date = Carbon::now()->format('Y-m-d H:i');
                else
                    $start_plan_date = NULL;

                if(isset($request->type_plan2_id))
                    $start_plan_date2 = Carbon::now()->format('Y-m-d H:i');
                else
                    $start_plan_date2 = NULL;

                if(isset($request->type_plan3_id))
                    $start_plan_date3 = Carbon::now()->format('Y-m-d H:i');
                else
                    $start_plan_date3 = NULL;

                if(isset($request->type_plan4_id))
                    $start_plan_date4 = Carbon::now()->format('Y-m-d H:i');
                else
                    $start_plan_date4 = NULL;

                if(isset($request->absolut_plan_documents))
                    $absolut_start_plan_date = Carbon::now()->format('Y-m-d H:i');
                else
                    $absolut_start_plan_date = NULL;

                $user->company()->create([
                    'user_id' => $user->id,
                    'identification_number' => $nit,
                    'dv' => $dv,
                    'language_id' => $request->language_id ?? 79,
                    'tax_id' => $request->tax_id ?? 1,
                    'type_environment_id' => $request->type_environment_id ?? 2,
                    'payroll_type_environment_id' => $request->payroll_type_environment_id ?? 2,
                    'eqdocs_type_environment_id' => $request->eqdocs_type_environment_id ?? 2,
                    'type_operation_id' => $request->type_operation_id ?? 10,
                    'type_document_identification_id' => $request->type_document_identification_id,
                    'country_id' => $request->country_id ?? 46,
                    'type_currency_id' => $request->type_currency_id ?? 35,
                    'type_organization_id' => $request->type_organization_id,
                    'type_regime_id' => $request->type_regime_id,
                    'type_liability_id' => $request->type_liability_id,
                    'municipality_id' => $request->municipality_id,
                    'merchant_registration' => $request->merchant_registration,
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'type_plan_id' => $request->type_plan_id ?? 0,
                    'type_plan2_id' => $request->type_plan2_id ?? 0,
                    'type_plan3_id' => $request->type_plan3_id ?? 0,
                    'type_plan4_id' => $request->type_plan4_id ?? 0,
                    'absolut_plan_documents' => $request->absolut_plan_documents,
                    'state' => $request->state ?? 1,
                    'start_plan_date' => $start_plan_date,
                    'start_plan_date2' => $start_plan_date2,
                    'start_plan_date3' => $start_plan_date3,
                    'start_plan_date4' => $start_plan_date4,
                    'absolut_start_plan_date' => $absolut_start_plan_date,
                ]);

                $user->save();
            }
            else{
                if(count(User::where('email', '=', $request->email)->where('id', '!=', Company::where('identification_number', '=', $nit)->get()->first()->user_id)->get()) > 0){
                    DB::rollBack();

                    return [
                        'message' => 'Error de registro, el correo electronico ya existe o es el mismo que tiene registrado actualmente.',
                        'success' => false,
                    ];
                }

                if(isset($request->type_plan_id) && (($request->type_plan_id != $user->company->type_plan_id) || (isset($request->renew_plan) && $request->renew_plan == TRUE)))
                    $start_plan_date = Carbon::now()->format('Y-m-d H:i');
                else
                    if($request->start_plan_date)
                        $start_plan_date = $request->start_plan_date;
                    else
                        $start_plan_date = $user->company->start_plan_date;

                if(isset($request->type_plan2_id) && (($request->type_plan2_id != $user->company->type_plan2_id) || (isset($request->renew_plan2) && $request->renew_plan2 == TRUE)))
                    $start_plan_date2 = Carbon::now()->format('Y-m-d H:i');
                else
                    if($request->start_plan_date2)
                        $start_plan_date2 = $request->start_plan_date2;
                    else
                        $start_plan_date2 = $user->company->start_plan_date2;

                if(isset($request->type_plan3_id) && (($request->type_plan3_id != $user->company->type_plan3_id) || (isset($request->renew_plan3) && $request->renew_plan3 == TRUE)))
                    $start_plan_date3 = Carbon::now()->format('Y-m-d H:i');
                else
                    if($request->start_plan_date3)
                        $start_plan_date3 = $request->start_plan_date3;
                    else
                        $start_plan_date3 = $user->company->start_plan_date3;

                if(isset($request->type_plan4_id) && (($request->type_plan4_id != $user->company->type_plan4_id) || (isset($request->renew_plan4) && $request->renew_plan4 == TRUE)))
                    $start_plan_date4 = Carbon::now()->format('Y-m-d H:i');
                else
                    if($request->start_plan_date4)
                        $start_plan_date4 = $request->start_plan_date4;
                    else
                        $start_plan_date4 = $user->company->start_plan_date4;

                if(isset($request->absolut_plan_documents) && (($request->absolut_plan_documents != $user->company->absolut_plan_documents) || (isset($request->renew_absolut_plan) && $request->renew_absolut_plan == TRUE)))
                    $absolut_start_plan_date = Carbon::now()->format('Y-m-d H:i');
                else
                    if($request->absolut_start_plan_date)
                        $absolut_start_plan_date = $request->absolut_start_plan_date;
                    else
                        $absolut_start_plan_date = $user->company->absolut_start_plan_date;

                $user->update([
                    'name' => $request->business_name,
                    'email' => $request->email,
                    'id_administrator' => $request->id_administrator ?? $user->id_administrator,
                    'mail_host' => $request->mail_host,
                    'mail_port' => $request->mail_port,
                    'mail_username' => $request->mail_username,
                    'mail_password' => $request->mail_password,
                    'mail_encryption' => $request->mail_encryption,
                ]);
                $user->company()->update([
                    'dv' => $dv,
                    'language_id' => $request->language_id ?? 79,
                    'tax_id' => $request->tax_id ?? 1,
//                    'type_environment_id' => $request->type_environment_id ?? 2,
//                    'payroll_type_environment_id' => $request->payroll_type_environment_id ?? 2,
                    'type_operation_id' => $request->type_operation_id ?? 10,
                    'type_document_identification_id' => $request->type_document_identification_id,
                    'country_id' => $request->country_id ?? 46,
                    'type_currency_id' => $request->type_currency_id ?? 35,
                    'type_organization_id' => $request->type_organization_id,
                    'type_regime_id' => $request->type_regime_id,
                    'type_liability_id' => $request->type_liability_id,
                    'municipality_id' => $request->municipality_id,
                    'merchant_registration' => $request->merchant_registration,
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'type_plan_id' => $request->type_plan_id ?? $user->company->type_plan_id,
                    'type_plan2_id' => $request->type_plan2_id ?? $user->company->type_plan2_id,
                    'type_plan3_id' => $request->type_plan3_id ?? $user->company->type_plan3_id,
                    'type_plan4_id' => $request->type_plan4_id ?? $user->company->type_plan4_id,
                    'absolut_plan_documents' => $request->absolut_plan_documents,
                    'state' => $request->state ?? $user->company->state,
                    'start_plan_date' => $start_plan_date,
                    'start_plan_date2' => $start_plan_date2,
                    'start_plan_date3' => $start_plan_date3,
                    'start_plan_date4' => $start_plan_date4,
                    'absolut_start_plan_date' => $absolut_start_plan_date,
                ]);
                $user->save();
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Empresa creada/actualizada con éxito',
                'password' => $user->password,
                'token' => $user->api_token,
                'company' => $user->company,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store software.
     *
     * @param \App\Http\Requests\Api\ConfigurationSoftwareRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     *
     * @OA\PathItem(path="/config/software",summary="Crear o actualizar software")
     */
    public function storeSoftware(ConfigurationSoftwareRequest $request)
    {
        DB::beginTransaction();

        try {
//            auth()->user()->company->software()->delete();
            $s = auth()->user()->company->software;
            if(is_null(auth()->user()->company->software))
                $software = auth()->user()->company->software()->create(
                    [
                        'identifier' => isset($request->id) ? $request->id : '',
                        'pin' => isset($request->pin) ? $request->pin : '',
                        'url' => isset($request->url) ? $request->url : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
                        'url_payroll' => isset($request->urlpayroll) ? $request->urlpayroll : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
                        'identifier_payroll' => isset($request->idpayroll) ? $request->idpayroll : '',
                        'pin_payroll' => isset($request->pinpayroll) ? $request->pinpayroll : '',
//                        'url_sd' => $request->urlsd ?? 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
//                        'identifier_sd' => $request->idsd ?? '',
//                        'pin_sd' => $request->pinsd ?? '',
                        'url_eqdocs' => isset($request->urleqdocs) ? $request->urleqdocs : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
                        'identifier_eqdocs' => isset($request->ideqdocs) ? $request->ideqdocs : '',
                        'pin_eqdocs' => isset($request->pineqdocs) ? $request->pineqdocs : '',
                    ]
                );
            else
                $software = auth()->user()->company->software()->update(
                    [
                        'identifier' => isset($request->id) ? $request->id : $s->identifier,
                        'pin' => isset($request->pin) ? $request->pin : $s->pin,
                        'url' => isset($request->url) ? $request->url : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
                        'url_payroll' => isset($request->urlpayroll) ? $request->urlpayroll : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
                        'identifier_payroll' => isset($request->idpayroll) ? $request->idpayroll : $s->identifier_payroll,
                        'pin_payroll' => isset($request->pinpayroll) ? $request->pinpayroll : $s->pin_payroll,
//                        'url_sd' => $request->urlsd ?? 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
//                        'identifier_sd' => $request->idsd ?? $s->identifier_sd,
//                        'pin_sd' => $request->pinsd ?? $s->pin_sd,
                        'url_eqdocs' => isset($request->urleqdocs) ? $request->urleqdocs : $s->url_eqdocs,
                        'identifier_eqdocs' => isset($request->ideqdocs) ? $request->ideqdocs : $s->identifier_eqdocs,
                        'pin_eqdocs' => isset($request->pineqdocs) ? $request->pineqdocs :  $s->pin_eqdocs,
                    ]
                );

            DB::commit();

            $s = Software::where('company_id', auth()->user()->company->id)->firstOrFail();
            return [
                'success' => true,
                'message' => 'Software creado/actualizado con éxito',
                'software' => $s,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Certificate End Date.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function CertificateEndDate($user = FALSE)
    {
        if($user === FALSE)
            $company = auth()->user()->company;
        else
            $company = $user->company;
        $pfxContent = file_get_contents(storage_path("app/certificates/".$company->certificate->name));
        try {
            if (!openssl_pkcs12_read($pfxContent, $x509certdata, $company->certificate->password)) {
                throw new Exception('The certificate could not be read.');
            }
            else{
                $CertPriv   = array();
                $CertPriv   = openssl_x509_parse(openssl_x509_read($x509certdata['cert']));

                $PrivateKey = $x509certdata['pkey'];

                $pub_key = openssl_pkey_get_public($x509certdata['cert']);
                $keyData = openssl_pkey_get_details($pub_key);

                $PublicKey  = $keyData['key'];

//                return $CertPriv['name'];                           //Nome
//                return $CertPriv['hash'];                           //hash
//                return $CertPriv['subject']['C'];                   //País
//                return $CertPriv['subject']['ST'];                  //Estado
//                return $CertPriv['subject']['L'];                   //Município
//                return $CertPriv['subject']['CN'];                  //Razão Social e CNPJ / CPF
                return date('d/m/Y', $CertPriv['validTo_time_t'] ); //Validade
//                return $CertPriv['extensions']['subjectAltName'];   //Emails Cadastrados separado por ,
//                return $CertPriv['extensions']['authorityKeyIdentifier'];
//                return $CertPriv['issuer'];                   //Emissor
//                return $PublicKey;
//                return $PrivateKey;
            }
        } catch (Exception $e) {
            if (false == ($error = openssl_error_string())) {
                return response([
                    'message' => $e->getMessage(),
                    'errors' => [
                        'certificate' => 'The base64 encoding is not valid.',
                    ],
                ], 422);
            }
            return $pfxContent;
        }
    }

    public function certificates_listing($company_identification_number = FALSE){
        $user = auth()->user();
        $company = $user->company;

        $administrator = Administrator::where('identification_number', $company->identification_number)->get();
        if(count($administrator) > 0){
            if($company_identification_number)
                $certificates = Certificate::where('name', '=', $company_identification_number.$this->validarDigVerifDIAN($company_identification_number).'.p12')->get();
            else
                $certificates = Certificate::where('id', '>', 0)->get();

            return[
                'success' => true,
                'certificates' => $certificates,
            ];
        }
        else
            return[
                'success' => false,
                'message' => 'No se pudo ejecutar la peticion, no pertenece al grupo de ADMINISTRADORES...',
            ];
    }

    /**
     * Store certificate.
     *
     * @param \App\Http\Requests\Api\ConfigurationCertificateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeCertificate(ConfigurationCertificateRequest $request)
    {
        try {
            if (!base64_decode($request->certificate, true)) {
                throw new Exception('The given data was invalid.');
            }
            if (!openssl_pkcs12_read($certificateBinary = base64_decode($request->certificate), $certificate, $request->password)) {
                throw new Exception('The certificate could not be read.');
            }
        } catch (Exception $e) {
            if (false == ($error = openssl_error_string())) {
                return response([
                    'message' => $e->getMessage(),
                    'errors' => [
                        'certificate' => 'The base64 encoding is not valid.',
                    ],
                ], 422);
            }

            return response([
                'message' => $e->getMessage(),
                'errors' => [
                    'certificate' => $error,
                    'password' => $error,
                ],
            ], 422);
        }

        DB::beginTransaction();

        try {
            auth()->user()->company->certificate()->delete();

            $company = auth()->user()->company;
            $name = "{$company->identification_number}{$company->dv}.p12";

            Storage::put("certificates/{$name}", $certificateBinary);

            $pfxContent = file_get_contents(storage_path("app/certificates/".$name));
            if (!openssl_pkcs12_read($pfxContent, $x509certdata, $request->password)) {
                throw new Exception('The certificate could not be read.');
            }
            else{
                $CertPriv   = array();
                $CertPriv   = openssl_x509_parse(openssl_x509_read($x509certdata['cert']));
                $PrivateKey = $x509certdata['pkey'];
                $pub_key = openssl_pkey_get_public($x509certdata['cert']);
                $keyData = openssl_pkey_get_details($pub_key);
                $PublicKey  = $keyData['key'];
                $expiration_date = date('Y/m/d H:i:s', $CertPriv['validTo_time_t']);
            }

            $certificate = auth()->user()->company->certificate()->create([
                'name' => $name,
                'password' => $request->password,
                'expiration_date' => $expiration_date,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Certificado creado con éxito',
                'certificado' => $certificate,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store logo.
     *
     * @param \App\Http\Requests\Api\ConfigurationLogoRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeLogo(ConfigurationLogoRequest $request)
    {
        try {
            if (!base64_decode($request->logo, true)) {
                throw new Exception('The given data was invalid.');
            }
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'errors' => [
                    'logo' => 'The base64 encoding is not valid.',
                ],
            ], 422);

            return response([
                'message' => $e->getMessage(),
                'errors' => [
                    'logo' => $error,
                ],
            ], 422);
        }

        try {

            $company = auth()->user()->company;
            $name = "{$company->identification_number}{$company->dv}.jpg";

            Storage::put("public/{$company->identification_number}/{$name}", base64_decode($request->logo));

            return [
                'success' => true,
                'message' => 'Logo almacenado con éxito',
            ];
        } catch (Exception $e) {

            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store resolution.
     *
     * @param \App\Http\Requests\Api\ConfigurationResolutionRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeResolution(ConfigurationResolutionRequest $request)
    {
        DB::beginTransaction();

        try {
            if($request->delete_all_type_resolutions){
                $resolution = auth()->user()->company->resolutions()->where('type_document_id', $request->type_document_id)->get();
                if(count($resolution) > 0)
                    foreach($resolution as $r)
                        $r->delete();
            }
            $resolution = auth()->user()->company->resolutions()->updateOrCreate([
                'type_document_id' => $request->type_document_id,
                'resolution' => $request->resolution,
                'prefix' => $request->prefix,
            ], [
                'resolution_date' => $request->resolution_date,
                'technical_key' => $request->technical_key,
                'from' => $request->from,
                'to' => $request->to,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Resolución creada/actualizada con éxito',
                'resolution' => $resolution,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register Customer.
     *
     * @param \App\Http\Requests\Api\CustomerRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function RegCustomer(CustomerRequest $request)
    {
        try{
            $r = $this->registerCustomer($request, $request->sendnotification, true);
            return [
                'success' => true,
                'message' => 'Cliente creado/actualizado con exito.',
            ];
        }
        catch (Exception $e) {
            return [
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ];
        }
    }

    /**
     * Store Initial Document.
     *
     * @param \App\Http\Requests\Api\ConfigurationInitialDocumentRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeInitialDocument(ConfigurationInitialDocumentRequest $request)
    {
        DB::beginTransaction();

        try {
            $initialdocument = Document::updateOrCreate([
                'identification_number' => $request->identification_number,
                'type_document_id' => $request->type_document_id,
                'prefix' => $request->prefix,
                'cufe' => 'cufe-initial-number',
            ],
            [
                'number' => $request->number,
                'state_document_id' => 1,
                'customer' => '222222222222',
                'xml' => 'INITIAL_NUMBER.XML',
                'client_id' => '222222222222',
                'client' => json_encode([]),
                'currency_id' => 35,
                'sale' => 0,
                'total_discount' => 0,
                'taxes' => json_encode([]),
                'total_tax' => 0,
                'subtotal' => 0,
                'total' => 0,
                'version_ubl_id' => 2,
                'ambient_id' => 2,
                'request_api' => json_encode([]),
                'pdf' => 'INITIAL_NUMBER.PDF',
                'date_issue' => date("Y-m-d H:i:s"),
            ]);

            $initialdocument->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Documento Inicial creado/actualizado con éxito',
                'number' => $request->number,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store environment.
     *
     * @param \App\Http\Requests\Api\ConfigurationEnvironmentRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeEnvironment(ConfigurationEnvironmentRequest $request)
    {
        if(!$request->type_environment_id)
            $request->type_environment_id = auth()->user()->company->type_environment_id;
        if(!$request->payroll_type_environment_id)
            $request->payroll_type_environment_id = auth()->user()->company->payroll_type_environment_id;
        if(!$request->eqdocs_type_environment_id)
            $request->eqdocs_type_environment_id = auth()->user()->company->eqdocs_type_environment_id;
        auth()->user()->company->update([
            'type_environment_id' => $request->type_environment_id,
            'payroll_type_environment_id' => $request->payroll_type_environment_id,
            'eqdocs_type_environment_id' => $request->eqdocs_type_environment_id,
        ]);

        if ($request->type_environment_id)
            if ($request->type_environment_id == 1)
              auth()->user()->company->software->update([
                  'url' => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc',
                ]);
            else
               auth()->user()->company->software->update([
                  'url' => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
              ]);

        if ($request->payroll_type_environment_id)
            if ($request->payroll_type_environment_id == 1)
              auth()->user()->company->software->update([
                  'url_payroll' => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc',
                ]);
            else
               auth()->user()->company->software->update([
                  'url_payroll' => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
              ]);

        if ($request->eqdocs_type_environment_id)
            if ($request->eqdocs_type_environment_id == 1)
              auth()->user()->company->software->update([
                  'url_eqdocs' => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc',
                ]);
            else
               auth()->user()->company->software->update([
                  'url_eqdocs' => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
              ]);

        return [
            'message' => 'Ambiente actualizado con éxito',
            'company' => auth()->user()->company,
        ];
    }

    /**
    * Borrar Company API
    */
    public function deleteCompany($nit, $dv)
    {
        $company = Company::where('identification_number', '=', $nit)->get()->first();
        $id_user = $company->user_id;
        Company::where('identification_number', $nit)->delete();
        Document::where('identification_number', $nit)->delete();
        Log::where('user_id', $id_user)->delete();
        User::where('id', $id_user)->delete();
        return [
            'message' => 'Empresa eliminada con exito.',
            'success' => true,
        ];
    }

    /**
    * Destroy Company used by FACTURADOR PRO
    */
    public function destroyCompany($nit, $email)
    {
        $id_user = User::select('id')->where('email', $email)->first();
        Company::where('identification_number', $nit)->delete();
        Document::where('identification_number', $nit)->delete();
        Log::where('user_id', $id_user->id)->delete();
        User::where('email', $email)->delete();
    }

    /**
     * storePlan
     *
     * @param \App\Http\Requests\Api\PlanRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storePlan(PlanRequest $request)
    {
        try {
            $a = TypePlan::where('id', $request->id)->get();
            if(count($a) > 0)
                $plan = TypePlan::updateOrCreate([
                    'id' => $request->id,
                ], [
                    'name' => $request->name ?? $a[0]->name,
                    'qty_docs_invoice' => $request->qty_docs_invoice,
                    'qty_docs_payroll' => $request->qty_docs_payroll,
                    'qty_docs_radian' => $request->qty_docs_radian,
                    'qty_docs_ds' => $request->qty_docs_ds,
                    'period' => $request->period,
                    'state' => $request->state ?? true,
                    'observations' => $request->observations,
                ]);
            else{
                $plan = TypePlan::updateOrCreate([
                    'id' => $request->id,
                ], [
                    'name' => $request->name,
                    'qty_docs_invoice' => $request->qty_docs_invoice,
                    'qty_docs_payroll' => $request->qty_docs_payroll,
                    'qty_docs_radian' => $request->qty_docs_radian,
                    'qty_docs_ds' => $request->qty_docs_ds,
                    'period' => $request->period,
                    'state' => $request->state ?? true,
                    'observations' => $request->observations,
                ]);
            }

            return [
                'success' => true,
                'message' => 'Plan creado/actualizado con éxito',
                'plan' => $plan,
            ];
        } catch (Exception $e) {
            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * storeAdministrator.
     *
     * @param \App\Http\Requests\Api\AdministratorRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function storeAdministrator(AdministratorRequest $request)
    {
        try {
            if($request->password)
                $password = $request->password;
            else
                $password = "12345*";

            $a = Administrator::where('identification_number', $request->identification_number)->get();
            if(count($a) > 0)
                $administrator = Administrator::updateOrCreate([
                    'identification_number' => $request->identification_number,
                ], [
                    'dv' => $request->dv,
                    'name' => $request->name,
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'contact_name' => $request->contact_name,
                    'password' => bcrypt($password),
                    'plan' => $request->plan,
                    'state' => $request->state ?? true,
                    'observation' => $request->observation,
                ]);
            else{
                $administrator = Administrator::updateOrCreate([
                    'identification_number' => $request->identification_number,
                ], [
                    'dv' => $request->dv,
                    'name' => $request->name,
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'contact_name' => $request->contact_name,
                    'password' => bcrypt($password),
                    'plan' => $request->plan,
                    'state' => $request->state ?? true,
                    'observation' => $request->observation,
                ]);
            }

            return [
                'success' => true,
                'message' => 'Administrador creado/actualizado con éxito',
                'password' => $password,
            ];
        } catch (Exception $e) {
            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * queryAdministrator.
     *
     * @param int                                         $id
     *
     * @return \Illuminate\Http\Response
     */
    public function queryPlan($id = FALSE)
    {
        try {
            if($id)
                $a = TypePlan::where('id', $id)->firstOrFail();
            else
                $a = TypePlan::where('id', '>', 0)->get();
            return [
                'success' => true,
                'message' => 'Plan/es existe/n, informacion del/los plan/es:',
                'data' => $a
            ];
        } catch (Exception $e) {
            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * queryAdministrator.
     *
     * @param int                                         $nit
     *
     * @return \Illuminate\Http\Response
     */
    public function queryAdministrator($nit = FALSE)
    {
        try {
            if($nit)
                $a = Administrator::where('identification_number', $nit)->firstOrFail();
            else
                $a = Administrator::where('identification_number', '>', 0)->get();
            return [
                'success' => true,
                'message' => 'Administrador/es existe/n, informacion del/los administrador/es:',
                'data' => $a
            ];
        } catch (Exception $e) {
            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * queryUsersByAdministrator.
     *
     * @param int                                         $nit
     *
     * @return \Illuminate\Http\Response
     */
    public function queryUsersByAdministrator($nit)
    {
        try {
            $a = Administrator::where('identification_number', $nit)->firstOrFail();
            $u = User::where('id_administrator', $a->id)->get();
            return [
                'success' => true,
                'message' => 'Usuarios registrados al administrador NIT: '.$nit,
                'user' => $u,
            ];
        } catch (Exception $e) {
            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * queryUsersByPlan.
     *
     * @param int                                         $id
     *
     * @return \Illuminate\Http\Response
     */
    public function queryUsersByPlan($id)
    {
        try {
            $a = TypePlan::where('id', $id)->firstOrFail();
            $u = Company::where('type_plan_id', $a->id)->get();
            return [
                'success' => true,
                'message' => 'Usuarios registrados al plan ID: '.$id,
                'user' => $u,
            ];
        } catch (Exception $e) {
            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * infoPlanUser.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function infoPlanUser()
    {
        $user = auth()->user();
        $company = $user->company;
        if($user->company->type_plan->period == 0){
          $period = "SIN LIMITES - NO PLAN";
          $renovation_date = NULL;
        }
        else
            if($user->company->type_plan->period == 1){
              $period = "MENSUAL";
              $renovation_date = date("Y-m-d H:i:s", strtotime($company->start_plan_date."+ 1 month"));
            }
            else
                if($user->company->type_plan->period == 2){
                  $period = "ANUAL";
                  $renovation_date = date("Y-m-d H:i:s", strtotime($company->start_plan_date."+ 1 year"));
                }
                else
                    if($user->company->type_plan->period == 3){
                        $period = "PAQUETE";
                        $renovation_date = NULL;
                    }

        if($user->company->type_plan2->period == 0){
          $period2 = "SIN LIMITES - NO PLAN";
          $renovation_date2 = NULL;
        }
        else
            if($user->company->type_plan2->period == 1){
              $period2 = "MENSUAL";
              $renovation_date2 = date("Y-m-d H:i:s", strtotime($company->start_plan_date2."+ 1 month"));
            }
            else
                if($user->company->type_plan2->period == 2){
                  $period2 = "ANUAL";
                  $renovation_date2 = date("Y-m-d H:i:s", strtotime($company->start_plan_date2."+ 1 year"));
                }
                else
                    if($user->company->type_plan2->period == 3){
                        $period2 = "PAQUETE";
                        $renovation_date2 = NULL;
                    }

        if($user->company->type_plan3->period == 0){
          $period3 = "SIN LIMITES - NO PLAN";
          $renovation_date3 = NULL;
        }
        else
            if($user->company->type_plan3->period == 1){
              $period3 = "MENSUAL";
              $renovation_date3 = date("Y-m-d H:i:s", strtotime($company->start_plan_date3."+ 1 month"));
            }
            else
                if($user->company->type_plan3->period == 2){
                  $period3 = "ANUAL";
                  $renovation_date3 = date("Y-m-d H:i:s", strtotime($company->start_plan_date3."+ 1 year"));
                }
                else
                    if($user->company->type_plan3->period == 3){
                        $period3 = "PAQUETE";
                        $renovation_date3 = NULL;
                    }

        if($user->company->type_plan4->period == 0){
          $period4 = "SIN LIMITES - NO PLAN";
          $renovation_date4 = NULL;
        }
        else
            if($user->company->type_plan4->period == 1){
              $period4 = "MENSUAL";
              $renovation_date4 = date("Y-m-d H:i:s", strtotime($company->start_plan_date4."+ 1 month"));
            }
            else
                if($user->company->type_plan4->period == 2){
                  $period4 = "ANUAL";
                  $renovation_date4 = date("Y-m-d H:i:s", strtotime($company->start_plan_date4."+ 1 year"));
                }
                else
                    if($user->company->type_plan4->period == 3){
                        $period4 = "PAQUETE";
                        $renovation_date4 = NULL;
                    }

        try {
            if($user->company->absolut_plan_documents == 0)
                return [
                    'success' => true,
                    'message' => 'Informacion del plan vigente para el usuario: '.$company->identification_number,
                    'plan' => $company->type_plan,
                    'period' => $period,
                    'start_plan_date' => $company->start_plan_date,
                    'renovation_date' => $renovation_date,
                    'plan2' => $company->type_plan2,
                    'period2' => $period2,
                    'start_plan_date2' => $company->start_plan_date2,
                    'renovation_date2' => $renovation_date2,
                    'plan3' => $company->type_plan3,
                    'period3' => $period3,
                    'start_plan_date3' => $company->start_plan_date3,
                    'renovation_date3' => $renovation_date3,
                    'plan4' => $company->type_plan4,
                    'period4' => $period4,
                    'start_plan_date4' => $company->start_plan_date4,
                    'renovation_date4' => $renovation_date4,
                    'docs_left_invoice' => $company->type_plan->qty_docs_invoice - $this->qty_docs_period(),
                    'docs_left_payroll' => $company->type_plan2->qty_docs_payroll - $this->qty_docs_period("PAYROLL"),
                    'docs_left_radian' => $company->type_plan3->qty_docs_radian - $this->qty_docs_period("RADIAN"),
                    'docs_left_ds' => $company->type_plan4->qty_docs_ds - $this->qty_docs_period("SUPPORT DOCUMENT"),
                ];
            else
                return [
                    'success' => true,
                    'message' => 'Informacion del plan vigente para el usuario: '.$company->identification_number,
                    'absolut_plan' => "PLAN MIXTO",
                    'absolut_start_plan_date' => $company->absolut_start_plan_date,
                    'absolut_plan_documents' => $company->absolut_plan_documents,
                    'docs_left_absolut' => $company->absolut_plan_documents - $this->qty_docs_period("ABSOLUT"),
                ];
        } catch (Exception $e) {
            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }
};
