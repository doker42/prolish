<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanySubscriptions;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Subscription;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mollie\Laravel\Facades\Mollie;

class MembershipController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::find($this->getCompany($request));

        return Membership::all()->each(function ($item) use ($company) {
            if ($item->id == $company->membership_id) {
                $item->active = true;
                $item->status = 'custom.active';
                $item->next_payment = Carbon::parse($company->active_until)->addMonth()->format('Y-m-d');

                if (!Carbon::parse($company->active_until)->gt(Carbon::now())) {
                    $item->status = 'custom.item_status_failed';
                }
            }
        });
    }

    public function invoices(Request $request)
    {
        return Invoice::where('company_id', $this->getCompany($request))->paginate();
    }

    private function getCompany($request)
    {
        if (Auth::user()->role == 'super_user' && $request->get('company_id')) {
           return $request->get('company_id');
        }

        return Auth::user()->company_id;
    }

    public function store(Request $request)
    {
        $invoice_id = Invoice::count() + 1;
        $membership = Membership::find($request->get('id'));
        $company_id = Auth::user()->company_id;

        Invoice::create([
            'title' => $this->generateInvoiceID($invoice_id),
            'price' => $membership->price,
            'payment_status' => 'Request sent',
            'company_id' => $company_id,
            'membership_id' => $membership->id
        ]);

        return ['message' => 'success'];
    }

    public function approve(Request $request)
    {
        if (Auth::user()->role == 'super_user') {
            $invoice = Invoice::find($request->get('invoice_id'));
            $invoice->payment_status = 'custom.payment_paid';
            $invoice->save();

            Company::where('id', $invoice->company_id)->update([
                'membership_id' => $invoice->membership_id
            ]);
        }

        return ['message' => 'success'];
    }

    public function card_payment($id)
    {
        $membership = Membership::find($id);

        if ($membership) {
            $invoice_id = Invoice::count() + 1;

            $customer = Mollie::api()->customers()->create([
                'name'  => Auth::user()->name,
                'email' => Auth::user()->email,
            ]);

            $invoice = Invoice::create([
                'title' => $this->generateInvoiceID($invoice_id),
                'price' => $membership->price,
                'membership_id' => $id,
                'company_id' => Auth::user()->company_id,
                'payment_status' => 'custom.request_sent',
                'recurring_id' => $customer->id
            ]);

            $payment = Mollie::api()->payments()->create([
                'amount' => [
                    'currency' => 'EUR',
                    'value' => str_replace(',', '.', $invoice->price), // You must send the correct number of decimals, thus we enforce the use of strings
                ],
                'customerId'   => $customer->id,
                'sequenceType' => 'first',
                'description' => $invoice->title,
                'webhookUrl' => route('webhooks.mollie'),
                'redirectUrl' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/#/memberships',
                'metadata' => [
                    'invoice_id' => $invoice->id
                ]
            ]);

            $payment = Mollie::api()->payments()->get($payment->id);

            // redirect customer to Mollie checkout page
            return redirect($payment->getCheckoutUrl(), 303);
        }

        return redirect('/', 303);
    }

    private function generateInvoiceID($num)
    {
        $title = 'INV';

        if (strlen($num) >= 6) {
            for ($i = 0; $i < 6 - strlen($num); $i++) {
                $title .= '0';
            }
        }

        return $title . $num;
    }

    public function show_invoice($id)
    {
        $invoice = Invoice::where('id', $id)->with('membership', 'company')->first();

        if ($invoice) {
            $vat = config('invoices.tax_rates.0.tax');
            $vatDivisor = 1 + ($vat / 100);
            $price = $invoice->membership->price;
            $priceBeforeVat = number_format(($price / $vatDivisor), 2);

            return \ConsoleTVs\Invoices\Classes\Invoice::make()
                ->addItem($invoice->membership->title, $priceBeforeVat)
                ->number($id)
                ->date(Carbon::createFromFormat('Y-m-d H:i:s', $invoice->created_at))
                ->customer([
                    'name'      => $invoice->company->title,
                    'phone'     => $invoice->company->company_number,
                    'location'  => $invoice->company->company_address
                ])
                ->show('Invoice #' . $id);
        }

        return redirect('/');
    }


    /*
     * returns Stripe Session id for checkout
     */
    public function createSessionForCheckout($price_id, $company_id, $regular)
    {

        $company = Company::where('id', $company_id)->first();
        $membership = Membership::where('year_stripe_key', $price_id)->orWhere('month_stripe_key', $price_id)
            ->orWhere('year_stripe_sub_key', $price_id)->orWhere('month_stripe_sub_key', $price_id)->first();

        $is_special_discounted = $regular == 'discounted';
        $regular = $is_special_discounted?false:$regular;

        if (Auth::check() && Auth::user()->id == $company->owner_id && !empty($membership)) {
            /*Temporary discount logic to be deleted soon*/
            if ($is_special_discounted) {

                $old_subscriptions = CompanySubscriptions::where('company_id', $company->id)->get();
                foreach ($old_subscriptions as $old_subscriptioin) {
                    CompanySubscriptions::manager()->delete($old_subscriptioin->id);
                }

                $temporary_discount_id = '3y9DLuYZ';

                $options = [
                    'form_params' => [
                        'line_items' => [0 => [
                            'price' => $price_id,
                            'quantity' => 1]
                        ],
                        'discounts' => [0 => [
                            'coupon' => $temporary_discount_id,
                        ]],
                        'payment_method_types' => ['card'],
                        'customer' => $company->stripe_id,
                        'success_url' => env('APP_URL') . '#/settings',
                        'cancel_url' => env('APP_URL') . '#/settings',
                        'mode' => 'payment',
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('STRIPE_SECRET'),
                    ],
                ];

            } else {

                $options = [
                    'form_params' => [
                        'line_items' => [0 => [
                            'price' => $price_id,
                            'quantity' => 1]
                        ],
                        'payment_method_types' => ['card'],
                        'customer' => $company->stripe_id,
                        'success_url' => env('APP_URL') . '#/settings',
                        'cancel_url' => env('APP_URL') . '#/settings',
                        'mode' => $regular ? 'subscription' : 'payment',
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('STRIPE_SECRET'),
                    ],
                ];
            }

            $client = new Client();
            $post_response = $client->request('POST', "https://api.stripe.com/v1/checkout/sessions", $options);

            $response = json_decode($post_response->getBody()->getContents(), true);



            if (!empty($response['id'])) {
               if($membership->year_stripe_sub_key == $price_id || $membership->year_stripe_key == $price_id){
                   $price = $membership->year_price;
               } else{
                   $price = $membership->month_price;
               }

               if($regular){
                   $date = Carbon::now();
                   $subscription = CompanySubscriptions::create([
                       'company_id' => $company_id,
                       'name' => $company->title.' '.$membership->title,
                       'stripe_id' => $response['id'],
                       'stripe_plan'=> $price_id,
                       'quantity' => 1,
                       'trial_ends_at' => null,
                       'ends_at' => $membership->year_stripe_sub_key == $price_id?$date->addYear():$date->addMonth()
                   ]);
               }
                $invoice = Invoice::create([
                    'title' => Invoice::manager()->generateInvoiceID(),
                    'price' => $response['amount_total']/100,
                    'stripe_id' => is_null($response['payment_intent'])?$response['id']:$response['payment_intent'],
                    'stripe_url' => null,
                    'payment_status'=> Invoice::STATUS_CREATED,
                    'membership_id' => $membership->id,
                    'company_id' => $company->id,
                ]);

                if($regular){
                    $invoice->recurring_id = $subscription->id;
                }
                $invoice->save();

                return $response;
            } else {
                return response()->json(['errors' => 'Unable to initiate payment'], 401);
            }

        } else {
            return response()->json(['errors' => 'Unable to initiate payment'], 401);
        }

    }

}