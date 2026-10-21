<?php
// One-time host check for exasecond. Delete from the server after use.
header('Content-Type: text/plain; charset=utf-8');
echo "PHP version: " . PHP_VERSION . "\n";
echo "64-bit int: " . (PHP_INT_SIZE === 8 ? "yes" : "NO") . "\n";
echo "intl: " . (extension_loaded('intl') ? "yes (ICU " . (defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : '?') . ")" : "NO") . "\n";
echo "json: " . (extension_loaded('json') ? "yes" : "NO") . "\n";
echo "mod_rewrite: " . (function_exists('apache_get_modules') ? (in_array('mod_rewrite', apache_get_modules()) ? "yes" : "NO") : "unknown") . "\n";
echo "Server time (UTC): " . gmdate('Y-m-d\TH:i:s\Z') . "\n";
