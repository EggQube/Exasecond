"""Count exported samples with tiktoken, independently of the PHP runtime.

Usage: python tools/tokenize_samples.py --php-exe path/to/php.exe
       --php-ini path/to/php.ini --tokenizer-dir path/to/tiktoken-install
Requires tiktoken. Reports are local artifacts under docs/ by default.
"""
import argparse
import json
import os
from pathlib import Path
import subprocess
import sys

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('--php-exe', default='php')
parser.add_argument('--php-ini')
parser.add_argument('--tokenizer-dir')
parser.add_argument('--output-dir', default='docs')
args = parser.parse_args()
if args.tokenizer_dir:
    sys.path.insert(0, str(Path(args.tokenizer_dir).resolve()))
import tiktoken

root = Path(__file__).resolve().parent.parent
out = Path(args.output_dir).resolve()
out.mkdir(parents=True, exist_ok=True)
os.environ.setdefault('TIKTOKEN_CACHE_DIR', str(out / '.tokenizer-cache'))
command = [args.php_exe]
if args.php_ini:
    command += ['-c', args.php_ini]
command += [str(root / 'tools' / 'measure_tokens.php'), '--json']
samples = json.loads(subprocess.check_output(command, cwd=root).decode('utf-8-sig'))
encodings = {name: tiktoken.get_encoding(name) for name in ['o200k_base', 'cl100k_base']}
rows = []
for sample in samples['rows']:
    rows.append({key: sample[key] for key in ['name', 'bytes', 'characters']} | {
        name: len(encoding.encode(sample['text'], disallowed_special=()))
        for name, encoding in encodings.items()
    })
report = {
    'captured_at_utc': samples['captured_at_utc'],
    'tiktoken_version': tiktoken.__version__,
    'method': 'Exact tokenization of the exported strings under the named encodings. Not a billing total. HTTP headers, fetch-tool wrappers, provider tool formatting and conversation framing are excluded. MCP result-text and wire-response rows are alternatives, not additive. Fixed language samples use historical instants; http and mcp rows are current local outputs.',
    'rows': rows,
}
(out / 'token-samples.json').write_text(json.dumps(samples, ensure_ascii=False, indent=2), encoding='utf-8')
(out / 'token-report.json').write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding='utf-8')
lines = ['# Exasecond token measurements', '',
         f"Captured: {report['captured_at_utc']}. tiktoken {tiktoken.__version__}.", '',
         report['method'], '',
         'A local preview works without internet at http://127.0.0.1:8791/ while the PHP server is running.', '',
         '| Sample | Bytes | o200k_base | cl100k_base |',
         '|---|---:|---:|---:|']
lines += [f"| {r['name']} | {r['bytes']} | {r['o200k_base']} | {r['cl100k_base']} |" for r in rows]
(out / 'TOKEN_REPORT.md').write_text('\n'.join(lines) + '\n', encoding='utf-8')
print(json.dumps(report, ensure_ascii=False, indent=2))
