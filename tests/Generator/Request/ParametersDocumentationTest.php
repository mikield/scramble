<?php

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as RouteFacade;

if (trait_exists(HasUuids::class)) {
    it('documents model keys uuid parameters as uuids', function () {
        $openApiDocument = generateForRoute(fn () => RouteFacade::get('api/test/{model}', [
            DocumentsModelKeysUuidParametersAsUuids_Test::class, 'index',
        ]));

        expect($params = $openApiDocument['paths']['/test/{model}']['get']['parameters'])
            ->toHaveCount(1)
            ->and($params[0])
            ->toMatchArray([
                'name' => 'model',
                'in' => 'path',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                    'format' => 'uuid',
                ],
            ]);
    });

    class DocumentsModelKeysUuidParametersAsUuids_Test
    {
        public function index(DocumentsModelKeysUuidParametersAsUuids_Model $model)
        {
            return response()->json();
        }
    }

    class DocumentsModelKeysUuidParametersAsUuids_Model extends \Illuminate\Database\Eloquent\Model
    {
        use HasUuids;
    }
}

it('supports @format annotation for validation rules', function () {
    $openApiDocument = generateForRoute(fn () => RouteFacade::get('api/test', SupportFormatAnnotation_ParametersDocumentationTestController::class));

    expect($openApiDocument['paths']['/test']['get']['parameters'])
        ->toHaveCount(1)
        ->and($openApiDocument['paths']['/test']['get']['parameters'][0])
        ->toBe([
            'name' => 'foo',
            'in' => 'query',
            'required' => true,
            'schema' => [
                'type' => 'string',
                'format' => 'uuid',
            ],
        ]);
});

class SupportFormatAnnotation_ParametersDocumentationTestController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            /** @format uuid */
            'foo' => ['required'],
        ]);
    }
}

it('supports optional parameters', function () {
    $openApiDocument = generateForRoute(fn () => RouteFacade::get('api/test/{payment_preference?}', SupportOptionalParam_ParametersDocumentationTestController::class));

    expect($openApiDocument['paths']['/test/{paymentPreference}']['get']['parameters'])
        ->toHaveCount(1)
        ->and($openApiDocument['paths']['/test/{paymentPreference}']['get']['parameters'][0])
        ->toBe([
            'name' => 'paymentPreference',
            'in' => 'path',
            'description' => '**Optional**. The name of the payment preference to use',
            'schema' => [
                'type' => ['string', 'null'],
                'default' => 'paypal',
            ],
            'x-optional' => true,
        ]);
});

class SupportOptionalParam_ParametersDocumentationTestController
{
    /**
     * @param  string|null  $paymentPreference  The name of the payment preference to use
     */
    public function __invoke(?string $paymentPreference = 'paypal') {}
}
