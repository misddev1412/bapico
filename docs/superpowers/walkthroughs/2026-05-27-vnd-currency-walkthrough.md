# Walkthrough: VND Default Currency Implementation

This walkthrough details the changes made to implement VND as the default currency for the application.

## Changes Made

### 1. Decimal Formats Configuration
- **File:** [storage/resources/decimalFormats.json](file:///Users/abc/project/bapico/storage/resources/decimalFormats.json#L267-L271)
- **Detail:** Added `vi-VN` decimal formatting standard so it is supported on both frontend and backend configurations:
  ```json
      "vi-VN": {
          "name": "Vietnamese (Vietnam) (1.234.567,89)",
          "code": "vi-VN",
          "format": "1.234.567,89"
      }
  ```

### 2. Backend Fallback Settings Constants
- **File:** [config/constants.php](file:///Users/abc/project/bapico/config/constants.php#L43-L46)
- **Detail:** Updated default values for the setting array:
  - `CURRENCY` -> `VND`
  - `CURRENCY_ICON` -> `đ`

### 3. Database Seeder Defaults
- **File:** [SettingSeeder.php](file:///Users/abc/project/bapico/database/seeders/SettingSeeder.php#L21-L24)
- **Detail:** Seeding is now set to use:
  - `currency` -> `VND`
  - `currency_icon` -> `đ`
  - `currency_position` -> `2` (POST)
  - `decimal_format` -> `vi-VN`

### 4. Database Migration
- **File:** [2026_05_27_014546_set_default_currency_to_vnd.php](file:///Users/abc/project/bapico/database/migrations/2026_05_27_014546_set_default_currency_to_vnd.php)
- **Detail:** Checks the settings database table. If a row exists with `currency` set to `USD`, it updates it to `VND` (with icon `đ`, position `2`, format `vi-VN`) and clears the Laravel cache.

### 5. Git ignore
- **File:** [.gitignore](file:///Users/abc/project/bapico/.gitignore#L24-L28)
- **Detail:** Ignored Nuxt build output directories (`_nuxt`, `admin`, `seller`, `user`) as requested.

## Verification Results

### Automated Tests
Ran the application PHPUnit test suite:
- `php artisan test` -> PASS

### Database Migration
Ran migration successfully:
- `php artisan migrate` -> Created and ran the migration successfully, updating the database configuration row to VND.
