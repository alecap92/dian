<?php

namespace App\Http\Controllers;

use App\Company;
use App\Customer;
use App\ReceivedDocument;
use App\Document;
use App\User;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Requests\Api\SendEventRequest;
use App\Http\Controllers\Api\SendEventController;
use Illuminate\Validation\Rule;
use App\Traits\DocumentTrait;
use Storage;

class AcceptRejectDocumentController extends Controller
{
    use DocumentTrait;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function ShowViewAcceptRejectDocument(Request $request, $company_idnumber, $customer_idnumber, $prefix, $docnumber, $issuedate)
    {
        return view('acceptrejectdocument', compact('request', 'company_idnumber', 'customer_idnumber', 'prefix', 'docnumber', 'issuedate'));
    }

    protected function DownloadFile(Request $request)
    {
        {
            $u = new \App\Utils;
            if(strpos($request->file, 'Attachment-') === false and strpos($request->file, 'ZipAttachm-') === false)
                if(file_exists(storage_path("app/public/{$request->identification}/{$request->file}")))
                    if($request->type_response && $request->type_response === 'BASE64')
                        return [
                            'success' => true,
                            'message' => "Archivo: ".$request->file." se encontro.",
                            'filebase64'=>base64_encode(file_get_contents(storage_path("app/public/{$request->identification}/{$request->file}")))
                        ];
                    else
                        return Storage::download("public/{$request->identification}/{$request->file}");
                else
                    return [
                        'success' => false,
                        'message' => "No se encontro el archivo: ".$request->file
                    ];
            else{
                if(strpos($request->file, 'ZipAttachm-') === false){
                    $filename = $u->attacheddocumentname($request->identification, $request->file);
                    if(file_exists(storage_path("app/public/{$request->identification}/{$filename}.xml")))
                        if($request->type_response && $request->type_response === 'BASE64')
                            return [
                                'success' => true,
                                'message' => "Archivo: ".$filename.".xml se encontro.",
                                'filebase64'=>base64_encode(file_get_contents(storage_path("app/public/{$request->identification}/{$filename}.xml")))
                            ];
                        else
                            return Storage::download("public/{$request->identification}/{$filename}.xml");
                    else
                        return [
                            'success' => false,
                            'message' => "No se encontro el archivo: ".$filename.".xml"
                        ];
                }
                else{
                    $filename = $u->attacheddocumentname($request->identification, $request->file);
                    if(file_exists(storage_path("app/public/{$request->identification}/{$filename}.zip")))
                        if($request->type_response && $request->type_response === 'BASE64')
                            return [
                                'success' => true,
                                'message' => "Archivo: ".$filename.".zip se encontro.",
                                'filebase64'=>base64_encode(file_get_contents(storage_path("app/public/{$request->identification}/{$filename}.zip")))
                            ];
                        else
                            return Storage::download("public/{$request->identification}/{$filename}.zip");
                    else
                        return [
                            'success' => false,
                            'message' => "No se encontro el archivo: ".$filename.".zip"
                        ];
                }
            }
        }
    }

    protected function ExecuteAcceptRejectDocument(Request $request)
    {
        $u = new \App\Utils;
        $e = new SendEventController();
        if($request->eventcode == "5"){
            if(!is_null($request->prefix) && $request->prefix != '')
                $d = Document::where('identification_number', $request->company_idnumber)->where('customer', $request->customer_idnumber)->where('prefix', $request->prefix)->where('number', $request->docnumber)->where('state_document_id', 1)->firstOrFail();
            else
                $d = Document::where('identification_number', $request->company_idnumber)->where('customer', $request->customer_idnumber)->where('number', $request->docnumber)->where('state_document_id', 1)->firstOrFail();
            $filename = $u->attacheddocumentname($d->identification_number, "Attachment-{$d->prefix}{$d->number}.xml").".xml";
            $att_str = base64_encode(file_get_contents(storage_path('app/public/'.$d->identification_number.'/'.$filename)));
        }
        else{
            if(!is_null($request->prefix) && $request->prefix != '')
                $d = ReceivedDocument::where('identification_number', $request->company_idnumber)->where('customer', $request->customer_idnumber)->where('prefix', $request->prefix)->where('number', $request->docnumber)->firstOrFail();
            else
                $d = ReceivedDocument::where('identification_number', $request->company_idnumber)->where('customer', $request->customer_idnumber)->where('number', $request->docnumber)->firstOrFail();
            $filename = $d->xml;
            $att_str = base64_encode(file_get_contents(storage_path('received/'.$d->customer.'/'.$d->xml)));
        }
        if($request->eventcode == "2")
            $send = [
                        'event_id' => $request->eventcode,
                        'base64_attacheddocument_name' => $filename,
                        'base64_attacheddocument' => $att_str,
                        'type_rejection_id' => $request->rejection_id
                    ];
        else
            $send = [
                        'event_id' => $request->eventcode,
                        'base64_attacheddocument_name' => $filename,
                        'base64_attacheddocument' => $att_str,
                    ];
        $data_send = json_encode($send);
        $r = new SendEventRequest($send);
        if($request->eventcode == "5")
            $r = $e->sendevent($r, $d->identification_number);
        else
            $r = $e->sendevent($r, $d->customer);
        if($r['success'] == true)
            if($r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->IsValid == "false"){
                $message = nl2br($r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->StatusMessage."\r\n\r\n", false);
                if(is_string($r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->ErrorMessage->string)){
                    $message = $message.$r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->ErrorMessage->string."<br>";
                }
                else{
                    foreach($r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->ErrorMessage->string as $m)
                        $message = $message.$m."<br>";
                }
                return view('customerloginmensaje', ['titulo' => 'Resultado del Evento: '.$r['message'], 'mensaje' => $message]);
            }
            else
                return view('customerloginmensaje', ['titulo' => 'Resultado del Evento: '.$r['message'], 'mensaje' => $r['ResponseDian']->Envelope->Body->SendEventUpdateStatusResponse->SendEventUpdateStatusResult->StatusMessage]);
        else
            return view('customerloginmensaje', ['titulo' => 'Resultado del Evento: '.$r['message'], 'mensaje' => $r['message']]);
    }
}

