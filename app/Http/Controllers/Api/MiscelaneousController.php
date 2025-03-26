<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Company;
use App\Resolution;
use App\Document;
use App\DocumentPayroll;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\JoinPDFsRequest;
use App\Http\Requests\Api\NextConsecutiveRequest;
use Storage;
use App\Traits\DocumentTrait;
use Exception;
use PDFMerger;
use Goutte\Client as ClientScrap;
use Symfony\Component\HttpClient\HttpClient;

class MiscelaneousController extends Controller
{
    use DocumentTrait;
    protected $nameclient;

    public function NextConsecutive(NextConsecutiveRequest $request)
    {

        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        $resolution = $company->resolutions->where('type_document_id', $request->type_document_id)->where('prefix', $request->prefix)->first();

        try{
            if($request->type_document_id == 9 || $request->type_document_id == 10){
                $document = DocumentPayroll::where('identification_number', $company->identification_number)->where('type_document_id', $request->type_document_id)->where('state_document_id', 1)->where('prefix', $resolution->prefix)->get()->sortByDesc('consecutive')->first();
                return [
                    'success' => true,
                    'type_document_id' => $request->type_document_id,
                    'prefix' => $resolution->prefix,
                    'number' => ($document) ? ((int)$document->consecutive + 1) : (int)$resolution->from,
                ];
            }
            else{
                $document = Document::where('identification_number', $company->identification_number)->where('type_document_id', $request->type_document_id)->where('state_document_id', 1)->where('prefix', $resolution->prefix)->get()->sortByDesc('number')->first();
                return [
                    'success' => true,
                    'type_document_id' => $request->type_document_id,
                    'prefix' => $resolution->prefix,
                    'number' => ($document) ? ((int)$document->number + 1) : (int)$resolution->from,
                ];
            }
        }
        catch(Exception $e) {
            return [
                'success' => false,
                'message' => "{$e->getLine()} - {$e->getMessage()}"
            ];
        }

    }

    public function joinPDFs(JoinPDFsRequest $request)
    {
        try {
            $user = auth()->user();

            $company = $user->company;

            $new_pdf = new PDFMerger();

            foreach($request->pdfs as $pdf){
                if($pdf['type_document_id'] == 1 || $pdf['type_document_id'] == 2 || $pdf['type_document_id'] == 3 || $pdf['type_document_id'] == 12)
                  $type_document = "FES-";
                else
                    if($pdf['type_document_id'] == 4)
                      $type_document = "NCS-";
                    else
                        if($pdf['type_document_id'] == 5)
                          $type_document = "NDS-";
                        else
                            if($pdf['type_document_id'] == 9)
                              $type_document = "NIS-";
                            else
                                if($pdf['type_document_id'] == 10)
                                  $type_document = "NAS-";
                                else
                                    if($pdf['type_document_id'] == 11)
                                      $type_document = "DSS-";
                $new_pdf->addPDF(storage_path("app/public/{$company->identification_number}/{$type_document}{$pdf['prefix']}{$pdf['number']}".".pdf"), 'all');
            }

            $new_pdf->merge('file', storage_path("app/public/{$company->identification_number}/{$request->name_joined_pdfs}"));
            return [
                'success' => true,
                'message' => 'Operacion realizada con exito.',
                'pdfbase64' => base64_encode(file_get_contents(storage_path("app/public/{$company->identification_number}/{$request->name_joined_pdfs}")))
            ];
        }
        catch(Exception $e) {
            return [
                'success' => false,
                'message' => "{$e->getLine()} - {$e->getMessage()}"
            ];
        }
    }

    public function setNameClient($name)
    {
        $this->nameclient = $name;
    }

    public function SearchCompany($nit)
    {
        $client = new ClientScrap(HttpClient::create(['timeout' => 80, 'verify_peer' => false]));
        $crawler = $client->request('GET', "https://www.einforma.co/servlet/app/portal/ENTP/prod/LISTA_EMPRESAS/razonsocial/{$nit}");
        $name = $crawler->filter('#titEtiqueta')->text("No se encontro NIT.");
        if($name !== "No se encontro NIT."){
            $nodeValues = $crawler->filter('td')->each(function ($node, $i) {
                return $node->text();
            });
            return [
                'success' => "true",
                'name' => $nodeValues[6],
                'type' => $nodeValues[8],
                'direction' =>  $nodeValues[12],
                'state' =>  $nodeValues[10],
                'activity' =>  $nodeValues[18],
            ];
        }else{
            return [
                'success' => "false",
                'message' => "No se encontro el NIT",
            ];
        }
    }

    public function nameByNit($nit)
    {
       $client = new ClientScrap(HttpClient::create(['timeout' => 80, 'verify_peer' => false]));
       $crawler = $client->request('GET', "https://www.einforma.co/servlet/app/portal/ENTP/prod/LISTA_EMPRESAS/razonsocial/{$nit}");
       $crawler->filter('h1[class="title01"]')->each(function($node){
          $this->setNameClient($node->text());
       });
       if(!is_null($this->nameclient)){
           $arrayName = explode(" ", $this->nameclient);
           if(count($arrayName) == 1)
               return [
                   'success' => true,
                   'result' => [
                                   'primer_nombre' => $arrayName[0],
                                   'otros_nombres' => '',
                                   'primer_apellido' => '',
                                   'segundo_apellido' => ''
                               ]
                    ];
           else
               if(count($arrayName) == 2)
                   return [
                       'success' => true,
                       'result' => [
                                       'primer_nombre' => $arrayName[1],
                                       'otros_nombres' => '',
                                       'primer_apellido' => $arrayName[0],
                                       'segundo_apellido' => ''
                                   ]
                        ];
               else
                   if(count($arrayName) == 3)
                        return [
                            'success' => true,
                            'result' => [
                                            'primer_nombre' => $arrayName[2],
                                            'otros_nombres' => '',
                                            'primer_apellido' => $arrayName[0],
                                            'segundo_apellido' => $arrayName[1]
                                        ]
                            ];
                    else
                        if(count($arrayName) == 4)
                            return [
                                'success' => true,
                                'result' => [
                                                'primer_nombre' => $arrayName[2],
                                                'otros_nombres' => $arrayName[3],
                                                'primer_apellido' => $arrayName[0],
                                                'segundo_apellido' => $arrayName[1]
                                            ]
                            ];
       }
       else
          return [
                'success' => false,
                'message' => "No se encontro el NIT."
          ];
   }

}
