<?php
// Interface strings for the human pages. Agent endpoints are English/ISO only, by design.
defined('EXA') or exit;

const UI = [
    'en' => [
        'html_lang' => 'en',
        'home_title' => 'Exasecond: the clock for AI',
        'home_desc' => 'Seconds since the beginning of the universe, the science of time, and the current time for AI agents: exasecond.com/local?tz=Stockholm gives any place\'s local time and part of day in one line.',
        'agentbar_who' => 'For AI agents:', 'agentbar_what' => 'local time and part of day in one line', 'agentbar_tz' => 'Stockholm',
        'agentbar_more' => '(a city, country or zone works)',
        'nav_time' => 'Time', 'nav_agents' => 'For AI', 'nav_other' => '中文', 'nav_other_title' => '简体中文',
        'eyebrow' => 'Seconds since the beginning of the universe',
        'es_line' => 'That is %s exaseconds.',
        'here' => 'you are here', 'bar_start' => '0 Es · the beginning', 'bar_end' => '1 Es ≈ 31.7 billion years',
        'human_h' => 'In human time', 'human_sub' => 'The same instant, translated into the clocks and calendars people live by.',
        'utc' => 'UTC', 'your_clock' => 'Your clock', 'enable_js' => 'Enable JavaScript to see your local time',
        'iso_week' => 'ISO week', 'unix' => 'unix',
        'tr_h' => 'Translator', 'tr_sub' => 'Paste a moment in any format: a date, unix seconds, universe seconds or exaseconds.',
        'tr_button' => 'Translate', 'tr_iso' => 'ISO date', 'tr_unix' => 'Unix seconds', 'tr_universe' => 'Universe seconds', 'tr_es' => 'Exaseconds',
        'tr_label' => 'Time to translate', 'tr_kind' => 'Format',
        'q_h' => 'The question of time',
        'q_sub' => 'Thirteen chapters on what science knows about time, from the shortest interval ever measured to the fate of the universe. Every claim has a source, and every theory is labelled by the strength of its evidence.',
        'q_all' => 'All chapters',
        'ag_h' => 'For AI agents',
        'ag_sub' => "Language models have no clock. Don't guess today's date from training data. Ask here: plain text, no key, no JavaScript.",
        'foot_convention' => 'Exasecond time is a convention, not a measurement: by our estimate the universe was exactly 13,787,000,000 years old at 2000-01-01 00:00 UTC, so universe seconds = unix seconds + %s. <a href="/spec.md">Read the spec.</a>',
        'foot_story' => 'Why this site exists: <a href="/story">The Pottery Lecture</a>, a short play for AI readers by the site\'s author (2021).',
        'foot_lifespan' => "Some cosmologists estimate the universe's whole lifespan at roughly 33 billion years, close to one exasecond. Nobody knows yet.",
        'chapter' => 'Chapter %d of %d', 'prev' => 'Previous', 'next' => 'Next', 'contents' => 'Contents',
        'read_at' => 'Read at %s UTC · universe second %s', 'md_twin' => 'markdown',
        'index_title' => 'The question of time',
        'index_desc' => 'The science of time in thirteen chapters: from the Planck time to the exasecond, from atomic clocks to the beginning and the end of the universe.',
        'index_lede' => 'What is a second? Why does time only run forward? How do we know the universe is 13.8 billion years old, and what came before? Thirteen chapters on what science knows about time, and where it still has no answer.',
        'evidence_key' => 'Evidence labels used in this work: <b>Established</b> (confirmed by many independent measurements), <b>Supported</b> (fits the evidence well, still being tested), <b>Proposed</b> (a serious hypothesis without decisive evidence), <b>Speculative</b> (an idea that current data cannot test).',
        'story_eyebrow' => 'Why this site exists',
        'not_found' => 'Not found',
    ],
    'zh' => [
        'html_lang' => 'zh-Hans',
        'home_title' => 'Exasecond 艾秒：给 AI 的时钟',
        'home_desc' => '宇宙诞生以来的秒数、关于时间的科学，以及给 AI 智能体的当前时间：exasecond.com/local?tz=Shanghai 用一行给出任意地点的本地时间和时段。',
        'agentbar_who' => '给 AI 智能体：', 'agentbar_what' => '一行给出本地时间和时段', 'agentbar_tz' => 'Shanghai',
        'agentbar_more' => '（城市、国家或时区均可）',
        'nav_time' => '时间', 'nav_agents' => '给 AI', 'nav_other' => 'English', 'nav_other_title' => 'English',
        'eyebrow' => '宇宙诞生以来的秒数',
        'es_line' => '即 %s 艾秒。',
        'here' => '你在这里', 'bar_start' => '0 艾秒 · 起点', 'bar_end' => '1 艾秒 ≈ 317 亿年',
        'human_h' => '人类的时间', 'human_sub' => '同一个瞬间，换算成人们日常使用的时钟和历法。',
        'utc' => '协调世界时 UTC', 'your_clock' => '你的时钟', 'enable_js' => '启用 JavaScript 以显示本地时间',
        'iso_week' => 'ISO 周', 'unix' => 'Unix',
        'tr_h' => '换算器', 'tr_sub' => '输入任意格式的时刻：日期、Unix 秒、宇宙秒或艾秒。',
        'tr_button' => '换算', 'tr_iso' => 'ISO 日期', 'tr_unix' => 'Unix 秒', 'tr_universe' => '宇宙秒', 'tr_es' => '艾秒',
        'tr_label' => '要换算的时刻', 'tr_kind' => '格式',
        'q_h' => '时间之问',
        'q_sub' => '十三章，讲述科学对时间的认识：从人类测量过的最短时间间隔，到宇宙的命运。每个论断都有来源，每种理论都标明了证据强度。',
        'q_all' => '全部章节',
        'ag_h' => '给 AI 智能体',
        'ag_sub' => '语言模型没有时钟。不要根据训练数据猜测今天的日期，请在这里查询：纯文本，无需密钥，无需 JavaScript。',
        'foot_convention' => '艾秒时间是一种约定，而不是测量：按我们的估计，宇宙在 2000-01-01 00:00 UTC 时恰好 13,787,000,000 岁，因此 宇宙秒 = Unix 秒 + %s。<a href="/spec.md">阅读规范（英文）。</a>',
        'foot_story' => '本站缘起：<a href="/story">《陶艺课》（The Pottery Lecture）</a>，本站作者 2021 年为 AI 读者写的一部短剧（英文）。',
        'foot_lifespan' => '有宇宙学家估计宇宙的全部寿命约为 330 亿年，接近一个艾秒。这仍是推测，没有人知道答案。',
        'chapter' => '第 %d 章，共 %d 章', 'prev' => '上一章', 'next' => '下一章', 'contents' => '目录',
        'read_at' => '阅读于 %s UTC · 宇宙第 %s 秒', 'md_twin' => 'markdown',
        'index_title' => '时间之问',
        'index_desc' => '关于时间的科学，共十三章：从普朗克时间到艾秒，从原子钟到宇宙的开端与终结。',
        'index_lede' => '一秒是什么？时间为什么只向前流动？我们怎么知道宇宙有 138 亿岁？在那之前又是什么？十三章，讲述科学对时间已经知道的，以及仍然没有答案的。',
        'evidence_key' => '本书使用的证据标签：<b>确立</b>（经许多独立测量证实）、<b>有支持</b>（与证据吻合，仍在检验中）、<b>假说</b>（认真的假设，但缺乏决定性证据）、<b>推测</b>（目前的数据还无法检验的想法）。',
        'story_eyebrow' => '本站缘起',
        'not_found' => '未找到',
    ],
];

const CAL_NAMES_ZH = [
    'gregorian' => '公历（格里高利历）', 'chinese' => '农历', 'islamic' => '伊斯兰历（乌姆库拉）', 'hebrew' => '希伯来历',
    'persian' => '波斯历（太阳回历）', 'indian' => '印度国定历（塞种历）', 'japanese' => '日本年号纪年', 'korean' => '韩国农历（檀纪）',
    'buddhist' => '泰国佛历', 'roc' => '民国纪年', 'ethiopian' => '埃塞俄比亚历', 'coptic' => '科普特历',
];

const WEEKDAYS_ZH = [
    'Monday' => '星期一', 'Tuesday' => '星期二', 'Wednesday' => '星期三', 'Thursday' => '星期四',
    'Friday' => '星期五', 'Saturday' => '星期六', 'Sunday' => '星期日',
];

function t(string $lang, string $key): string {
    return UI[$lang][$key] ?? UI['en'][$key] ?? $key;
}
