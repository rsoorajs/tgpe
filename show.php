<?php
if (!isset($code) || !isset($data))
	exit('Invalid call');

$url = $data['url'];
$shortWith = "https://tg.pe/$code";
$shortWithout = "tg.pe/$code";

$lang = $_COOKIE['lang'] ?? '';
if ($lang !== 'zh' && $lang !== 'en') {
	$al = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
	$lang = stripos($al, 'zh') !== false ? 'zh' : 'en';
}

$h = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html lang="<?= $lang === 'zh' ? 'zh-TW' : 'en' ?>" class="lang-<?= $lang ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex,nosnippet">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $lang === 'zh' ? '分享連結' : 'Share link' ?>: tg.pe/<?= $h($code) ?></title>
<link rel="icon" type="image/png" href="/logo-192.png" sizes="192x192">
<link rel="icon" type="image/png" href="/logo-128.png" sizes="128x128">
<link rel="icon" type="image/png" href="/logo-64.png" sizes="64x64">
<style>
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; height: 100%; }
body {
	font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
	background: #EBF5FB;
	color: #222;
	display: flex;
	min-height: 100vh;
	line-height: 1.4;
}

.lang-en .tr-zh, .lang-zh .tr-en { display: none; }

#sidebar {
	width: 280px;
	flex-shrink: 0;
	background: #fff;
	border-right: 1px solid #dbe4ec;
	padding: 16px;
	display: flex;
	flex-direction: column;
	gap: 16px;
}

.logo img { height: 56px; display: block; }

.btn-group { display: flex; flex-direction: column; gap: 6px; }

.btn {
	font: inherit;
	font-size: 15px;
	padding: 10px 12px;
	border: 1px solid #0088CC;
	background: #fff;
	color: #0088CC;
	border-radius: 8px;
	cursor: pointer;
	text-align: center;
	transition: background-color .2s, color .2s;
}
.btn:hover { background: #e6f4fb; }
.btn.active { background: #0088CC; color: #fff; }

.toggle-row {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 14px;
	user-select: none;
	cursor: pointer;
}
.toggle-row input { cursor: pointer; }

.label {
	font-size: 12px;
	text-transform: uppercase;
	letter-spacing: .5px;
	color: #888;
	margin-bottom: 4px;
}

.link-row { display: flex; gap: 6px; }
.link-row input {
	flex: 1;
	min-width: 0;
	font: inherit;
	font-size: 14px;
	padding: 8px 10px;
	border: 1px solid #cfd7df;
	border-radius: 6px;
	background: #f7fafc;
}
.link-row button {
	font: inherit;
	font-size: 14px;
	padding: 8px 12px;
	border: 1px solid #0088CC;
	background: #0088CC;
	color: #fff;
	border-radius: 6px;
	cursor: pointer;
	white-space: nowrap;
}
.link-row button:hover { background: #007ab8; }

.original {
	font-size: 13px;
	color: #337ab7;
	word-break: break-all;
	display: block;
	max-height: 4.5em;
	overflow-y: auto;
}

.spacer { flex: 1; }

.lang-btn {
	align-self: flex-start;
	background: none;
	border: none;
	cursor: pointer;
	font: inherit;
	font-size: 14px;
	color: #555;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 6px 8px;
	border-radius: 6px;
}
.lang-btn:hover { background: #f0f4f8; }
.lang-btn svg { width: 18px; height: 18px; }

main {
	flex: 1;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 24px;
	overflow: hidden;
}

.view { display: none; width: 100%; height: 100%; flex-direction: column; align-items: center; justify-content: center; gap: 24px; }
.view.active { display: flex; }

/* Force QR lib canvas/img to fit their container (lib outputs fixed px size) */
.qr-big canvas, .qr-big img,
.qr-small canvas, .qr-small img {
	width: 100%;
	height: 100%;
	max-width: 100%;
	max-height: 100%;
}

/* QR mode: big QR + medium caption */
#qr-view .qr-big {
	width: min(80vh, 80vw, 560px);
	height: min(80vh, 80vw, 560px);
	background: #fff;
	padding: 16px;
	border-radius: 12px;
	box-shadow: 0 2px 12px rgba(0,0,0,.08);
	flex-shrink: 0;
}
#qr-view .qr-big > div { width: 100%; height: 100%; }
#qr-view .caption {
	font-family: 'Courier New', monospace;
	font-size: clamp(20px, 4vh, 40px);
	color: #222;
	letter-spacing: 1px;
	flex-shrink: 0;
}

/* Big text mode: giant text (flex-grow) + medium QR */
#big-view { gap: 16px; }
#big-view .big-text {
	flex: 1;
	width: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	overflow: hidden;
	min-height: 0;
}
#big-view .big-text span {
	font-family: 'Courier New', monospace;
	font-weight: bold;
	color: #0f1419;
	letter-spacing: 2px;
	line-height: 1;
	white-space: nowrap;
}
#big-view .qr-small {
	width: min(28vh, 28vw, 240px);
	height: min(28vh, 28vw, 240px);
	background: #fff;
	padding: 8px;
	border-radius: 8px;
	box-shadow: 0 2px 8px rgba(0,0,0,.08);
	flex-shrink: 0;
}
#big-view .qr-small > div { width: 100%; height: 100%; }

