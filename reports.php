<?php /* Reports — Γραφήματα (χωρίς sidebar) */ ?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Αναφορές</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    /* ===== Header / Layout χωρίς sidebar ===== */
    body, html { height:100%; }
    .topbar{
      position: sticky; top:0; z-index:10;
      display:flex; align-items:center; justify-content:space-between; gap:12px;
      padding:14px 18px;
      background: var(--bg);
      border-bottom: 1px solid var(--border);
    }
    .brand-line{ display:flex; align-items:center; gap:10px }
    .brand-line .logo{ width:36px; height:36px; border-radius:10px; }
    .brand-line h1{ margin:0; font-size:18px; font-weight:800; letter-spacing:.2px; }
    .status{ display:flex; align-items:center; gap:8px; color:var(--muted); }
    .dot{ width:10px; height:10px; border-radius:50%; background:#777; box-shadow:0 0 0 2px rgba(0,0,0,.15) inset }
    .dot.ok { background:#36d399; }
    .dot.err{ background:#ef4444; }

    .page-wrap{ max-width:1100px; margin:0 auto; padding:18px; }
    .page-head{ display:flex; align-items:center; gap:10px; margin:0 0 10px 0; }
    .page-head h2{ margin:0; font-weight:900; }
    .hint{ color:var(--muted); }

    /* Κάρτα & καμβάς */
    .card h2{ margin-top:4px; }
    .chart-box{
      background: rgba(255,255,255,.02);
      border:1px solid var(--border);
      border-radius:16px;
      padding:10px;
    }
    .table-wrap{ border-radius:14px; }

    .toolbar{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    @media (max-width:640px){
      .page-head{ flex-direction:column; align-items:flex-start; }
    }

    /* ===== ΜΟΝΟ τα απαραίτητα για fade-in ===== */
    @media (prefers-reduced-motion: no-preference){
      .card._hidden{ opacity:0; transform: translateY(10px); }
      .card._show{ opacity:1; transform:none; transition: opacity .5s ease, transform .5s ease; }
    }
  </style>
</head>
<body>

  <!-- Topbar -->
  <header class="topbar">
    <div class="brand-line">
      <div class="logo"></div>
      <h1>Αναφορές</h1>
    </div>
    <div class="status">
      <span id="connDot" class="dot"></span>
      <span id="connText">—</span>
    </div>
  </header>

  <!-- Περιεχόμενο -->
  <main class="page-wrap">
    <div class="page-head">
      <h2>Αναφορές παρουσιών</h2>
      <div class="hint">Σύνολα &amp; γραφήματα με βάση επιλεγμένο εύρος</div>
    </div>

    <section class="card" id="reportsCard">
      <div class="toolbar" style="margin-bottom:8px">
        <label style="min-width:160px">
          Κλίμακα
          <select id="repGranularity">
            <option value="day" selected>Ημέρα</option>
            <option value="week">Βδομάδα</option>
            <option value="month">Μήνας</option>
            <option value="year">Χρόνος</option>
          </select>
        </label>
        <label>Από <input type="date" id="repFrom" /></label>
        <label>Έως <input type="date" id="repTo" /></label>
        <button id="repRun" class="btn small" type="button">Προβολή</button>
        <div class="spacer"></div>
        <div class="row" style="flex:unset; gap:6px">
          <button class="btn small ghost" type="button" id="repPresetMonth">Τρέχων μήνας</button>
          <button class="btn small ghost" type="button" id="repPreset30">Τελευταίες 30 ημέρες</button>
          <button class="btn small ghost" type="button" id="repPresetYTD">YTD</button>
        </div>
      </div>

      <div class="row" style="align-items:flex-end; margin:6px 0 10px">
        <div class="muted">Σύνολο στο εύρος: <strong id="repTotal">—</strong></div>
        <div class="spacer"></div>
        <label style="width:auto; min-width:140px">
          Τύπος
          <select id="repChartType">
            <option value="bar" selected>Bar</option>
            <option value="line">Line</option>
          </select>
        </label>
      </div>

      <div class="chart-box">
        <canvas id="attChart" height="260"></canvas>
      </div>

      <div id="reportsArea" class="muted" style="margin-top:8px"></div>
    </section>
  </main>
<div class="mt-4" style="display:flex; justify-content:center;">
  <a href="home.php" class="btn btn-outline-light">
    <i class="bi bi-arrow-left me-1"></i> Επιστροφή
  </a>
</div>

  <div class="toasts" id="toasts"></div>

  <script>
  const qs=s=>document.querySelector(s);
  const connDot=qs('#connDot'), connText=qs('#connText');
  function setStatus(kind,text){ connDot.classList.remove('ok','err'); if(kind==='ok')connDot.classList.add('ok'); if(kind==='err')connDot.classList.add('err'); if(text)connText.textContent=text; }
  const pad2=n=>String(n).padStart(2,'0');
  const todayISO=()=>{ const d=new Date(); return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; };

  function startOfDay(d){ const x=new Date(d); x.setHours(0,0,0,0); return x; }
  function toISO_d(d){ return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; }
  function startOfWeek(d){ const x=startOfDay(d); const day=(x.getDay()||7); x.setDate(x.getDate()-(day-1)); return x; }
  function startOfMonth(d){ return new Date(d.getFullYear(), d.getMonth(), 1); }
  function startOfYear(d){ return new Date(d.getFullYear(), 0, 1); }
  function buildRangePreset(p){
    const today=startOfDay(new Date());
    if(p==='month'){ const a=startOfMonth(today); const b=new Date(a.getFullYear(), a.getMonth()+1, 0); return [toISO_d(a),toISO_d(b)]; }
    if(p==='30'){ const b=today; const a=new Date(b); a.setDate(a.getDate()-29); return [toISO_d(a),toISO_d(b)]; }
    if(p==='ytd'){ const a=startOfYear(today); const b=today; return [toISO_d(a),toISO_d(b)]; }
    return [toISO_d(today),toISO_d(today)];
  }
  function aggregatorKey(dateStr,gran){
    const d=new Date(dateStr);
    if(gran==='day') return toISO_d(d);
    if(gran==='week') return toISO_d(startOfWeek(d));
    if(gran==='month') return `${d.getFullYear()}-${pad2(d.getMonth()+1)}`;
    if(gran==='year') return String(d.getFullYear());
    return toISO_d(d);
  }
  function labelPretty(key,gran){
    if(gran==='day') return new Date(key).toLocaleDateString('el-GR');
    if(gran==='week') return `Εβδ. ${new Date(key).toLocaleDateString('el-GR')}`;
    if(gran==='month'){ const [y,m]=key.split('-'); return `${m}/${y}`; }
    return key;
  }

  async function api(action,{method='GET',body=null,params=null}={}){
    const u=new URL('api.php',location.href);
    u.searchParams.set('action',action);
    if(params) for(const[k,v]of Object.entries(params)) u.searchParams.set(k,v);
    const r=await fetch(u,{method,headers:{'Content-Type':'application/json'},body:body?JSON.stringify(body):null});
    if(!r.ok){ throw new Error(await r.text()); }
    return r.json();
  }

  // Ping για ένδειξη σύνδεσης
  (async()=>{ try{ await api('ping'); setStatus('ok','Συνδεδεμένο'); }catch{ setStatus('err','Μη συνδεδεμένο'); } })();

  // ------- Reports -------
  const repGranularity=qs('#repGranularity'),
        repFrom=qs('#repFrom'),
        repTo=qs('#repTo'),
        repRun=qs('#repRun'),
        repPresetMonth=qs('#repPresetMonth'),
        repPreset30=qs('#repPreset30'),
        repPresetYTD=qs('#repPresetYTD'),
        repTotal=qs('#repTotal'),
        repChartType=qs('#repChartType'),
        canvas=qs('#attChart');

  let chartInstance=null;
  const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

  async function runReport(){
    const [defFrom,defTo]=buildRangePreset('month');
    const fromISO=repFrom.value||defFrom;
    const toISO=repTo.value||defTo;
    const gran=repGranularity.value;

    const data=await api('report_attendance',{params:{from:fromISO,to:toISO}});
    const agg=new Map();
    for(const r of (data||[])){
      const k=aggregatorKey(r.attended_on,gran);
      agg.set(k,(agg.get(k)||0)+1);
    }
    const keys=Array.from(agg.keys()).sort();
    const values=keys.map(k=>agg.get(k));
    const labels=keys.map(k=>labelPretty(k,gran));
    repTotal.textContent=values.reduce((a,b)=>a+b,0);

    const ctx=canvas.getContext('2d');
    if(chartInstance) chartInstance.destroy();
    chartInstance=new Chart(ctx,{
      type: repChartType.value,
      data: { labels, datasets:[{ label:'Παρουσίες', data:values }] },
      options:{
        responsive:true, maintainAspectRatio:false,
        scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } },
        plugins:{ legend:{ display:false } },
        animation: reducedMotion ? { duration: 0 } : {
          duration: 700,
          easing: 'easeOutCubic',
          delay: (ctx) => (ctx.type === 'data' && ctx.mode === 'default') ? ctx.dataIndex * 40 : 0
        }
      }
    });
  }

  (function init(){
    // fade-in για την κάρτα αναφορών (μόνο απαραίτητα)
    if(!reducedMotion){
      const card = document.getElementById('reportsCard');
      if(card){
        card.classList.add('_hidden');
        const io = new IntersectionObserver((entries)=>{
          if(entries.some(e=>e.isIntersecting)){
            requestAnimationFrame(()=>{ card.classList.add('_show'); card.classList.remove('_hidden'); });
            io.disconnect();
          }
        }, {threshold:.2});
        io.observe(card);
      }
    }

    const [a,b]=buildRangePreset('month');
    repFrom.value=a; repTo.value=b; repGranularity.value='day';
    repRun.addEventListener('click',runReport);
    repChartType.addEventListener('change',runReport);
    repGranularity.addEventListener('change',runReport);
    repPresetMonth.addEventListener('click',()=>{ const [a,b]=buildRangePreset('month'); repFrom.value=a; repTo.value=b; repGranularity.value='day'; runReport(); });
    repPreset30.addEventListener('click',()=>{ const [a,b]=buildRangePreset('30'); repFrom.value=a; repTo.value=b; repGranularity.value='day'; runReport(); });
    repPresetYTD.addEventListener('click',()=>{ const [a,b]=buildRangePreset('ytd'); repFrom.value=a; repTo.value=b; repGranularity.value='month'; runReport(); });
    runReport();
  })();
  </script>
</body>
</html>
