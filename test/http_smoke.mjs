// Start PHP with: php -S 127.0.0.1:8791 site/index.php
// Then run: node test/http_smoke.mjs [http://127.0.0.1:8791]
import assert from 'node:assert/strict';

const base = process.argv[2] || 'http://127.0.0.1:8791';
let checks = 0;
async function get(path, status = 200, options = {}) {
    const response = await fetch(base + path, { ...options, signal: AbortSignal.timeout(10000) });
    assert.equal(response.status, status, path);
    assert.equal(response.headers.get('cache-control'), 'no-store', path);
    checks++;
    return { body: await response.text(), headers: response.headers };
}
async function rpc(message, status = 200, version = '2025-06-18') {
    const response = await get('/mcp', status, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json, text/event-stream', 'MCP-Protocol-Version': version },
        body: JSON.stringify(message),
    });
    return response.body ? JSON.parse(response.body) : null;
}

for (const lang of ['en', 'zh', 'de', 'fr', 'es', 'pt', 'ru', 'sv', 'ja', 'ko']) {
    const prefix = lang === 'en' ? '' : '/' + lang;
    for (const path of ['/', '/about', '/developers']) {
        const { body } = await get(prefix + path);
        assert.ok(body.includes('<html lang='));
        assert.ok(!body.includes('\uFFFD'));
        assert.ok(!body.includes('Fatal error'));
        if (path === '/') {
            assert.equal((body.match(/<h1\b/g) || []).length, 1);
            assert.match(body, /<output>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z<\/output>/);
            const schema = JSON.parse(body.match(/<script type="application\/ld\+json">(.*?)<\/script>/s)[1]);
            assert.equal(schema['@type'], 'WebAPI');
            assert.equal(schema.documentation, 'https://exasecond.com' + prefix + '/developers');
            assert.ok(body.includes('href="' + prefix + '/developers"'));
            assert.ok(!body.includes('service_title'));
        }
    }
    const { body: markdown, headers } = await get(prefix + '/', 200, { headers: { Accept: 'text/markdown' } });
    assert.match(headers.get('content-type'), /^text\/markdown/);
    assert.ok(markdown.includes('https://exasecond.com' + prefix + '/developers.md'));
    assert.ok(markdown.includes('/utc') && markdown.includes('/local?tz=Oslo'));
    assert.ok(!markdown.includes('<html'));
    const { body: setup } = await get(prefix + '/developers.md');
    assert.ok(setup.includes('curl https://exasecond.com/utc'));
    assert.ok(setup.includes('"mcpServers"'));
    const { body: local } = await get('/tokyo?lang=' + lang);
    const tool = await rpc({ jsonrpc: '2.0', id: lang, method: 'tools/call', params: { name: 'get_time', arguments: { tz: 'Tokyo', detail: 'local', lang } } });
    // Ignore the minute, which may turn between these two live requests.
    assert.equal(tool.result.content[0].text.replace(/\d{2}:\d{2}/g, 'HH:MM'), local.trim().replace(/\d{2}:\d{2}/g, 'HH:MM'));
}
assert.match((await get('/utc')).body, /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z\n$/);
const initialized = await rpc({ jsonrpc: '2.0', id: 1, method: 'initialize', params: { protocolVersion: '2025-06-18', capabilities: {}, clientInfo: { name: 'http-smoke', version: '1' } } });
assert.equal(initialized.result.protocolVersion, '2025-06-18');
assert.equal(await rpc({ jsonrpc: '2.0', method: 'notifications/initialized' }, 202), null);
assert.equal((await rpc({ jsonrpc: '2.0', id: 2, method: 'tools/list' })).result.tools.length, 2);
assert.equal((await rpc(null, 400)).error.code, -32600);
await get('/mcp', 405);
const converted = JSON.parse((await get('/api/convert?unix=-0.125')).body);
assert.equal(converted.technical.unix_ms, -125);
await get('/api/convert?iso=2026-02-29', 400);
await get('/api/convert?iso=2000-01-01&unix=0', 400);
await get('/api/convert?iso%5B%5D=2000-01-01', 400);
assert.ok(JSON.parse((await get('/openapi.json')).body).paths['/local']);
await get('/kiruna');
assert.ok((await get('/', 200, { headers: { Accept: 'text/markdown' } })).body.startsWith('For AI agents:'));
const head = await get('/local?tz=Oslo', 200, { method: 'HEAD' });
assert.equal(head.body, '');
const batch = [{ jsonrpc: '2.0', id: 1, method: 'ping' }, { jsonrpc: '2.0', method: 'notifications/initialized' }];
assert.equal((await rpc(batch, 200, '2025-03-26')).length, 1);
assert.equal((await rpc(batch, 400)).error.code, -32600);
assert.equal((await rpc({ jsonrpc: '2.0', id: 1, method: 'ping' }, 400, '2099-01-01')).error.code, -32600);
assert.equal(await rpc([batch[1]], 202, '2025-03-26'), null);
console.log(`${checks} HTTP checks passed, including 10-language pages, protocol versions and MCP/HTTP sentence parity.`);

