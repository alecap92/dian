<?php

namespace App\Http\Controllers\Api;

use App\ReceivedDocument;
use Illuminate\Http\Request;
use App\Traits\DocumentTrait;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class RadianEventController extends Controller
{
    use DocumentTrait;

    private function getResponse($success, $message)
    {
        return [
            'success' => $success,
            'message' => $message,
        ];
    }

    protected function processSellerDocumentReception(Request $request)
    {

        try
        {
            $att = new \DOMDocument('1.0', 'utf-8');
            $att->preserveWhiteSpace = false;
            $att->formatOutput = true;

            $company_idnumber = $request->company_idnumber;
            $attXMLStr = str_replace("&", "&amp;", $request->xml_document);

            if(!$att->loadXML(base64_decode($attXMLStr))){
                return $this->getResponse(false, "El archivo no se pudo cargar, revise los problemas asociados");
            }

            else{

                if(!strpos($att->saveXML(), "<AttachedDocument")){
                    return $this->getResponse(false, "El archivo  no es un AttachedDocument XML");
                }

                if(!strpos($att->saveXML(), "<ApplicationResponse")){
                    return $this->getResponse(false, "El archivo no se encontro el ApplicationResponse dentro del AttachedDocument XML");
                }

                if(!strpos($att->saveXML(), "<Invoice")){
                    return $this->getResponse(false, "el archivo no corresponde al AttachedDocument XML de un documento Invoice");
                }

                $invoiceXMLStr = $att->documentElement->getElementsByTagName('Description')->item(0)->nodeValue;
                $invoiceXMLStr = str_replace("&", "&amp;", substr(base64_decode($attXMLStr), strpos(base64_decode($attXMLStr), "<Invoice"), strpos(base64_decode($attXMLStr), "/Invoice>") - strpos(base64_decode($attXMLStr), "<Invoice") + 9));
                $invoiceXMLStr = preg_replace("/[\r\n|\n|\r]+/", "","<?xml version=\"1.0\" encoding=\"utf-8\"?>".$invoiceXMLStr);

                $invoice_doc = new \stdClass;
                $invoice_doc->identification_number = $this->ValueXML($invoiceXMLStr, "/Invoice/cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                $invoice_doc->dv = $this->validarDigVerifDIAN($invoice_doc->identification_number);
                $invoice_doc->name_seller = $this->getTag($invoiceXMLStr, 'RegistrationName', 0)->nodeValue;
                $invoice_doc->state_document_id = 1;
                $invoice_doc->type_document_id = $this->getTag($invoiceXMLStr, 'InvoiceTypeCode', 0)->nodeValue;
                $invoice_doc->customer = $this->ValueXML($invoiceXMLStr, "/Invoice/cac:AccountingCustomerParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID/");
                if(strpos($invoiceXMLStr, "</sts:Prefix>"))
                    $invoice_doc->prefix = $this->getTag($invoiceXMLStr, 'Prefix', 0)->nodeValue;
                else
                    $invoice_doc->prefix = "";
                $i = 0;
                if($invoice_doc->prefix != "")
                    do{
//                            $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");
                        $invoice_doc->number =  $this->getTag($invoiceXMLStr, "ID", $i)->nodeValue;
                        $i++;
                    }while(strpos($invoice_doc->number, $invoice_doc->prefix) === false);
                else
                    $invoice_doc->number =  $this->ValueXML($invoiceXMLStr, "/Invoice/cbc:ID/");

                $invoice_doc->xml = null;
                $invoice_doc->cufe = $this->getTag($invoiceXMLStr, 'UUID', 0)->nodeValue;
                $invoice_doc->date_issue = $this->getTag($invoiceXMLStr, 'IssueDate', 0)->nodeValue.' '.str_replace('-05:00', '', $this->getTag($invoiceXMLStr, 'IssueTime', 0)->nodeValue);
                $invoice_doc->sale = $this->getTag($invoiceXMLStr, 'TaxInclusiveAmount', 0)->nodeValue;
                if(isset($this->getTag($invoiceXMLStr, 'AllowanceTotalAmount', 0)->nodeValue))
                    $invoice_doc->total_discount =  $this->getTag($invoiceXMLStr, 'AllowanceTotalAmount', 0)->nodeValue;
                else
                    $invoice_doc->total_discount = 0;
                $invoice_doc->subtotal = $this->getTag($invoiceXMLStr, 'LineExtensionAmount', 0)->nodeValue;
                $invoice_doc->total_tax = $invoice_doc->sale - $invoice_doc->subtotal;
                $invoice_doc->total = $this->getTag($invoiceXMLStr, 'PayableAmount', 0)->nodeValue;
                $invoice_doc->ambient_id = $this->getTag($invoiceXMLStr, 'ProfileExecutionID', 0)->nodeValue;
                $invoice_doc->pdf = null;
                $invoice_doc->acu_recibo = 0;
                $invoice_doc->rec_bienes = 0;
                $invoice_doc->aceptacion = 0;
                $invoice_doc->rechazo = 0;

                if($invoice_doc->customer != $company_idnumber){
                    return $this->getResponse(false, "El archivo  no corresponde un AttachedDocument XML del adquiriente ".$company_idnumber);

                }

                $exists = ReceivedDocument::where('customer', $company_idnumber)->where('identification_number', $invoice_doc->identification_number)->where('prefix', $invoice_doc->prefix)->where('number', $invoice_doc->number)->get();

                if(count($exists) == 0)
                {
                    return [
                        'success' => true,
                        'message' => "El archivo fue cargado satisfactoriamente...",
                        'data' => $invoice_doc,
                    ];
                }
                else{
                    return $this->getResponse(false, "El archivo  ya existe en la base de datos...");
                }

                return $this->getResponse(true, "El archivo fue cargado satisfactoriamente...");

            }

        }
        catch (\Exception $e)
        {
            return $this->getResponse(false, "Error en la carga: {$e->getMessage()}");
        }
    }


}
