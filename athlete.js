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
  const m1=await api('member_get',{params:{id:String(id)}}); const d=(m1&&m1.data)||{};
  document.getElementById('f_fullname').value=`${d.first_name||''} ${d.last_name||''}`.trim();
  document.getElementById('f_dob').value=d.dob||'';
  document.getElementById('f_address').value=d.address||'';
  document.getElementById('f_phone').value=d.phone||'';
  document.getElementById('f_email').value=d.email||'';
  document.getElementById('f_medical').value=d.medical_notes||'';

  const js=await api('meas_list',{params:{member_id:String(id)}}); const tbody=document.querySelector('#measTable tbody'); tbody.innerHTML='';
  (js.data||[]).forEach(r=>{
    const tr=document.createElement('tr'); tr.innerHTML=`
      <td>${r.measured_on??''}</td><td>${r.weight_kg??''}</td><td>${r.fat_percent??''}</td>
      <td>${r.twb??''}</td><td>${r.mbw??''}</td><td>${r.kcal??''}</td><td>${r.bones??''}</td><td>${r.visceral??''}</td>
      <td><button class="btn small ghost" data-del="${r.id}">Διαγραφή</button></td>`;
    tbody.appendChild(tr);
  });
  _staggerMeasRows();
}

document.getElementById('cardMemberSelect')?.addEventListener('change',e=>cardLoadCard(e.target.value));
document.getElementById('btnPrintCard')?.addEventListener('click',()=>window.print());

document.getElementById('btnSaveMember')?.addEventListener('click', async ()=>{
  const id=document.getElementById('cardMemberSelect').value;
  const [first_name,...rest]=(document.getElementById('f_fullname').value||'').trim().split(' ');
  const payload={ id, first_name:first_name||'', last_name:rest.join(' ')||'', dob:qs('#f_dob').value||null,
    address:qs('#f_address').value||null, phone:qs('#f_phone').value||null, email:qs('#f_email').value||null,
    medical_notes:qs('#f_medical').value||null };
  try{ await api('member_save',{method:'POST',body:payload}); toast('✅ Αποθηκεύτηκε'); }
  catch(e){ toast(e.message,'err',3500); }
});

document.getElementById('btnAddMeas')?.addEventListener('click', async ()=>{
  const member_id=qs('#cardMemberSelect').value, measured_on=qs('#m_date').value;
  if(!measured_on){ toast('Βάλε ημερομηνία','err'); return; }
  const payload={ member_id, measured_on,
    weight_kg:qs('#m_weight').value||null, fat_percent:qs('#m_fat').value||null,
    twb:qs('#m_twb').value||null, mbw:qs('#m_mbw').value||null, kcal:qs('#m_kcal').value||null,
    bones:qs('#m_bones').value||null, visceral:qs('#m_visc').value||null };
  try{
    const js=await api('meas_add',{method:'POST',body:payload}); toast('✅ Προστέθηκε'); const r=js.data;
    const tr=document.createElement('tr'); tr.classList.add('_hidden');
    tr.innerHTML=`<td>${r.measured_on??''}</td><td>${r.weight_kg??''}</td><td>${r.fat_percent??''}</td>
      <td>${r.twb??''}</td><td>${r.mbw??''}</td><td>${r.kcal??''}</td><td>${r.bones??''}</td><td>${r.visceral??''}</td>
      <td><button class="btn small ghost" data-del="${r.id}">Διαγραφή</button></td>`;
    qs('#measTable tbody').prepend(tr);
    setTimeout(()=>{ tr.classList.add('_show'); tr.classList.remove('_hidden'); },20);
    ['m_weight','m_fat','m_twb','m_mbw','m_kcal','m_bones','m_visc'].forEach(id=>qs('#'+id).value='');
  }catch(e){ toast(e.message,'err',3500); }
});

qs('#measTable tbody')?.addEventListener('click', async (e)=>{
  const id=e.target?.dataset?.del; if(!id) return;
  if(!confirm('Διαγραφή μέτρησης;')) return;
  try{ await api('meas_delete',{method:'POST',body:{id:Number(id)}}); e.target.closest('tr').remove(); }
  catch(e2){ toast(e2.message,'err',3500); }
});

/* Fade-in */
(function(){
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

function _staggerMeasRows(){
  if(matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const rows=document.querySelectorAll('#measTable tbody tr');
  rows.forEach(r=>r.classList.add('_hidden'));
  rows.forEach((r,i)=>{ setTimeout(()=>{ r.classList.add('_show'); r.classList.remove('_hidden'); }, i*60); });
}

cardLoadMembers();
