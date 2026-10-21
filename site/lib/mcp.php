<?php
// Minimal stateless remote MCP server (Streamable HTTP transport, JSON responses, no sessions). Port of src/mcp.js.
// Spec: https://modelcontextprotocol.io  Kept deliberately tiny so weak tool-callers cope.
defined('EXA') or exit;

const MCP_SUPPORTED = ['2025-06-18', '2025-03-26', '2024-11-05'];

function mcp_tools(): array {
    return [
        [
            'name' => 'get_time',
            'description' => "Get the current date and time. Call this instead of guessing today's date from training data.",
            'inputSchema' => ['type' => 'object', 'properties' => [
                'tz' => ['type' => 'string', 'description' => 'Time zone of the person you are talking to: a city (Oslo), country (Norway), IANA name (Europe/Oslo), abbreviation (CET) or offset (+02:00)'],
                'cutoff' => ['type' => 'string', 'description' => 'Your training cutoff, YYYY-MM'],
                'detail' => ['type' => 'string', 'enum' => ['basic', 'full', 'local'], 'description' => 'local: one sentence with the person\'s date, time and part of day (use with tz when talking to someone); full adds all calendars and world clocks'],
            ]],
        ],
        [
            'name' => 'convert_time',
            'description' => 'Convert one instant (iso, unix, universe or es) into UTC, a time zone and all human calendars.',
            'inputSchema' => ['type' => 'object', 'properties' => [
                'iso' => ['type' => 'string'], 'unix' => ['type' => 'string'], 'universe' => ['type' => 'string'], 'es' => ['type' => 'string'],
                'tz' => ['type' => 'string'],
            ]],
        ],
    ];
}

class McpError extends Exception {}

function mcp_call_tool($name, $args): string {
    $args = is_array($args) ? $args : [];
    $str = fn($k) => isset($args[$k]) && is_scalar($args[$k]) ? (string)$args[$k] : null;
    if ($name === 'get_time') {
        $o = tz_opts($str('tz'), $str('cutoff'));
        $s = snapshot(null, $o);
        if ($str('detail') === 'local') return local_sentence($s, $o['tz_res']);
        return $str('detail') === 'full' ? now_md($s) : now_text($s);
    }
    if ($name === 'convert_time') {
        $d = parse_instant($args);
        return now_md(snapshot($d, tz_opts($str('tz'), null)), 'Converted time');
    }
    throw new McpError('Unknown tool: ' . (is_scalar($name) ? $name : ''), -32602);
}

function mcp_handle_one($msg): ?array {
    if (!is_array($msg) || !array_key_exists('id', $msg)) return null;   // notification (e.g. notifications/initialized)
    $id = $msg['id'];
    $method = $msg['method'] ?? null;
    $params = is_array($msg['params'] ?? null) ? $msg['params'] : [];
    $ok = fn($result) => ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    $err = fn($code, $message) => ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    switch ($method) {
        case 'initialize':
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
            try {
                return $ok(['content' => [['type' => 'text', 'text' => mcp_call_tool($params['name'] ?? null, $params['arguments'] ?? [])]]]);
            } catch (McpError $e) {
                return $err($e->getCode(), $e->getMessage());
            } catch (InputError $e) {
                return $ok(['content' => [['type' => 'text', 'text' => 'Error: ' . $e->getMessage()]], 'isError' => true]);
            }
        default: return $err(-32601, 'Method not found: ' . (is_scalar($method) ? $method : ''));
    }
}

/** Returns [status, body|null]. */
function mcp_response(string $method, string $raw): array {
    if ($method !== 'POST') return [405, null];
    $body = json_decode($raw, true);
    if ($body === null && trim($raw) !== 'null') {
        return [400, ['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32700, 'message' => 'Parse error']]];
    }
    $batch = is_array($body) && array_is_list_compat($body) && $body !== [];
    $results = array_values(array_filter(array_map('mcp_handle_one', $batch ? $body : [$body])));
    if (!$results) return [202, null];
    return [200, $batch ? $results : $results[0]];
}

function array_is_list_compat(array $a): bool {   // array_is_list() is PHP 8.1+
    return array_keys($a) === range(0, count($a) - 1);
}