/* Fullscreen: hide sidebar */
body:fullscreen #sidebar,
body:-webkit-full-screen #sidebar { display: none; }

/* Mobile: sidebar -> top bar */
@media (max-width: 720px) {
	body { flex-direction: column; }
	#sidebar {
		width: 100%;
		border-right: none;
		border-bottom: 1px solid #dbe4ec;
		flex-direction: row;
		flex-wrap: wrap;
		align-items: center;
		gap: 10px;
		padding: 10px;
	}
	.logo img { height: 36px; }
	.btn-group { flex-direction: row; }
	.btn { padding: 6px 10px; font-size: 13px; }
	.toggle-row { font-size: 13px; }
	.label { display: none; }
	.link-row { flex: 1; min-width: 200px; }
	.original { flex-basis: 100%; max-height: 3em; font-size: 12px; }
	.spacer { display: none; }
	main { padding: 16px; }
	#qr-view .qr-big { width: min(90vw, 70vh); height: min(90vw, 70vh); }
	#big-view .qr-small { width: min(30vw, 20vh); height: min(30vw, 20vh); }
}
</style>
</head>
<body>
<aside id="sidebar">
	<a href="/" class="logo"><img src="/logo_boderless.png" alt="tg.pe"></a>

	<div class="btn-group">
		<button class="btn mode-btn active" data-mode="qr"><span class="tr-en">QR code</span><span class="tr-zh">QR 模式</span></button>
		<button class="btn mode-btn" data-mode="big"><span class="tr-en">Big text</span><span class="tr-zh">大字報</span></button>
		<button class="btn" id="fullscreen-btn"><span class="tr-en">Fullscreen</span><span class="tr-zh">全螢幕</span></button>
	</div>

	<label class="toggle-row">
		<input type="checkbox" id="scheme-toggle" checked>
		<span class="tr-en">Show https://</span><span class="tr-zh">顯示 https://</span>
	</label>

	<div>
		<div class="label"><span class="tr-en">Short link</span><span class="tr-zh">短網址</span></div>
		<div class="link-row">
			<input id="short-link" value="<?= $h($shortWith) ?>" readonly>
			<button id="copy-btn" data-state="idle"><span class="tr-en">Copy</span><span class="tr-zh">複製</span></button>
		</div>
	</div>

	<div>
		<div class="label"><span class="tr-en">Original URL</span><span class="tr-zh">原始網址</span></div>
		<a class="original" href="<?= $h($url) ?>" target="_blank" rel="noopener noreferrer"><?= $h($url) ?></a>
	</div>

	<div class="spacer"></div>

	<button class="lang-btn" id="lang-btn" title="Language / 語言">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="12" cy="12" r="10"></circle>
			<line x1="2" y1="12" x2="22" y2="12"></line>
			<path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
		</svg>
		<span class="tr-en">中文</span><span class="tr-zh">English</span>
	</button>
</aside>

<main>
	<section id="qr-view" class="view active">
		<div class="qr-big"><div id="qr-big-inner"></div></div>
		<div class="caption" id="qr-caption"></div>
	</section>

	<section id="big-view" class="view">
		<div class="big-text"><span id="big-text"></span></div>
		<div class="qr-small"><div id="qr-small-inner"></div></div>
	</section>
</main>

