<?php /* Athlete Card — στοιχεία μέλους & σωματομετρήσεις (χωρίς sidebar) */ ?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Καρτέλα Αθλητή</title>
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
    .card h2{ margin-top:4px; }

    /* ===== Ειδικό layout καρτέλας ===== */
    @media print{
      .topbar, .toolbar{ display:none !important; }
      body, .page-wrap, #athleteCard{ background:#fff !important; color:#000 !important; }
      #athleteCard *{ color:#000 !important; background:#fff !important; }
      table.sheet-table th, table.sheet-table td{ border:1px solid #000 !important; }
    }
    .sheet-grid{display:grid;grid-template-columns:140px 1fr 140px;gap:12px;align-items:center;margin-bottom:8px;}
    .logo-box{display:flex;align-items:center;justify-content:center}
    .sheet-title{text-align:center;font-weight:900;font-size:20px;letter-spacing:.6px}
    .fields{margin-top:6px}
    .field-row{display:grid;grid-template-columns:220px 1fr;gap:10px;margin:6px 0}
    .field-label{color:var(--muted);font-size:13px}
    .table-wrap{overflow:auto;border-radius:12px;border:1px solid var(--border)}
    .sheet-table{width:100%;border-collapse:collapse}
    .sheet-table th,.sheet-table td{border:1px solid var(--border);padding:8px;text-align:left}

    /* ===== ΜΟΝΟ τα απαραίτητα για fade-in ===== */
    @media (prefers-reduced-motion: no-preference){
      #athleteCard._hidden{ opacity:0; transform: translateY(10px); }
      #athleteCard._show{ opacity:1; transform:none; transition: opacity .5s ease, transform .5s ease; }
      #measTable tbody tr._hidden{ opacity:0; transform: translateY(6px); }
      #measTable tbody tr._show{ opacity:1; transform:none; transition: opacity .35s ease, transform .35s ease; }
    }
  </style>
</head>
<body>

  <!-- Topbar -->
  <header class="topbar">
    <div class="brand-line">
      <div class="logo"></div>
      <h1>Καρτέλα Αθλητή</h1>
    </div>
    <div class="status">
      <span id="connDot" class="dot"></span>
      <span id="connText">—</span>
    </div>
  </header>

  <!-- Περιεχόμενο -->
  <main class="page-wrap">
    <div class="card" id="athleteCard">
      <div class="toolbar" style="margin:-6px 0 8px">
        <h2 style="margin:0">Καρτέλα Αθλητή</h2>
        <div class="spacer"></div>
        <select id="cardMemberSelect"></select>
        <a class="btn small ghost" href="home.php" style="margin-right:6px">Αρχική</a>
        <button class="btn small ghost" id="btnPrintCard" type="button">Εκτύπωση</button>
      </div>

      <div class="sheet-grid">
        <div class="logo-box"><div class="logo"></div></div>
        <div class="sheet-title">ΚΑΡΤΕΛΑ ΠΕΛΑΤΗ</div>
        <div></div>
      </div>

      <div class="fields">
        <div class="field-row"><div class="field-label">ΟΝΟΜΑΤΕΠΩΝΥΜΟ:</div><div><input id="f_fullname"></div></div>
        <div class="field-row"><div class="field-label">ΗΜ/ΝΙΑ ΓΕΝΝΗΣΗΣ:</div><div><input id="f_dob" type="date"></div></div>
        <div class="field-row"><div class="field-label">ΔΙΕΥΘΥΝΣΗ ΚΑΤΟΙΚΙΑΣ:</div><div><input id="f_address"></div></div>
        <div class="field-row"><div class="field-label">ΤΗΛΕΦΩΝΟ:</div><div><input id="f_phone"></div></div>
        <div class="field-row"><div class="field-label">E-MAIL:</div><div><input id="f_email" type="email"></div></div>
        <div class="field-row"><div class="field-label">ΣΗΜΕΙΩΣΕΙΣ ΙΑΤΡΙΚΟΥ ΙΣΤΟΡΙΚΟΥ:</div><div><textarea id="f_medical" rows="3"></textarea></div></div>
        <div class="row" style="margin-top:4px">
          <button class="btn" id="btnSaveMember" type="button">Αποθήκευση στοιχείων</button>
          <span class="muted">Αποθηκεύονται στο μέλος.</span>
        </div>
      </div>

      <h3 style="margin:16px 0 8px">ΣΩΜΑΤΟΜΕΤΡΗΣΕΙΣ</h3>
      <div class="row" style="gap:8px;flex-wrap:wrap">
        <input type="date" id="m_date">
        <input type="number" step="0.01" id="m_weight" placeholder="ΒΑΡΟΣ (kg)">
        <input type="number" step="0.01" id="m_fat" placeholder="ΛΙΠΟΣ %">
        <input type="number" step="0.01" id="m_twb" placeholder="TWB">
        <input type="number" step="0.01" id="m_mbw" placeholder="MBW">
        <input type="number" step="0.01" id="m_kcal" placeholder="KCAL">
        <input type="number" step="0.01" id="m_bones" placeholder="BONES">
        <input type="number" step="0.01" id="m_visc"  placeholder="ΣΠΛΑΧΝ">
        <button class="btn" id="btnAddMeas" type="button">Προσθήκη</button>
      </div>

      <div class="table-wrap" style="margin-top:8px">
        <table class="sheet-table" id="measTable">
          <thead><tr>
            <th>ΗΜ/ΝΙΑ</th><th>ΒΑΡΟΣ</th><th>ΛΙΠΟΣ%</th><th>TWB</th><th>MBW</th><th>KCAL</th><th>BONES</th><th>ΣΠΛΑΧΝ</th><th></th>
          </tr></thead>
          <tbody></tbody>
        </table>
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
  function toast(msg,kind='ok',timeout=2500){ const box=document.createElement('div'); box.className=`toast ${kind}`; box.innerHTML=escapeHtml(msg); document.getElementById('toasts').appendChild(box); setTimeout(()=>box.remove(),timeout); }

  async function api(action,{method='GET',body=null,params=null}={}){
    const u=new URL('api.php',location.href);
    u.searchParams.set('action',action);
    if(params) for(const[k,v] of Object.entries(params)) u.searchParams.set(k,v);
    const r=await fetch(u,{method,headers:{'Content-Type':'application/json'},body:body?JSON.stringify(body):null});
    if(!r.ok){ throw new Error(await r.text()); }
    return r.json();
  }

  // Ping για ένδειξη σύνδεσης
  (async()=>{ try{ await api('ping'); setStatus('ok','Συνδεδεμένο'); }catch{ setStatus('err','Μη συνδεδεμένο'); } })();

  // ------- Card logic -------
  async function cardLoadMembers(){
    const sel=document.getElementById('cardMemberSelect'); sel.innerHTML='<option>Φόρτωση…</option>';
    try{
      const rows=await api('list_members');
      if(!Array.isArray(rows)||!rows.length){ sel.innerHTML=''; return; }
      sel.innerHTML=rows.map(r=>`<option value="${r.id}">#${r.id} — ${escapeHtml(r.first_name||'')} ${escapeHtml(r.last_name||'')}</option>`).join('');
      cardLoadCard(sel.value);
    }catch(e){ sel.innerHTML=''; toast(e.message,'err',3500); }
  }

  async function cardLoadCard(id){
    const m1=await api('member_get',{params:{id:String(id)}});
    const d=(m1&&m1.data)||{};
    document.getElementById('f_fullname').value=`${d.first_name||''} ${d.last_name||''}`.trim();
    document.getElementById('f_dob').value=d.dob||'';
    document.getElementById('f_address').value=d.address||'';
    document.getElementById('f_phone').value=d.phone||'';
    document.getElementById('f_email').value=d.email||'';
    document.getElementById('f_medical').value=d.medical_notes||'';

    const js=await api('meas_list',{params:{member_id:String(id)}});
    const tbody=document.querySelector('#measTable tbody'); tbody.innerHTML='';
    (js.data||[]).forEach(r=>{
      const tr=document.createElement('tr'); tr.innerHTML=`
        <td>${r.measured_on??''}</td><td>${r.weight_kg??''}</td><td>${r.fat_percent??''}</td>
        <td>${r.twb??''}</td><td>${r.mbw??''}</td><td>${r.kcal??''}</td><td>${r.bones??''}</td><td>${r.visceral??''}</td>
        <td><button class="btn small ghost" data-del="${r.id}">Διαγραφή</button></td>`;
      tbody.appendChild(tr);
    });
    _staggerMeasRows(); /* ← μόνο απαραίτητη κλήση για fade-in rows */
  }

  document.getElementById('cardMemberSelect')?.addEventListener('change',e=>cardLoadCard(e.target.value));
  document.getElementById('btnPrintCard')?.addEventListener('click',()=>window.print());

  document.getElementById('btnSaveMember')?.addEventListener('click', async ()=>{
    const id=document.getElementById('cardMemberSelect').value;
    const [first_name,...rest]=(document.getElementById('f_fullname').value||'').trim().split(' ');
    const payload={
      id,
      first_name:first_name||'',
      last_name:rest.join(' ')||'',
      dob:document.getElementById('f_dob').value||null,
      address:document.getElementById('f_address').value||null,
      phone:document.getElementById('f_phone').value||null,
      email:document.getElementById('f_email').value||null,
      medical_notes:document.getElementById('f_medical').value||null
    };
    try{ await api('member_save',{method:'POST',body:payload}); toast('✅ Αποθηκεύτηκε'); }
    catch(e){ toast(e.message,'err',3500); }
  });

  document.getElementById('btnAddMeas')?.addEventListener('click', async ()=>{
    const member_id=document.getElementById('cardMemberSelect').value;
    const measured_on=document.getElementById('m_date').value;
    if(!measured_on){ toast('Βάλε ημερομηνία','err'); return; }
    const payload={
      member_id, measured_on,
      weight_kg:document.getElementById('m_weight').value||null,
      fat_percent:document.getElementById('m_fat').value||null,
      twb:document.getElementById('m_twb').value||null,
      mbw:document.getElementById('m_mbw').value||null,
      kcal:document.getElementById('m_kcal').value||null,
      bones:document.getElementById('m_bones').value||null,
      visceral:document.getElementById('m_visc').value||null
    };
    try{
      const js=await api('meas_add',{method:'POST',body:payload});
      toast('✅ Προστέθηκε');
      const r=js.data;
      const tr=document.createElement('tr');
      tr.classList.add('_hidden'); /* ← απαραίτητο για fade-in νέας γραμμής */
      tr.innerHTML=`
        <td>${r.measured_on??''}</td><td>${r.weight_kg??''}</td><td>${r.fat_percent??''}</td>
        <td>${r.twb??''}</td><td>${r.mbw??''}</td><td>${r.kcal??''}</td><td>${r.bones??''}</td><td>${r.visceral??''}</td>
        <td><button class="btn small ghost" data-del="${r.id}">Διαγραφή</button></td>`;
      document.querySelector('#measTable tbody').prepend(tr);
      setTimeout(()=>{ tr.classList.add('_show'); tr.classList.remove('_hidden'); }, 20);

      ['m_weight','m_fat','m_twb','m_mbw','m_kcal','m_bones','m_visc'].forEach(id=>document.getElementById(id).value='');
    }catch(e){ toast(e.message,'err',3500); }
  });

  document.querySelector('#measTable tbody')?.addEventListener('click', async (e)=>{
    const id=e.target?.dataset?.del; if(!id) return;
    if(!confirm('Διαγραφή μέτρησης;')) return;
    try{ await api('meas_delete',{method:'POST',body:{id:Number(id)}}); e.target.closest('tr').remove(); }
    catch(e2){ toast(e2.message,'err',3500); }
  });

  /* ===== ΜΟΝΟ τα απαραίτητα JS για fade-in ===== */
  (function(){ // κάρτα
    if(matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const card=document.getElementById('athleteCard'); if(!card) return;
    card.classList.add('_hidden');
    const io=new IntersectionObserver(es=>{
      if(es.some(e=>e.isIntersecting)){
        requestAnimationFrame(()=>{ card.classList.add('_show'); card.classList.remove('_hidden'); });
        io.disconnect();
      }
    },{threshold:.2});
    io.observe(card);
  })();

  function _staggerMeasRows(){ // γραμμές μετρήσεων
    if(matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const rows=document.querySelectorAll('#measTable tbody tr');
    rows.forEach(r=>r.classList.add('_hidden'));
    rows.forEach((r,i)=>{ setTimeout(()=>{ r.classList.add('_show'); r.classList.remove('_hidden'); }, i*60); });
  }

  cardLoadMembers();
  </script>
</body>
</html>
