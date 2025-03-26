<?php

namespace App\Traits;

use QrCode;
use App\Tax;
use Storage;
use App\User;
use Exception;
use Mpdf\Mpdf;
use ZipArchive;
use App\Company;
use App\Document;
use App\DocumentPayroll;
use App\ReceivedDocument;
use DOMDocument;
use App\Customer;
use App\Employee;
use App\Resolution;
use App\TypeRegime;
use ubl21dian\Sign;
use App\TypeDocument;
use App\TypeLiability;
use App\TypeOperation;
use Mpdf\HTMLParserMode;
use App\Mail\InvoiceMail;
use App\Custom\zipfileDIAN;
use InvalidArgumentException;
use Mpdf\Config\FontVariables;
use Mpdf\Config\ConfigVariables;
use App\Mail\PasswordCustomerMail;
use App\Mail\PasswordEmployeeMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Api\ConfigurationController;
use DateTime;
use Carbon\Carbon;

/**
 * Document trait.
 */
trait DocumentTrait
{
    /**
     * PPP.
     *
     * @var string
     */
    public $ppp = '000';

    /**
     * Payment form default.
     *
     * @var array
     */
    private $paymentFormDefault = [
        'payment_form_id' => 1,
        'payment_method_id' => 10,
    ];

    /**
     * Get query.
     *
     * @param string $query
     * @param bool   $validate
     * @param int    $item
     *
     * @return mixed
     */
    protected function getQuery($document, $query, $validate = true, $item = 0)
    {
        if (is_string($document)){
            $xml = $document;
            $document = new \DOMDocument;
            $document->loadXML($xml);
            $domXPath = new \DOMXPath($document);
        }

        $tag = $domXPath->query($query);
        if (($validate) && (null == $tag->item(0))) {
            return null;
//            throw new Exception('Class '.get_class($this).": The query {$query} does not exist.");
        }
        if (is_null($item)) {
            return $tag;
        }

        return $tag->item($item);
    }

    protected function getTag($document, $tagName, $item = 0, $attribute = NULL, $attribute_value = NULL)
    {
        if (is_string($document)){
            $xml = $document;
            $document = new \DOMDocument;
            $document->loadXML($xml);
        }

        $tag = $document->documentElement->getElementsByTagName($tagName);

        if (is_null($tag->item(0))) {
            return;
        }

        if($attribute)
            if($attribute_value){
                $tag->item($item)->setAttribute($attribute, $attribute_value);
                return;
            }
            else
                return $tag->item($item)->getAttribute($attribute);
        else
            return $tag->item($item);
    }

    protected function registerEmployee($data, $sendmail = false)
    {
        $user = auth()->user();
        if($user->validate_mail_server()){
            \Config::set('mail.host', $user->mail_host);
            \Config::set('mail.port', $user->mail_port);
            \Config::set('mail.username', $user->mail_username);
            \Config::set('mail.password', $user->mail_password);
            \Config::set('mail.encryption', $user->mail_encryption);
        }

        $password = "12345";
        $employee = Employee::where('identification_number', '=', $data->identification_number)->get();
        if(count($employee) == 0){
            $password = \Str::random(6);
            $data->password = bcrypt($password);
        }
        else
            $data->password = $employee[0]->password;
        $employee = Employee::updateOrCreate(['identification_number' => $data->identification_number],
                                             ['first_name' => $data->first_name,
                                              'middle_name' => $data->middle_name,
                                              'surname' => $data->surname,
                                              'second_surname' => $data->second_surname,
                                              'password' => $data->password,
                                              'address' => $data->address,
                                              'email' => $data->email
                                             ]);

        if($sendmail && $data->identification_number != '222222222222' && isset($data->email) && ($data->email != ''))
            if(\Carbon\Carbon::now()->format('Y-m-d H:i') === date_format(date_create($employee->created_at), 'Y-m-d H:i'))
                Mail::to($employee->email)->send(new PasswordEmployeeMail($employee, $password));
    }

    protected function registerCustomer($data, $sendmail = false, $sendingcustomer = false)
    {
        $user = auth()->user();
        if($user->validate_mail_server()){
            \Config::set('mail.host', $user->mail_host);
            \Config::set('mail.port', $user->mail_port);
            \Config::set('mail.username', $user->mail_username);
            \Config::set('mail.password', $user->mail_password);
            \Config::set('mail.encryption', $user->mail_encryption);
        }

        $password = "12345";
        if($sendingcustomer)
            $customer = Customer::where('identification_number', '=', $data->identification_number)->get();
        else
            $customer = Customer::where('identification_number', '=', $data->company->identification_number)->get();
        if(count($customer) == 0){
            $password = \Str::random(6);
            $data->password = bcrypt($password);
        }
        else
            $data->password = $customer[0]->password;
        if($sendingcustomer){
            if(array_key_exists('dv', $data->all()))
              $dv = $data->dv;
            else
              $dv = NULL;
            $customer = Customer::updateOrCreate(['identification_number' => $data->identification_number],
                                                 ['dv' => $dv,
                                                  'name' => $data->name,
                                                  'phone' => $data->phone,
                                                  'password' => $data->password,
                                                  'address' => $data->address,
                                                  'email' => $data->email
                                                 ]);
            if($sendmail && $data->identification_number != '222222222222')
                if(\Carbon\Carbon::now()->format('Y-m-d H:i') === date_format(date_create($customer->created_at), 'Y-m-d H:i'))
                    Mail::to($customer->email)->send(new PasswordCustomerMail($customer, $password));
        }
        else{
            $customer = Customer::updateOrCreate(['identification_number' => $data->company->identification_number],
                                                 ['dv' => $data->company->dv,
                                                  'name' => $data->name,
                                                  'phone' => $data->company->phone,
                                                  'password' => $data->password,
                                                  'address' => $data->company->address,
                                                  'email' => $data->email
                                                 ]);

            if($sendmail && $data->company->identification_number != '222222222222')
                if(\Carbon\Carbon::now()->format('Y-m-d H:i') === date_format(date_create($customer->created_at), 'Y-m-d H:i'))
                    Mail::to($customer->email)->send(new PasswordCustomerMail($customer, $password));
        }
    }

