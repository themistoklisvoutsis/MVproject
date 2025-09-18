<?php /* Members — Προσθήκη/Αναζήτηση/Επεξεργασία (χωρίς sidebar) */ ?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Μέλη</title>
  <link rel="stylesheet" href="style.css">
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

    .card h2{ margin-top:4px; }
    .table-wrap{ border-radius:14px; }

    @media (max-width:640px){
      .page-head{ flex-direction:column; align-items:flex-start; }
    }
  </style>
</head>
<body>

  <!-- Topbar -->
  <header class="topbar">
    <div class="brand-line">
      <div class="logo"></div>
      <h1>Μέλη</h1>
    </div>
    <div class="status">
      <span id="connDot" class="dot"></span>
      <span id="connText">—</span>
    </div>
  </header>

  <!-- Περιεχόμενο -->
  <main class="page-wrap">
    <div class="page-head">
      <h2>Διαχείριση μελών</h2>
      <div class="hint">Προσθήκη, αναζήτηση και επεξεργασία στοιχείων</div>
    </div>

    <section class="card">
      <h2>Προσθήκη μέλους</h2>
      <form id="addMemberForm">
        <div class="row">
          <div><label>Όνομα</label><input id="firstName" required></div>
          <div><label>Επώνυμο</label><input id="lastName" required></div>
        </div>
        <div class="row" style="margin-top:10px">
          <button class="btn" type="submit">Αποθήκευση</button>
          <output id="addMemberMsg" class="muted"></output>
        </div>
      </form>
    </section>

    <section class="card">
      <h2>Αναζήτηση / Διαχείριση</h2>
      <div class="row">
        <input id="searchQ" placeholder="Αναζήτηση..." />
        <button id="btnSearch" class="btn" type="button">Αναζήτηση</button>
      </div>
      <div id="membersList" class="muted table-wrap" style="margin-top:10px">—</div>
    </section>
  </main>

<!-- Modal Επεξεργασίας -->
<div id="memberModal" class="modal" hidden>
  <div class="modal-card card">
    <h2 style="margin:6px 0 12px">Επεξεργασία μέλους</h2>
    <div class="row">
      <div><label>Όνομα</label><input id="editFirst" /></div>
      <div><label>Επώνυμο</label><input id="editLast" /></div>
    </div>
    <div class="row" style="margin-top:8px">
      <div>
        <label>Μετρητής από</label>
        <input id="editEpoch" type="date" />
      </div>
      <div>
        <label>Σύνολα</label>
        <div class="muted">Μετρητής: <span id="statSince">—</span> • Έτος: <span id="statYear">—</span></div>
      </div>
    </div>
    <div class="row" style="margin-top:10px">
      <button id="btnSaveMember" class="btn" type="button">Αποθήκευση</button>
      <button id="btnCloseMember" class="btn ghost" type="button">Κλείσιμο</button>
    </div>
    <div id="memberMsg" class="muted" style="margin-top:8px"></div>
  </div>
</div>

<div class="mt-4" style="display:flex; justify-content:center;">
  <a href="home.php" class="btn btn-outline-light">
    <i class="bi bi-arrow-left me-1"></i> Επιστροφή
  </a>
</div>

<div class="toasts" id="toasts"></div>

<script>
/* ---- Helpers / API ---- */
const qs=s=>document.querySelector(s);
const connDot=qs('#connDot'), connText=qs('#connText');
function setStatus(kind,text){ connDot.classList.remove('ok','err'); if(kind==='ok')connDot.classList.add('ok'); if(kind==='err')connDot.classList.add('err'); if(text)connText.textContent=text; }
function escapeHtml(s){ return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]) ); }
function toast(msg,kind='ok',timeout=2500){ const box=document.createElement('div'); box.className=`toast ${kind}`; box.innerHTML=escapeHtml(msg); qs('#toasts').appendChild(box); setTimeout(()=>box.remove(),timeout); }
const pad2=n=>String(n).padStart(2,'0'); const todayISO=()=>{ const d=new Date(); return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; };
async function api(action,{method='GET',body=null,params=null}={}){ const u=new URL('api.php',location.href); u.searchParams.set('action',action); if(params)for(const[k,v]of Object.entries(params)) u.searchParams.set(k,v); const r=await fetch(u,{method,headers:{'Content-Type':'application/json'},body:body?JSON.stringify(body):null}); if(!r.ok){throw new Error(await r.text());} return r.json(); }
(async()=>{ try{ await api('ping'); setStatus('ok','Συνδεδεμένο'); }catch{ setStatus('err','Μη συνδεδεμένο'); } })();

