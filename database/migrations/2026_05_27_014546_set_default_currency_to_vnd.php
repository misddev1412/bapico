<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $setting = Setting::first();
        if ($setting && $setting->currency === 'USD') {
            Setting::where('id', $setting->id)->update([
                'currency' => 'VND',
                'currency_icon' => 'đ',
                'currency_position' => 2,
                'decimal_format' => 'vi-VN'
            ]);
            Artisan::call('cache:clear');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $setting = Setting::first();
        if ($setting && $setting->currency === 'VND') {
            Setting::where('id', $setting->id)->update([
                'currency' => 'USD',
                'currency_icon' => '$',
                'currency_position' => 1,
                'decimal_format' => 'en-US'
            ]);
            Artisan::call('cache:clear');
        }
    }
};