    /**
     * Create xml.
     *
     * @param array $data
     *
     * @return DOMDocument
     */
    protected function createXML(array $data)
    {
        if($data['typeDocument']['code'] === '01' or $data['typeDocument']['code'] === '02' or $data['typeDocument']['code'] === '03' or $data['typeDocument']['code'] === '05' or $data['typeDocument']['code'] === '95' or $data['typeDocument']['code'] === '91' or $data['typeDocument']['code'] === '92' or $data['typeDocument']['code'] === '20' or $data['typeDocument']['code'] === '35' or $data['typeDocument']['code'] === '25' or $data['typeDocument']['code'] === '60' or $data['typeDocument']['code'] === '93' or $data['typeDocument']['code'] === '94'){
            if($data['typeDocument']['code'] === '01' or $data['typeDocument']['code'] === '02' or $data['typeDocument']['code'] === '03' or $data['typeDocument']['code'] === '04' or $data['typeDocument']['code'] === '05' or $data['typeDocument']['code'] === '95' or $data['typeDocument']['code'] === '91' or $data['typeDocument']['code'] === '92'){
                if($data['company']['type_environment_id'] == 2)
                    $urlquery = 'https://catalogo-vpfe-hab.dian.gov.co';
                else
                    $urlquery = 'https://catalogo-vpfe.dian.gov.co';
            }
            else{
                if($data['typeDocument']['code'] === '20' or $data['typeDocument']['code'] === '35' or $data['typeDocument']['code'] === '25' or $data['typeDocument']['code'] === '60' or $data['typeDocument']['code'] === '93' or $data['typeDocument']['code'] === '94'){
                    if($data['company']['eqdocs_type_environment_id'] == 2)
                        $urlquery = 'https://catalogo-vpfe-hab.dian.gov.co';
                    else
                        $urlquery = 'https://catalogo-vpfe.dian.gov.co';
                }
            }

            $QRCode = $urlquery.'/document/searchqr?documentkey=-----CUFECUDE-----';
            \Log::debug($data['company']);
            \Log::debug($QRCode);

//            if($data['typeDocument']['code'] === '01' or $data['typeDocument']['code'] === '02' or $data['typeDocument']['code'] === '03' or $data['typeDocument']['code'] === '20' or $data['typeDocument']['code'] === '35' or $data['typeDocument']['code'] === '24')
//                if(isset($data['request']['tax_totals'][0]['tax_amount']))
//                    $QRCode = 'NumFac: '.$data['resolution']['next_consecutive'].PHP_EOL.'FecFac: '.$data['date'].PHP_EOL.'NitFac: '.$data['user']['company']['identification_number'].PHP_EOL.'DocAdq: '.$data['customer']['company']['identification_number'].PHP_EOL.'ValFac: '.$data['legalMonetaryTotals']['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$data['request']['tax_totals'][0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$data['legalMonetaryTotals']['allowance_total_amount'].PHP_EOL.'ValTotal: '.$data['legalMonetaryTotals']['payable_amount'].PHP_EOL.'CUFE: -----CUFECUDE-----'.PHP_EOL.$urlquery.'/document/searchqr?documentkey=-----CUFECUDE-----';
//                else
//                    $QRCode = 'NumFac: '.$data['resolution']['next_consecutive'].PHP_EOL.'FecFac: '.$data['date'].PHP_EOL.'NitFac: '.$data['user']['company']['identification_number'].PHP_EOL.'DocAdq: '.$data['customer']['company']['identification_number'].PHP_EOL.'ValFac: '.$data['legalMonetaryTotals']['tax_exclusive_amount'].PHP_EOL.'ValIva: '.'0.00'.PHP_EOL.'ValOtroIm: '.$data['legalMonetaryTotals']['allowance_total_amount'].PHP_EOL.'ValTotal: '.$data['legalMonetaryTotals']['payable_amount'].PHP_EOL.'CUFE: -----CUFECUDE-----'.PHP_EOL.$urlquery.'/document/searchqr?documentkey=-----CUFECUDE-----';
//            else
//                if($data['typeDocument']['code'] === '91' or $data['typeDocument']['code'] === '92' or $data['typeDocument']['code'] === '93' or $data['typeDocument']['code'] === '94'){
//                    if(isset($data['request']['tax_totals'][0]['tax_amount']))
//                        if(in_array($data['typeDocument']['code'], ['93', '92']))
//                            $QRCode = 'NumFac: '.$data['resolution']['next_consecutive'].PHP_EOL.'FecFac: '.$data['date'].PHP_EOL.'NitFac: '.$data['user']['company']['identification_number'].PHP_EOL.'DocAdq: '.$data['customer']['company']['identification_number'].PHP_EOL.'ValFac: '.$data['requestedMonetaryTotals']['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$data['request']['tax_totals'][0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$data['requestedMonetaryTotals']['allowance_total_amount'].PHP_EOL.'ValTotal: '.$data['requestedMonetaryTotals']['payable_amount'].PHP_EOL.'CUFE: -----CUFECUDE-----'.PHP_EOL.$urlquery.'/document/searchqr?documentkey=-----CUFECUDE-----';
//                        else
//                            $QRCode = 'NumFac: '.$data['resolution']['next_consecutive'].PHP_EOL.'FecFac: '.$data['date'].PHP_EOL.'NitFac: '.$data['user']['company']['identification_number'].PHP_EOL.'DocAdq: '.$data['customer']['company']['identification_number'].PHP_EOL.'ValFac: '.$data['legalMonetaryTotals']['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$data['request']['tax_totals'][0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$data['legalMonetaryTotals']['allowance_total_amount'].PHP_EOL.'ValTotal: '.$data['legalMonetaryTotals']['payable_amount'].PHP_EOL.'CUFE: -----CUFECUDE-----'.PHP_EOL.$urlquery.'/document/searchqr?documentkey=-----CUFECUDE-----';
//                    else
//                        if(in_array($data['typeDocument']['code'], ['93', '92']))
//                            $QRCode = 'NumFac: '.$data['resolution']['next_consecutive'].PHP_EOL.'FecFac: '.$data['date'].PHP_EOL.'NitFac: '.$data['user']['company']['identification_number'].PHP_EOL.'DocAdq: '.$data['customer']['company']['identification_number'].PHP_EOL.'ValFac: '.$data['requestedMonetaryTotals']['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: '.$data['requestedMonetaryTotals']['allowance_total_amount'].PHP_EOL.'ValTotal: '.$data['requestedMonetaryTotals']['payable_amount'].PHP_EOL.'CUFE: -----CUFECUDE-----'.PHP_EOL.$urlquery.'/document/searchqr?documentkey=-----CUFECUDE-----';
//                        else
//                            $QRCode = 'NumFac: '.$data['resolution']['next_consecutive'].PHP_EOL.'FecFac: '.$data['date'].PHP_EOL.'NitFac: '.$data['user']['company']['identification_number'].PHP_EOL.'DocAdq: '.$data['customer']['company']['identification_number'].PHP_EOL.'ValFac: '.$data['legalMonetaryTotals']['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: '.$data['legalMonetaryTotals']['allowance_total_amount'].PHP_EOL.'ValTotal: '.$data['legalMonetaryTotals']['payable_amount'].PHP_EOL.'CUFE: -----CUFECUDE-----'.PHP_EOL.$urlquery.'/document/searchqr?documentkey=-----CUFECUDE-----';
//                }
//                else
//                    if($data['typeDocument']['code'] === '05' or $data['typeDocument']['code'] === '95')
//                        $QRCode = $urlquery.'/document/searchqr?documentkey=-----CUFECUDE-----';
//                    else
//                        $QRCode = 'NumFac: '.$data['resolution']['next_consecutive'].PHP_EOL.'FecFac: '.$data['date'].PHP_EOL.'NitFac: '.$data['user']['company']['identification_number'].PHP_EOL.'DocAdq: '.$data['customer']['company']['identification_number'].PHP_EOL.'ValFac: '.$data['requestedMonetaryTotals']['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$data['request']['tax_totals'][0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$data['requestedMonetaryTotals']['allowance_total_amount'].PHP_EOL.'ValTotal: '.$data['requestedMonetaryTotals']['payable_amount'].PHP_EOL.'CUFE: -----CUFECUDE-----'.PHP_EOL.$urlquery.'/document/searchqr?documentkey=-----CUFECUDE-----';
            $data['QRCode'] = $QRCode;
        }
        else{
            if($data['typeDocument']['code'] === '88')
                $urlquery = 'https://catalogo-vpfe.dian.gov.co';
            else
                if($data['company']['payroll_type_environment_id'] == 2)
                    $urlquery = 'https://catalogo-vpfe-hab.dian.gov.co';
                else
                    $urlquery = 'https://catalogo-vpfe.dian.gov.co';
            $QRCode = $urlquery.'/document/searchqr?documentkey=-----CUFECUDE-----';
            $data['QRCode'] = $QRCode;
        }
        try {
            $DOMDocumentXML = new DOMDocument();
            $DOMDocumentXML->preserveWhiteSpace = false;
            $DOMDocumentXML->formatOutput = true;
            if(isset($data['request']['is_eqdoc']) && ($data['request']['is_eqdoc'] == true)){
                if($data['request']['is_eqdoc'] == true && $data['typeDocument']['code'] == 94)
                    $DOMDocumentXML->loadXML(view("xml.91", $data)->render());
                if($data['request']['is_eqdoc'] == true && $data['typeDocument']['code'] == 93)
                    $DOMDocumentXML->loadXML(view("xml.92", $data)->render());
            }
            else{
                $DOMDocumentXML->loadXML(view("xml.{$data['typeDocument']['code']}", $data)->render());
            }
            if(isset($data['signedxml']) and ($data['typeDocument']['code'] === '89')){
                $rootNode = $DOMDocumentXML->documentElement;
                $nodeCDATAInvoice = $rootNode->getElementsByTagName("ExternalReference")->item(0);
                $elementCDATA = $DOMDocumentXML->createElement('cbc:Description');
                $CDATA = $DOMDocumentXML->createCDATASection($data['signedxml']);
                $elementCDATA->appendChild($CDATA);
                $nodeCDATAInvoice->appendChild($elementCDATA);
            }

            if(isset($data['appresponsexml']) and ($data['typeDocument']['code'] === '89')){
                $rootNode = $DOMDocumentXML->documentElement;
                $nodeCDATAAppResponse = $rootNode->getElementsByTagName("ExternalReference")->item(1);
                $elementCDATA = $DOMDocumentXML->createElement('cbc:Description');
                $CDATA = $DOMDocumentXML->createCDATASection($data['appresponsexml']);
                $elementCDATA->appendChild($CDATA);
                $nodeCDATAAppResponse->appendChild($elementCDATA);
            }

            return $DOMDocumentXML;
        } catch (InvalidArgumentException $e) {
            throw new Exception("The API does not support the type of document '{$data['typeDocument']['name']}' Error: {$e->getMessage()}");
        } catch (Exception $e) {
            throw new Exception("Error: {$e->getMessage()}");
        }
    }