<script src="/qrcode.min.js"></script>
<script>
(function () {
	var SHORT_WITH = <?= json_encode($shortWith) ?>;
	var SHORT_WITHOUT = <?= json_encode($shortWithout) ?>;
	var CODE = <?= json_encode($code) ?>;

	var TR = {
		en: { copy: 'Copy', copied: 'Copied!', title: 'Share link' },
		zh: { copy: '複製', copied: '已複製！', title: '分享連結' }
	};

	var mode = 'qr';
	var showScheme = true;

	new QRCode(document.getElementById('qr-big-inner'), {
		text: SHORT_WITH, width: 1024, height: 1024, correctLevel: QRCode.CorrectLevel.M
	});
	new QRCode(document.getElementById('qr-small-inner'), {
		text: SHORT_WITH, width: 512, height: 512, correctLevel: QRCode.CorrectLevel.M
	});

	var qrCaption = document.getElementById('qr-caption');
	var bigText = document.getElementById('big-text');
	var schemeToggle = document.getElementById('scheme-toggle');
	var bigView = document.getElementById('big-view');
	var copyBtn = document.getElementById('copy-btn');
	var shortInput = document.getElementById('short-link');
	var htmlEl = document.documentElement;

	function currentLang() {
		return htmlEl.classList.contains('lang-zh') ? 'zh' : 'en';
	}

	function currentText() {
		return showScheme ? SHORT_WITH : SHORT_WITHOUT;
	}

	function fitBigText() {
		if (!bigView.classList.contains('active')) return;
		var box = bigText.parentElement;
		var w = box.clientWidth;
		var h = box.clientHeight;
		if (w <= 0 || h <= 0) return;
		var lo = 16, hi = 1400;
		for (var i = 0; i < 20; i++) {
			var mid = (lo + hi) / 2;
			bigText.style.fontSize = mid + 'px';
			if (bigText.scrollWidth <= w && bigText.scrollHeight <= h) lo = mid;
			else hi = mid;
		}
		bigText.style.fontSize = lo + 'px';
	}

	function renderText() {
		var txt = currentText();
		qrCaption.textContent = txt;
		bigText.textContent = txt;
		fitBigText();
	}

	function setMode(next) {
		mode = next;
		document.querySelectorAll('.mode-btn').forEach(function (b) {
			b.classList.toggle('active', b.dataset.mode === next);
		});
		document.getElementById('qr-view').classList.toggle('active', next === 'qr');
		document.getElementById('big-view').classList.toggle('active', next === 'big');
		showScheme = (next === 'qr');
		schemeToggle.checked = showScheme;
		renderText();
	}

	document.querySelectorAll('.mode-btn').forEach(function (b) {
		b.addEventListener('click', function () { setMode(b.dataset.mode); });
	});

	schemeToggle.addEventListener('change', function () {
		showScheme = schemeToggle.checked;
		renderText();
	});

	document.getElementById('fullscreen-btn').addEventListener('click', function () {
		if (document.fullscreenElement) document.exitFullscreen();
		else document.body.requestFullscreen();
	});

	var copyLabel = copyBtn.innerHTML;
	var copyResetTimer = null;
	copyBtn.addEventListener('click', function () {
		var done = function () {
			copyBtn.dataset.state = 'copied';
			copyBtn.textContent = TR[currentLang()].copied;
			if (copyResetTimer) clearTimeout(copyResetTimer);
			copyResetTimer = setTimeout(function () {
				copyBtn.dataset.state = 'idle';
				copyBtn.innerHTML = copyLabel;
			}, 2000);
		};
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(shortInput.value).then(done, function () {
				shortInput.select(); document.execCommand('copy'); done();
			});
		} else {
			shortInput.select(); document.execCommand('copy'); done();
		}
	});

	document.getElementById('lang-btn').addEventListener('click', function () {
		var next = currentLang() === 'zh' ? 'en' : 'zh';
		htmlEl.classList.remove('lang-zh', 'lang-en');
		htmlEl.classList.add('lang-' + next);
		htmlEl.lang = next === 'zh' ? 'zh-TW' : 'en';
		document.title = TR[next].title + ': tg.pe/' + CODE;
		if (copyBtn.dataset.state === 'copied')
			copyBtn.textContent = TR[next].copied;
		document.cookie = 'lang=' + next + '; path=/; max-age=31536000; samesite=lax';
	});

	window.addEventListener('resize', fitBigText);
	renderText();
})();
</script>
</body>
</html>
