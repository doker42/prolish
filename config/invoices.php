<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | This value is the default currency that is going to be used in invoices.
    | You can change it on each invoice individually.
    */

    'currency' => 'EUR',

    /*
    |--------------------------------------------------------------------------
    | Default Decimal Precision
    |--------------------------------------------------------------------------
    |
    | This value is the default decimal precision that is going to be used
    | to perform all the calculations.
    */

    'decimals' => 2,


    /*
    |--------------------------------------------------------------------------
    | Default Invoice Logo
    |--------------------------------------------------------------------------
    |
    | This value is the default invoice logo that is going to be used in invoices.
    | You can change it on each invoice individually.
    */

    'logo' => 'https://www.my3dscanning.com/images/1544624975_5c111b4f3dc1c.jpeg',

    /*
    |--------------------------------------------------------------------------
    | Default Invoice Logo Height
    |--------------------------------------------------------------------------
    |
    | This value is the default invoice logo height that is going to be used in invoices.
    | You can change it on each invoice individually.
    */

    'logo_height' => 60,

    /*
    |--------------------------------------------------------------------------
    | Default Invoice Buissness Details
    |--------------------------------------------------------------------------
    |
    | This value is going to be the default attribute displayed in
    | the customer model.
    */

    'business_details' => [
        'name'        => 'SIA "WHITE CARDINALS Engineering"',
        'id'          => '52103083481',
        'phone'       => '+371 23 880088',
        'location'    => 'Mežu 41-33',
        'zip'         => 'LV-3405',
        'city'        => 'Liepāja',
        'country'     => 'Latvija',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Invoice Footnote
    |--------------------------------------------------------------------------
    |
    | This value is going to be at the end of the document, sometimes telling you
    | some copyright message or simple legal terms.
    */

    'footnote' => '',

    /*
    |--------------------------------------------------------------------------
    | Default Tax Rates
    |--------------------------------------------------------------------------
    |
    | This array group multiple tax rates.
    |
    | The tax type accepted values are: 'percentage' and 'fixed'.
    | The percentage type calculates the tax depending on the invoice price, and
    | the fixed type simply adds a fixed ammount to the total price.
    | You can't mix percentage and fixed tax rates.
    */
    'tax_rates' => [
        [
            'name'      => 'PVN',
            'tax'       => 21,
            'tax_type'  => 'percentage',
        ],
    ],

    /*
    | Default Invoice Due Date
    |--------------------------------------------------------------------------
    |
    | This value is the default due date that is going to be used in invoices.
    | You can change it on each invoice individually.
    | You can set it null to remove the due date on all invoices.
    */
    'due_date' => null,

    /*
    | Default pagination parameter
    |--------------------------------------------------------------------------
    |
    | This value is the default pagination parameter.
    | If true and page count are higher than 1, pagination will show at the bottom.
    */
    'with_pagination' => true,

    /*
    | Duplicate header parameter
    |--------------------------------------------------------------------------
    |
    | This value is the default header parameter.
    | If true header will be duplicated on each page.
    */
    'duplicate_header' => false,

];
