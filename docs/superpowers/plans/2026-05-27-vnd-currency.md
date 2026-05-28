# VND Default Currency Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Set the default currency of the application to VND with the symbol "đ" placed after the price.

**Architecture:** Change application configurations, seeding files, and database migrations to default settings and support the "vi-VN" decimal format.

**Tech Stack:** PHP (Laravel)

---

### Task 1: Add `vi-VN` to decimalFormats resource

**Files:**
- Modify: `storage/resources/decimalFormats.json`

- [ ] **Step 1: Write the updated decimalFormats.json content**
  Open [storage/resources/decimalFormats.json](file:///Users/abc/project/bapico/storage/resources/decimalFormats.json) and add `vi-VN` to the end of the JSON object.

  ```json
      "zh-TW": {
          "name": "Taiwan, traditional (1,234,567.89)",
          "code": "zh-TW",
          "format": "1,234,567.89"
      },
      "vi-VN": {
          "name": "Vietnamese (Vietnam) (1.234.567,89)",
          "code": "vi-VN",
          "format": "1.234.567,89"
      }
  ```

- [ ] **Step 2: Verify JSON syntax is valid**
  Verify the file syntax is correct JSON.

- [ ] **Step 3: Commit changes**
  ```bash
  git add storage/resources/decimalFormats.json
  git commit -m "config: add vi-VN decimal format resource"
  ```

---

### Task 2: Modify Backend Constants

**Files:**
- Modify: `config/constants.php`

- [ ] **Step 1: Update constants file**
  Update the fallback currency setting inside [config/constants.php](file:///Users/abc/project/bapico/config/constants.php) around line 43-46 to VND and đ:
  ```php
      'setting' => [
          'CURRENCY' => 'VND',
          'CURRENCY_ICON' => 'đ'
      ],
  ```

- [ ] **Step 2: Commit changes**
  ```bash
  git add config/constants.php
  git commit -m "config: update default fallback currency constants to VND"
  ```

---

### Task 3: Update Database Seeder

**Files:**
- Modify: `database/seeders/SettingSeeder.php`

- [ ] **Step 1: Update SettingSeeder**
  Update default seeded settings in [database/seeders/SettingSeeder.php](file:///Users/abc/project/bapico/database/seeders/SettingSeeder.php) to default to VND, "đ", position 2 (POST), and decimal format `vi-VN`:
  ```php
          $items = [
              [
                  'id' => 1,
                  'currency' => 'VND',
                  'currency_icon' => 'đ',
                  'currency_position' => 2,
                  'decimal_format' => 'vi-VN',
  
                  'phone' => '4534345656',
                  'email' => 'webzedcontact@gmail.com',
                  'address_1' => 'House 4/3, Road: 34, Bronx, NY',
                  'city' => 'New York',
                  'state' => 'New York',
                  'zip' => '78947',
                  'country' => 'USA',
                  'admin_id' => 1
              ]
          ];
  ```

- [ ] **Step 2: Commit changes**
  ```bash
  git add database/seeders/SettingSeeder.php
  git commit -m "database: update SettingSeeder to seed VND defaults"
  ```

---

### Task 4: Create Database Migration for Existing Database

**Files:**
- Create: `database/migrations/2026_05_27_083237_set_default_currency_to_vnd.php`

- [ ] **Step 1: Propose the migration file**
  Create a new Laravel migration file at [database/migrations/2026_05_27_083237_set_default_currency_to_vnd.php](file:///Users/abc/project/bapico/database/migrations/2026_05_27_083237_set_default_currency_to_vnd.php):
  ```php
  <?php
  
  use Illuminate\Database\Migrations\Migration;
  use App\Models\Setting;
  use Illuminate\Support\Facades\Artisan;
  
  class SetDefaultCurrencyToVnd extends Migration
  {
      /**
       * Run the migrations.
       *
       * @return void
       */
      public function up()
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
       *
       * @return void
       */
      public function down()
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
  }
  ```

- [ ] **Step 2: Run migration**
  Run command in cwd `/Users/abc/project/bapico`:
  `php artisan migrate`

- [ ] **Step 3: Clear Cache**
  Run:
  `php artisan cache:clear`

- [ ] **Step 4: Commit migration**
  ```bash
  git add database/migrations/2026_05_27_083237_set_default_currency_to_vnd.php
  git commit -m "database: create migration to update currency to VND"
  ```
