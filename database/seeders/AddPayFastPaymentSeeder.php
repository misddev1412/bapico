<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class AddPayFastPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Admin::where('id', 1)->first();
        $payment = Payment::first();

        if($payment && $admin){
            Payment::where('id', $payment->id)->update([
                'payfast_payment' => true,
                'payfast_base_url' => env('PAYFAST_BASE_URL', 'https://sandbox.payfast.co.za'),
                'payfast_merchant_id' => env('PAYFAST_MERCHANT_ID', ''),
                'payfast_merchant_key' => env('PAYFAST_MERCHANT_KEY', ''),
                'payfast_passphrase' => env('PAYFAST_PASSPHRASE', '')
            ]);
        }
    }
}
