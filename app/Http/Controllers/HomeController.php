<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use App\Company;
use App\Document;
use App\ReceivedDocument;
use App\DocumentPayroll;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $companies = Company::get()->transform( function($row) {
            $documents = Document::where('identification_number', $row->identification_number)->count();
            $row->total_documents = $documents;
            return $row;
        });

        return view('home', ['companies' => $companies]);
    }

    public function company(Company $company)
    {
        $documents = Document::where('identification_number', $company->identification_number)->orderBy('id', 'DESC')->paginate(20);

        return view('company.documents', ['company' => $company, 'documents' => $documents]);
    }

    public function getXml(Company $company, $cufe)
    {
        $token = $company->user->api_token;
        $url = url('/api/ubl2.1/xml/document/'.$cufe);

        $client = new Client();
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ]
        ]);

        // dd($response);
        $responseBody = json_decode($response->getBody(), true);

        // Manejar la respuesta
        if ($response->getStatusCode() == 200) {
            return response()->json($responseBody);
        } else {
            return response()->json([
                'error' => 'Error al hacer la solicitud a la API',
                'status_code' => $response->getStatusCode(),
                'body' => $responseBody,
            ], $response->getStatusCode());
        }
    }

    // replica de SellerLoginController@SellersRadianEventsView
    public function events($company_idnumber){
        $documents = ReceivedDocument::where('customer','=',$company_idnumber)->where('state_document_id', '=', 1)->paginate(10);
        return view('company.events', compact('documents', 'company_idnumber'));
    }

    // replica de SellerLoginController@SellersPayrolls
    public function payrolls($company_idnumber)
    {
        $documents = DocumentPayroll::where('state_document_id', '=', 1)->where('identification_number', $company_idnumber)->paginate(20);
        return view('company.payrolls', compact('documents', 'company_idnumber'));
    }
}
