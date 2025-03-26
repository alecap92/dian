<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// UBL 2.1
Route::prefix('/ubl2.1')->group(function () {
    Route::get('/emailconfig', 'Api\ConfigurationController@emailconfig');

    // Configuration
    Route::prefix('/config')->middleware(['check.api.register'])->group(function () {
        Route::post('/{nit}/{dv?}', 'Api\ConfigurationController@store');
        Route::post('/delete/{nit}/{email}', 'Api\ConfigurationController@destroyCompany');
    });

    Route::get('/listing', 'Api\ListingController@all');

    // Plan
    Route::put('/plan', 'Api\ConfigurationController@storePlan');
    Route::get('/plan/query/{id?}', 'Api\ConfigurationController@queryPlan');
    Route::get('/plan/queryusersbyplan/{id}', 'Api\ConfigurationController@queryUsersByPlan');

    // Administrador
    Route::put('/administrator', 'Api\ConfigurationController@storeAdministrator');
    Route::get('/administrator/query/{nit?}', 'Api\ConfigurationController@queryAdministrator');
    Route::get('/administrator/queryusersbyadmin/{nit}', 'Api\ConfigurationController@queryUsersByAdministrator');

    // Sign Document XML
    Route::prefix('/signdocument')->group(function () {
        Route::post('/', 'Api\SignDocumentController@signdocument');
    });

    // Send Document XML
    Route::prefix('/senddocument')->group(function () {
        Route::post('/', 'Api\SendDocumentController@senddocument');
    });

    Route::prefix('/statusdocument')->group(function () {
        Route::post('/', 'Api\StatusDocumentController@statusdocument');
    });

    Route::prefix('/statuszip')->group(function () {
        Route::post('/', 'Api\StatusZipController@statuszip');
    });
});

