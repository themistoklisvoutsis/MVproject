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

async function api(action,{method='GET',body=null,params=null}={}) {
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
  // fade-in κάρτας αναφορών
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
