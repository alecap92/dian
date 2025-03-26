<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Traits\DocumentTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendDocumentRequest;
use ubl21dian\XAdES\SignInvoice;
use ubl21dian\XAdES\SignCreditNote;
use ubl21dian\XAdES\SignDebitNote;
use ubl21dian\Templates\SOAP\SendBillAsync;
use ubl21dian\Templates\SOAP\SendBillSync;
use ubl21dian\Templates\SOAP\SendTestSetAsync;
use Storage;

class SendDocumentController extends Controller
{
    use DocumentTrait;

    /**
     * Store.
     *
     * @param \App\Http\Requests\Api\SendDocumentRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function SendDocument(SendDocumentRequest $request)
    {
        try {
            if (!base64_decode($request->certificate, true)) {
                throw new Exception('The given data of the certificate was invalid.');
            }
            if (!base64_decode($request->documentbase64, true)) {
                throw new Exception('The given data of the document was invalid.');
            }
            if (!openssl_pkcs12_read($certificateBinary = base64_decode($request->certificate), $certificate, $request->password)) {
                throw new Exception('The certificate could not be read.');
            }
        } catch (Exception $e) {
            if (false == ($error = openssl_error_string())) {
                return response([
                    'message' => $e->getMessage(),
                    'errors' => [
                        'errors' => 'The base64 encoding is not valid.',
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

        $name = "{$request->password}.p12";
        Storage::put("certificates/{$name}", $certificateBinary);

        // Create XML
        $invoice = base64_decode($request->documentbase64);

        // Signature XML
        if($request->tipodoc == 'INVOICE')
            $SendDocument = new SignInvoice(storage_path("app/certificates/".$name), $request->password);
        else
            if($request->tipodoc == 'NC')    
                $SendDocument = new SignCreditNote(storage_path("app/certificates/".$name), $request->password);
            else
                if($request->tipodoc == 'ND')
                    $SendDocument = new SignDebitNote(storage_path("app/certificates/".$name), $request->password);
                else    
                    return [
                        'message' => "El tipo de documento {$request->tipodoc} no es soportado por esta peticion",
                        'success' => 'false'
                    ];
    
        $SendDocument->softwareID = $request->softwareid;
        $SendDocument->pin = $request->pin;
        if($request->tipodoc == 'INVOICE')
            $SendDocument->technicalKey = $request->technicalKey;

        if (!is_dir(storage_path("app/public/{$request->password}"))) {
            mkdir(storage_path("app/public/{$request->password}"));
        }

        $SendDocument->GuardarEn = storage_path("app/public/{$request->password}/DOC-{$request->documentnumber}.xml");
        $file = fopen(storage_path("app/public/{$request->password}/DOCS-{$request->documentnumber}.xml"), "w");
        fwrite($file, $SendDocument->sign($invoice)->xml);
        fclose($file);

        if($request->ambiente == 'HABILITACION')
        {
            $sendTestSetAsync = new SendTestSetAsync(storage_path("app/certificates/".$name), $request->password);
            $sendTestSetAsync->To = 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc';
            $sendTestSetAsync->fileName = "{$request->documentnumber}.xml";
            $sendTestSetAsync->contentFile = $this->zipBase64SendDocument($request->password, $request->identificationnumber, $request->tipodoc, $request->documentnumber, $SendDocument->sign($invoice), storage_path("app/public/{$request->password}/DOCS-{$request->documentnumber}"));
            $sendTestSetAsync->testSetId = $request->testSetID;
        }
        else
            if($request->ambiente == 'PRODUCCION')
            {
                $sendBillSync = new SendBillSync(storage_path("app/certificates/".$name), $request->password);
                $sendBillSync->To = 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc';
                $sendBillSync->fileName = "{$request->documentnumber}.xml";
                $sendBillSync->contentFile = $this->zipBase64SendDocument($request->password, $request->identificationnumber, $request->tipodoc, $request->documentnumber, $SendDocument->sign($invoice), storage_path("app/public/{$request->password}/DOCS-{$request->documentnumber}"));
            }
            else
                return [
                    'message' => "El ambiente de trabajo {$request->ambiente} no es valido para esta peticion",
                    'success' => 'false'
                ];

        
        if($request->tipodoc == 'INVOICE')
            if($request->ambiente == 'PRODUCCION')
                return [
                    'message' => "El documento Nro {$request->documentnumber} firmado y enviado con éxito",
                    'ResponseDian' => $sendBillSync->signToSend(storage_path("app/public/{$request->password}/ReqDOC-{$request->documentnumber}.xml"))->getResponseToObject(storage_path("app/public/{$request->password}/RptaDOC-{$request->documentnumber}.xml")),
                    'invoicexml'=>base64_encode(file_get_contents(storage_path("app/public/{$request->password}/DOCS-{$request->documentnumber}.xml"))),
                    'cufe' => $SendDocument->ConsultarCUFE()
                ];
            else    
                return [
                    'message' => "El documento Nro {$request->documentnumber} firmado y enviado con éxito",
                    'ResponseDian' => $sendTestSetAsync->signToSend(storage_path("app/public/{$request->password}/ReqDOC-{$request->documentnumber}.xml"))->getResponseToObject(storage_path("app/public/{$request->password}/RptaDOC-{$request->documentnumber}.xml")),
                    'invoicexml'=>base64_encode(file_get_contents(storage_path("app/public/{$request->password}/DOCS-{$request->documentnumber}.xml"))),
                    'cufe' => $SendDocument->ConsultarCUFE()
                ];
        else
            if($request->ambiente == 'PRODUCCION')
                return [
                    'message' => "El documento Nro {$request->documentnumber} firmado y enviado con éxito",
                    'ResponseDian' => $sendBillSync->signToSend(storage_path("app/public/{$request->password}/ReqDOC-{$request->documentnumber}.xml"))->getResponseToObject(storage_path("app/public/{$request->password}/RptaDOC-{$request->documentnumber}.xml")),
                    'invoicexml'=>base64_encode(file_get_contents(storage_path("app/public/{$request->password}/DOCS-{$request->documentnumber}.xml"))),
                    'cude' => $SendDocument->ConsultarCUDE()
                ];
            else
                return [
                    'message' => "El documento Nro {$request->documentnumber} firmado con éxito",
                    'ResponseDian' => $sendTestSetAsync->signToSend(storage_path("app/public/{$request->password}/ReqDOC-{$request->documentnumber}.xml"))->getResponseToObject(storage_path("app/public/{$request->password}/RptaDOC-{$request->documentnumber}.xml")),
                    'invoicexml'=>base64_encode(file_get_contents(storage_path("app/public/{$request->password}/DOCS-{$request->documentnumber}.xml"))),
                    'cude' => $SendDocument->ConsultarCUDE()
                ];

    }
}
