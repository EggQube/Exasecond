<?php
// Minimal stateless remote MCP server (Streamable HTTP transport, JSON responses, no sessions). Port of src/mcp.js.
// Spec: https://modelcontextprotocol.io  Kept deliberately tiny so weak tool-callers cope.
defined('EXA') or exit;

const MCP_SUPPORTED = ['2025-06-18', '2025-03-26'];

function mcp_tools(): array {
    return [
        [
            'name' => 'get_time',
            'description' => 'Current date and time.',
            'inputSchema' => ['type' => 'object', 'properties' => [
                'tz' => ['type' => 'string', 'description' => 'City (Oslo), country, IANA zone, abbreviation or UTC offset (+02:00)'],
                'cutoff' => ['type' => 'string', 'description' => 'Training cutoff, YYYY-MM'],
                'lang' => ['type' => 'string', 'enum' => SENTENCE_LANGS, 'description' => 'Local sentence language; default en'],
                'detail' => ['type' => 'string', 'enum' => ['basic', 'full', 'local'], 'description' => 'basic (default): UTC, weekday, Unix; local: date, time and part of day in one sentence (use tz); full: calendars and world clocks'],
            ]],
        ],
        [
            'name' => 'convert_time',
            'description' => 'Convert exactly one of iso, unix, universe or es to UTC, local time and calendars.',
            'inputSchema' => ['type' => 'object', 'oneOf' => array_map(fn($k) => ['required' => [$k]], ['iso', 'unix', 'universe', 'es']), 'properties' => [
                'iso' => ['type' => 'string'], 'unix' => ['type' => 'string'], 'universe' => ['type' => 'string'], 'es' => ['type' => 'string'],
                'tz' => ['type' => 'string'],
            ]],
        ],
    ];
}

class McpError extends Exception {}

function mcp_call_tool($name, $args): string {
    $args = is_array($args) ? $args : [];
    foreach ($args as $key => $value) {
        if (in_array($key, ['tz', 'cutoff', 'detail', 'lang', 'iso', 'unix', 'universe', 'es'], true) && !is_string($value)) throw new InputError("$key must be a string");
    }
    $str = fn($k) => isset($args[$k]) && is_scalar($args[$k]) ? (string)$args[$k] : null;
    if ($name === 'get_time') {
        if (isset($args['detail']) && !in_array($args['detail'], ['basic', 'full', 'local'], true)) throw new InputError('detail must be basic, full or local');
        if (isset($args['lang']) && !in_array($args['lang'], SENTENCE_LANGS, true)) throw new InputError('unsupported lang');
        $o = tz_opts($str('tz'), $str('cutoff'));
        $s = snapshot(null, $o);
        if ($str('detail') === 'local') return local_sentence($s, $o['tz_res'], $str('lang') ?? 'en');
        return $str('detail') === 'full' ? now_md($s) : now_text($s);
    }
    if ($name === 'convert_time') {
        $d = parse_instant($args);
        return now_md(snapshot($d, tz_opts($str('tz'), null)), 'Converted time');
    }
    throw new McpError('Unknown tool: ' . (is_scalar($name) ? $name : ''), -32602);
}

function mcp_handle_one($msg): ?array {
    $invalid = fn() => ['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32600, 'message' => 'Invalid Request']];
    if (!($msg instanceof stdClass)) return $invalid();
    $msg = (array)$msg;
    if (($msg['jsonrpc'] ?? null) !== '2.0' || !is_string($msg['method'] ?? null)) return $invalid();
    $hasId = array_key_exists('id', $msg);
    $id = $msg['id'] ?? null;
    if ($hasId && !is_string($id) && !is_int($id)) return $invalid();
    $method = $msg['method'] ?? null;
    $params = isset($msg['params']) && $msg['params'] instanceof stdClass ? (array)$msg['params'] : [];
    $ok = fn($result) => ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    $err = fn($code, $message) => ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    if (array_key_exists('params', $msg) && !($msg['params'] instanceof stdClass)) {
        // MCP uses named parameters. Valid notifications never receive a JSON-RPC reply.
        return $hasId ? $err(-32602, 'params must be an object') : null;
    }
    if (!$hasId) return null;
    switch ($method) {
        case 'initialize':
            $client = $params['clientInfo'] ?? null;
            if (!is_string($params['protocolVersion'] ?? null) || !(($params['capabilities'] ?? null) instanceof stdClass) || !($client instanceof stdClass) || !is_string($client->name ?? null) || !is_string($client->version ?? null)) return $err(-32602, 'initialize requires protocolVersion, capabilities and clientInfo');
            $v = in_array($params['protocolVersion'] ?? null, MCP_SUPPORTED, true) ? $params['protocolVersion'] : MCP_SUPPORTED[0];
            return $ok([
                'protocolVersion' => $v,
                'capabilities' => ['tools' => ['listChanged' => false]],
                'serverInfo' => ['name' => 'exasecond', 'version' => SPEC_VERSION],
                'instructions' => "Use get_time whenever you need today's date or the current time.",
            ]);
        case 'ping': return $ok(new stdClass());
        case 'tools/list': return $ok(['tools' => mcp_tools()]);
        case 'tools/call':
            if (!is_string($params['name'] ?? null) || (array_key_exists('arguments', $params) && !($params['arguments'] instanceof stdClass))) return $err(-32602, 'tools/call requires a name and object arguments');
            try {
                return $ok(['content' => [['type' => 'text', 'text' => mcp_call_tool($params['name'], (array)($params['arguments'] ?? new stdClass()))]]]);
            } catch (McpError $e) {
                return $err($e->getCode(), $e->getMessage());
            } catch (InputError $e) {
                return $ok(['content' => [['type' => 'text', 'text' => 'Error: ' . $e->getMessage()]], 'isError' => true]);
            }
        default: return $err(-32601, 'Method not found: ' . (is_scalar($method) ? $method : ''));
    }
}

/** Returns [status, body|null]. */
function mcp_response(string $method, string $raw, array $headers = []): array {
    if ($method !== 'POST') return [405, null];
    // Streamable HTTP specifies March 2025 behavior when this header is absent.
    $version = $headers['mcp-protocol-version'] ?? '2025-03-26';
    if (!in_array($version, MCP_SUPPORTED, true)) return [400, ['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32600, 'message' => 'Unsupported MCP-Protocol-Version']]];
    $body = json_decode($raw);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [400, ['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32700, 'message' => 'Parse error']]];
    }
    // March clients require batches; June clients send one message per POST.
    if ($version === '2025-03-26' && is_array($body) && $body !== []) {
        $results = array_values(array_filter(array_map('mcp_handle_one', $body)));
        return $results ? [200, $results] : [202, null];
    }
    $result = mcp_handle_one($body);
    if ($result === null) return [202, null];
    return [($result['error']['code'] ?? null) === -32600 ? 400 : 200, $result];
}
