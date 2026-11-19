<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ini_set('session.cookie_secure', '1');
session_start();
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
$hash = '$2b$12$tM86Nu/Tk/h8FkMmtEzPJ.r9dq/LeYcuExQE3qNIfWF9bo7ddrvye';
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: ./');
    exit;
}
$error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attempts = (int) ($_SESSION['attempts'] ?? 0);
    $until = (int) ($_SESSION['locked_until'] ?? 0);
    if (time() < $until) {
        $error = true;
    } elseif (password_verify((string) ($_POST['password'] ?? ''), $hash)) {
        session_regenerate_id(true);
        $_SESSION['calculator_authenticated'] = true;
        $_SESSION['attempts'] = 0;
        header('Location: ./');
        exit;
    } else {
        $_SESSION['attempts'] = $attempts + 1;
        if ($_SESSION['attempts'] >= 5) {
            $_SESSION['locked_until'] = time() + 300;
            $_SESSION['attempts'] = 0;
        }
        $error = true;
    }
}
if (empty($_SESSION['calculator_authenticated'])) {
?><!doctype html>
<html lang="pl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Dostęp wewnętrzny | Outstanding Studios</title><style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f3f6fa;color:#18243a;font-family:system-ui,-apple-system,sans-serif;padding:20px}.card{background:#fff;border:1px solid #e6ebf2;border-radius:20px;box-shadow:0 12px 45px #23436a12;padding:36px;width:min(100%,400px)}.brand{font-weight:800;color:#284a78;letter-spacing:.14em;font-size:13px;margin-bottom:32px}h1{font-size:28px;letter-spacing:-.04em;margin:0 0 8px}p{color:#68758a;line-height:1.5;margin-bottom:26px}label{font-weight:650;font-size:14px;display:block;margin-bottom:8px}input{font-size:18px;border:1px solid #cdd8e6;border-radius:10px;padding:14px;width:100%;margin-bottom:16px}button{background:#244a7c;color:#fff;width:100%;border:0;border-radius:10px;font-weight:700;font-size:16px;padding:14px;cursor:pointer}.error{color:#ba3535;font-size:14px;margin-top:16px}
</style></head><body><form class="card" method="post"><div class="brand">OUTSTANDING STUDIOS</div><h1>Kalkulator rentowności</h1><p>To narzędzie jest przeznaczone do użytku wewnętrznego. Podaj hasło, aby przejść dalej.</p><label for="password">Hasło dostępu</label><input type="password" id="password" name="password" required autofocus autocomplete="current-password"><button type="submit">Otwórz kalkulator</button><?php if ($error): ?><div class="error" role="alert">Nieprawidłowe hasło lub chwilowa blokada po kilku próbach.</div><?php endif; ?></form></body></html>
<?php exit; }
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex,nofollow,noarchive">
  <title>Kalkulator rentowności | Outstanding Studios</title>
  <style>
    :root{font-family:Inter,Outfit,system-ui,-apple-system,sans-serif;color:#18243a;background:#f3f6fa;font-synthesis:none}
    *{box-sizing:border-box}body{margin:0}main{max-width:1100px;margin:auto;padding:35px 20px 60px}
    header{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:35px} .brand{font-size:14px;font-weight:800;letter-spacing:.16em;color:#284a78}.tag{font-size:12px;color:#6b7890}
    h1{font-size:clamp(29px,4vw,43px);letter-spacing:-.04em;margin:0 0 8px}p.lead{color:#68758a;margin:0 0 26px;line-height:1.6}
    .tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px}.tab{padding:13px 20px;border:1px solid #dbe3ee;background:#fff;border-radius:12px;color:#52617a;font-weight:700;cursor:pointer}.tab.active{background:#244a7c;color:white;border-color:#244a7c}
    .layout{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(290px,.8fr);gap:22px}.panel{background:#fff;border-radius:19px;box-shadow:0 8px 34px #23436a0c;border:1px solid #e6ebf2;padding:25px}
    h2{font-size:19px;margin:0 0 20px;letter-spacing:-.02em}.grid{display:grid;grid-template-columns:1fr 1fr;gap:17px}.field{display:block}.field span{font-size:13px;font-weight:650;color:#5f6b7f;display:block;margin-bottom:7px}.field input{width:100%;padding:13px 12px;border:1px solid #d7e0ec;border-radius:10px;font:600 18px inherit;font-size:18px;color:#172740;background:#fbfdff;outline-color:#345f94}.divider{height:1px;background:#e9edf2;margin:23px 0}.subtle{color:#8290a4;font-size:12px;margin-top:15px;line-height:1.5}
    .metric{margin-bottom:23px}.metric small{display:block;font-size:13px;color:#6d7b8f;margin-bottom:5px}.metric strong{font-size:30px;letter-spacing:-.035em;font-variant-numeric:tabular-nums}.metric.accent strong{color:#136c59}.metric.negative strong{color:#c44949}.bar{height:11px;background:#e7edf3;border-radius:20px;overflow:hidden;margin-top:13px}.fill{height:100%;background:#168d75;transition:width .2s}.summary{padding:15px 0;border-top:1px solid #e9edf2;display:flex;justify-content:space-between;font-size:14px;gap:10px}.summary b{font-variant-numeric:tabular-nums}.actions{display:flex;gap:9px;flex-wrap:wrap;margin-top:20px}.action{border-radius:10px;border:1px solid #d5e0eb;background:#fff;padding:11px 13px;color:#244a7c;font-weight:700;cursor:pointer}.action.primary{background:#244a7c;color:#fff;border-color:#244a7c}.note{margin-top:20px;font-size:12px;color:#748196;line-height:1.65}
    @media(max-width:780px){.layout{grid-template-columns:1fr}.panel{padding:20px}.grid{gap:12px}header{margin-bottom:25px}}@media(max-width:370px){.grid{grid-template-columns:1fr}}
    @media print{body{background:#fff}.tabs,.actions,.tag{display:none}main{padding:0}.layout{grid-template-columns:1fr 1fr}.panel{box-shadow:none}}
  </style>
</head>
<body><main>
  <header><div class="brand">OUTSTANDING STUDIOS</div><div class="tag">Narzędzie wewnętrzne · Kalkulacja netto</div><a href="?logout=1" style="font-size:12px;color:#64758c;text-decoration:none">Wyloguj</a></header>
  <h1>Kalkulator rentowności</h1><p class="lead">Ekspertka w Kadrze · Zmieniaj koszty, cenę sprzedaży i sprawdzaj wynik każdej realizacji.</p>
  <div class="tabs" role="tablist" aria-label="Wybierz pakiet"><button class="tab active" data-package="premium">PREMIUM · kanapowy</button><button class="tab" data-package="setka">100% · setkowy</button><button class="tab" data-package="zdalny">ZDALNY · w oknach</button></div>
  <div class="layout"><section class="panel"><h2>Wycena produkcji</h2><div class="grid"><label class="field"><span>Cena sprzedaży netto (zł)</span><input type="number" id="price" min="0" step="50" inputmode="decimal"></label><label class="field"><span>Studio (zł)</span><input type="number" id="studio" min="0" step="50" inputmode="decimal"></label><label class="field"><span>Operator / sprzęt (zł)</span><input type="number" id="operator" min="0" step="50" inputmode="decimal"></label><label class="field"><span>Fotosy (zł)</span><input type="number" id="fotosy" min="0" step="50" inputmode="decimal"></label><label class="field"><span>Montaż wywiadu i rolek (zł)</span><input type="number" id="editing" min="0" step="50" inputmode="decimal"></label><label class="field"><span>Anka · prowadzenie (zł)</span><input type="number" id="anka" min="0" step="50" inputmode="decimal"></label><label class="field"><span>Dojazd / logistyka (zł)</span><input type="number" id="travel" min="0" step="50" inputmode="decimal"></label><label class="field"><span>Pozostałe koszty (zł)</span><input type="number" id="other" min="0" step="50" inputmode="decimal"></label></div>
  <div class="divider"></div><h2>Dodatki sprzedane klientce</h2><div class="grid"><label class="field"><span>Liczba dodatkowych rolek</span><input type="number" id="reels" min="0" step="1"></label><label class="field"><span>Cena dodatkowej rolki (zł)</span><input type="number" id="reelPrice" min="0" step="50"></label><label class="field"><span>Koszt montażu dodatkowej rolki (zł)</span><input type="number" id="reelCost" min="0" step="50"></label><label class="field"><span>Inne przychody netto (zł)</span><input type="number" id="extra" min="0" step="50"></label></div><div class="subtle">Kwoty są edytowalne. Zmiany zapisują się automatycznie w tej przeglądarce, osobno dla każdego pakietu.</div></section>
  <aside class="panel"><h2>Wynik realizacji</h2><div class="metric"><small>Łączny przychód netto</small><strong id="revenue">0 zł</strong></div><div class="metric"><small>Łączny koszt</small><strong id="cost">0 zł</strong></div><div class="metric accent" id="profitWrap"><small>Zysk na realizacji</small><strong id="profit">0 zł</strong></div><div class="metric accent" id="marginWrap"><small>Marża na realizacji</small><strong id="margin">0%</strong><div class="bar"><div class="fill" id="fill"></div></div></div><div class="summary"><span>Cena bazowa netto</span><b id="base">0 zł</b></div><div class="summary"><span>Przychód z dodatków</span><b id="extraRevenue">0 zł</b></div><div class="summary"><span>Koszty dodatkowych rolek</span><b id="extraCost">0 zł</b></div><div class="actions"><button class="action primary" id="print">Drukuj / zapisz PDF</button><button class="action" id="reset">Przywróć wartości</button></div><div class="note">Zysk liczony przed kosztami stałymi spółki i podatkami. Marża = zysk / przychód × 100%. Dane zapisują się lokalnie na urządzeniu i nie są wysyłane na serwer.</div></aside></div>
</main><script>
  const initial={premium:{price:6900,studio:0,operator:1500,fotosy:300,editing:1400,anka:800,travel:0,other:300,reels:0,reelPrice:300,reelCost:120,extra:0},setka:{price:5490,studio:600,operator:1100,fotosy:0,editing:1200,anka:800,travel:0,other:300,reels:0,reelPrice:300,reelCost:120,extra:0},zdalny:{price:2990,studio:0,operator:0,fotosy:0,editing:900,anka:700,travel:0,other:150,reels:0,reelPrice:300,reelCost:120,extra:0}};
  const keys=Object.keys(initial.premium);const money=x=>new Intl.NumberFormat('pl-PL',{style:'currency',currency:'PLN',maximumFractionDigits:0}).format(x);const num=x=>Math.max(0,Number(x)||0);
  let saved={};try{saved=JSON.parse(localStorage.getItem('ostudios-calculator-v1')||'{}')}catch{};const state=Object.fromEntries(Object.keys(initial).map(k=>[k,{...initial[k],...(saved[k]||{})}]));let current='premium';
  const byId=id=>document.getElementById(id);function update(){let x=state[current];let extras=x.reels*x.reelPrice+x.extra;let revenue=x.price+extras;let reelCost=x.reels*x.reelCost;let cost=x.studio+x.operator+x.fotosy+x.editing+x.anka+x.travel+x.other+reelCost;let profit=revenue-cost;let margin=revenue?profit/revenue*100:0;byId('revenue').textContent=money(revenue);byId('cost').textContent=money(cost);byId('profit').textContent=money(profit);byId('margin').textContent=margin.toFixed(1).replace('.',',')+'%';byId('base').textContent=money(x.price);byId('extraRevenue').textContent=money(extras);byId('extraCost').textContent=money(reelCost);byId('fill').style.width=Math.max(0,Math.min(100,margin))+'%';byId('profitWrap').className='metric '+(profit<0?'negative':'accent');byId('marginWrap').className='metric '+(margin<0?'negative':'accent');byId('fill').style.background=margin<0?'#c44949':'#168d75'}
  function render(){document.querySelectorAll('.tab').forEach(b=>{b.classList.toggle('active',b.dataset.package===current);b.setAttribute('aria-selected',String(b.dataset.package===current))});keys.forEach(k=>byId(k).value=state[current][k]);update()}
  document.querySelectorAll('.tab').forEach(b=>b.addEventListener('click',()=>{current=b.dataset.package;render()}));keys.forEach(k=>byId(k).addEventListener('input',e=>{state[current][k]=num(e.target.value);localStorage.setItem('ostudios-calculator-v1',JSON.stringify(state));update()}));byId('reset').addEventListener('click',()=>{if(confirm('Przywrócić domyślne wartości dla wybranego pakietu?')){state[current]={...initial[current]};localStorage.setItem('ostudios-calculator-v1',JSON.stringify(state));render()}});byId('print').addEventListener('click',()=>window.print());render();
</script></body></html>