Route::middleware('auth:api')->group(function () {

    Route::get('reload-pdf/{identification}/{file}/{cufe}', 'Api\DownloadController@reloadPdf');

    Route::post('process-seller-document-reception', 'Api\RadianEventController@processSellerDocumentReception');

    Route::prefix('/table')->group(function(){
        Route::get('/health_type_document_identifications', 'Api\ConfigurationController@table_health_type_document_identifications');
        Route::get('/health_type_users', 'Api\ConfigurationController@table_health_type_users');
        Route::get('/health_contracting_payment_methods', 'Api\ConfigurationController@table_health_contracting_payment_methods');
        Route::get('/health_coverages', 'Api\ConfigurationController@table_health_coverages');
        Route::get('/resolutions/{identification_number}', 'Api\ConfigurationController@table_resolutions');
    });

    // UBL 2.1
    Route::prefix('/ubl2.1')->group(function () {
        // Xml Document
        Route::prefix('/xml')->group(function () {
	        Route::post('/document/{trackId}/{GuardarEn?}', 'Api\XmlDocumentController@document');
        });

        // Plan info
        Route::get('/plan/infoplanuser', 'Api\ConfigurationController@infoPlanUser');

        // Join PDFs
        Route::post('/join-pdfs', 'Api\MiscelaneousController@joinPDFs');

        Route::get('/name-by-nit/{nit}', 'Api\MiscelaneousController@nameByNit');
        Route::get('/SearchCompany/{nit}', 'Api\MiscelaneousController@SearchCompany');

        // Register Customer
        Route::put('/register-update-customer', 'Api\ConfigurationController@RegCustomer');

        // Certificate End Date
        Route::put('/certificate-end-date', 'Api\ConfigurationController@CertificateEndDate');

        // Configuration
        Route::prefix('/config')->group(function () {
            Route::put('/software', 'Api\ConfigurationController@storeSoftware');
            Route::put('/softwarepayroll', 'Api\ConfigurationController@storeSoftware');
            Route::put('/softwareeqdocs', 'Api\ConfigurationController@storeSoftware');
            Route::put('/certificate', 'Api\ConfigurationController@storeCertificate');
            Route::put('/resolution', 'Api\ConfigurationController@storeResolution');
            Route::put('/environment', 'Api\ConfigurationController@storeEnvironment');
            Route::put('/logo', 'Api\ConfigurationController@storeLogo');
            Route::put('/generateddocuments', 'Api\ConfigurationController@storeInitialDocument');
        });

        Route::prefix('/delete')->middleware(['check.api.register'])->group(function () {
            Route::post('/company/{nit}/{dv}', 'Api\ConfigurationController@deleteCompany');
        });

        // Next Consecutive
        Route::post('/next-consecutive', 'Api\MiscelaneousController@NextConsecutive');

        // Regenerate PDF
        Route::prefix('/regeneratepdf')->group(function () {
            Route::post('/', 'Api\RegeneratePDFController@document_request');
            Route::post('/{prefix}/{number}/{cufe}', 'Api\RegeneratePDFController@document_url');
        });

        // Certificate Listing
        Route::get('/certificates-listing', 'Api\ConfigurationController@certificates_listing');
        Route::get('/certificates-listing/{company_identification_number}', 'Api\ConfigurationController@certificates_listing');

        // Equivalent Documents
        Route::prefix('/eqdoc')->group(function () {
            Route::post('/{testSetId}', 'Api\EqDocController@testSetStore');
            Route::post('/', 'Api\EqDocController@store');
        });

        // Invoice
        Route::prefix('/invoice')->group(function () {
            Route::post('/preeliminar-view', 'Api\InvoiceController@preeliminarview');
            Route::post('/{testSetId}', 'Api\InvoiceController@testSetStore');
            Route::post('/', 'Api\InvoiceController@store');
            Route::get('/current_number/{type}/{prefix?}/{ignore_state_document_id?}', 'Api\InvoiceController@currentNumber');
            Route::get('/state_document/{type}/{number}', 'Api\InvoiceController@changestateDocument');
        });

        // Export Invoice
        Route::prefix('/invoice-export')->group(function () {
            Route::post('/{testSetId}', 'Api\InvoiceExportController@testSetStore');
            Route::post('/', 'Api\InvoiceExportController@store');
        });

        // Contingency Invoice type 3
        Route::prefix('/invoice-contingency')->group(function () {
            Route::post('/{testSetId}', 'Api\InvoiceContingencyController@testSetStore');
            Route::post('/', 'Api\InvoiceContingencyController@store');
        });

        // Contingency Invoice type 4
        Route::prefix('/invoice-contingency-4')->group(function () {
            Route::post('/send_pendings/{prefix?}/{number?}', 'Api\InvoiceContingencyController@send_pendings');
            Route::post('/', 'Api\InvoiceContingencyController@store_type_4');
        });

        // AUI Invoice
        Route::prefix('/invoice-aiu')->group(function () {
            Route::post('/{testSetId}', 'Api\InvoiceAIUController@testSetStore');
            Route::post('/', 'Api\InvoiceAIUController@store');
        });

        // Mandate Invoice
        Route::prefix('/invoice-mandate')->group(function () {
            Route::post('/{testSetId}', 'Api\InvoiceMandateController@testSetStore');
            Route::post('/', 'Api\InvoiceMandateController@store');
        });

        // Transport Invoice
        Route::prefix('/invoice-transport')->group(function () {
            Route::post('/{testSetId}', 'Api\InvoiceTransportController@testSetStore');
            Route::post('/', 'Api\InvoiceTransportController@store');
        });

        // Credit Notes
        Route::prefix('/credit-note')->group(function () {
            Route::post('/{testSetId}', 'Api\CreditNoteController@testSetStore');
            Route::post('/', 'Api\CreditNoteController@store');
        });

        // Debit Notes
        Route::prefix('/debit-note')->group(function () {
            Route::post('/{testSetId}', 'Api\DebitNoteController@testSetStore');
            Route::post('/', 'Api\DebitNoteController@store');
        });

        // Support Document
        Route::prefix('/support-document')->group(function () {
            Route::post('/{testSetId}', 'Api\SupportDocumentController@testSetStore');
            Route::post('/', 'Api\SupportDocumentController@store');
        });

        // Support Document Credit Notes
        Route::prefix('/sd-credit-note')->group(function () {
            Route::post('/{testSetId}', 'Api\sdCreditNoteController@testSetStore');
            Route::post('/', 'Api\sdCreditNoteController@store');
        });

        // Add to batch
        Route::prefix('/add-to-batch')->group(function () {
            Route::post('/invoice/{batch}', 'Api\BatchController@addinvoice');
            Route::post('/invoice-aiu/{batch}', 'Api\BatchController@addinvoiceaiu');
            Route::post('/invoice-mandate/{batch}', 'Api\BatchController@addinvoicemandate');
            Route::post('/invoice-export/{batch}', 'Api\BatchController@addinvoiceexport');
            Route::post('/invoice-contingency/{batch}', 'Api\BatchController@addinvoicecontingency');
            Route::post('/credit-note/{batch}', 'Api\BatchController@addcreditnote');
            Route::post('/debit-note/{batch}', 'Api\BatchController@adddebitnote');
        });

        // Send batch
        Route::post('send-batch/{batch}', 'Api\BatchController@sendbatch');

        // Status
        Route::prefix('/status')->group(function () {
            Route::post('/zip/{trackId}/{GuardarEn?}', 'Api\StateController@zip');
            Route::post('/document/{trackId}/{GuardarEn?}', 'Api\StateController@document');
            Route::post('/events-document/{trackId}', 'Api\StateController@events_document');
        });

        // Numbering Ranges
        Route::prefix('/numbering-range')->group(function () {
            Route::post('/', 'Api\NumberingRangeController@NumberingRange');
        });

        // Send email
        Route::prefix('/send-email')->group(function () {
            Route::post('/', 'Api\SendEmailController@SendEmail');
        });

        // envio de correo nomina - pro 2
        Route::post('send-email-document-payroll', 'Api\SendEmailController@sendEmailDocumentPayroll');

        // Send email utilizado por el facturador pro 1

        Route::post('send_mail', 'EmailController@send');

        // Send event
        Route::prefix('/send-event')->group(function () {
            Route::post('/', 'Api\SendEventController@sendevent');
        });

        // Query events prefix and number
        Route::prefix('/query-events-prefix-number')->group(function () {
            Route::post('/{prefix}/{number}', 'Api\SendEventController@queryeventsprefixnumber');
        });

        // Send event
        Route::prefix('/send-event-data')->group(function () {
            Route::post('/', 'Api\SendEventController@sendeventdata');
        });

        // Query events UUID
        Route::prefix('/query-events-uuid')->group(function () {
            Route::post('/{uuid}', 'Api\SendEventController@queryeventsuuid');
        });

        // Query events UUID
        Route::prefix('/query-events-cufe-dian')->group(function () {
            Route::post('/{cufe}/{ambiente}', 'Api\SendEventController@queryeventscufedian');
        });

        // Payroll
        Route::prefix('/payroll')->group(function () {
            Route::post('/{testSetId}', 'Api\PayrollController@testSetStore');
            Route::post('/', 'Api\PayrollController@store');
            Route::get('/current_number/{type}/{ignore_state_document_id?}/{prefix?}', 'Api\PayrollController@currentNumber');
        });

        // Payroll Adjust Note
        Route::prefix('/payroll-adjust-note')->group(function () {
            Route::post('/{testSetId}', 'Api\PayrollAdjustNoteController@testSetStore');
            Route::post('/', 'Api\PayrollAdjustNoteController@store');
        });

        Route::get('download/{identification}/{file}/{type_response?}', 'Api\DownloadController@publicDownload');

    });
});