/* ---- Members ---- */
const addMemberForm=qs('#addMemberForm'), addMemberMsg=qs('#addMemberMsg'), membersList=qs('#membersList'), searchInput=qs('#searchQ');
let editingMember=null;

async function loadMembers(){
  membersList.textContent='Φόρτωση…';
  try{
    const q=searchInput.value.trim();
    const data=await api('search_members',{params:{q}});
    if(!data?.length){ membersList.textContent='— Καμία εγγραφή —'; return; }
    const year=new Date().getFullYear();
    const rows=data.map(m=>`
      <tr data-id="${m.id}">
        <td><span class="badge copy" data-copy="${m.id}">#${m.id}</span></td>
        <td>${escapeHtml(m.first_name||'')}</td>
        <td>${escapeHtml(m.last_name||'')}</td>
        <td><span class="count-since">${Number(m.total_since_reset||0)}</span> | <span class="count-year" data-id="${m.id}">…</span></td>
        <td style="text-align:right;white-space:nowrap">
          <button class="btn small ghost quick-mark" type="button">✔️ Σήμερα</button>
          <button class="btn small ghost reset-one" type="button" title="Μηδενισμός">0️⃣</button>
          <button class="btn small ghost edit-member" type="button" title="Επεξεργασία">✎</button>
        </td>
      </tr>`).join('');
    membersList.innerHTML=`<table>
      <thead><tr><th>ID</th><th>Όνομα</th><th>Επώνυμο</th><th>Μετρητής | Έτος ${year}</th><th></th></tr></thead>
      <tbody>${rows}</tbody></table>`;

    membersList.querySelectorAll('.badge.copy').forEach(b=>b.addEventListener('click',ev=>{
      const id=ev.currentTarget.getAttribute('data-copy'); navigator.clipboard?.writeText(id); toast(`Αντιγράφηκε #${id}`);
    }));
    membersList.querySelectorAll('.quick-mark').forEach(btn=>btn.addEventListener('click', async ev=>{
      const id=Number(ev.currentTarget.closest('tr').getAttribute('data-id'));
      try{ await api('upsert_attendance',{method:'POST',body:{member_id:id, attended_on:todayISO()}}); toast('✅ Καταχωρήθηκε'); }catch(e){ toast(e.message,'err',3000); }
    }));
    membersList.querySelectorAll('.reset-one').forEach(btn=>btn.addEventListener('click', async ev=>{
      const id=Number(ev.currentTarget.closest('tr').getAttribute('data-id'));
      if(!confirm(`Μηδενισμός μετρητή για #${id};`)) return;
      try{ await api('reset_counter',{method:'POST',body:{id}}); toast('🔄 Έγινε'); loadMembers(); }catch(e){ toast(e.message,'err',3000); }
    }));
    membersList.querySelectorAll('.edit-member').forEach(btn=>btn.addEventListener('click',ev=>{
      const id=Number(ev.currentTarget.closest('tr').getAttribute('data-id')); openMemberEditor(id);
    }));

    // Σύνολα έτους (ένα call για όλους)
    const totals=await api('year_totals',{params:{from:`${year}-01-01`,to:`${year}-12-31`}});
    membersList.querySelectorAll('.count-year').forEach(el=>{
      const id=Number(el.getAttribute('data-id')); el.textContent = totals[id] || 0;
    });
  }catch(e){ membersList.innerHTML=`❌ ${escapeHtml(e.message)}`; }
}

