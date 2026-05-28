# Design Spec: Set Default Currency to VND

This specification details the changes required to implement VND as the default currency in the system with the symbol "đ" positioned after the price.

## Proposed Changes

### 1. Backend Fallback Config
Modify [config/constants.php](file:///Users/abc/project/bapico/config/constants.php) to change the default fallback currency settings:
- `CURRENCY` -> `VND`
- `CURRENCY_ICON` -> `đ`

### 2. Database Seeder
Modify [database/seeders/SettingSeeder.php](file:///Users/abc/project/bapico/database/seeders/SettingSeeder.php) to seed default settings using VND:
- `currency` -> `VND`
- `currency_icon` -> `đ`
- `currency_position` -> `2` (POST / after the price)
- `decimal_format` -> `vi-VN`

### 3. Decimal Formats Resource
Add `vi-VN` configuration to [storage/resources/decimalFormats.json](file:///Users/abc/project/bapico/storage/resources/decimalFormats.json) to support Vietnamese digit grouping (e.g. dot as thousand separator, comma as decimal separator):
```json
    "vi-VN": {
        "name": "Vietnamese (Vietnam) (1.234.567,89)",
        "code": "vi-VN",
        "format": "1.234.567,89"
    }
```

### 4. Database Migration
Create a new migration [database/migrations/2026_05_27_083237_set_default_currency_to_vnd.php](file:///Users/abc/project/bapico/database/migrations/2026_05_27_083237_set_default_currency_to_vnd.php):
- Check if settings row exists in database.
- If it exists and is set to `USD`, update it to `VND`, with icon `đ`, position `2`, and decimal format `vi-VN`.
- Clear the cache if any changes are made, to ensure cached settings are refreshed.

## Verification Plan

### Manual Verification
1. Run database migration using `php artisan migrate`.
2. Verify that the settings in the DB settings table are updated.
3. Clear application cache via `php artisan cache:clear` if needed.
4. Verify the currency symbol `đ` and format are correctly displayed on the pages.