Route::get('invoice/xml/{filename}', function($fisicroute)
{
    $path = storage_path($fisicroute);
    return response(file_get_contents($path), 200, [
        'Content-Type' => 'application/xml'
    ]);
});

Route::get('invoice/pdf/{filename}', function($fisicroute)
{
    $path = storage_path("app/".$fisicroute);
    return response(file_get_contents($path), 200, [
        'Content-Type' => 'application/pdf'
    ]);
});

Route::get('invoice/{identification}/{filename}', function($identification, $filename)
{
    $path = storage_path("app/public/".$identification."/".$filename);
//    return response(base64_encode(file_get_contents($path)), 200);
    return response()->download($path);
});

Route::get('receivedfile/{identification}/{filename}', function($identification, $filename)
{
    try{
        $path = storage_path("received/".$identification."/".$filename);
//    return response(base64_encode(file_get_contents($path)), 200);
        return response()->download($path);
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => "No fue posible descargar el archivo {$filename} {$e->getMessage()}"
        ];
    }
});

Route::prefix('/information')->group(function () {
    Route::get('/{nit}', 'ResumeController@information');
    Route::get('/{nit}/{desde}', 'ResumeController@information');
    Route::get('/{nit}/{desde}/{hasta}', 'ResumeController@information');
    Route::get('/{nit}/page/{page}/page', 'ResumeController@information_by_page');
});

