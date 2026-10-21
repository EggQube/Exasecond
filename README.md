# exasecond

The clock for AI agents. Universe seconds and "The question of time" for humans.

```
curl https://exasecond.com/now
```

## Folders

| Folder | What |
|---|---|
| `site/` | **The current site** (PHP 8.0+). Upload the contents of this folder, including the `.htaccess` files, to the web root. |
| `tools/check.php` | One-time host check (PHP version, intl). Upload, open, read, delete. |
| `test/` | `php test/php_routes.php` tests every route and chapter. |
| `backup/version 02 node-worker/` | The Node / Cloudflare Worker version, kept as an alternative deploy target and as the reference for its `test/parity.mjs`. It does not have the chapters. |

Run the site locally: `php -S 127.0.0.1:8791 site/index.php` (from this folder), then open http://127.0.0.1:8791/

Exasecond time: `universe_seconds = unix + 435084630253315200`, a convention fixing the universe's age at 13,787,000,000 years on 2000-01-01T00:00:00Z. See `/spec.md`.