// Deployment comparison: static document representations and generated CSS can be
// compared exactly; live clock pages must be checked without comparing timestamps.
if (process.argv.includes('--compare-local')) {
    const localBase = 'http://127.0.0.1:8791';
    const differences = [];
    const fresh = '?upload-check=' + Date.now();
    const toolRequest = { jsonrpc: '2.0', id: 'upload-check', method: 'tools/list' };
    const localToolsResponse = await fetch(localBase + '/mcp', {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'MCP-Protocol-Version': '2025-06-18' },
        body: JSON.stringify(toolRequest), signal: AbortSignal.timeout(10000),
    });
    assert.equal(localToolsResponse.status, 200);
    const localTools = (await localToolsResponse.json()).result.tools;
    const publishedTools = (await rpc(toolRequest)).result.tools;
    const localSchema = JSON.stringify(localTools);
    const publicSchema = JSON.stringify(publishedTools);
    if (publicSchema !== localSchema) {
        differences.push('MCP tool definitions differ: public ' + Buffer.byteLength(publicSchema) + ' bytes, local ' + Buffer.byteLength(localSchema) + ' bytes');
    } else {
        console.log('Public MCP tool definitions match local exactly (' + Buffer.byteLength(publicSchema) + ' bytes).');
    }
    for (const lang of ['en', 'zh', 'de', 'fr', 'es', 'pt', 'ru', 'sv', 'ja', 'ko']) {
        const prefix = lang === 'en' ? '' : '/' + lang;
        for (const page of ['/about.md', '/developers.md']) {
            const path = prefix + page;
            const local = await fetch(localBase + path, { signal: AbortSignal.timeout(10000) });
            assert.equal(local.status, 200, 'Local reference ' + path);
            const published = (await get(path + fresh)).body;
            const reference = await local.text();
            if (published !== reference) {
                const publishedLines = published.split('\n');
                const referenceLines = reference.split('\n');
                const first = referenceLines.findIndex((line, index) => line !== publishedLines[index]);
                differences.push(path + ': first difference: ' + JSON.stringify(publishedLines[first]) + ' instead of ' + JSON.stringify(referenceLines[first]));
            }
        }
    }
    const localCss = await fetch(localBase + '/site.css', { signal: AbortSignal.timeout(10000) });
    const publicCss = await fetch(base + '/site.css' + fresh, { signal: AbortSignal.timeout(10000) });
    assert.equal(localCss.status, 200);
    assert.equal(publicCss.status, 200);
    if (await publicCss.text() !== await localCss.text()) differences.push('Published stylesheet differs from local');
    const home = (await get('/')).body;
    for (const fragment of ['class="site-menu"', '<summary>Menu</summary>', 'o200k_base', 'cl100k_base']) {
        if (!home.includes(fragment)) differences.push('Homepage missing ' + fragment);
    }
    if (!(await get('/story')).body.includes('class="read read-story"')) differences.push('Story missing its narrow layout class');
    if ((await get('/time/nature')).body.includes('read-story')) differences.push('Science chapter incorrectly has story layout');
    if (differences.length) {
        console.log(differences.join('\n'));
        process.exitCode = 1;
    } else {
    console.log('Published copy matches all 20 local About/developer documents and the stylesheet; current menu, token table and story width are present.');
    }
}
