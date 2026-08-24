# Changelog - Version 6.0

## Overview

Version 6.0 represents a major update to the PHP AnyDataset JSON library, bringing PHP 8.4 support, improved iterator implementation, enhanced documentation, and updated dependencies.

## New Features

### PHP 8.4 Support
- Added support for PHP 8.4
- Updated minimum PHP requirement from 8.1 to 8.3
- PHP version range now supports: `>=8.3 <8.6`

### Enhanced Iterator Implementation
- Improved internal iterator implementation with better state management
- Enhanced JSON parsing logic for more robust data handling
- Added `#[Override]` and `#[ReturnTypeWillChange]` attributes for better type safety
- Improved row parsing with dedicated `parseRow()` and `getJsonObjectForCurrentRow()` methods
- Better separation of concerns in field parsing logic

### Updated Dependencies
- Updated `byjg/anydataset` from `^5.0` to `^6.0`
- Updated PHPUnit from `^9.6` to `^10.5|^11.5`
- Updated Psalm from `^5.6` to `^5.9|^6.13`

### Improved Documentation
- Enhanced README with better examples
- Improved documentation for dynamic fields
- More comprehensive JsonFieldDefinition documentation
- Added clearer explanations of simple manipulation features

### CI/CD Improvements
- Updated GitHub Actions PHPUnit workflow with improved container options
- Updated action versions for better compatibility
- Enhanced testing infrastructure

## Bug Fixes

- Fixed Psalm static analysis issues
- Various minor fixes for improved stability
- Fixed type handling in iterator implementation

## Breaking Changes

| Before (5.x) | After (6.0) | Description |
|--------------|-------------|-------------|
| `php: >=8.1 <8.4` | `php: >=8.3 <8.6` | Minimum PHP version increased from 8.1 to 8.3. PHP versions 8.1 and 8.2 are no longer supported. |
| `byjg/anydataset: ^5.0` | `byjg/anydataset: ^6.0` | Dependency updated to version 6.0 of the core anydataset library. |
| `$iterator->hasNext()` | Use standard iterator pattern | The `hasNext()` method has been removed. Use `foreach` or standard iterator methods (`valid()`, `current()`, `next()`). |
| `$iterator->moveNext()` | Use standard iterator pattern | The `moveNext()` method has been removed. Use `foreach` or standard iterator methods. |
| `ByJG\AnyDataset\Core\Row` | `ByJG\AnyDataset\Core\RowInterface` | Changed from using concrete `Row` class to `RowInterface`. |
| `private ?array $jsonObject` | `private array $jsonObject` | Internal property type changed - always array, never null (empty array used instead). |
| `private int $current` | `private int $currentIndex` | Internal property renamed for clarity. |

## Path to Upgrade from 5.x to 6.x

### Step 1: Update PHP Version
Ensure your environment is running PHP 8.3 or later:
```bash
php -v
```

If you're running PHP 8.1 or 8.2, you'll need to upgrade your PHP installation before proceeding.

### Step 2: Update Composer Dependencies
Update your `composer.json`:
```bash
composer require byjg/anydataset-json:^6.0
```

This will automatically pull in the required `byjg/anydataset:^6.0` dependency.

### Step 3: Remove Deprecated Methods
If your code uses `hasNext()` or `moveNext()`, replace them with standard iterator patterns:

**Before (5.x):**
```php
$iterator = $dataset->getIterator("/menu/items");
while ($iterator->hasNext()) {
    $row = $iterator->moveNext();
    // process $row
}
```

**After (6.0):**
```php
$iterator = $dataset->getIterator("/menu/items");
foreach ($iterator as $row) {
    // process $row
}
```

Alternatively, if you need manual iteration control:
```php
$iterator = $dataset->getIterator("/menu/items");
$iterator->rewind();
while ($iterator->valid()) {
    $row = $iterator->current();
    // process $row
    $iterator->next();
}
```

### Step 4: Update Type Hints (if applicable)
If your code has type hints referencing the old `Row` class, update them to use `RowInterface`:

**Before (5.x):**
```php
use ByJG\AnyDataset\Core\Row;

function processRow(Row $row): void {
    // ...
}
```

**After (6.0):**
```php
use ByJG\AnyDataset\Core\RowInterface;

function processRow(RowInterface $row): void {
    // ...
}
```

### Step 5: Test Your Application
Run your test suite to ensure everything works correctly:
```bash
vendor/bin/phpunit
```

### Step 6: Run Static Analysis
If you're using Psalm or PHPStan, run static analysis to catch any remaining issues:
```bash
vendor/bin/psalm
```

## Notes

- The core functionality of the library remains the same
- Field definitions using `JsonFieldDefinition` work exactly as before
- Path navigation syntax remains unchanged
- Data validation features are fully compatible
- No changes required to your JSON data structures

## Support

For issues or questions:
- GitHub Issues: https://github.com/byjg/php-anydataset-json/issues
- Documentation: https://opensource.byjg.com/anydataset