Route::prefix('/total_documents')->group(function () {
    Route::get('/{nit}', 'ResumeController@information_totals');
    Route::get('/{nit}/{desde}', 'ResumeController@information_totals');
    Route::get('/{nit}/{desde}/{hasta}', 'ResumeController@information_totals');
});

// Send email change customer password
Route::prefix('/change-customer-password')->group(function () {
    Route::post('/{customer_idnumber}/{show_view}', 'CustomerLoginController@RetrievePassword');
});

// Send email customer
Route::post('/send-email-customer', 'Api\SendEmailController@SendEmailCustomer')->name('send-email-customer');
Route::post('/send-email-customer/{ShowView}', 'Api\SendEmailController@SendEmailCustomer')->name('send-email-customer-view');

// Send email employee
Route::post('/send-email-employee', 'Api\SendEmailController@SendEmailEmployee')->name('send-email-employee');
Route::post('/send-email-employee/{ShowView}', 'Api\SendEmailController@SendEmailEmployee')->name('send-email-employee-view');

// Add customers/documents from xml
Route::post('/add-customers-documentos-xml/{nit}', 'Api\AddCostumersDocumentsXML@Organize')->name('add-customers-documentos-xml');

Route::post('/accept-reject-document', 'AcceptRejectDocumentController@ExecuteAcceptRejectDocument')->name('acceptrejectdocument');

Route::post('/download-file', 'AcceptRejectDocumentController@DownloadFile')->name('downloadfile');

if(env('ALLOW_PUBLIC_DOWNLOAD', TRUE)){
    Route::get('download/{identification}/{file}/{type_response?}',
        function($identification, $file, $type_response = FALSE)
        {
            $u = new \App\Utils;
            if(strpos($file, 'Attachment-') === false and strpos($file, 'ZipAttachm-') === false)
                if(file_exists(storage_path("app/public/{$identification}/{$file}")))
                    if($type_response && $type_response === 'BASE64')
                        return [
                            'success' => true,
                            'message' => "Archivo: ".$file." se encontro.",
                            'filebase64'=>base64_encode(file_get_contents(storage_path("app/public/{$identification}/{$file}")))
                        ];
                    else
                        return Storage::download("public/{$identification}/{$file}");
                else
                    return [
                        'success' => false,
                        'message' => "No se encontro el archivo: ".$file
                    ];
            else{
                if(strpos($file, 'ZipAttachm-') === false){
                    $filename = $u->attacheddocumentname($identification, $file);
                    if(file_exists(storage_path("app/public/{$identification}/{$filename}.xml")))
                        if($type_response && $type_response === 'BASE64')
                            return [
                                'success' => true,
                                'message' => "Archivo: ".$filename.".xml se encontro.",
                                'filebase64'=>base64_encode(file_get_contents(storage_path("app/public/{$identification}/{$filename}.xml")))
                            ];
                        else
                            return Storage::download("public/{$identification}/{$filename}.xml");
                    else
                        return [
                            'success' => false,
                            'message' => "No se encontro el archivo: ".$filename.".xml"
                        ];
                }
                else{
                    $filename = $u->attacheddocumentname($identification, $file);
                    if(file_exists(storage_path("app/public/{$identification}/{$filename}.zip")))
                        if($type_response && $type_response === 'BASE64')
                            return [
                                'success' => true,
                                'message' => "Archivo: ".$filename.".zip se encontro.",
                                'filebase64'=>base64_encode(file_get_contents(storage_path("app/public/{$identification}/{$filename}.zip")))
                            ];
                        else
                            return Storage::download("public/{$identification}/{$filename}.zip");
                    else
                        return [
                            'success' => false,
                            'message' => "No se encontro el archivo: ".$filename.".zip"
                        ];
                }
            }
        }
    );
}
