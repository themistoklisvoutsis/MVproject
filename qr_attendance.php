<?php /* qr_attendance.php — Live σάρωση (HTTPS) + σάρωση από φωτογραφία (HTTP) + τοπικά libs με fallback */ ?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>QR Παρουσίες</title>
  <link rel="stylesheet" href="style.css">
  <style>
    :root{ --brand-yellow-1:#FFE257; --brand-yellow-2:#F9C80E; }
    body, html { height:100%; }
    .topbar{ position:sticky; top:0; z-index:10; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 18px; background:var(--bg); border-bottom:1px solid var(--border); }
    .brand-line{ display:flex; align-items:center; gap:10px }
    .brand-line .logo{ width:36px; height:36px; border-radius:10px; }
    .brand-line h1{ margin:0; font-size:18px; font-weight:800; letter-spacing:.2px; }
    .status{ display:flex; align-items:center; gap:8px; color:var(--muted) }
    .dot{ width:10px; height:10px; border-radius:50%; background:#777; box-shadow:0 0 0 2px rgba(0,0,0,.15) inset }
    .dot.ok{ background:#36d399 } .dot.err{ background:#ef4444 }

    .page-wrap{ max-width:1100px; margin:0 auto; padding:18px; }
    .grid2{ display:grid; grid-template-columns:1fr; gap:16px; }
    @media (min-width:920px){ .grid2{ grid-template-columns: 1.15fr .85fr; } }

    .btn-back{
      display:inline-block; width:100%; padding:14px 18px; border-radius:14px;
      font-weight:800; text-align:center; color:#000!important;
      background:linear-gradient(180deg,var(--brand-yellow-1) 0%,var(--brand-yellow-2) 100%);
      border:1px solid rgba(0,0,0,.25);
      box-shadow:0 1px 0 rgba(255,255,255,.35) inset, 0 10px 22px rgba(0,0,0,.25);
      transition:transform .08s ease, filter .15s ease;
    }
    .btn-back:hover{ filter:brightness(.98); transform:translateY(-1px); }

    #reader{ width:100%; min-height:320px; }
    .scan-log{ max-height:240px; overflow:auto; border:1px solid var(--border); border-radius:10px; padding:8px; background:rgba(255,255,255,.02)}
    .scan-log .row{ display:flex; align-items:center; gap:8px; padding:6px 4px; border-bottom:1px dashed var(--border); }
    .scan-log .row:last-child{ border-bottom:none; }
    .ok-badge{ background:#36d39922; border:1px solid #36d39966; color:#36d399; padding:2px 6px; border-radius:8px; font-size:12px; }
    .err-badge{ background:#ef444422; border:1px solid #ef444466; color:#ef4444; padding:2px 6px; border-radius:8px; font-size:12px; }
    .muted-sm{ color:var(--muted); font-size:12px; }
    .kbd{ background:#00000033; border:1px solid var(--border); border-radius:6px; padding:2px 6px; font-size:12px }

    .qr-card{ display:flex; align-items:center; gap:12px; padding:12px; border:1px solid var(--border); border-radius:12px; background:var(--card); }
    .qr-img{ width:140px; height:140px; display:grid; place-items:center; background:#fff; border:1px solid var(--border); border-radius:10px; }
    .qr-grid{ display:grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap:12px; margin-top:10px; }
    .qr-actions{ display:flex; gap:6px; flex-wrap:wrap; }

    @media print{
      .topbar, .toolbar, .btn, #cameraSel, .qr-actions{ display:none !important; }
      body, .page-wrap{ background:#fff; color:#000; }
      .qr-card{ page-break-inside: avoid; }
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand-line"><div class="logo"></div><h1>QR Παρουσίες</h1></div>
    <div class="status"><span id="connDot" class="dot"></span><span id="connText">—</span></div>
  </header>

  <main class="page-wrap">
    <div class="grid2">
      <!-- ΣΑΡΩΣΗ -->
      <section class="card">
        <div class="toolbar" style="gap:8px; flex-wrap:wrap">
          <h2 style="margin:0">Σάρωση & άμεση παρουσία</h2>
          <div class="spacer"></div>
          <button id="btnStart" class="btn">Έναρξη σάρωσης</button>
          <button id="btnStop" class="btn ghost">Στοπ</button>
          <button id="btnScanPhoto" class="btn ghost">Σάρωση από φωτογραφία</button>
          <input id="fileScan" type="file" accept="image/*" capture="environment" hidden>
          <select id="cameraSel" class="btn small" title="Επιλογή κάμερας" style="min-width:220px"><option>— φόρτωση… —</option></select>
        </div>
        <div class="muted" style="margin:4px 0 10px">
          Ζωντανή κάμερα: απαιτεί <strong>HTTPS</strong> ή <strong>localhost</strong>. Η <strong>Σάρωση από φωτογραφία</strong> δουλεύει και σε HTTP (iPhone/Android).
        </div>
        <div id="scanBox" style="border:1px solid var(--border); border-radius:12px; overflow:hidden">
          <div id="reader"></div>
        </div>
        <div style="margin-top:12px">
          <div class="muted" style="margin-bottom:6px">Πρόσφατες σαρώσεις</div>
          <div id="scanLog" class="scan-log">—</div>
        </div>
      </section>

      <!-- ΔΗΜΙΟΥΡΓΙΑ QR ΓΙΑ ΜΕΛΗ -->
      <section class="card">
        <div class="toolbar" style="gap:8px; flex-wrap:wrap">
          <h2 style="margin:0">QR για μέλη</h2>
          <div class="spacer"></div>
          <button id="btnLoadAll" class="btn">Φόρτωση μελών</button>
          <button id="btnPrintAll" class="btn ghost" disabled>Εκτύπωση όλων</button>
        </div>
        <div id="qrList" class="qr-grid">
          <div class="muted">Πάτα «Φόρτωση μελών» για να δημιουργηθούν οι QR.</div>
        </div>
      </section>
    </div>

    <div style="max-width:560px; margin:18px auto 0">
      <a href="home.php" class="btn-back">⟵ Αρχική</a>
    </div>
  </main>

  <script>
    /* ===== Multi-source loader: πρώτα τοπικά, μετά CDN ===== */
    function loadOne(url){ return new Promise((res,rej)=>{ const s=document.createElement('script'); s.src=url; s.async=true; s.onload=res; s.onerror=rej; document.head.appendChild(s); }); }
    async function loadFrom(urls, global){ for(const u of urls){ try{ await loadOne(u); await new Promise(r=>setTimeout(r,20)); if(!global || window[global]) return true; }catch(e){} } return false; }

    const HTML5QR = [
      'assets/js/html5-qrcode.min.js',
      'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.10/minified/html5-qrcode.min.js',
      'https://unpkg.com/html5-qrcode@2.3.10/minified/html5-qrcode.min.js'
    ];
    const QRCODE = [
      'assets/js/qrcode.min.js',
      'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js',
      'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js'
    ];
    const JSQR = [
      'assets/js/jsQR.min.js',
      'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js',
      'https://cdnjs.cloudflare.com/ajax/libs/jsqr/1.4.0/jsQR.min.js'
    ];

    (async()=>{ await Promise.all([ loadFrom(HTML5QR,'Html5Qrcode'), loadFrom(QRCODE,'QRCode'), loadFrom(JSQR,'jsQR') ]); })();

    // ===== helpers/API =====
    const qs=s=>document.querySelector(s);
    const connDot=qs('#connDot'), connText=qs('#connText');
    function setStatus(kind,text){ connDot.classList.remove('ok','err'); if(kind==='ok')connDot.classList.add('ok'); if(kind==='err')connDot.classList.add('err'); if(text)connText.textContent=text; }
    function escapeHtml(s){ return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]) ); }
    function toastRow(msg, ok=true){ const row=document.createElement('div'); row.className='row'; row.innerHTML=`<span class="${ok?'ok-badge':'err-badge'}">${ok?'OK':'ERR'}</span><span>${escapeHtml(msg)}</span><span class="muted-sm" style="margin-left:auto">${new Date().toLocaleTimeString('el-GR')}</span>`; const box=qs('#scanLog'); if(box.textContent==='—') box.textContent=''; box.prepend(row); while(box.children.length>60) box.lastChild.remove(); }

    async function api(action,{method='GET',body=null,params=null}={}) {
      const u=new URL('api.php',location.href); u.searchParams.set('action',action);
      if(params) for(const[k,v] of Object.entries(params)) u.searchParams.set(k,v);
      const r=await fetch(u,{method,headers:{'Content-Type':'application/json'},body:body?JSON.stringify(body):null});
      if(!r.ok) throw new Error(await r.text()); return r.json();
    }
    (async()=>{ try{ await api('ping'); setStatus('ok','Συνδεδεμένο'); }catch{ setStatus('err','Μη συνδεδεμένο'); } })();

    // ===== σάρωση =====
    let html5Qr, currentCamId=null, isRunning=false, lastPayload=null;

    function parseMemberId(text){
      const s=String(text||'').trim();
      let m=s.match(/^GYM:(\d{1,10})$/i); if(m) return Number(m[1]);
      m=s.match(/^(\d{1,10})$/); if(m) return Number(m[1]);
      return null;
    }
    async function markAttendance(memberId){
      await api('upsert_attendance',{method:'POST',body:{member_id:Number(memberId)}});
    }

    async function listCameras(){
      if(!window.Html5Qrcode){ qs('#cameraSel').innerHTML='<option>— library δεν φορτώθηκε —</option>'; return; }
      try{
        const cams=await Html5Qrcode.getCameras();
        const sel=qs('#cameraSel'); sel.innerHTML='';
        cams.forEach(c=>{ const o=document.createElement('option'); o.value=c.id; o.textContent=c.label||('Camera '+(sel.length+1)); if(/back|rear|environment/i.test(c.label)) o.selected=true; sel.appendChild(o); });
        currentCamId=sel.value || (cams[0]&&cams[0].id) || null;
      }catch(e){ qs('#cameraSel').innerHTML='<option>— Καμία κάμερα —</option>'; }
    }

    async function startCamera(){
      if(isRunning) return;
      if(!window.Html5Qrcode){ toastRow('Δεν φορτώθηκε το html5-qrcode. Χρησιμοποίησε «Σάρωση από φωτογραφία».',false); return; }
      if(location.protocol!=='https:' && location.hostname!=='localhost'){
        toastRow('Ζωντανή κάμερα μόνο σε HTTPS/localhost. Χρησιμοποίησε «Σάρωση από φωτογραφία».',false); return;
      }
      html5Qr=html5Qr || new Html5Qrcode('reader',false);
      if(!currentCamId) await listCameras();
      if(!currentCamId){ toastRow('Δεν βρέθηκε κάμερα',false); return; }

      const cfg={ fps:10, qrbox:{width:260,height:260}, rememberLastUsedCamera:true };
      await html5Qr.start({deviceId:{exact:currentCamId}}, cfg,
        async (text) => {
          if(text===lastPayload) return;
          lastPayload=text;
          const id=parseMemberId(text);
          if(!id){ toastRow('Μη έγκυρος κωδικός: '+text,false); return; }
          try{ await markAttendance(id); toastRow(`✅ Παρουσία για #${id}`,true); } catch(e){ toastRow('Σφάλμα API: '+(e?.message||'—'),false); }
          setTimeout(()=>lastPayload=null,1200);
        }, ()=>{});
      isRunning=true;
    }
    async function stopCamera(){ if(!isRunning) return; await html5Qr.stop(); isRunning=false; }

    qs('#btnStart').addEventListener('click', startCamera);
    qs('#btnStop').addEventListener('click', stopCamera);
    qs('#cameraSel').addEventListener('change', e=>{ currentCamId=e.target.value; if(isRunning){ stopCamera().then(startCamera); }});
    window.addEventListener('load', async ()=>{
      await listCameras();
      // auto-start αν είμαστε HTTPS/localhost και υπάρχει library
      if((location.protocol==='https:' || location.hostname==='localhost') && window.Html5Qrcode){
        startCamera();
      }
    });

    // === Σάρωση από φωτογραφία (δουλεύει και σε HTTP) ===
    const fileScan=qs('#fileScan');
    qs('#btnScanPhoto').addEventListener('click', ()=>fileScan.click());

    function readImageData(file){
      return new Promise((resolve,reject)=>{
        const fr=new FileReader();
        fr.onload=()=>{ const img=new Image(); img.onload=()=>{
            const c=document.createElement('canvas'); c.width=img.naturalWidth||img.width; c.height=img.naturalHeight||img.height;
            const ctx=c.getContext('2d'); ctx.drawImage(img,0,0); const data=ctx.getImageData(0,0,c.width,c.height);
            resolve({data,canvas:c});
          }; img.onerror=()=>reject(new Error('Αποτυχία φόρτωσης εικόνας')); img.src=fr.result; };
        fr.onerror=()=>reject(new Error('Αποτυχία ανάγνωσης αρχείου')); fr.readAsDataURL(file);
      });
    }

    fileScan.addEventListener('change', async e=>{
      const f=e.target.files && e.target.files[0]; if(!f) return;
      try{
        if(isRunning && window.Html5Qrcode) await stopCamera();

        // 1) Αν υπάρχει html5-qrcode, δοκίμασέ το
        if(window.Html5Qrcode){
          const temp=new Html5Qrcode('reader',false);
          let text=null;
          if(temp.scanFileV2){ const r=await temp.scanFileV2(f,true); text=r?.decodedText||r?.text||r; }
          else { text=await temp.scanFile(f,true); }
          const id=parseMemberId(text); if(!id) throw new Error('Μη έγκυρος κωδικός: '+String(text));
          await markAttendance(id); toastRow(`✅ Παρουσία για #${id}`,true);
          return;
        }

        // 2) Fallback: jsQR
        if(!window.jsQR) throw new Error('QR βιβλιοθήκη δεν φορτώθηκε');
        const {data,canvas}=await readImageData(f);
        const code=jsQR(data.data,data.width,data.height); if(!code||!code.data) throw new Error('Δεν βρέθηκε QR');
        qs('#reader').innerHTML=''; canvas.style.maxWidth='100%'; qs('#reader').appendChild(canvas);
        const id=parseMemberId(code.data); if(!id) throw new Error('Μη έγκυρος κωδικός: '+String(code.data));
        await markAttendance(id); toastRow(`✅ Παρουσία για #${id}`,true);
      }catch(err){ toastRow('Σφάλμα σάρωσης εικόνας: '+(err?.message||'—'),false); }
      finally{ fileScan.value=''; }
    });

    // ===== QR για μέλη =====
    const qrList=qs('#qrList');
    function makeQR(el,text){ el.innerHTML=''; if(!window.QRCode){ el.innerHTML='<div class="muted">⚠️ Δεν φορτώθηκε η QR βιβλιοθήκη.</div>'; return; }
      new QRCode(el,{text,width:140,height:140,colorDark:"#000",colorLight:"#fff",correctLevel:QRCode.CorrectLevel.H}); }
    async function loadAllMembers(){
      qrList.innerHTML='<div class="muted">Φόρτωση…</div>';
      try{
        const rows=await api('members_basic',{params:{q:''}});
        if(!rows?.length){ qrList.innerHTML='<div class="muted">— Κανένα μέλος —</div>'; qs('#btnPrintAll').disabled=true; return; }
        qrList.innerHTML='';
        for(const r of rows){
          const id=r.id; const full=`${r.first_name||''} ${r.last_name||''}`.trim(); const payload=`GYM:${id}`;
          const card=document.createElement('div'); card.className='qr-card'; card.innerHTML=`
            <div class="qr-img"><div id="qr_${id}"></div></div>
            <div style="flex:1; min-width:160px">
              <div style="font-weight:800">#${id} — ${escapeHtml(full||'—')}</div>
              <div class="muted-sm" style="margin-top:2px">QR: <span class="kbd">${payload}</span></div>
              <div class="qr-actions" style="margin-top:8px">
                <button class="btn small" data-dl="${id}">Λήψη PNG</button>
                <button class="btn small ghost" data-print="${id}">Εκτύπωση</button>
              </div>
            </div>`; qrList.appendChild(card); makeQR(card.querySelector('#qr_'+id),payload);
        }
        qs('#btnPrintAll').disabled=false;
      }catch(e){ qrList.innerHTML=`<div class="muted">❌ ${escapeHtml(e?.message||'—')}</div>`; qs('#btnPrintAll').disabled=true; }
    }
    qrList.addEventListener('click',e=>{
      const dl=e.target?.dataset?.dl, pr=e.target?.dataset?.print;
      if(dl){
        const img=qs('#qr_'+dl).querySelector('img')||qs('#qr_'+dl).querySelector('canvas');
        try{ const png=(img.tagName==='IMG')?img.src:img.toDataURL('image/png'); const a=document.createElement('a'); a.href=png; a.download=`qr_member_${dl}.png`; a.click(); }catch{}
      }
      if(pr){
        const img=qs('#qr_'+pr).querySelector('img')||qs('#qr_'+pr).querySelector('canvas');
        const png=(img.tagName==='IMG')?img.src:img.toDataURL('image/png');
        const w=window.open('','_blank');
        w.document.write(`<html><head><title>QR #${pr}</title></head><body style="display:grid;place-items:center;margin:0;padding:20px;background:#fff;font-family:system-ui,sans-serif"><img src="${png}" style="width:260px;height:260px;image-rendering:pixelated"/><div style="margin-top:10px;font-weight:700">Μέλος #${pr}</div></body></html>`); w.document.close(); w.focus(); w.print();
      }
    });
    qs('#btnLoadAll').addEventListener('click',loadAllMembers);
    qs('#btnPrintAll').addEventListener('click',()=>{
      const items=Array.from(qrList.querySelectorAll('.qr-card')); if(!items.length) return;
      const imgs=items.map(card=>{ const img=card.querySelector('img')||card.querySelector('canvas'); const id=card.querySelector('[data-dl]')?.dataset?.dl||''; const name=card.querySelector('div[style*="font-weight:800"]')?.textContent||''; try{ const png=(img.tagName==='IMG')?img.src:img.toDataURL('image/png'); return {png,id,name}; }catch{return null;} }).filter(Boolean);
      const w=window.open('','_blank'); w.document.write('<html><head><title>QR Members</title></head><body style="margin:0;padding:24px;background:#fff;font-family:system-ui,sans-serif;display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px">');
      for(const it of imgs){ w.document.write(`<div style="border:1px solid #ddd;border-radius:10px;padding:12px;text-align:center"><img src="${it.png}" style="width:200px;height:200px;image-rendering:pixelated"/><div style="margin-top:8px;font-weight:700">${escapeHtml(it.name)}</div></div>`); }
      w.document.write('</body></html>'); w.document.close(); w.focus(); w.print();
    });
  </script>
</body>
</html>
