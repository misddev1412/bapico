<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Seeder;

class AddPaymentIyzicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payment = Payment::first();

        if($payment && !$payment->ip_base_url){

            Payment::where('id', $payment->id)->update([
                'iyzico_payment' => 1,
                'ip_api_key' => env('IYZICO_API_KEY', ''),
                'ip_secret_key' => env('IYZICO_SECRET_KEY', ''),
                'ip_base_url' => env('IYZICO_BASE_URL', 'https://sandbox-api.iyzipay.com')
            ]);

        }
    }
}
