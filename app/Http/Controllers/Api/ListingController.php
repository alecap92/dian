<?php

namespace App\Http\Controllers\Api;

use App\Tax;
use App\Country;
use App\CreditNoteDiscrepancyResponse;
use App\DebitNoteDiscrepancyResponse;
use App\Discount;
use App\Language;
use App\Department;
use App\Event;
use App\HealthContractingPaymentMethod;
use App\HealthCoverage;
use App\HealthTypeDocumentIdentification;
use App\HealthTypeOperation;
use App\HealthTypeUser;
use App\TypeRegime;
use App\PaymentForm;
use App\UnitMeasure;
use Illuminate\Support\Arr;
use App\Municipality;
use App\TypeCurrency;
use App\TypeDocument;
use Illuminate\Http\Request;
use App\PaymentMethod;
use App\TypeLiability;
use App\TypeOperation;
use App\ReferencePrice;
use App\TypeEnvironment;
use App\TypeOrganization;
use App\Http\Controllers\Controller;
use App\Incoterm;
use App\PayrollPeriod;
use App\PayrollTypeDocumentIdentification;
use App\SubTypeWorker;
use App\TypeItemIdentification;
use App\TypeDocumentIdentification;
use App\TypeContract;
use App\TypeDisability;
use App\TypeDiscount;
use App\TypeGenerationTransmition;
use App\TypeLawDeduction;
use App\TypeOvertimeSurcharge;
use App\TypePlan;
use App\TypeRejection;
use App\TypeWorker;

class ListingController extends Controller
{
    /**
     * Models
     * @var array
     */
    private $models = [
        'Country' => Country::class,
        'CreditNoteDiscrepancyResponse' => CreditNoteDiscrepancyResponse::class,
        'DebitNoteDiscrepancyResponse' => DebitNoteDiscrepancyResponse::class,
        'Department' => Department::class,
        'Discount' => Discount::class,
        'Event' => Event::class,
        'HealthContractingPaymentMethod' => HealthContractingPaymentMethod::class,
        'HealthCoverage' => HealthCoverage::class,
        'HealthTypeDocumentIdentification' => HealthTypeDocumentIdentification::class,
        'HealthTypeOperation' => HealthTypeOperation::class,
        'HealthTypeUser' => HealthTypeUser::class,
        'Incoterm' => Incoterm::class,
        'Language' => Language::class,
        'Municipality' => Municipality::class,
        'PaymentForm' => PaymentForm::class,
        'PaymentMethod' => PaymentMethod::class,
        'PayrollPeriod' => PayrollPeriod::class,
        'PayrollTypeDocumentIdentification' => PayrollTypeDocumentIdentification::class,
        'ReferencePrice' => ReferencePrice::class,
        'SubTypeWorker' => SubTypeWorker::class,
        'Tax' => Tax::class,
        'TypeContract' => TypeContract::class,
        'TypeCurrency' => TypeCurrency::class,
        'TypeDisability' => TypeDisability::class,
        'TypeDiscount' => TypeDiscount::class,
        'TypeDocumentIdentification' => TypeDocumentIdentification::class,
        'TypeDocument' => TypeDocument::class,
        'TypeEnvironment' => TypeEnvironment::class,
        'TypeGenerationTransmition' => TypeGenerationTransmition::class,
        'TypeItemIdentification' => TypeItemIdentification::class,
        'TypeLawDeduction' => TypeLawDeduction::class,
        'TypeLiability' => TypeLiability::class,
        'TypeOperation' => TypeOperation::class,
        'TypeOrganization' => TypeOrganization::class,
        'TypeOvertimeSurcharge' => TypeOvertimeSurcharge::class,
        'TypeRegime' => TypeRegime::class,
        'TypeRejection' => TypeRejection::class,
        'TypeWorker' => TypeWorker::class,
        'UnitMeasure' => UnitMeasure::class,
    ];

    /**
     * Get all models
     * @param  Request $request
     * @return \Illuminate\Support\Collection
     */
    public function all(Request $request)
    {
        $request->validate([
            'models' => 'nullable|string'
        ]);

        $modelNames = $request->has('models') 
            ? explode(',', str_replace(' ', '', $request->models)) 
            : array_keys($this->models);

        $modelNames = array_intersect($modelNames, array_keys($this->models));

        $allListing = collect();

        foreach ($modelNames as $modelName) {
            $class = $this->models[$modelName];
            $allListing->put($modelName, $class::all());
        }

        return $allListing;
    }
}
