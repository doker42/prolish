<?php
declare(strict_types=1);

namespace App\Domain\Billing;


use App\Foundation\Bridge\Laravel\UpTrait;
use App\Models\Invoice;

class InvoiceManager
{
    use UpTrait;

    public function generateInvoiceID():string {

        $num = Invoice::count() + 1;

        $title = 'INV';

        if (strlen(strval($num)) >= 6) {
            for ($i = 0; $i < 6 - strlen($num); $i++) {
                $title .= '0';
            }
        }

        return $title . $num;
    }

}