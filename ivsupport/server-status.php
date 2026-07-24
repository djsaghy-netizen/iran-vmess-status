<?php
declare(strict_types=1);

/*
 * پس از فعال‌شدن GitHub Pages، این آدرس را تغییر بده:
 * https://YOUR_USERNAME.github.io/YOUR_REPOSITORY/status.json
 */
const STATUS_JSON_URL = 'https://YOUR_USERNAME.github.io/YOUR_REPOSITORY/status.json';

if (isset($_GET['json'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, max-age=0');

    if (str_contains(STATUS_JSON_URL, 'YOUR_USERNAME')) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'message' => 'آدرس GitHub Pages هنوز داخل فایل تنظیم نشده است.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $context = stream_context_create([
        'http' => [
            'timeout' => 12,
            'ignore_errors' => true,
            'header' => "User-Agent: IranVmessStatus/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $body = @file_get_contents(STATUS_JSON_URL . '?t=' . time(), false, $context);

    if ($body === false || json_decode($body, true) === null) {
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'message' => 'نتیجه مانیتور دریافت نشد.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo $body;
    exit;
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>وضعیت سرورها | Iran Vmess</title>
  <style>
    body{margin:0;background:#07101f;color:#edf2ff;font-family:Tahoma,Arial,sans-serif}
    .wrap{width:min(800px,100%);margin:auto;padding:18px 12px 42px}
    .head,.row,.sum{background:#0f1d36;border:1px solid #273a60;border-radius:18px}
    .head{padding:20px;margin-bottom:12px}
    h1{font-size:24px;margin:0 0 9px}
    .note{font-size:13px;color:#9aacca;line-height:1.8}
    .summary{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:12px 0}
    .sum{text-align:center;padding:12px 5px}.sum b{display:block;font-size:20px}.sum span{font-size:11px;color:#93a4c4}
    .list{display:grid;gap:9px}
    .row{display:grid;grid-template-columns:1fr auto auto;gap:10px;align-items:center;padding:14px}
    .name{font-weight:bold;overflow-wrap:anywhere}.state{padding:6px 9px;border-radius:99px;font-size:12px;font-weight:bold}
    .on{background:#163b31;color:#61e7aa}.off{background:#441f2a;color:#ff8e9a}.ping{direction:ltr;font-weight:bold;min-width:70px}
    button{border:0;border-radius:12px;padding:10px 15px;background:#4774ff;color:#fff;font:inherit;font-weight:bold}
    .tools{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:14px}
    @media(max-width:520px){.summary{grid-template-columns:repeat(2,1fr)}.row{grid-template-columns:1fr auto}.ping{grid-column:1/-1}}
  </style>
</head>
<body>
<main class="wrap">
  <section class="head">
    <h1>📡 وضعیت سرورهای Iran Vmess</h1>
    <div class="note">وضعیت اتصال واقعی V2Ray سرورها به‌صورت دوره‌ای بررسی می‌شود.</div>
    <div class="summary">
      <div class="sum"><b id="total">—</b><span>کل</span></div>
      <div class="sum"><b id="online">—</b><span>آنلاین</span></div>
      <div class="sum"><b id="offline">—</b><span>آفلاین</span></div>
      <div class="sum"><b id="avg">—</b><span>میانگین پینگ</span></div>
    </div>
    <div class="tools"><small id="time">در حال دریافت…</small><button onclick="loadData()">بروزرسانی</button></div>
  </section>
  <section class="list" id="list"><div class="row">در حال بارگذاری…</div></section>
</main>
<script>
const f=n=>new Intl.NumberFormat("fa-IR").format(n);
const esc=s=>String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]));
async function loadData(){
  const box=document.getElementById("list");
  box.innerHTML='<div class="row">در حال دریافت آخرین نتیجه…</div>';
  try{
    const r=await fetch("?json=1&t="+Date.now(),{cache:"no-store"});
    const d=await r.json();
    if(!r.ok||!d.success) throw new Error(d.message||"خطا");
    total.textContent=f(d.total); online.textContent=f(d.online); offline.textContent=f(d.offline);
    avg.textContent=d.averageLatencyMs==null?"—":f(d.averageLatencyMs)+" ms";
    time.textContent="آخرین بروزرسانی: "+new Intl.DateTimeFormat("fa-IR-u-ca-persian",{dateStyle:"medium",timeStyle:"short"}).format(new Date(d.updatedAt));
    box.innerHTML=d.servers.map(s=>`<div class="row"><div class="name">${esc(s.name)}</div><div class="state ${s.online?"on":"off"}">${s.online?"آنلاین":"آفلاین"}</div><div class="ping">${s.online&&s.latencyMs!=null?f(s.latencyMs)+" ms":"—"}</div></div>`).join("");
  }catch(e){box.innerHTML='<div class="row">دریافت وضعیت ممکن نشد: '+esc(e.message)+'</div>'}
}
loadData(); setInterval(loadData,60000);
</script>
</body>
</html>