    /**
     * Create pdf.
     *
     * @param array $data
     *
     * @return DOMDocument
     */
    protected function createPDF($user, $company, $customer, $typeDocument, $resolution, $date, $time, $paymentForm, $request, $cufecude, $tipodoc = "INVOICE", $withHoldingTaxTotal = NULL, $notes = NULL, $healthfields)
    {
        $template_json = false;
        set_time_limit(0);
        if(isset($request->invoice_template))
          if(!is_null($request->invoice_template) && ($request->invoice_template <> '')){
            if(password_verify($company->identification_number, $request->template_token)){
                $template_pdf = $request->invoice_template;
                $template_json = true;
            }
            else
                $template_pdf = env("GRAPHIC_REPRESENTATION_TEMPLATE", 2);
          }
          else
            $template_pdf = env("GRAPHIC_REPRESENTATION_TEMPLATE", 2);
        else
          $template_pdf = env("GRAPHIC_REPRESENTATION_TEMPLATE", 2);
        ini_set("pcre.backtrack_limit", "5000000");
        $QRStr = '';
//        try {
            define("DOMPDF_ENABLE_REMOTE", true);
            if(isset($request->establishment_logo)){
                $filenameLogo   = storage_path("app/public/{$company->identification_number}/alternate_{$company->identification_number}{$company->dv}.jpg");
                $this->storeLogo($request->establishment_logo);
            }
            else
                $filenameLogo   = storage_path("app/public/{$company->identification_number}/{$company->identification_number}{$company->dv}.jpg");

            $firma_facturacion = null;
            if(isset($request->firma_facturacion)){
                $firma_facturacion = "data:image/jpg;base64, ".$request->firma_facturacion;
            }

            $logo_empresa_emisora = null;
            if(isset($request->logo_empresa_emisora)){
                $logo_empresa_emisora = "data:image/jpg;base64, ".$request->logo_empresa_emisora;
            }

            if(file_exists($filenameLogo)) {
                $logoBase64     = base64_encode(file_get_contents($filenameLogo));
                $imgLogo        = "data:image/jpg;base64, ".$logoBase64;
            } else {
                $logoBase64     = NULL;
                $imgLogo        = NULL;
            }

            if($tipodoc == "ND")
                $totalbase = $request->requested_monetary_totals['line_extension_amount'];
            else
                $totalbase = $request->legal_monetary_totals['line_extension_amount'];

            if($tipodoc == 'TTR' or $tipodoc == 'SRV' or $tipodoc == 'CIN')
                if($company->eqdocs_type_environment_id == 1)
                    return 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                else
                    return 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;

            if($tipodoc == "INVOICE" || $tipodoc == "POS"){
                if($company->type_environment_id == 2){
                    if(isset($request->tax_totals[0]['tax_amount'])){
                        $qrBase64 = base64_encode(QrCode::format('png')
                                                ->errorCorrection('Q')
                                                ->size(220)
                                                ->margin(0)
//                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                        $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                    }
                    else{
                        $qrBase64 = base64_encode(QrCode::format('png')
                                                ->errorCorrection('Q')
                                                ->size(220)
                                                ->margin(0)
//                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                        $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                    }
                }
                else{
                    if(isset($request->tax_totals[0]['tax_amount'])){
                        $qrBase64 = base64_encode(QrCode::format('png')
                                                ->errorCorrection('Q')
                                                ->size(220)
                                                ->margin(0)
//                                            ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                    $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                        $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                    }
                    else{
                        $qrBase64 = base64_encode(QrCode::format('png')
                                                ->errorCorrection('Q')
                                                ->size(220)
                                                ->margin(0)
//                                            ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                    $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                        $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                    }
                }
                $imageQr    =  "data:image/png;base64, ".$qrBase64;

                if($template_json){
                    if($tipodoc == 'POS')
                        $pdf = $this->initMPdf('pos', $template_pdf);
                    else
                        $pdf = $this->initMPdf('invoice', $template_pdf);
                    $pdf->SetHTMLHeader(View::make("pdfs.".strtolower($tipodoc).".header".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields", "firma_facturacion", "logo_empresa_emisora")));
                    $pdf->SetHTMLFooter(View::make("pdfs.".strtolower($tipodoc).".footer".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields", "firma_facturacion", "logo_empresa_emisora")));
                    $pdf->WriteHTML(View::make("pdfs.".strtolower($tipodoc).".template".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields", "firma_facturacion", "logo_empresa_emisora")), HTMLParserMode::HTML_BODY);
                }
                else{
                    if($tipodoc == 'POS')
                        $pdf = $this->initMPdf('pos', $template_pdf);
                    else
                        $pdf = $this->initMPdf();
                    $pdf->SetHTMLHeader(View::make("pdfs.".strtolower($tipodoc).".header".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields", "firma_facturacion", "logo_empresa_emisora")));
                    $pdf->SetHTMLFooter(View::make("pdfs.".strtolower($tipodoc).".footer".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields", "firma_facturacion", "logo_empresa_emisora")));
                    $pdf->WriteHTML(View::make("pdfs.".strtolower($tipodoc).".template".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields", "firma_facturacion", "logo_empresa_emisora")), HTMLParserMode::HTML_BODY);
//                    $pdf->SetHTMLHeader(View::make("pdfs.invoice.header", compact("resolution", "date", "time", "user", "request", "company", "imgLogo")));
//                    $pdf->SetHTMLFooter(View::make("pdfs.invoice.footer", compact("resolution", "request", "cufecude", "date", "time")));
//                    $pdf->WriteHTML(View::make("pdfs.invoice.template".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields")), HTMLParserMode::HTML_BODY);
                }

                if($tipodoc == 'POS')
                    $filename = storage_path("app/public/{$company->identification_number}/POSS-{$resolution->next_consecutive}.pdf");
                else
                    $filename = storage_path("app/public/{$company->identification_number}/FES-{$resolution->next_consecutive}.pdf");
            }
            else
                if($tipodoc == "NC"){
                    if ($company->type_environment_id == 2){
                        if(isset($request->tax_totals[0]['tax_amount']))
                            $qrBase64 = base64_encode(QrCode::format('png')
                                                    ->errorCorrection('Q')
                                                    ->size(220)
                                                    ->margin(0)
//                                              ->generate('NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                              $QRStr = 'NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                ->generate('NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                        else
                                $qrBase64 = base64_encode(QrCode::format('png')
                                                    ->errorCorrection('Q')
                                                    ->size(220)
                                                    ->margin(0)
//                                              ->generate('NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                              $QRStr = 'NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                ->generate('NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                $QRStr = 'NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                    }
                    else{
                        if(isset($request->tax_totals[0]['tax_amount'])){
                            $qrBase64 = base64_encode(QrCode::format('png')
                                                ->errorCorrection('Q')
                                                ->size(220)
                                                ->margin(0)
//                                                ->generate('NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                        $QRStr = 'NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                ->generate('NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                            $QRStr = 'NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                            $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                        }
                        else{
                            $qrBase64 = base64_encode(QrCode::format('png')
                                                ->errorCorrection('Q')
                                                ->size(220)
                                                ->margin(0)
//                                                ->generate('NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                        $QRStr = 'NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                ->generate('NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                            $QRStr = 'NumCr: '.$request->number.PHP_EOL.'FecCr: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                            $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                        }
                    }
                    $imageQr    =  "data:image/png;base64, ".$qrBase64;

                    $pdf = $this->initMPdf('credit-note');
                    $pdf->SetHTMLHeader(View::make("pdfs.credit-note.header", compact("resolution", "date", "time", "user", "request", "company", "imgLogo")));
                    $pdf->SetHTMLFooter(View::make("pdfs.credit-note.footer", compact("resolution", "request", "cufecude", "date", "time")));
                    $pdf->WriteHTML(View::make("pdfs.credit-note.template".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields")), HTMLParserMode::HTML_BODY);
                    $filename = storage_path("app/public/{$company->identification_number}/NCS-{$resolution->next_consecutive}.pdf");
                }
                else{
                    if($tipodoc == "ND"){
                        if($company->type_environment_id == 2){
                            if(isset($request->tax_totals[0]['tax_amount'])){
                                $qrBase64 = base64_encode(QrCode::format('png')
                                                    ->errorCorrection('Q')
                                                    ->size(220)
                                                    ->margin(0)
//                                                    ->generate('NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->requested_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                            $QRStr = 'NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->requested_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                        ->generate('NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                        ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                $QRStr = 'NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                            }
                            else{
                                $qrBase64 = base64_encode(QrCode::format('png')
                                                    ->errorCorrection('Q')
                                                    ->size(220)
                                                    ->margin(0)
//                                                    ->generate('NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->requested_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                            $QRStr = 'NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->requested_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                        ->generate('NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                        ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                $QRStr = 'NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                            }
                        }
                        else{
                            if(isset($request->tax_totals[0]['tax_amount'])){
                                $qrBase64 = base64_encode(QrCode::format('png')
                                                    ->errorCorrection('Q')
                                                    ->size(220)
                                                    ->margin(0)
//                                                    ->generate('NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->requested_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                            $QRStr = 'NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->requested_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                    ->generate('NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                    ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                $QRStr = 'NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                            }
                            else{
                                $qrBase64 = base64_encode(QrCode::format('png')
                                                    ->errorCorrection('Q')
                                                    ->size(220)
                                                    ->margin(0)
//                                                    ->generate('NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->requested_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                            $QRStr = 'NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->requested_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                    ->generate('NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                    ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                $QRStr = 'NumDb: '.$request->number.PHP_EOL.'FecDb: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->requested_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->requested_monetary_totals['payable_amount'].PHP_EOL.'CUDE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                            }
                        }
                        $imageQr    =  "data:image/png;base64, ".$qrBase64;

                        $pdf = $this->initMPdf('debit-note');
                        $pdf->SetHTMLHeader(View::make("pdfs.debit-note.header", compact("resolution", "date", "time", "user", "request", "company", "imgLogo")));
                        $pdf->SetHTMLFooter(View::make("pdfs.debit-note.footer", compact("resolution", "request", "cufecude", "date", "time")));
                        $pdf->WriteHTML(View::make("pdfs.debit-note.template".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields")), HTMLParserMode::HTML_BODY);

                        $filename = storage_path("app/public/{$company->identification_number}/NDS-{$resolution->next_consecutive}.pdf");
                    }
                    else
                        if($tipodoc == "SUPPORTDOCUMENT"){
                            if($company->type_environment_id == 2){
                                if(isset($request->tax_totals[0]['tax_amount'])){
                                    $qrBase64 = base64_encode(QrCode::format('png')
                                                            ->errorCorrection('Q')
                                                            ->size(220)
                                                            ->margin(0)
//                                                            ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                    $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                            ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                            ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                    $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                    $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                }
                                else{
                                    $qrBase64 = base64_encode(QrCode::format('png')
                                                            ->errorCorrection('Q')
                                                            ->size(220)
                                                            ->margin(0)
//                                                            ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                    $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                            ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                            ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                    $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                    $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                }
                            }
                            else{
                                if(isset($request->tax_totals[0]['tax_amount'])){
                                    $qrBase64 = base64_encode(QrCode::format('png')
                                                            ->errorCorrection('Q')
                                                            ->size(220)
                                                            ->margin(0)
//                                                            ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                    $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                            ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                            ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                    $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                    $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                }
                                else{
                                    $qrBase64 = base64_encode(QrCode::format('png')
                                                            ->errorCorrection('Q')
                                                            ->size(220)
                                                            ->margin(0)
//                                                            ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                    $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                            ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                            ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                    $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                    $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                }
                            }
                            $imageQr    =  "data:image/png;base64, ".$qrBase64;

                            $pdf = $this->initMPdf();
                            $pdf->SetHTMLHeader(View::make("pdfs.support.header".$template_pdf, compact("resolution", "date", "time", "user", "request", "company", "imgLogo", "logo_empresa_emisora")));
                            $pdf->SetHTMLFooter(View::make("pdfs.support.footer".$template_pdf, compact("resolution", "request", "cufecude", "date", "time", "logo_empresa_emisora")));
                            $pdf->WriteHTML(View::make("pdfs.support.template".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields", "logo_empresa_emisora")), HTMLParserMode::HTML_BODY);

                            $filename = storage_path("app/public/{$company->identification_number}/DSS-{$resolution->next_consecutive}.pdf");
                        }
                        else
                            if($tipodoc == "SUPPORTDOCUMENTNOTE"){
                                if($company->type_environment_id == 2){
                                    if(isset($request->tax_totals[0]['tax_amount'])){
                                        $qrBase64 = base64_encode(QrCode::format('png')
                                                                ->errorCorrection('Q')
                                                                ->size(220)
                                                                ->margin(0)
//                                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                                ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                        $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                    }
                                    else{
                                        $qrBase64 = base64_encode(QrCode::format('png')
                                                                ->errorCorrection('Q')
                                                                ->size(220)
                                                                ->margin(0)
//                                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                                ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                        $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                    }
                                }
                                else{
                                    if(isset($request->tax_totals[0]['tax_amount'])){
                                        $qrBase64 = base64_encode(QrCode::format('png')
                                                                ->errorCorrection('Q')
                                                                ->size(220)
                                                                ->margin(0)
//                                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                                ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                        $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                    }
                                    else{
                                        $qrBase64 = base64_encode(QrCode::format('png')
                                                                ->errorCorrection('Q')
                                                                ->size(220)
                                                                ->margin(0)
//                                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: '.$request->tax_totals[0]['tax_amount'].PHP_EOL.'ValOtroIm: '.$request->legal_monetary_totals['allowance_total_amount'].PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
//                                                                ->generate('NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                                                                ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
//                                        $QRStr = 'NumFac: '.$request->number.PHP_EOL.'FecFac: '.$date.PHP_EOL.'NitFac: '.$company->identification_number.PHP_EOL.'DocAdq: '.$customer->company->identification_number.PHP_EOL.'ValFac: '.$request->legal_monetary_totals['tax_exclusive_amount'].PHP_EOL.'ValIva: 0.00'.PHP_EOL.'ValOtroIm: 0.00'.PHP_EOL.'ValTotal: '.$request->legal_monetary_totals['payable_amount'].PHP_EOL.'CUFE: '.$cufecude.PHP_EOL.'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                        $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                                    }
                                }
                                $imageQr    =  "data:image/png;base64, ".$qrBase64;

                                $pdf = $this->initMPdf();
                                $pdf->SetHTMLHeader(View::make("pdfs.support-credit-note.header", compact("resolution", "date", "time", "user", "request", "company", "imgLogo")));
                                $pdf->SetHTMLFooter(View::make("pdfs.support-credit-note.footer", compact("resolution", "request", "cufecude", "date", "time")));
                                $pdf->WriteHTML(View::make("pdfs.support-credit-note.template".$template_pdf, compact("user", "company", "customer", "resolution", "date", "time", "paymentForm", "request", "cufecude", "imageQr", "imgLogo", "withHoldingTaxTotal", "notes", "healthfields")), HTMLParserMode::HTML_BODY);

                                $filename = storage_path("app/public/{$company->identification_number}/NDSNS-{$resolution->next_consecutive}.pdf");
                            }
            }
            $pdf->Output($filename);
            return $QRStr;
    }

    /**
     * Create payroll pdf.
     *
     * @param array $data
     *
     * @return DOMDocument
     */
    protected function createPDFPayroll($user, $company, $predecessor, $period, $worker, $resolution, $payment, $payment_dates, $typeDocument, $notes = NULL, $accrued, $deductions, $request, $cufecude, $tipodoc = "PAYROLL")
    {
        set_time_limit(0);
        ini_set("pcre.backtrack_limit", "5000000");
        $QRStr = '';
//        try {
            define("DOMPDF_ENABLE_REMOTE", true);
            if(isset($request->establishment_logo)){
                $filenameLogo   = storage_path("app/public/{$company->identification_number}/alternate_{$company->identification_number}{$company->dv}.jpg");
                $this->storeLogo($request->establishment_logo);
            }
            else
                $filenameLogo   = storage_path("app/public/{$company->identification_number}/{$company->identification_number}{$company->dv}.jpg");

            if(file_exists($filenameLogo)) {
                $logoBase64     = base64_encode(file_get_contents($filenameLogo));
                $imgLogo        = "data:image/jpg;base64, ".$logoBase64;
            } else {
                $logoBase64     = NULL;
                $imgLogo        = NULL;
            }
            if($tipodoc = "PAYROLL"){
                if ($company->payroll_type_environment_id == 2){
                    $qrBase64 = base64_encode(QrCode::format('png')
                                            ->errorCorrection('Q')
                                            ->size(220)
                                            ->margin(0)
                                            ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                    $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                }
                else{
                    $qrBase64 = base64_encode(QrCode::format('png')
                                            ->errorCorrection('Q')
                                            ->size(220)
                                            ->margin(0)
                                            ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                    $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
                }
            }

            $imageQr    =  "data:image/png;base64, ".$qrBase64;
            $pdf = $this->initMPdf('payroll');
            $pdf->SetHTMLHeader(View::make("pdfs.payroll.header", compact("resolution", "period", "user", "request", "company", "imgLogo")));
            $pdf->SetHTMLFooter(View::make("pdfs.payroll.footer", compact("resolution", "request", "cufecude", "period")));
            $pdf->WriteHTML(View::make("pdfs.payroll.template", compact("user", "company", "predecessor", "period", "worker", "resolution", "payment", "typeDocument", "notes", "accrued", "deductions", "request", "imageQr")), HTMLParserMode::HTML_BODY);

            if($request->type_document_id == 9)
                $filename = storage_path("app/public/{$company->identification_number}/NIS-{$resolution->next_consecutive}.pdf");
            else
                $filename = storage_path("app/public/{$company->identification_number}/NAS-{$resolution->next_consecutive}.pdf");
            $pdf->Output($filename);
//            return compact("resolution", "period", "user", "request", "company", "imgLogo");
            return $QRStr;
    }

    /**
     * Create event pdf.
     *
     * @param array $data
     *
     * @return DOMDocument
     */
    protected function createPDFEvent($user, $company, $typeDocument, $event, $sender, $documentReference, $typeDocumentReference, $issuerparty, $typerejection, $notes, $request, $cufecude)
    {
        set_time_limit(0);
        ini_set("pcre.backtrack_limit", "5000000");
        $QRStr = '';
//        try {
            define("DOMPDF_ENABLE_REMOTE", true);
            if(isset($request->establishment_logo)){
                $filenameLogo   = storage_path("app/public/{$company->identification_number}/alternate_{$company->identification_number}{$company->dv}.jpg");
                $this->storeLogo($request->establishment_logo);
            }
            else
                $filenameLogo   = storage_path("app/public/{$company->identification_number}/{$company->identification_number}{$company->dv}.jpg");

            if(file_exists($filenameLogo)) {
                $logoBase64     = base64_encode(file_get_contents($filenameLogo));
                $imgLogo        = "data:image/jpg;base64, ".$logoBase64;
            } else {
                $logoBase64     = NULL;
                $imgLogo        = NULL;
            }
            if ($company->payroll_type_environment_id == 2){
                $qrBase64 = base64_encode(QrCode::format('png')
                                        ->errorCorrection('Q')
                                        ->size(220)
                                        ->margin(0)
                                        ->generate('https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                $QRStr = 'https://catalogo-vpfe-hab.dian.gov.co/document/searchqr?documentkey='.$cufecude;
            }
            else{
                $qrBase64 = base64_encode(QrCode::format('png')
                                        ->errorCorrection('Q')
                                        ->size(220)
                                        ->margin(0)
                                        ->generate('https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude));
                $QRStr = 'https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey='.$cufecude;
            }

            $imageQr    =  "data:image/png;base64, ".$qrBase64;
            $pdf = $this->initMPdf('event');
            $pdf->SetHTMLHeader(View::make("pdfs.event.header", compact("user", "company", "typeDocument", "event", "sender", "documentReference", "typeDocumentReference", "issuerparty", "typerejection", "notes", "request", "cufecude", "imageQr", "imgLogo")));
            $pdf->SetHTMLFooter(View::make("pdfs.event.footer", compact("user", "company", "typeDocument", "event", "sender", "documentReference", "typeDocumentReference", "issuerparty", "typerejection", "notes", "request", "cufecude", "imageQr", "imgLogo")));
            $pdf->WriteHTML(View::make("pdfs.event.template", compact("user", "company", "typeDocument", "event", "sender", "documentReference", "typeDocumentReference", "issuerparty", "typerejection", "notes", "request", "cufecude", "imageQr", "imgLogo")), HTMLParserMode::HTML_BODY);

            $filename = preg_replace("/[\r\n|\n|\r]+/", "", storage_path("app/public/{$company->identification_number}/EVS-{$sender->company->identification_number}{$documentReference->number}{$event->code}.pdf"));
            $pdf->Output($filename);
//            return compact("resolution", "period", "user", "request", "company", "imgLogo");
            return $QRStr;
    }

    protected function initMPdf(string $type = 'invoice', string $template = null): Mpdf
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        $margin_left = null;
        $margin_right = null;
        $margin_top = null;
        $margin_bottom = null;

        $filename = base_path('resources/views/pdfs/' . $type . '/config'.$template.'.json');
            if (file_exists($filename)) {
            $jsonD =  file_get_contents('config'.$template.'.json');
            $margin = json_decode($jsonD,true);
            if(isset($margin)){
                $arr_margin = explode(",",$request->margin);
                $margin_top = $margin['top'];
                $margin_right = $margin['right'];
                $margin_bottom = $margin['bottom'];
                $margin_left = $margin['left'];
            }
        }

         if($template == 1){

            $format_print = 'A4';
            $margin_top = '28';
            $margin_left = '5';
            $margin_right = '5';
            $margin_bottom = '12';

         }elseif($template == 2)  {

            $format_print = 'A4';
            $margin_top = '39';
            $margin_left = '10';
            $margin_right = '10';
            $margin_bottom = '12';

         } elseif($template == 3)  {


            $margin_top = '2';
            // Definir el ancho y el alto en milímetros (por ejemplo, para un largo de 150mm)
            $ancho = 80;  // 80mm de ancho para la impresora de tirilla
            $alto = 297;   // El alto puede ser más largo dependiendo del contenido a imprimir
            $margin_left = '2';
            $margin_right = '2';
            $margin_bottom = '5';

            $format_print = [$ancho, $alto];

         }

        if($template){
            $pdf = new Mpdf([
                'fontDir' => array_merge($fontDirs, [
                    base_path('public/fonts/roboto/'),
                ]),
                'fontdata' => $fontData + [
                    'Roboto' => [
                        'R' => 'Roboto-Regular.ttf',
                        'B' => 'Roboto-Bold.ttf',
                        'I' => 'Roboto-Italic.ttf',
                    ]
                ],
                'default_font' => 'Roboto',
                'margin_left' => $margin_left,
                'margin_right' => $margin_right,
                'margin_top' => $margin_top,
                'margin_bottom' => $margin_bottom ,
                'margin_header' => 5,
                'margin_footer' => 2,
                'format' => isset($format_print) ? $format_print : null ,  // Esta es la línea que se añade para definir el tamaño del papel
            ]);
        }
        else{
            $pdf = new Mpdf([
            'fontDir' => array_merge($fontDirs, [
                base_path('public/fonts/roboto/'),
            ]),
            'fontdata' => $fontData + [
                'Roboto' => [
                    'R' => 'Roboto-Regular.ttf',
                    'B' => 'Roboto-Bold.ttf',
                    'I' => 'Roboto-Italic.ttf',
                ]
            ],
            'default_font' => 'Roboto',
            'margin_left' => 5,
            'margin_top' => 35,
            'margin_bottom' => 5,
            'margin_header' => 5,
            'margin_footer' => 2
            ]);
        }
        if($template)
            $pdf->WriteHTML(file_get_contents(base_path('resources/views/pdfs/' . $type . '/styles'.$template.'.css')), HTMLParserMode::HEADER_CSS);
        else
            $pdf->WriteHTML(file_get_contents(base_path('resources/views/pdfs/' . $type . '/styles.css')), HTMLParserMode::HEADER_CSS);
        return $pdf;
    }
    /**
     * Zip Email Payroll
     *
     */
    protected function zipEmailPayroll($xml, $pdf)
    {
//        $nameXML = preg_replace("/[\r\n|\n|\r]+/", "", $xml);
        $namePDF = preg_replace("/[\r\n|\n|\r]+/", "", $pdf);
        $nameXML = preg_replace("/[\r\n|\n|\r]+/", "", substr($pdf, 0, strlen($pdf) - 3)."xml");
        $nameZip = preg_replace("/[\r\n|\n|\r]+/", "", substr($pdf, 0, strlen($pdf) - 3)."zip");

        $zip = new ZipArchive();

        $result_code = $zip->open($nameZip, ZipArchive::CREATE);
        $zip->addFile($nameXML, basename($nameXML));
        $zip->addFile($namePDF, str_replace('xml', 'pdf', basename($nameXML)));

        $zip->close();
        return $nameZip;
    }

    /**
     * Zip Email Event
     *
     */
    protected function zipEmailEvent($xml, $pdf)
    {
//        $nameXML = preg_replace("/[\r\n|\n|\r]+/", "", $xml);
        $namePDF = preg_replace("/[\r\n|\n|\r]+/", "", $pdf);
        $nameXML = preg_replace("/[\r\n|\n|\r]+/", "", substr($pdf, 0, strlen($pdf) - 3)."xml");
        $nameZip = preg_replace("/[\r\n|\n|\r]+/", "", substr($pdf, 0, strlen($pdf) - 3)."zip");

        $zip = new ZipArchive();

        $result_code = $zip->open($nameZip, ZipArchive::CREATE);
        $zip->addFile($nameXML, basename($nameXML));
        $zip->addFile($namePDF, str_replace('xml', 'pdf', basename($nameXML)));

        $zip->close();
        return $nameZip;
    }

    /**
     * Zip Email
     *
     */
    protected function zipEmail($xml, $pdf)
    {
        $nameXML = preg_replace("/[\r\n|\n|\r]+/", "", $xml);
        $namePDF = preg_replace("/[\r\n|\n|\r]+/", "", $pdf);
        $nameZip = preg_replace("/[\r\n|\n|\r]+/", "", substr($xml, 0, strlen($xml) - 3)."zip");
        $nameAD = preg_replace("/[\r\n|\n|\r]+/", "", substr($xml, 0, strlen($xml) - 4));

        $zip = new ZipArchive();

        $result_code = $zip->open($nameZip, ZipArchive::CREATE);
        $zip->addFile($nameXML, basename($nameXML));
        $zip->addFile($namePDF, str_replace('xml', 'pdf', basename($nameXML)));

        $R = substr($nameAD, 0, strlen($nameAD) - strlen(basename($nameAD)));
        $listado = glob($R.'anx-*'.basename($nameAD).'.*');
        foreach($listado as $elemento) {
            $zip->addFile($elemento, basename($elemento));
        }

        $zip->close();
        return $nameZip;
    }

    /**
     * Zip base64.
     *
     * @param \App\Company              $company
     * @param \App\Resolution           $resolution
     * @param \Stenfrank\UBL21dian\Sign $sign
     *
     * @return string
     */
    protected function zipBase64(Company $company, Resolution $resolution, Sign $sign, $GuardarEn = false, $batch = false, $ret_array = false)
    {
        $dir = preg_replace("/[\r\n|\n|\r]+/", "", "zip/{$resolution->company_id}");
        $nameXML = preg_replace("/[\r\n|\n|\r]+/", "", $this->getFileName($company, $resolution));
        if ($batch)
          $nameZip = $batch.".zip";
        else
          $nameZip = preg_replace("/[\r\n|\n|\r]+/", "", $this->getFileName($company, $resolution, 6, '.zip'));

        $this->pathZIP = preg_replace("/[\r\n|\n|\r]+/", "", "app/zip/{$resolution->company_id}/{$nameZip}");

        Storage::put(preg_replace("/[\r\n|\n|\r]+/", "", "xml/{$resolution->company_id}/{$nameXML}"), $sign->xml);

        if (!Storage::has($dir)) {
            Storage::makeDirectory($dir);
        }

        $zip = new ZipArchive();

        $result_code = $zip->open(storage_path($this->pathZIP), ZipArchive::CREATE);
        if($result_code !== true){
            $zip = new zipfileDIAN();
            $zip->add_file(implode("", file(preg_replace("/[\r\n|\n|\r]+/", "", storage_path("app/xml/{$resolution->company_id}/{$nameXML}")))), preg_replace("/[\r\n|\n|\r]+/", "", $nameXML));
			Storage::put(preg_replace("/[\r\n|\n|\r]+/", "", "zip/{$resolution->company_id}/{$nameZip}"), $zip->file());
        }
        else{
            $zip->addFile(preg_replace("/[\r\n|\n|\r]+/", "", storage_path("app/xml/{$resolution->company_id}/{$nameXML}")), preg_replace("/[\r\n|\n|\r]+/", "", $nameXML));
            $zip->close();
        }

        if ($GuardarEn){
            copy(preg_replace("/[\r\n|\n|\r]+/", "", storage_path("app/xml/{$resolution->company_id}/{$nameXML}")), $GuardarEn.".xml");
            copy(preg_replace("/[\r\n|\n|\r]+/", "", storage_path($this->pathZIP)), $GuardarEn.".zip");
        }

        if($ret_array)
            return[
                'ZipBase64Bytes' => $this->ZipBase64Bytes = base64_encode(file_get_contents(preg_replace("/[\r\n|\n|\r]+/", "", storage_path($this->pathZIP)))),
                'xml_filename' => $nameXML
            ];
        else
            return $this->ZipBase64Bytes = base64_encode(file_get_contents(preg_replace("/[\r\n|\n|\r]+/", "", storage_path($this->pathZIP))));
    }

    /**
     * Zip base64.
     *
     * @param \App\Company              $company
     * @param \App\Resolution           $resolution
     * @param \Stenfrank\UBL21dian\Sign $sign
     *
     * @return string
     */
    protected function zipBase64SendEvent(Company $company, $codeevent, $identificationnumber, $prefixnumberdoc, Sign $sign, $GuardarEn = false)
    {
        $dir = preg_replace("/[\r\n|\n|\r]+/", "", "zip/{$company->id}");
        $nameXML = preg_replace("/[\r\n|\n|\r]+/", "", $this->getFileNameSendEvent($codeevent, $identificationnumber, $prefixnumberdoc));
        $nameZip = preg_replace("/[\r\n|\n|\r]+/", "", $this->getFileNameSendEvent($codeevent, $identificationnumber, $prefixnumberdoc, '.zip'));
        $GuardarEn = preg_replace("/[\r\n|\n|\r]+/", "", $GuardarEn);
        $this->pathZIP = preg_replace("/[\r\n|\n|\r]+/", "", "app/zip/{$company->id}/{$nameZip}");

        Storage::put(preg_replace("/[\r\n|\n|\r]+/", "", "xml/{$company->id}/{$nameXML}"), $sign->xml);

        if (!Storage::has($dir)) {
            Storage::makeDirectory($dir);
        }

        $zip = new ZipArchive();

        $result_code = $zip->open(storage_path($this->pathZIP), ZipArchive::CREATE);
        if($result_code !== true){
            $zip = new zipfileDIAN();
            $zip->add_file(implode("", file(preg_replace("/[\r\n|\n|\r]+/", "", storage_path("app/xml/{$company->id}/{$nameXML}")))), preg_replace("/[\r\n|\n|\r]+/", "", $nameXML));
			Storage::put(preg_replace("/[\r\n|\n|\r]+/", "", "zip/{$company->id}/{$nameZip}"), $zip->file());
        }
        else{
            $zip->addFile(preg_replace("/[\r\n|\n|\r]+/", "", storage_path("app/xml/{$company->id}/{$nameXML}")), preg_replace("/[\r\n|\n|\r]+/", "", $nameXML));
            $zip->close();
        }
        if ($GuardarEn){
            copy(preg_replace("/[\r\n|\n|\r]+/", "", storage_path("app/xml/{$company->id}/{$nameXML}")), $GuardarEn.".xml");
            copy(preg_replace("/[\r\n|\n|\r]+/", "", storage_path($this->pathZIP)), $GuardarEn.".zip");
        }

        return $this->ZipBase64Bytes = base64_encode(file_get_contents(preg_replace("/[\r\n|\n|\r]+/", "", storage_path($this->pathZIP))));
    }

    /**
     * Zip base64 Send Document XML.
     *
     * @param \App\Company              $company
     * @param \App\Resolution           $resolution
     * @param \Stenfrank\UBL21dian\Sign $sign
     *
     * @return string
     */
    protected function zipBase64SendDocument($passwordcertificate, $identificationnumber, $tipodoc, $documentnumber, Sign $sign, $GuardarEn = false)
    {
        $dir = preg_replace("/[\r\n|\n|\r]+/", "", "zip/{$passwordcertificate}");
        $nameXML = preg_replace("/[\r\n|\n|\r]+/", "", $this->getFileNameSendDocument($identificationnumber, $tipodoc, $documentnumber));
        $nameZip = preg_replace("/[\r\n|\n|\r]+/", "", $this->getFileNameSendDocument($identificationnumber, 'ZIP', $documentnumber, '.zip'));

        $this->pathZIP = preg_replace("/[\r\n|\n|\r]+/", "", "app/zip/{$passwordcertificate}/{$nameZip}");

        Storage::put(preg_replace("/[\r\n|\n|\r]+/", "", "xml/{$passwordcertificate}/{$nameXML}"), $sign->xml);

        if (!Storage::has($dir)) {
            Storage::makeDirectory($dir);
        }

        $zip = new ZipArchive();

        $result_code = $zip->open(storage_path($this->pathZIP), ZipArchive::CREATE);
        if($result_code !== true){
            $zip = new zipfileDIAN();
            $zip->add_file(implode("", file(preg_replace("/[\r\n|\n|\r]+/", "", storage_path("app/xml/{$passwordcertificate}/{$nameXML}")))), preg_replace("/[\r\n|\n|\r]+/", "", $nameXML));
			Storage::put(preg_replace("/[\r\n|\n|\r]+/", "", "zip/{$passwordcertificate}/{$nameZip}"), $zip->file());
        }
        else{
            $zip->addFile(preg_replace("/[\r\n|\n|\r]+/", "", storage_path("app/xml/{$passwordcertificate}/{$nameXML}")), preg_replace("/[\r\n|\n|\r]+/", "", $nameXML));
            $zip->close();
        }

        if ($GuardarEn){
            copy(preg_replace("/[\r\n|\n|\r]+/", "", storage_path("app/xml/{$passwordcertificate}/{$nameXML}")), $GuardarEn.".xml");
            copy(preg_replace("/[\r\n|\n|\r]+/", "", storage_path($this->pathZIP)), $GuardarEn.".zip");
        }

        return $this->ZipBase64Bytes = base64_encode(file_get_contents(preg_replace("/[\r\n|\n|\r]+/", "", storage_path($this->pathZIP))));
    }

    /**
     * Get file name.
     *
     * @param \App\Company    $company
     * @param \App\Resolution $resolution
     *
     * @return string
     */
    protected function getFileName(Company $company, Resolution $resolution, $typeDocumentID = null, $extension = '.xml')
    {
        $date = now();
        $prefix = (is_null($typeDocumentID)) ? $resolution->type_document->prefix : TypeDocument::findOrFail($typeDocumentID)->prefix;

        $send = $company->send()->firstOrCreate([
            'year' => $date->format('Y'),
            'type_document_id' => $typeDocumentID ?? $resolution->type_document_id,
        ]);

        $name = "{$prefix}{$this->stuffedString($company->identification_number)}{$this->ppp}{$date->format('y')}{$this->stuffedString($send->next_consecutive ?? 1, 8)}{$extension}";

        $send->increment('next_consecutive');

        return $name;
    }

    /**
     * Get file name Send Document.
     *
     *
     * @return string
     */
    protected function getFileNameSendDocument($identificationnumber, $tipodoc = null, $documentnumber, $extension = '.xml')
    {
        $date = now();
        if($tipodoc == 'INVOICE')
            $prefix = 'fv';
        else
            if($tipodoc == 'NC')
                $prefix = 'nc';
            else
                if($tipodoc == 'ND')
                    $prefix = 'nd';
                else
                    $prefix = 'z';

        $send = $documentnumber;

        $name = "{$prefix}{$this->stuffedString($identificationnumber)}{$this->ppp}{$date->format('y')}{$this->stuffedString($documentnumber ?? 1, 8)}{$extension}";

        return $name;
    }

    /**
     * Get file name Send Document.
     *
     *
     * @return string
     */
    protected function getFileNameSendEvent($codeevent, $identificationnumber, $documentnumber, $extension = '.xml')
    {
        $date = now();
        $prefix = 'ar';

        $send = $documentnumber;

        $name = "{$prefix}{$codeevent}{$this->stuffedString($identificationnumber)}{$this->ppp}{$date->format('y')}{$this->stuffedString($documentnumber ?? 1, 8)}{$extension}";

        return $name;
    }

    /**
     * Stuffed string.
     *
     * @param string $string
     * @param int    $length
     * @param int    $padString
     * @param int    $padType
     *
     * @return string
     */
    protected function stuffedString($string, $length = 10, $padString = 0, $padType = STR_PAD_LEFT)
    {
        return str_pad($string, $length, $padString, $padType);
    }

    /**
     * Get ZIP.
     *
     * @return string
     */
    protected function getZIP()
    {
        return $this->ZipBase64Bytes;
    }

    /**
     * post sendEmail.
     *
     * @return string
     */
    protected function sendEmail(string $filename, array $data){
        $company    = $data['user'];
        $customer   = $data['customer'];

        $message = Mail::to($customer->email)->send(new InvoiceMail($data, $filename));

        return $message;
    }

    protected function InvoiceByZipKey($company_idnumber, $zipkey){
        $directory = storage_path('app/public/'.$company_idnumber, SCANDIR_SORT_DESCENDING);
        $scanned_directory = array_diff(scandir($directory), array('..', '.'));
        foreach($scanned_directory as $archivo){
            if (substr($archivo, 0, 7) == "RptaFE-"){
                $signedxml = file_get_contents(storage_path("app/public/".$company_idnumber."/".$archivo));
                if(strpos($signedxml, "<b:ZipKey>{$zipkey}</b:ZipKey>") <> false)
                    return substr($archivo, strpos($archivo, '-') + 1);
            }
        }
        return false;
    }

    protected function ValueXML($stringXML, $xpath){
        if(substr($xpath, 0, 1) != '/')
            return NULL;
        $search = substr($xpath, 1, strpos(substr($xpath, 1), '/'));
        $posinicio = strpos($stringXML, "<".$search);
        if($posinicio == 0 and $search != 's:Envelope')
           return NULL;
        $posinicio = strpos($stringXML, ">", $posinicio) + 1;
        $posCierre = strpos($stringXML, "</".$search.">", $posinicio);
        if($posCierre == 0)
            return NULL;
        $valorXML = substr($stringXML, $posinicio, $posCierre - $posinicio);
        if(strcmp(substr($xpath, strpos($xpath, $search) + strlen($search)), '/') != 0)
            return $this->ValueXML($valorXML, substr($xpath, strpos($xpath, $search) + strlen($search)));
        else
            return $valorXML;
    }

    protected function readSimpleXML($path){
        $xml = new \SimpleXMLElement(file_get_contents($path));
        return $xml;
    }

    protected function readXML($path){
        $xml = new \SimpleXMLElement(file_get_contents($path));
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        return $dom;
    }

    protected function ActualizarTablas(){
        // User
        $user = auth()->user();

        // type regimes

        $typeregime = TypeRegime::where('id', '!=', '')->get();
        foreach($typeregime as $regime){
            switch($regime->id){
                case '1':
                    $regime->name = 'Responsable de IVA';
                    $regime->code = '48';
                    break;
                case '2':
                    $regime->name = 'No Responsable de IVA';
                    $regime->code = '49';
                    break;
            }
            $regime->save();
        }

        // type liabilities

        $typeliabilities = TypeLiability::where('id', '!=', 7)->where('id', '!=', 9)->where('id', '!=', 14)->where('id', '!=', 112)->where('id', '!=', 117)->get();
        if($typeliabilities != NULL){
            foreach($typeliabilities as $typeliabilitie)
                $typeliabilitie->delete();
        }

        // type operations

        $borrar = TypeOperation::where('id', 1);
        if($borrar != NULL)
            $borrar->delete();
        $borrar = TypeOperation::where('id', 2);
        if($borrar != NULL)
            $borrar->delete();
        $borrar = TypeOperation::where('id', 3);
        if($borrar != NULL)
            $borrar->delete();

        $typeoperation = TypeOperation::where('id', '>=', 4)->where('id', '<=', 12)->get();
        foreach($typeoperation as $operation){
            switch($operation->id){
                case '4':
                    $operation->name = 'Nota Débito para facturación electrónica V1 (Decreto 2242)';
                    $operation->code = '33';
                    break;
                case '5':
                    $operation->name = 'Nota Débito sin referencia a facturas';
                    $operation->code = '32';
                    break;
                case '6':
                    $operation->name = 'Nota Débito que referencia una factura electrónica';
                    $operation->code = '30';
                    break;
                case '7':
                    $operation->name = 'Nota Crédito para facturación electrónica V1 (Decreto 2242)';
                    $operation->code = '23';
                    break;
                case '8':
                    $operation->name = 'Nota Crédito sin referencia a facturas';
                    $operation->code = '22';
                    break;
                case '9':
                    $operation->name = 'AIU';
                    $operation->code = '09';
                    break;
                case '10':
                    $operation->name = 'Estandar';
                    $operation->code = '10';
                    break;
                case '11':
                    $operation->name = 'Mandatos';
                    $operation->code = '11';
                    break;
                case '12':
                    $operation->name = 'Nota Crédito que referencia una factura electrónica';
                    $operation->code = '20';
                    break;
            }
            $operation->save();
        }

        // taxes

        $taxes = Tax::where('id', '!=', '')->get();
        foreach($taxes as $tax){
            switch($tax->id){
                case '1':
                    $tax->description = 'Impuesto sobre la Ventas';
                    break;
                case '2':
                    $tax->description = 'Impuesto al Consumo Departamental';
                    break;
                case '6':
                    $tax->name = 'ReteRenta';
                    break;
            }
            $tax->save();
        }

        // type_documents

        $type_documents = TypeDocument::where('id', '==', '3')->get();
        foreach($type_documents as $type_document){
            switch($type_document->id){
                case '3':
                    $type_document->cufe_algorithm = 'CUDE-SHA384';
                    break;
            }
            $type_document->save();
        }

        $type_documento = TypeDocument::updateOrCreate(
                            ['id' => 7],
                            ['name' => 'AttachedDocument',
                             'code' => '89',
                             'cufe_algorithm' => '',
                             'prefix' => 'at']
                          );
    }

    protected function validarDigVerifDIAN($nit)
    {
        if(is_numeric(trim($nit))){
            $secuencia = array(3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71);
            $d = str_split(trim($nit));
            krsort($d);
            $cont = 0;
            unset($val);
            foreach ($d as $key => $value) {
                $val[$cont] = $value * $secuencia[$cont];
                $cont++;
            }
            $suma = array_sum($val);
            $div = intval($suma / 11);
            $num = $div * 11;
            $resta = $suma - $num;
            if ($resta == 1)
                return $resta;
            else
                if($resta != 0)
                    return 11 - $resta;
                else
                    return $resta;
        } else {
            return FALSE;
        }
    }

    protected function saveAnnexes($annexes, $filename)
    {
        $company = auth()->user()->company;
        $i = 0;
        foreach($annexes as $document){
            $i++;
            $document_filename = "anx-{$i}-{$filename}.{$document['extension']}";
            if (base64_decode($document['document'], true)) {
                Storage::put("public/{$company->identification_number}/{$document_filename}", base64_decode($document['document']));
            }
        }
    }

    protected function debugTofile($variable)
    {
        $file = fopen(storage_path("DEBUG.TXT"), "a+");
        fwrite($file, \Carbon\Carbon::now()->format('Y-m-d H:i'));
        fwrite($file, ' --> '.json_encode($variable));
        fwrite($file, PHP_EOL);
        fwrite($file, PHP_EOL);
        fclose($file);
    }

    protected function split_name($name){
        $name = strtoupper($name);
        if(strpos($name, " DE LA "))
            $name = str_replace(" DE LA ", " DE_LA_", $name);
        if(strpos($name, " DE "))
            $name = str_replace(" DE ", " DE_", $name);
        return explode(' ', $name);
    }

    public function storeLogo($base64logo)
    {
        try {
            if (!base64_decode($base64logo, true)) {
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
            $name = "alternate_{$company->identification_number}{$company->dv}.jpg";
            Storage::put("public/{$company->identification_number}/{$name}", base64_decode($base64logo));

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

    public function qty_docs_period($document_name = "INVOICE"){
        $qty_docs = 0;

        try{
            $company = auth()->user()->company;

            if(!is_null($company->absolut_start_plan_date)){
                if($document_name == "ABSOLUT"){
                    $qty_docs = (Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('type_document_id', 11)->where('created_at', '>=', $company->absolut_start_plan_date)->count()) +
                                (Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('type_document_id', 13)->where('created_at', '>=', $company->absolut_start_plan_date)->count()) +
                                (ReceivedDocument::where('customer', $company->identification_number)->where('state_document_id', 1)->where('created_at', '>=', $company->absolut_start_plan_date)->count() + Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('aceptacion', 1)->where('created_at', '>=', $company->absolut_start_plan_date)->count()) +
                                (DocumentPayroll::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('created_at', '>=', $company->absolut_start_plan_date)->count()) +
                                (Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('type_document_id', 1)->where('created_at', '>=', $company->absolut_start_plan_date)->count()) +
                                (Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('type_document_id', 2)->where('created_at', '>=', $company->absolut_start_plan_date)->count()) +
                                (Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('type_document_id', 3)->where('created_at', '>=', $company->absolut_start_plan_date)->count()) +
                                (Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('type_document_id', 4)->where('created_at', '>=', $company->absolut_start_plan_date)->count()) +
                                (Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('type_document_id', 5)->where('created_at', '>=', $company->absolut_start_plan_date)->count());
                    return $qty_docs;
                }
            }
            else{
                if(!is_null($company->start_plan_date))
                    if($document_name == "INVOICE"){
                        $qty_docs = Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('type_document_id', '>=', '1')->where('type_document_id', '<=', '5')->where('created_at', '>=', $company->start_plan_date)->count();
                        return $qty_docs;
                    }

                if(!is_null($company->start_plan_date2)){
                    if($document_name == "PAYROLL"){
                        $qty_docs = DocumentPayroll::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('created_at', '>=', $company->start_plan_date2)->count();
                        return $qty_docs;
                    }
                }

                if(!is_null($company->start_plan_date3))
                    if($document_name == "RADIAN"){
                        $qty_docs = ReceivedDocument::where('customer', $company->identification_number)->where('state_document_id', 1)->where('created_at', '>=', $company->start_plan_date3)->count() + Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('aceptacion', 1)->where('created_at', '>=', $company->start_plan_date3)->count();
                        return $qty_docs;
                    }

                if(!is_null($company->start_plan_date4))
                    if($document_name == "SUPPORT DOCUMENT"){
//                        $qty_docs = Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->where('type_document_id', '11')->orWhere('type_document_id', '13')->where('created_at', '>=', $company->start_plan_date4)->count();
                        $qty_docs = Document::where('identification_number', $company->identification_number)->where('state_document_id', 1)->whereIn('type_document_id', ['11', '13'])->where('created_at', '>=', $company->start_plan_date4)->count();                        return $qty_docs;
                    }
            }

        } catch (Exception $e) {
            return response([
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }

    }

    function file_get_contents_utf8($fn){
        $file = fopen($fn, 'r');
        $data = preg_replace("/[\r\n|\n|\r]+/", "", stream_get_contents($file));
        fclose($file);
        return $data;
    }

    function days_between_dates($date_from, $date_to){
        $date_initial = new DateTime(Carbon::parse($date_from)->format('Y-m-d'));
        $date_final = new DateTime(Carbon::parse($date_to)->format('Y-m-d'));
        $interval = $date_initial->diff($date_final);
        if($interval->invert)
            return $interval->days * (-1);
        else
            return $interval->days;
    }

    function verify_certificate($user = FALSE){
        $c = new ConfigurationController();
        $certificate_end_date = new DateTime(Carbon::parse(str_replace("/", "-", $c->CertificateEndDate($user)))->format('Y-m-d'));
        $actual_date = new DateTime(Carbon::now()->format('Y-m-d'));
        $interval = $actual_date->diff($certificate_end_date);
        $certificate_days_left = 0;
        if($interval->days == 0 || $interval->invert == 1)
            return [
                'success' => false,
                'message' => 'El certificado digital ya se encuentra vencido...',
                'expiration_date' => $certificate_end_date,
                'certificate_days_left' => 0,
            ];
        else
            return [
                'success' => true,
                'message' => 'El certificado digital es valido...',
                'expiration_date' => $certificate_end_date,
                'certificate_days_left' => $interval->days,
            ];
    }

    //validar estado de los servicios DIAN
    private function verificarEstadoDIAN($url, $retries = 1)
    {
        $attempt = 0;
        $backoff = 1; // Tiempo inicial de espera en segundos

        do {
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', $url, [
                    'timeout' => 10,
                    'connect_timeout' => 10,
                    'read_timeout' => 10,
                ]);
                // Si el código de estado es 200, la DIAN está disponible
                return $response->getStatusCode() == 200;
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                $attempt++;
                if ($attempt >= $retries) {
                    return false; // Regresa false si el número de intentos alcanza el máximo
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                return false; // Regresa false en caso de un error en la solicitud
            } catch (\Exception $e) {
                return false; // Regresa false en caso de un error inesperado
            }
            // Esperar un poco antes de intentar nuevamente, usando backoff exponencial
            sleep($backoff);
            $backoff *= 2; // Duplicar el tiempo de espera para el próximo intento
        } while ($attempt < $retries);
        return false; // Regresa false si todos los intentos fallan
    }
}
