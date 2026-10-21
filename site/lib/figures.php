<?php
// Server-drawn figures for the chapters (no JavaScript, readable without CSS too).
// A "log ruler": one row per event, a dot placed on a logarithmic time axis.
defined('EXA') or exit;

const FIGURES = [
    'scale' => [
        'min' => -44, 'max' => 18,
        'caption' => [
            'en' => 'Durations on a logarithmic scale. Each step along the axis is ten times longer than the one before. From the Planck time to one exasecond is 62 such steps.',
            'zh' => '对数刻度上的时长。轴上每前进一格，时长增加十倍。从普朗克时间到一艾秒，共 62 格。',
        ],
        'rows' => [
            [5.39e-44, 'Planck time', '普朗克时间', '5.4 × 10^-44 s'],
            [2.47e-19, 'Shortest interval ever measured (2020)', '测量过的最短时间间隔（2020 年）', '247 zs'],
            [1e-14, 'A chemical bond vibrates once', '化学键振动一次', '~10 fs'],
            [1.088e-10, 'One cycle of the caesium-133 clock transition', '铯 133 钟跃迁的一个周期', '0.11 ns'],
            [0.86, 'One heartbeat', '一次心跳', '~1 s'],
            [86400, 'One day', '一天', '86,400 s'],
            [2.52e9, 'A human life of 80 years', '80 年的人生', '2.5 Gs'],
            [1.64e11, 'Since writing began, about 5,200 years', '文字出现至今，约 5200 年', '0.16 Ts'],
            [9.5e12, 'Since Homo sapiens appeared, about 300,000 years', '智人出现至今，约 30 万年', '9.5 Ts'],
            [2.08e15, 'Since the dinosaurs died out, 66 million years', '非鸟恐龙灭绝至今，6600 万年', '2.1 Ps'],
            [1.433e17, 'Age of the Earth, 4.54 billion years', '地球的年龄，45.4 亿年', '0.14 Es'],
            [4.351e17, 'Age of the universe, 13.8 billion years', '宇宙的年龄，138 亿年', '0.435 Es'],
            [1e18, 'One exasecond, 31.7 billion years', '一艾秒，317 亿年', '1 Es'],
        ],
    ],
    'history' => [
        'min' => -43, 'max' => 18,
        'caption' => [
            'en' => 'Time since the beginning, on a logarithmic scale. Almost everything that shaped the universe happened in its first second; everything that shaped us happened in the last few percent of the axis.',
            'zh' => '自开端以来的时间，对数刻度。塑造宇宙的几乎一切都发生在第一秒之内；塑造我们的一切，都挤在轴的最后几个百分点里。',
        ],
        'rows' => [
            [1e-43, 'End of the Planck epoch: known physics begins to apply', '普朗克时期结束：已知物理开始适用', '10^-43 s'],
            [1e-34, 'Inflation (if it happened; timing depends on the model)', '暴胀（如果确实发生过；时间取决于模型）', '~10^-36 to 10^-32 s'],
            [1e-12, 'Electroweak transition: particles gain mass', '电弱相变：粒子获得质量', '~10^-12 s'],
            [1e-5, 'Quarks bind into protons and neutrons', '夸克结合成质子和中子', '~10 µs'],
            [1, 'Neutrinos stop interacting', '中微子退耦', '~1 s'],
            [200, 'First atomic nuclei: hydrogen, helium, a little lithium', '最早的原子核：氢、氦和少量锂', '~3 to 20 min'],
            [1.2e13, 'Atoms form; the cosmic microwave background is released', '原子形成，宇宙微波背景辐射释放', '380,000 yr'],
            [3.2e15, 'First stars (predicted, not yet observed directly)', '第一代恒星（理论预测，尚未直接观测到）', '~100 Myr'],
            [8.8e15, 'MoM-z14, the most distant galaxy confirmed so far', 'MoM-z14，目前已确认的最遥远星系', '~280 Myr'],
            [3.2e16, 'Reionization ends; intergalactic space turns transparent to ultraviolet light', '再电离结束，星系际空间对紫外光变得透明', '~1 Gyr'],
            [2.91e17, 'The Sun and its planets form', '太阳和行星形成', '9.2 Gyr'],
            [4.351e17, 'Today', '今天', '13.8 Gyr'],
        ],
    ],
];

function figure_html(string $name, string $lang): string {
    $f = FIGURES[$name] ?? null;
    if (!$f) return '';
    $span = $f['max'] - $f['min'];
    $rows = '';
    foreach ($f['rows'] as [$v, $en, $zh, $val]) {
        $pct = max(0, min(100, (log10($v) - $f['min']) / $span * 100));
        $label = $lang === 'zh' ? $zh : $en;
        $rows .= '<li><span class="lr-label">' . md_inline($label) . '</span><span class="lr-val">' . md_inline($val) . '</span>'
            . '<span class="lr-track" aria-hidden="true"><i style="left:' . number_format($pct, 2, '.', '') . '%"></i></span></li>';
    }
    $ticks = '';
    $k = 0;
    for ($e = $f['min'] + (6 - ($f['min'] % 6 + 6) % 6) % 6; $e <= $f['max']; $e += 6) {
        $pct = ($e - $f['min']) / $span * 100;
        $ticks .= '<span' . ($k++ % 2 ? ' class="odd"' : '') . ' style="left:' . number_format($pct, 2, '.', '') . '%">10<sup>' . $e . '</sup></span>';
    }
    return '<figure class="ruler"><ol class="lr">' . $rows . '</ol><div class="lr-axis" aria-hidden="true">' . $ticks . '</div>'
        . '<figcaption>' . h($f['caption'][$lang] ?? $f['caption']['en']) . '</figcaption></figure>';
}