async function addMember(first,last){
  const r=await api('add_member',{method:'POST',body:{first_name:first,last_name:last}});
  return r.id;
}

addMemberForm?.addEventListener('submit', async e=>{
  e.preventDefault(); addMemberMsg.textContent='Αποθήκευση…';
  try{
    const id=await addMember(qs('#firstName').value.trim(),qs('#lastName').value.trim());
    addMemberMsg.textContent=`✅ #${id}`; addMemberForm.reset(); loadMembers();
  }catch(err){ addMemberMsg.innerHTML=`❌ ${escapeHtml(err.message)}`; }
});
document.getElementById('btnSearch')?.addEventListener('click',loadMembers);
searchInput?.addEventListener('keydown',e=>{ if(e.key==='Enter'){ e.preventDefault(); loadMembers(); }});

/* Modal Editor */
const memberModal=qs('#memberModal'), editFirst=qs('#editFirst'), editLast=qs('#editLast'), editEpoch=qs('#editEpoch'), statSince=qs('#statSince'), statYear=qs('#statYear');
function open(el){ el.hidden=false } function close(el){ el.hidden=true }
qs('#btnCloseMember')?.addEventListener('click',()=>close(memberModal));

async function countAttendanceFor(memberId,from,to){
  const r=await api('count_attendance',{params:{member_id:String(memberId),from,to}});
  return r.count||0;
}
async function openMemberEditor(id){
  try{
    const recs=await api('get_member',{params:{id:String(id)}});
    const m=Array.isArray(recs)?recs[0]:null; if(!m) throw new Error('Δεν βρέθηκε το μέλος.');
    editingMember=m;
    editFirst.value=m.first_name||''; editLast.value=m.last_name||'';
    editEpoch.value=(m.counter_epoch||todayISO()).slice(0,10);
    statSince.textContent=Number(m.total_since_reset)||0; statYear.textContent='…';
    open(memberModal);
    const y=(new Date()).getFullYear();
    statYear.textContent=await countAttendanceFor(m.id,`${y}-01-01`,`${y}-12-31`);
  }catch(e){ toast(e.message,'err',3000); }
}
qs('#btnSaveMember')?.addEventListener('click', async ()=>{
  if(!editingMember) return;
  try{
    await api('update_member',{method:'POST',body:{
      id:editingMember.id,
      first_name:editFirst.value.trim(),
      last_name:editLast.value.trim(),
      counter_epoch:editEpoch.value||null
    }});
    toast('✅ Αποθηκεύτηκε'); close(memberModal); loadMembers();
  }catch(e){ toast(e.message,'err',3000); }
});

/* init */
loadMembers();
// —— Staggered fade-in στις κάρτες της σελίδας ——
(function(){
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(reduced) return;
  const cards = Array.from(document.querySelectorAll('.page-wrap .card'));
  if(!cards.length) return;

  cards.forEach(c => c.classList.add('_hidden'));
  const wrap = document.querySelector('.page-wrap');
  const reveal = () => {
    cards.forEach((c, i) => {
      setTimeout(() => { c.classList.add('_show'); c.classList.remove('_hidden'); }, i * 160);
    });
    io.disconnect();
  };
  const io = new IntersectionObserver((es)=>{ if(es.some(e=>e.isIntersecting)) reveal(); }, {threshold:.2});
  io.observe(wrap);
})();

// —— Staggered fade-in για τις σειρές του πίνακα μετά από loadMembers() ——
const _staggerRows = () => {
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(reduced) return;
  const rows = document.querySelectorAll('#membersList tbody tr');
  rows.forEach(r => r.classList.add('_hidden'));
  rows.forEach((r, i) => {
    setTimeout(() => { r.classList.add('_show'); r.classList.remove('_hidden'); }, i * 60);
  });
};

</script>
</body>
</html>
