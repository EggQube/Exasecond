# exasecond

Current time for AI. To the second. Minimal token overhead. Fetch a short UTC timestamp over HTTP or connect through MCP, with no key and no sign-up needed. Local conversational answers and "The question of time", a sourced reference, are available separately.

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

## Validate changes

Use 64-bit PHP 8.0 or newer. Enable `intl` and `mbstring` for the full multilingual and calendar behavior. There are no Composer or npm dependencies for the PHP site.

```
php test/php_routes.php
php test/php_reliability.php
php tools/measure_tokens.php
```

With the local PHP server running, `node test/http_smoke.mjs` checks the HTTP responses, translated pages and MCP initialization/tool flow. It uses Node's built-in APIs and needs no packages.

If PHP is not on PATH, use the full path to `php.exe` and `-c path/to/php.ini`. The size report uses characters divided by four, not a model tokenizer. `php tools/measure_tokens.php --json` includes the sample texts for measurement with a target model's tokenizer. It covers both MCP schemas and all ten answer languages, with and without a nearby clock change. Client framing and billing are not measured.

For comparison with the archived Node time engine, run from `backup/version 02 node-worker/`:

```
node test/parity.mjs path/to/php.exe path/to/php.ini
```

The original shared calculations should still match. New PHP features and stricter input validation are covered by the PHP suites; the archive is not the current feature set.

## Deploy and restore

Upload all contents of `site/`, including hidden `.htaccess` files and `site/data/places/`, to the web root. All 256 PHP place shards are required for the full place-name lookup. They are ignored by Git, so a fresh Git clone alone is incomplete. Without shards, built-in city and zone names still work but many place addresses return 404.

Restore the place shards from a complete project backup, or rebuild them with `tools/build_places.php` and the three GeoNames input files described at the top of that script. Rebuilding replaces the shard files; do it in a separate restored copy and validate before deployment. Keep GeoNames attribution with the site.

`docs/` and `CLAUDE.md` are local handoff material excluded by `.git/info/exclude`. Keep them in complete backups alongside Git history and place data; they are not deployed. Dated local snapshots live in `backup/snapshots/`. Verify an archive against its `.sha256.txt` file and restore into a separate directory before replacing a working copy. Keep another copy off this drive.

Before publishing, run both PHP suites and smoke-test `/`, `/local?tz=Oslo`, a GeoNames place such as `/kiruna`, `/api/convert` and the MCP initialization/tool flow on the target host. The local PHP server does not exercise Apache rewrite rules. The server clock and time-zone database must be maintained by the host; this application does not independently certify clock synchronization.

Native proofreading and tests through ChatGPT, Claude, DeepSeek, Gemini and a local model remain separate acceptance checks. Use a fresh conversation for each, record model/client/version and the UTC retrieval time, and compare with an independent clock. Include Stockholm's clock change, Tokyo's part of day, a sourced time-zone fact and a translated MCP local answer. The local regression suites do not substitute for these app-specific checks.
