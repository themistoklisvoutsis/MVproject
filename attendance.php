<?php /* Attendance — Δήλωση & λίστα ανά ημέρα (χωρίς sidebar) */ ?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Παρουσίες</title>
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

    /* Κάρτες/πίνακες λίγο πιο “ανοικτοί” */
    .card h2{ margin-top:4px; }
    .table-wrap{ border-radius:14px; }

    /* Μικρή στοίχιση εργαλείων ημερομηνίας */
    .toolbar{ gap:8px; }
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
      <h1>Παρουσίες</h1>
    </div>
    <div class="status">
      <span id="connDot" class="dot"></span>
      <span id="connText">—</span>
    </div>
  </header>

  <!-- Περιεχόμενο -->
  <main class="page-wrap">

    <div class="page-head">
      <h2>Διαχείριση παρουσιών</h2>
      <div class="hint">Καταχώρισε παρουσία και δες τη λίστα για την επιλεγμένη ημερομηνία</div>
    </div>

    <div class="grid">
      <!-- Δήλωση παρουσίας -->
      <div class="card">
        <h2>Δήλωση παρουσίας</h2>
        <form id="markAttendanceForm">
          <div class="row">
            <div><label>ID μέλους</label><input id="memberId" type="number" placeholder="π.χ. 42" required /></div>
            <div><label>Ημερομηνία</label><input id="attDate" type="date" /></div>
          </div>
          <div style="margin-top:10px"><label>Σχόλιο</label><input id="note" placeholder="π.χ. Πρωινή προπόνηση" /></div>
          <div class="row" style="margin-top:10px">
            <button class="btn" type="submit">Καταχώριση</button>
            <output id="markMsg" class="muted"></output>
          </div>
        </form>
      </div>

      <!-- Λίστα ημερομηνίας -->
      <div class="card">
        <h2>Παρουσίες ανά ημερομηνία</h2>
        <div class="toolbar" style="display:flex; align-items:center; flex-wrap:wrap">
          <button id="prevDate" class="btn small ghost" type="button" title="Προηγούμενη">←</button>
          <input id="filterDate" type="date" />
          <button id="nextDate" class="btn small ghost" type="button" title="Επόμενη">→</button>
          <div class="spacer"></div>
          <button id="btnToday" class="btn small ghost" type="button">Σήμερα</button>
          <button id="btnFilterDate" class="btn small" type="button">Προβολή</button>
        </div>
        <div id="todayList" class="muted table-wrap" style="margin-top:6px">—</div>
      </div>
    </div>
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
  function escapeHtml(s){ return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]) ); }
  function toast(msg,kind='ok',timeout=2500){ const box=document.createElement('div'); box.className=`toast ${kind}`; box.innerHTML=escapeHtml(msg); qs('#toasts').appendChild(box); setTimeout(()=>box.remove(),timeout); }
  const pad2=n=>String(n).padStart(2,'0');
  const todayISO=()=>{ const d=new Date(); return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; };
  function fmt(d){ return new Date(d).toLocaleDateString('el-GR'); }

  async function api(action,{method='GET',body=null,params=null}={}) {
    const u=new URL('api.php',location.href);
    u.searchParams.set('action',action);
    if(params) for(const [k,v] of Object.entries(params)) u.searchParams.set(k,v);
    const r=await fetch(u,{method,headers:{'Content-Type':'application/json'},body:body?JSON.stringify(body):null});
    if(!r.ok){ throw new Error(await r.text()); }
    return r.json();
  }

  // Ping για ένδειξη σύνδεσης
  (async()=>{ try{ await api('ping'); setStatus('ok','Συνδεδεμένο'); }catch{ setStatus('err','Μη συνδεδεμένο'); } })();

  const filterDate=qs('#filterDate'), todayList=qs('#todayList');
  filterDate.value=todayISO();

  async function loadAttendance(dateISO){
    todayList.textContent='Φόρτωση…';
    try{
      const data=await api('list_attendance',{params:{date:dateISO}});
      if(!data?.length){ todayList.textContent='— Καμία παρουσία —'; return; }
      const total=data.filter(r=>r.present).length;
      todayList.innerHTML=`
        <div class="muted" style="margin-bottom:8px">Σύνολο: <strong>${total}</strong></div>
        <table>
          <thead><tr><th>Μέλος</th><th>Ημ/νία</th><th>Σχόλιο</th><th></th></tr></thead>
          <tbody>${
            data.map(r=>`
              <tr data-att-id="${r.id}" data-present="${r.present?'1':'0'}">
                <td><span class="kbd">#${r.members.id}</span> ${escapeHtml(r.members.first_name)} ${escapeHtml(r.members.last_name)}</td>
                <td>${fmt(r.attended_on)} ${r.present?'✔️':'✖️'}</td>
                <td>${escapeHtml(r.note||'')}</td>
                <td style="text-align:right;white-space:nowrap">
                  <button class="btn small ghost btn-edit-att" type="button" title="Επεξεργασία σημείωσης">✎</button>
                  <button class="btn small ghost btn-toggle-att" type="button" title="Εναλλαγή">↔︎</button>
                </td>
              </tr>`).join('')}
          </tbody>
        </table>`;
      todayList.querySelectorAll('.btn-edit-att').forEach(btn=>btn.addEventListener('click', async ev=>{
        const tr=ev.currentTarget.closest('tr'); const id=Number(tr.getAttribute('data-att-id'));
        const current=tr.children[2].textContent||''; const note=prompt('Σχόλιο:',current); if(note===null) return;
        try{ await api('update_attendance_note',{method:'POST',body:{id,note}}); toast('✅ Ενημερώθηκε'); loadAttendance(filterDate.value); }catch(e){ toast(e.message,'err',3000); }
      }));
      todayList.querySelectorAll('.btn-toggle-att').forEach(btn=>btn.addEventListener('click', async ev=>{
        const tr=ev.currentTarget.closest('tr'); const id=Number(tr.getAttribute('data-att-id')); const cur=tr.getAttribute('data-present')==='1';
        try{ await api('toggle_attendance',{method:'POST',body:{id,present:cur}}); toast('🔁 Άλλαξε'); loadAttendance(filterDate.value); }catch(e){ toast(e.message,'err',3000); }
      }));
    }catch(e){ todayList.innerHTML=`❌ ${escapeHtml(e.message)}`; }
  }

  document.getElementById('btnFilterDate').addEventListener('click',()=>loadAttendance(filterDate.value||todayISO()));
  document.getElementById('btnToday').addEventListener('click',()=>{ filterDate.value=todayISO(); loadAttendance(filterDate.value); });
  document.getElementById('prevDate').addEventListener('click',()=>{ const d=new Date(filterDate.value||todayISO()); d.setDate(d.getDate()-1); filterDate.value=`${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; loadAttendance(filterDate.value); });
  document.getElementById('nextDate').addEventListener('click',()=>{ const d=new Date(filterDate.value||todayISO()); d.setDate(d.getDate()+1); filterDate.value=`${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; loadAttendance(filterDate.value); });

  document.getElementById('markAttendanceForm').addEventListener('submit', async e=>{
    e.preventDefault();
    const member_id=Number(document.getElementById('memberId').value);
    const attended_on=document.getElementById('attDate').value||todayISO();
    const note=document.getElementById('note').value||null;
    const out=document.getElementById('markMsg'); out.textContent='Καταχώριση…';
    try{
      await api('upsert_attendance',{method:'POST',body:{member_id,attended_on,note}});
      out.textContent='✅ Έγινε';
      loadAttendance(filterDate.value||todayISO());
    }catch(err){ out.innerHTML=`❌ ${escapeHtml(err.message)}`; }
  });

  loadAttendance(filterDate.value);
  // —— Staggered fade-in στις κάρτες ——
(function(){
  const cards = Array.from(document.querySelectorAll('.grid .card'));
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!cards.length || reduced) return;

  // Αρχικά κρυφές
  cards.forEach(c => c.classList.add('_hidden'));

  // Όταν μπουν στο viewport → εμφανίζονται μία-μία
  const grid = document.querySelector('.grid');
  const reveal = () => {
    cards.forEach((c, i) => {
      setTimeout(() => {
        c.classList.add('_show');
        c.classList.remove('_hidden');
      }, i * 180); // delay ανά κάρτα
    });
    io.disconnect();
  };

  const io = new IntersectionObserver((entries) => {
    if (entries.some(e => e.isIntersecting)) reveal();
  }, { threshold: 0.2 });

  if (grid) io.observe(grid);
})();

  </script>
</body>
</html>
