# Code review notes — Indexa_Posthog

Summary of a review against Magento 2 and PSR-12 practices. Use these as optional improvements for open-source quality.

## Strengths

- **Strict types:** `declare(strict_types=1);` present in PHP classes.
- **Type hints:** Parameters and return types are type-hinted; PHPDoc `@param` / `@return` used where needed.
- **Security:** API key stored encrypted; templates use `escapeJs()` and JSON encoding flags (`JSON_HEX_*`) for script output; no raw SQL.
- **Architecture:** Clear separation (Block, Helper, Model, Observer, Plugin); dependency injection used; no service locators.
- **Caching:** `OrderCompleted` disables block cache for success page (session-specific).

## Optional improvements

1. **Constructor property promotion:** Classes (e.g. `EventDataBuilder`, `Helper\Data`, ecommerce Blocks) use traditional properties + constructor assignment. Consider migrating to constructor property promotion with `readonly` for brevity and immutability (PHP 8.0+).
2. **Templates:** `script.phtml` and ecommerce `.phtml` files could add `@var` block for the block variable to improve IDE support and consistency with Magento frontend guidelines.
3. **EventDataBuilder:** `getProductCategoryName` and `getBrand` swallow exceptions and return empty string; consider logging at debug level in development for easier troubleshooting (optional).

## Automated checks (recommended)

Before contributing or releasing, run:

- `vendor/bin/phpcs --standard=Magento2 app/code/Indexa/Posthog/`
- `vendor/bin/phpstan analyse app/code/Indexa/Posthog/` (if phpstan/psalm is configured)

No critical or high-severity issues were identified; the module is suitable for open-source release from a code-quality perspective.
