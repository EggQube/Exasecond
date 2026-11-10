# exasecond

Current time for AI. To the second. Minimal token overhead. Fetch a short UTC timestamp over HTTP or connect through MCP, with no key and no sign-up needed. Local conversational answers and "The question of time", a sourced reference, are available separately.

```
curl https://exasecond.com/utc
```

## Folders

| Folder | What |
|---|---|
| `site/` | **The current site** (PHP 8.0+). Upload the contents of this folder, including the `.htaccess` files, to the web root. |
| `tools/check.php` | One-time host check (PHP version, intl). Upload, open, read, delete. |
| `test/` | `php test/php_routes.php` tests every route and chapter. |

Run the site locally: `php -S 127.0.0.1:8791 site/index.php` (from this folder), then open http://127.0.0.1:8791/

Exasecond time: `universe_seconds = unix + 435084630253315200`, a convention fixing the universe's age at 13,787,000,000 years on 2000-01-01T00:00:00Z. See `/spec.md`.

## Validate changes

Use 64-bit PHP 8.0 or newer. Enable `intl` and `mbstring` for the full multilingual and calendar behavior. There are no Composer or npm dependencies for the PHP site.

```
php test/php_routes.php
php test/php_reliability.php
php tools/measure_tokens.php
```

### HTTP smoke test

With the local server running:

```
node test/http_smoke.mjs [http://127.0.0.1:8791]
```

It checks the HTTP responses, the translated pages and the MCP initialization and tool flow. It uses Node's built-in APIs only.

If PHP is not on your PATH, call `php.exe` by its full path and pass `-c path/to/php.ini`.

### Response sizes

`php tools/measure_tokens.php` estimates response sizes as characters divided by four; it is not a model tokenizer. With `--json` it also exports the sample texts, so they can be measured with a target model's own tokenizer. The samples cover both MCP schemas and all ten answer languages, with and without an upcoming clock change. Client framing and billing are not measured.

## Deploy

1. Run both PHP test suites.
2. Upload the contents of `site/` to the web root, including the hidden `.htaccess` files and `site/data/places/`.
3. On the live host, check `/`, `/local?tz=Oslo`, a GeoNames place such as `/kiruna`, `/api/convert` and the MCP initialization and tool flow. The local PHP server does not run Apache's rewrite rules, so these checks matter on the host itself.

The server's clock and time-zone database are maintained by the host. Exasecond.com reports the server's time; it does not certify clock synchronization on its own.

### Place data

The full place-name lookup needs all 256 place shards in `site/data/places/`. They are not in Git, so a fresh clone is incomplete: without them, built-in city and time-zone names still work, but many place addresses return 404.

To restore them, copy them from a complete backup, or rebuild them with `tools/build_places.php` from the three GeoNames input files listed at the top of that script. A rebuild replaces the shard files, so run it in a separate copy and test before deploying. Place names come from GeoNames (CC BY 4.0); keep the attribution with the site.
