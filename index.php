<?php /* index.php — Frontend UI που μιλάει στο api.php (Supabase proxy) */ ?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Παρουσιολόγιο — Supabase (backend proxy)</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    #page-clientele .card { position: relative; }
    #sumMemberSelect { position: relative; z-index: 5; pointer-events: auto; }
  </style>
</head>
<body>
  <div class="app">
    <!-- ===== Sidebar / Topbar ===== -->
    <aside class="sidebar">
      <div class="brand">
        <div class="logo"></div>
        <h1>Παρουσιολόγιο <span class="pill">Supabase · μέσω backend</span></h1>
      </div>
      <div class="status" style="margin:8px 0 14px">
        <span id="connDot" class="dot" title="Κατάσταση σύνδεσης"></span>
        <span id="connText">Μη συνδεδεμένο</span>
      </div>
      <nav class="nav" id="spaNav">
        <a href="#/members" data-page="members" class="active">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm7 9v-2a5 5 0 0 0-5-5H10a5 5 0 0 0-5 5v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          Μέλη
        </a>
        <a href="#/attendance" data-page="attendance">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 2v4M16 2v4M4 9h16M7 13h2m3 0h2m3 0h2M7 17h2m3 0h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          Παρουσίες
        </a>
        <a href="#/reports" data-page="reports">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 20V6a2 2 0 0 1 2-2h9λ5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Zm11-13v-3λ5 5h-3a2 2 0 0 1-2-2Z" stroke="currentColor" stroke-width="1.5"/></svg>
          Αναφορές
        </a>
        <a href="#/clientele" data-page="clientele">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm6 8v-1a5 5 0 0 0-5-5H11a5 5 0 0 0-5 5v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          Πελατολόγιο
        </a>
        <!-- ΝΕΟ: Καρτέλα Αθλητή -->
        <a href="#/card" data-page="card">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm6 8v-1a5 5 0 0 0-5-5H11a5 5 0 0 0-5 5v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          Καρτέλα Αθλητή
        </a>
        <a href="#/settings" data-page="settings">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="1.5"/><path d="M19.4 15a1 1 0 0 1 .2 1.1l-.6 1a1 1 0 0 1-1 .5l-1.2-.2a6.7 6.7 0 0 1-1.2.7l-.2 1.2a1 1 0 0 1-.7.9l-1.2.4a1 1 0 0 1-1.1-.3l-.8-.9a7.5 7.5 0 0 1-1.4 0l-.8.9a1 1 0 0 1-1.1.3l-1.2-.4a1 1 0 0 1-.7-.9l-.2-1.2-1.2-.7-1.2.2a1 1 0 0 1-1-.5l-.6-1a1 1 0 0 1 .2-1.1l.9-1c.1.5.1.9.1 1.4λ.9 1Z" stroke="currentColor" stroke-width="1.5"/></svg>
          Ρυθμίσεις
        </a>
      </nav>
    </aside>

    <!-- ===== Main content ===== -->
    <main class="content">
      <!-- Page: Members -->
      <section class="page active" id="page-members">
        <div class="grid">
          <div class="card">
            <h2>Προσθήκη μέλους</h2>
            <form id="addMemberForm">
              <div class="row">
                <div><label>Όνομα</label><input id="firstName" placeholder="π.χ. Γιώργος" required /></div>
                <div><label>Επώνυμο</label><input id="lastName" placeholder="π.χ. Παπαδόπουλος" required /></div>
              </div>
              <div class="row" style="margin-top:10px">
                <button class="btn" type="submit">Αποθήκευση</button>
                <output id="addMemberMsg" class="muted"></output>
              </div>
            </form>
          </div>

          <div class="card">
            <h2>Αναζήτηση / Διαχείριση μελών</h2>
            <div class="row">
              <input id="searchQ" placeholder="Αναζήτηση σε όνομα/επώνυμο..." />
              <button id="btnSearch" class="btn" type="button">Αναζήτηση</button>
            </div>
            <div id="membersList" style="margin-top:10px" class="muted table-wrap">—</div>
          </div>
        </div>
      </section>

      <!-- Page: Attendance -->
      <section class="page" id="page-attendance">
        <div class="grid">
          <div class="card">
            <h2>Δήλωση παρουσίας</h2>
            <form id="markAttendanceForm">
              <div class="row">
                <div><label>ID μέλους</label><input id="memberId" type="number" placeholder="π.χ. 42" required /></div>
                <div><label>Ημερομηνία (προαιρετικό)</label><input id="attDate" type="date" /></div>
              </div>
              <div style="margin-top:10px"><label>Σχόλιο (προαιρετικό)</label><input id="note" placeholder="π.χ. Πρωινή προπόνηση" /></div>
              <div class="row" style="margin-top:10px">
                <button class="btn" type="submit">Καταχώριση</button>
                <output id="markMsg" class="muted"></output>
              </div>
            </form>
          </div>

          <div class="card">
            <h2>Παρουσίες ανά ημερομηνία</h2>
            <div class="toolbar">
              <button id="prevDate" class="btn small ghost" type="button">←</button>
              <input id="filterDate" type="date" />
              <button id="nextDate" class="btn small ghost" type="button">→</button>
              <div class="spacer"></div>
              <button id="btnToday" class="btn small ghost" type="button">Σήμερα</button>
              <button id="btnFilterDate" class="btn small" type="button">Προβολή</button>
            </div>
            <div id="todayList" class="muted table-wrap">—</div>
          </div>
        </div>
      </section>

      <!-- Page: Reports -->
      <section class="page" id="page-reports">
        <div class="card">
          <h2>Αναφορές παρουσιών</h2>
          <div class="toolbar" style="flex-wrap:wrap">
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
              Τύπος διαγράμματος
              <select id="repChartType">
                <option value="bar" selected>Bar</option>
                <option value="line">Line</option>
              </select>
            </label>
          </div>
          <div style="background:rgba(255,255,255,.02); border:1px solid var(--border); border-radius:16px; padding:10px">
            <canvas id="attChart" height="260"></canvas>
          </div>
          <div id="reportsArea" class="muted" style="margin-top:8px"></div>
        </div>
      </section>

      <!-- Page: Clientele (Πελατολόγιο) -->
      <section class="page" id="page-clientele">

        <!-- ΚΑΡΤΑ ΜΕΛΟΥΣ (ΣΥΝΟΨΗ) -->
        <div class="card">
          <h2>Κάρτα μέλους (σύνοψη)</h2>
          <div class="row" style="gap:8px; flex-wrap:wrap;">
            <label style="min-width:280px">
              Επιλογή μέλους
              <select id="sumMemberSelect"></select>
            </label>
            <div class="spacer"></div>
            <span class="muted" id="sumStatus">—</span>
          </div>

          <div class="grid" style="grid-template-columns: repeat(4, minmax(160px,1fr)); gap:12px; margin-top:10px">
            <div class="stat-box"><div class="muted">Όνομα</div><div class="big" id="sumFirst">—</div></div>
            <div class="stat-box"><div class="muted">Επώνυμο</div><div class="big" id="sumLast">—</div></div>
            <div class="stat-box"><div class="muted">Παρουσίες από μηδενισμό</div><div class="big" id="sumSince">—</div></div>
            <div class="stat-box"><div class="muted">Παρουσίες έτους</div><div class="big" id="sumYear">—</div></div>
          </div>

          <div class="grid" style="grid-template-columns: repeat(4, minmax(160px,1fr)); gap:12px; margin-top:10px">
            <div class="stat-box"><div class="muted">Κιλά τελευταίας ζύγισης</div><div class="big" id="sumLastWeight">—</div></div>
          </div>
        </div>

        <!-- Master–detail Πελατολογίου -->
        <div class="grid" style="grid-template-columns: 1fr 1.2fr; gap:16px; margin-top:16px;">
          <div class="card">
            <h2>Πελατολόγιο</h2>
            <div class="row" style="gap:8px; flex-wrap:wrap;">
              <input id="clSearch" placeholder="Αναζήτηση ονόματος/επωνύμου..." />
              <button id="clSearchBtn" class="btn small" type="button">Αναζήτηση</button>
              <div class="spacer"></div>
              <span class="muted" id="clCount">—</span>
            </div>
            <div id="clList" class="muted table-wrap" style="margin-top:10px; max-height: 60vh; overflow:auto">—</div>
          </div>

          <div class="card" id="clDetailCard">
            <h2>Κάρτα πελάτη</h2>
            <div id="clDetailHeader" class="muted" style="margin-bottom:8px">Επίλεξε μέλος από αριστερά.</div>
            <div class="row">
              <div style="flex:1">
                <label>Ημερομηνία ζύγισης</label>
                <input id="clWeightDate" type="date" />
              </div>
              <div style="flex:1">
                <label>Βάρος (kg)</label>
                <input id="clWeightKg" type="number" step="0.1" min="0" placeholder="π.χ. 72.5" />
              </div>
            </div>
            <div class="row" style="margin-top:10px">
              <button id="clSaveWeight" class="btn" type="button" disabled>Αποθήκευση</button>
              <output id="clMsg" class="muted"></output>
            </div>
            <div class="muted" style="margin-top:12px">Ιστορικό βάρους</div>
            <div id="clWeightHistory" class="muted" style="max-height:260px; overflow:auto; border:1px solid var(--border); border-radius:10px; padding:8px; margin-top:4px">—</div>
          </div>
        </div>
      </section>

      <!-- ΝΕΑ ΣΕΛΙΔΑ: Athlete Card -->
      <section class="page" id="page-card">
        <div class="card" id="athleteCard">
          <style>
            @media print{
              .sidebar, .toolbar{ display:none !important; }
              body, .content, #athleteCard{ background:#fff !important; color:#000 !important; }
              #athleteCard *{ color:#000 !important; background:#fff !important; }
              table.sheet-table th, table.sheet-table td{ border:1px solid #000 !important; }
            }
            .sheet-grid{display:grid;grid-template-columns:140px 1fr 140px;gap:12px;align-items:center;margin-bottom:8px;}
            .logo-box{display:flex;align-items:center;justify-content:center}
            .sheet-title{text-align:center;font-weight:900;font-size:20px;letter-spacing:.6px}
            .fields{margin-top:6px}
            .field-row{display:grid;grid-template-columns:220px 1fr;gap:10px;margin:6px 0}
            .field-label{color:#cfcfcf;font-size:13px}
            .table-wrap{overflow:auto;border-radius:12px;border:1px solid var(--border)}
            .sheet-table{width:100%;border-collapse:collapse}
            .sheet-table th,.sheet-table td{border:1px solid var(--border);padding:8px;text-align:left}
          </style>

          <div class="toolbar" style="margin:-6px 0 8px">
            <h2 style="margin:0">Καρτέλα Αθλητή</h2>
            <div class="spacer"></div>
            <select id="cardMemberSelect"></select>
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
      </section>

      <!-- Page: Settings -->
      <section class="page" id="page-settings">
        <div class="card">
          <h2>Ρυθμίσεις</h2>
          <p class="muted">Η σύνδεση γίνεται αυτόματα μέσω backend (api.php).</p>
          <div class="row" style="margin-top:10px">
            <a class="btn ghost" href="#/members">Κλείσιμο</a>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Modal Επεξεργασίας μέλους -->
  <div id="memberModal" class="modal" hidden>
    <div class="modal-card card">
      <h2 style="margin:6px 0 12px">Επεξεργασία μέλους</h2>
      <div class="row">
        <div><label>Όνομα</label><input id="editFirst" /></div>
        <div><label>Επώνυμο</label><input id="editLast" /></div>
      </div>
      <div class="row" style="margin-top:8px">
        <div>
          <label>Μετρητής από (counter_epoch)</label>
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

  <div class="toasts" id="toasts"></div>

  <script>
  // ---------- DOM refs ----------
  const qs = s => document.querySelector(s);
  const nav = qs('#spaNav');

  // Status
  const connDot  = qs('#connDot');
  const connText = qs('#connText');
  function setStatus(kind,text){
    connDot.classList.remove('ok','err');
    if(kind==='ok') connDot.classList.add('ok');
    if(kind==='err') connDot.classList.add('err');
    if(text) connText.textContent=text;
  }

  // Pages
  const pages = {
    members: qs('#page-members'),
    attendance: qs('#page-attendance'),
    reports: qs('#page-reports'),
    clientele: qs('#page-clientele'),
    card: qs('#page-card'),            // ΝΕΟ
    settings: qs('#page-settings')
  };

  // Members
  const addMemberForm = qs('#addMemberForm');
  const addMemberMsg  = qs('#addMemberMsg');
  const membersList   = qs('#membersList');
  const btnSearch     = qs('#btnSearch');
  const searchInput   = qs('#searchQ');

  // Attendance
  const markForm   = qs('#markAttendanceForm');
  const markMsg    = qs('#markMsg');
  const filterDate = qs('#filterDate');
  const todayList  = qs('#todayList');

  // Modal
  const memberModal = qs('#memberModal');
  const btnCloseMember = qs('#btnCloseMember');
  const btnSaveMember  = qs('#btnSaveMember');
  const editFirst = qs('#editFirst');
  const editLast  = qs('#editLast');
  const editEpoch = qs('#editEpoch');
  const statSince = qs('#statSince');
  const statYear  = qs('#statYear');
  const memberMsg = qs('#memberMsg');

  // Reports
  const repGranularity = document.getElementById('repGranularity');
  const repFrom = document.getElementById('repFrom');
  const repTo = document.getElementById('repTo');
  const repRun = document.getElementById('repRun');
  const repPresetMonth = document.getElementById('repPresetMonth');
  const repPreset30 = document.getElementById('repPreset30');
  const repPresetYTD = document.getElementById('repPresetYTD');
  const repTotal = document.getElementById('repTotal');
  const repChartType = document.getElementById('repChartType');
  const attChartCanvas = document.getElementById('attChart');

  // ---------- helpers ----------
  let searchTimer=null, editingMember=null, membersLoadToken=0, chartInstance=null;
  const pad2=n=>String(n).padStart(2,'0');
  const todayISO = () => { const d=new Date(); return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; };
  const yearFromISO = iso => Number((iso||todayISO()).slice(0,4));
  const yearStartEnd = y => [`${y}-01-01`,`${y}-12-31`];

  function escapeHtml(s){ return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]) ); }
  function open(el){ el.hidden=false } function close(el){ el.hidden=true }
  function fmt(d){ return new Date(d).toLocaleDateString('el-GR'); }
  function disableUI(dis){ document.querySelectorAll('form input, form button, .toolbar .btn, .toolbar input, .btn.small').forEach(el=>el.disabled=dis); }
  function toast(msg,kind='ok',timeout=2500){ const box=document.createElement('div'); box.className=`toast ${kind}`; box.innerHTML=escapeHtml(msg); qs('#toasts').appendChild(box); setTimeout(()=>box.remove(),timeout); }
  function addDays(iso, delta){ const [y,m,d]=(iso||todayISO()).split('-').map(Number); const dt=new Date(y,m-1,d); dt.setDate(dt.getDate()+delta); return `${dt.getFullYear()}-${pad2(dt.getMonth()+1)}-${pad2(dt.getDate())}`; }

  // Reports helpers
  function startOfDay(d){ const x=new Date(d); x.setHours(0,0,0,0); return x; }
  function toISO_d(d){ return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; }
  function startOfWeek(d){ const x=startOfDay(d); const day=(x.getDay()||7); x.setDate(x.getDate()-(day-1)); return x; }
  function startOfMonth(d){ return new Date(d.getFullYear(), d.getMonth(), 1); }
  function startOfYear(d){ return new Date(d.getFullYear(), 0, 1); }

  function buildRangePreset(preset){
    const today=startOfDay(new Date());
    if(preset==='month'){ const a=startOfMonth(today); const b=new Date(a.getFullYear(), a.getMonth()+1, 0); return [toISO_d(a), toISO_d(b)]; }
    if(preset==='30'){ const b=today; const a=new Date(b); a.setDate(a.getDate()-29); return [toISO_d(a), toISO_d(b)]; }
    if(preset==='ytd'){ const a=startOfYear(today); const b=today; return [toISO_d(a), toISO_d(b)]; }
    return [toISO_d(today), toISO_d(today)];
  }
  function aggregatorKey(dateStr, gran){
    const d=new Date(dateStr);
    if(gran==='day') return toISO_d(d);
    if(gran==='week') return toISO_d(startOfWeek(d));
    if(gran==='month') return `${d.getFullYear()}-${pad2(d.getMonth()+1)}`;
    if(gran==='year') return String(d.getFullYear());
    return toISO_d(d);
  }
  function labelPretty(key, gran){
    if(gran==='day') return new Date(key).toLocaleDateString('el-GR');
    if(gran==='week') return `Εβδ. ${new Date(key).toLocaleDateString('el-GR')}`;
    if(gran==='month'){ const [y,m]=key.split('-'); return `${m}/${y}`; }
    if(gran==='year') return key;
    return key;
  }

  // ---------- API helper ----------
  async function api(action, { method='GET', body=null, params=null } = {}){
    const url = new URL('api.php', location.href);
    url.searchParams.set('action', action);
    if (params) for (const [k,v] of Object.entries(params)) url.searchParams.set(k, v);
    const res = await fetch(url.toString(), {
      method,
      headers: { 'Content-Type':'application/json' },
      body: body ? JSON.stringify(body) : null
    });
    if (!res.ok) { const t = await res.text(); throw new Error(`API ${action} failed: ${t}`); }
    return res.json();
  }

  // ---------- SPA Router ----------
  function setActive(page){
    Object.values(pages).forEach(p=>p.classList.remove('active'));
    const el = pages[page] || pages.members; el.classList.add('active');
    nav.querySelectorAll('a').forEach(a=>a.classList.toggle('active', a.dataset.page===page));
    if(page==='attendance' && !filterDate.value){ filterDate.value=todayISO(); }
  }
  function handleHash(){
    const page=(location.hash.replace('#/','')||'members').split('?')[0];
    setActive(page);
    if (page==='clientele') clLoadList();
    if (page==='card') cardLoadMembers();   // ΝΕΟ: όταν ανοίγει η Καρτέλα
  }
  window.addEventListener('hashchange', handleHash);

  // ---------- Data ops ----------
  async function addMember(first,last){
    const r = await api('add_member', { method:'POST', body:{ first_name:first, last_name:last } }); return r.id;
  }
  async function upsertAttendance(memberId,dateVal,note){
    return await api('upsert_attendance',{ method:'POST', body:{ member_id:memberId, attended_on:dateVal||todayISO(), note:note||null }});
  }
  async function countAttendanceFor(memberId, fromISO=null, toISO=null){
    const r = await api('count_attendance', { params:{ member_id:String(memberId), ...(fromISO?{from:fromISO}:{}) , ...(toISO?{to:toISO}:{}) } });
    return r.count||0;
  }

  // ---------- UI: Members ----------
  async function loadMembers(){
    const myToken=Date.now(); membersLoadToken=myToken;
    const q = searchInput.value.trim(); membersList.textContent='Φόρτωση…';
    try{
      const data = await api('search_members', { params:{ q } });
      if (membersLoadToken!==myToken) return;
      if(!data?.length){ membersList.textContent='— Καμία εγγραφή —'; return; }

      const y = yearFromISO(filterDate.value||todayISO());
      const rows = data.map(m=>`
        <tr data-id="${m.id}">
          <td><span class="badge copy" data-copy="${m.id}">#${m.id}</span></td>
          <td>${escapeHtml(m.first_name||'')}</td>
          <td>${escapeHtml(m.last_name||'')}</td>
          <td><span class="count-since" data-id="${m.id}">${Number(m.total_since_reset||0)}</span> | <span class="count-year" data-id="${m.id}">…</span></td>
          <td style="text-align:right;white-space:nowrap">
            <button class="btn small ghost quick-mark" type="button">✔️ Παρ. στη μέρα</button>
            <button class="btn small ghost reset-one" type="button" title="Μηδενισμός μετρητή">0️⃣</button>
            <button class="btn small ghost edit-member" type="button" title="Επεξεργασία">✎</button>
          </td>
        </tr>`).join('');

      membersList.innerHTML = `<table>
        <thead><tr><th>ID</th><th>Όνομα</th><th>Επώνυμο</th><th>Μετρητής | Έτος ${y}</th><th></th></tr></thead>
        <tbody>${rows}</tbody>
      </table>`;

      // Actions...
      membersList.querySelectorAll('.reset-one').forEach(btn =>
        btn.addEventListener('click', async ev => {
          ev.stopPropagation();
          const tr = ev.currentTarget.closest('tr');
          const id = Number(tr.getAttribute('data-id'));
          if (!confirm(`Μηδενισμός μετρητή για #${id};`)) return;
          try{
            await api('reset_counter', { method:'POST', body:{ id }});
            toast(`🔄 Μηδενίστηκε ο μετρητής για #${id}`);
            await loadMembers();
            await loadAttendance(filterDate.value || todayISO());
          }catch(err){ toast(err.message,'err',3500); }
        })
      );
      membersList.querySelectorAll('.badge.copy').forEach(b=>b.addEventListener('click',ev=>{
        const id=ev.currentTarget.getAttribute('data-copy'); navigator.clipboard?.writeText(id); toast(`Αντιγράφηκε ID #${id}`);
      }));
      membersList.querySelectorAll('.quick-mark').forEach(btn=>btn.addEventListener('click', async ev=>{
        const tr=ev.currentTarget.closest('tr'); const id=Number(tr.getAttribute('data-id'));
        try{ await upsertAttendance(id, filterDate.value||todayISO(), null); toast(`✅ Παρουσία για #${id}`); location.hash = '#/attendance'; loadAttendance(filterDate.value); loadMembers(); }
        catch(err){ toast(err.message,'err',3000); }
      }));
      membersList.querySelectorAll('.edit-member').forEach(btn=>btn.addEventListener('click', async ev=>{
        ev.stopPropagation(); const id=Number(ev.currentTarget.closest('tr').getAttribute('data-id')); openMemberEditor(id);
      }));
      membersList.querySelectorAll('tbody tr').forEach(tr=>addRowClickHandler(tr)); // helper κάτω

      const [yStart, yEnd] = yearStartEnd(y);
      const totals = await api('year_totals', { params:{ from:yStart, to:yEnd } });
      for (const m of data) {
        const el2 = membersList.querySelector(`.count-year[data-id="${m.id}"]`);
        if (el2) el2.textContent = totals[m.id] || 0;
      }
    }catch(err){
      if (membersLoadToken===myToken) membersList.innerHTML=`❌ ${escapeHtml(err.message)}`;
    }
  }
  function addRowClickHandler(tr){
    tr.addEventListener('click',ev=>{
      if (ev.target.closest('.quick-mark') || ev.target.classList.contains('badge') || ev.target.closest('.edit-member')) return;
      location.hash = '#/attendance'; qs('#memberId').value = tr.getAttribute('data-id');
    });
  }

  async function openMemberEditor(id){
    try{
      const recs = await api('get_member', { params:{ id:String(id) } });
      const m = Array.isArray(recs) ? recs[0] : null;
      if (!m) throw new Error('Δεν βρέθηκε το μέλος.');
      editingMember = m;
      editFirst.value = m.first_name || '';
      editLast.value  = m.last_name  || '';
      editEpoch.value = (m.counter_epoch || todayISO()).slice(0,10);
      const y = yearFromISO(filterDate.value||todayISO());
      const [yStart,yEnd] = yearStartEnd(y);
      statSince.textContent = Number(m.total_since_reset) || 0;
      statYear.textContent  = '…';
      open(memberModal);
      const yearTotal = await countAttendanceFor(m.id, yStart, yEnd);
      statYear.textContent  = yearTotal;
    }catch(err){ toast(err.message,'err',3500); }
  }

  // ---------- UI: Attendance ----------
  async function loadAttendance(dateISO){
    todayList.textContent='Φόρτωση…';
    try{
      const data = await api('list_attendance', { params:{ date: dateISO } });
      if(!data?.length){
        todayList.textContent='— Καμία παρουσία για την επιλεγμένη ημερομηνία —';
        return;
      }
      const totalCount = data.filter(r => r.present).length;
      todayList.innerHTML=`
        <div class="muted" style="margin-bottom:8px">Σύνολο παρουσιών: <strong>${totalCount}</strong></div>
        <table>
          <thead><tr><th>Μέλος</th><th>Ημερομηνία</th><th>Σχόλιο</th><th></th></tr></thead>
          <tbody>${
            data.map(r=>`
              <tr data-att-id="${r.id}" data-present="${r.present?'1':'0'}">
                <td><span class="kbd">#${r.members.id}</span> ${escapeHtml(r.members.first_name)} ${escapeHtml(r.members.last_name)}</td>
                <td>${fmt(r.attended_on)} ${r.present?'✔️':'✖️'}</td>
                <td>${escapeHtml(r.note||'')}</td>
                <td style="text-align:right;white-space:nowrap">
                  <button class="btn small ghost btn-edit-att" type="button" title="Επεξεργασία σημείωσης">✎</button>
                  <button class="btn small ghost btn-toggle-att" type="button" title="Εναλλαγή παρ./απ.">↔︎</button>
                </td>
              </tr>`).join('')}
          </tbody>
        </table>`;
      todayList.querySelectorAll('.btn-edit-att').forEach(btn=>btn.addEventListener('click', async ev=>{
        const tr=ev.currentTarget.closest('tr'); const id=Number(tr.getAttribute('data-att-id'));
        const current = tr.children[2].textContent || '';
        const newNote = prompt('Σχόλιο:', current); if(newNote===null) return;
        try{
          await api('update_attendance_note', { method:'POST', body:{ id, note:newNote }});
          toast('✅ Ενημερώθηκε'); loadAttendance(filterDate.value);
        }catch(err){ toast(err.message,'err',3500); }
      }));
      todayList.querySelectorAll('.btn-toggle-att').forEach(btn=>btn.addEventListener('click', async ev=>{
        const tr=ev.currentTarget.closest('tr'); const id=Number(tr.getAttribute('data-att-id'));
        const cur = tr.getAttribute('data-present')==='1';
        try{
          await api('toggle_attendance', { method:'POST', body:{ id, present:cur }});
          toast('🔁 Άλλαξε'); loadAttendance(filterDate.value); loadMembers();
        }catch(err){ toast(err.message,'err',3500); }
      }));
    }catch(err){
      todayList.innerHTML=`❌ ${escapeHtml(err.message)}`;
    }
  }

  // Date toolbar
  document.getElementById('btnFilterDate').addEventListener('click', ()=>{ loadAttendance(filterDate.value||todayISO()); loadMembers(); });
  filterDate.addEventListener('change', ()=>{ loadAttendance(filterDate.value||todayISO()); loadMembers(); });
  document.getElementById('prevDate').addEventListener('click', ()=>{ filterDate.value=addDays(filterDate.value,-1); loadAttendance(filterDate.value); loadMembers(); });
  document.getElementById('nextDate').addEventListener('click', ()=>{ filterDate.value=addDays(filterDate.value, 1); loadAttendance(filterDate.value); loadMembers(); });
  document.getElementById('btnToday').addEventListener('click', ()=>{ filterDate.value=todayISO(); loadAttendance(filterDate.value); loadMembers(); });

  // Search
  document.getElementById('btnSearch').addEventListener('click', loadMembers);
  document.getElementById('searchQ').addEventListener('input', ()=>{ clearTimeout(searchTimer); searchTimer=setTimeout(loadMembers,300); });
  document.getElementById('searchQ').addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); loadMembers(); } });

  // ---------- Reports ----------
  async function runReport(){
    const gran = repGranularity.value;
    const [defFrom, defTo] = buildRangePreset('month');
    const fromISOv = repFrom.value || defFrom;
    the_toISOv   = repTo.value   || defTo;

    const data = await api('report_attendance', { params:{ from:fromISOv, to:the_toISOv } });
    const agg = new Map();
    for(const r of (data||[])){
      const k = aggregatorKey(r.attended_on, gran);
      agg.set(k, (agg.get(k)||0)+1);
    }
    const keys = Array.from(agg.keys()).sort();
    const values = keys.map(k=>agg.get(k));
    const labels = keys.map(k=>labelPretty(k, gran));
    repTotal.textContent = values.reduce((a,b)=>a+b,0);

    const ctx = attChartCanvas.getContext('2d');
    if(chartInstance){ chartInstance.destroy(); }
    chartInstance = new Chart(ctx, {
      type: repChartType.value,
      data: { labels, datasets: [{ label: 'Παρουσίες', data: values }] },
      options: {
        responsive: true, maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { precision:0 } } },
        plugins: { legend: { display:false }, tooltip: { callbacks: { label: (c)=>` ${c.parsed.y} παρουσίες` } } }
      }
    });
  }
  function wireReports(){
    const [a,b] = buildRangePreset('month'); repFrom.value=a; repTo.value=b; repGranularity.value='day';
    repPresetMonth.addEventListener('click', ()=>{ const [a,b]=buildRangePreset('month'); repFrom.value=a; repTo.value=b; repGranularity.value='day'; runReport(); });
    repPreset30.addEventListener('click',   ()=>{ const [a,b]=buildRangePreset('30');   repFrom.value=a; repTo.value=b; repGranularity.value='day'; runReport(); });
    repPresetYTD.addEventListener('click',  ()=>{ const [a,b]=buildRangePreset('ytd');  repFrom.value=a; repTo.value=b; repGranularity.value='month'; runReport(); });
    repRun.addEventListener('click', runReport);
    repChartType.addEventListener('change', runReport);
    repGranularity.addEventListener('change', runReport);
  }

  /* ===== Κάρτα μέλους (σύνοψη) ===== */
  const sumMemberSelect = document.getElementById('sumMemberSelect');
  const sumStatus = document.getElementById('sumStatus');
  const sumFirst = document.getElementById('sumFirst');
  const sumLast = document.getElementById('sumLast');
  const sumSince = document.getElementById('sumSince');
  const sumYear = document.getElementById('sumYear');
  const sumLastWeight = document.getElementById('sumLastWeight');

  async function sumPopulateMembers(){
    try{
      sumStatus.textContent = 'Φόρτωση μελών…';
      const rows = await api('members_basic', { params:{ q:'' }});
      if (!Array.isArray(rows) || rows.length===0){
        sumMemberSelect.innerHTML = '';
        sumMemberSelect.disabled = true;
        sumStatus.textContent = 'Δεν βρέθηκαν μέλη';
        return;
      }
      sumMemberSelect.disabled = false;
      sumMemberSelect.innerHTML = rows.map(r =>
        `<option value="${r.id}">#${r.id} — ${escapeHtml(r.first_name||'')} ${escapeHtml(r.last_name||'')}</option>`
      ).join('');
      sumStatus.textContent = `${rows.length} μέλη`;
      sumMemberSelect.value = rows[0].id;
      await sumLoadSelected();
    }catch(err){
      console.error(err);
      sumStatus.textContent = 'Σφάλμα στη φόρτωση';
      sumMemberSelect.innerHTML = '';
      sumMemberSelect.disabled = true;
    }
  }

  async function tryFetchLastWeight(memberId){
    try{
      const arr = await api('list_weights', { params:{ member_id:String(memberId) }});
      if (Array.isArray(arr) && arr.length){
        const w = arr[0];
        return `${w.weight_kg} kg (${fmt(w.measured_on)})`;
      }
    }catch(e){}
    return null;
  }

  async function sumLoadSelected(){
    const id = Number(sumMemberSelect.value);
    if (!id){ return; }
    sumFirst.textContent = '…'; sumLast.textContent = '…'; sumSince.textContent = '…'; sumYear.textContent = '…'; sumLastWeight.textContent = '…';

    try{
      const recs = await api('get_member', { params:{ id:String(id) }});
      const m = Array.isArray(recs) ? recs[0] : null;
      if (!m){ throw new Error('Δεν βρέθηκε το μέλος'); }

      sumFirst.textContent = m.first_name || '—';
      sumLast.textContent  = m.last_name  || '—';
      sumSince.textContent = Number(m.total_since_reset||0);

      const y = (new Date()).getFullYear();
      const yStart = `${y}-01-01`; const yEnd = `${y}-12-31`;
      const yearTotal = await countAttendanceFor(id, yStart, yEnd);
      sumYear.textContent = yearTotal;

      const lw = await tryFetchLastWeight(id);
      sumLastWeight.textContent = lw || '—';
    }catch(err){
      console.error(err);
      sumFirst.textContent = sumLast.textContent = sumSince.textContent = sumYear.textContent = sumLastWeight.textContent = '—';
      toast(err.message,'err',3500);
    }
  }
  sumMemberSelect?.addEventListener('change', sumLoadSelected);

  // ---------- Πελατολόγιο (master–detail) ----------
  async function membersWithLatestWeight(q=''){
    try{ return await api('members_with_latest_weight', { params:{ ...(q?{q}:{}) }}); }
    catch(e){ const rows = await api('search_members', { params:{ q }}); return rows.map(r=>({ ...r, weights: [] })); }
  }
  async function listWeights(memberId, fromISO=null, toISO=null){
    return api('list_weights', { params:{ member_id:String(memberId), ...(fromISO?{from:fromISO}:{}) , ...(toISO?{to:toISO}:{}) }});
  }
  async function addWeight(memberId, weightKg, measuredOn=null){
    return api('add_weight', { method:'POST', body:{ member_id:memberId, weight_kg:Number(weightKg), measured_on: measuredOn || null }});
  }

  const clSearch = document.getElementById('clSearch');
  const clSearchBtn = document.getElementById('clSearchBtn');
  const clCount = document.getElementById('clCount');
  const clList = document.getElementById('clList');
  const clDetailHeader = document.getElementById('clDetailHeader');
  const clWeightDate = document.getElementById('clWeightDate');
  const clWeightKg = document.getElementById('clWeightKg');
  const clSaveWeight = document.getElementById('clSaveWeight');
  const clMsg = document.getElementById('clMsg');
  const clWeightHistory = document.getElementById('clWeightHistory');
  let clCurrentMember = null;

  async function clLoadList(){
    clList.textContent = 'Φόρτωση…';
    try{
      const q = (clSearch?.value || '').trim();
      const rows = await membersWithLatestWeight(q);
      clCount.textContent = rows?.length ? `${rows.length} μέλη` : '0 μέλη';
      if (!rows?.length){ clList.textContent = '— Κανένα μέλος —'; return; }

      const htmlRows = rows.map(m=>{
        const last = Array.isArray(m.weights) && m.weights.length ? m.weights[0] : null;
        const lastVal = last ? `${last.weight_kg} kg (${fmt(last.measured_on)})` : '—';
        return `
          <tr class="cl-item" data-id="${m.id}" data-first="${escapeHtml(m.first_name||'')}" data-last="${escapeHtml(m.last_name||'')}">
            <td><span class="badge">#${m.id}</span></td>
            <td>${escapeHtml(m.first_name||'')}</td>
            <td>${escapeHtml(m.last_name||'')}</td>
            <td>${lastVal}</td>
          </tr>`;
      }).join('');

      clList.innerHTML = `
        <table>
          <thead><tr><th>ID</th><th>Όνομα</th><th>Επώνυμο</th><th>Τελευταίο βάρος</th></tr></thead>
          <tbody>${htmlRows}</tbody>
        </table>`;

      clList.querySelectorAll('.cl-item').forEach(tr=>{
        tr.addEventListener('click', ()=>{
          const member = { id:Number(tr.getAttribute('data-id')), first_name: tr.getAttribute('data-first'), last_name: tr.getAttribute('data-last') };
          clSelectMember(member);
        });
      });

      if (!clCurrentMember && rows.length){
        clSelectMember({ id: rows[0].id, first_name: rows[0].first_name, last_name: rows[0].last_name });
      }
    }catch(err){
      clList.innerHTML = `❌ ${escapeHtml(err.message)}`;
    }
  }
  async function clSelectMember(member){
    clCurrentMember = member;
    clDetailHeader.innerHTML = `Μέλος: <strong>#${member.id}</strong> — ${escapeHtml(member.first_name||'')} ${escapeHtml(member.last_name||'')}`;
    clWeightDate.value = todayISO();
    clWeightKg.value = '';
    clMsg.textContent = '';
    clSaveWeight.disabled = false;
    clWeightHistory.textContent = 'Φόρτωση…';
    try{
      const hist = await listWeights(member.id);
      if (!hist?.length) { clWeightHistory.textContent = '— Κανένα ιστορικό —'; return; }
      clWeightHistory.innerHTML = hist.map(r=>`<div>${fmt(r.measured_on)}: <strong>${r.weight_kg} kg</strong></div>`).join('');
    }catch(err){
      clWeightHistory.innerHTML = `❌ ${escapeHtml(err.message)}`;
    }
  }
  clSaveWeight?.addEventListener('click', async ()=>{
    if (!clCurrentMember) return;
    const date = clWeightDate.value || todayISO();
    const kg = clWeightKg.value.trim();
    if (!kg){ clMsg.textContent='Συμπλήρωσε βάρος.'; return; }
    try{
      clMsg.textContent='Αποθήκευση…';
      await addWeight(clCurrentMember.id, kg, date);
      clMsg.textContent='✅ Έγινε';
      toast('✅ Καταχωρήθηκε βάρος');
      const hist = await listWeights(clCurrentMember.id);
      clWeightHistory.innerHTML = hist.map(r=>`<div>${fmt(r.measured_on)}: <strong>${r.weight_kg} kg</strong></div>`).join('');
      await clLoadList();
      if (Number(sumMemberSelect.value) === clCurrentMember.id) sumLoadSelected();
    }catch(err){
      clMsg.innerHTML = `❌ ${escapeHtml(err.message)}`;
      toast(err.message,'err',3500);
    }
  });

  document.getElementById('clSearchBtn')?.addEventListener('click', clLoadList);
  document.getElementById('clSearch')?.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); clLoadList(); }});

  // -------- Add Member submit ----------
  addMemberForm?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    addMemberMsg.textContent='Αποθήκευση…';
    try{
      const id = await addMember(qs('#firstName').value.trim(), qs('#lastName').value.trim());
      addMemberMsg.textContent = `✅ Προστέθηκε #${id}`;
      addMemberForm.reset();
      await loadMembers();
    }catch(err){
      addMemberMsg.innerHTML=`❌ ${escapeHtml(err.message)}`;
    }
  });

  // ===== Καρτέλα Αθλητή: JS =====
  async function cardLoadMembers(){
    const sel = document.getElementById('cardMemberSelect');
    sel.innerHTML = '<option>Φόρτωση…</option>';
    try{
      const rows = await api('list_members');
      if(!Array.isArray(rows) || !rows.length){ sel.innerHTML=''; return; }
      sel.innerHTML = rows.map(r=>`<option value="${r.id}">#${r.id} — ${escapeHtml(r.first_name||'')} ${escapeHtml(r.last_name||'')}</option>`).join('');
      cardLoadCard(sel.value);
    }catch(e){
      sel.innerHTML = '';
      toast(e.message,'err',3500);
    }
  }

  async function cardLoadCard(id){
    const m1 = await api('member_get', { params:{ id:String(id) } });
    const d = (m1 && m1.data) || {};
    document.getElementById('f_fullname').value = `${d.first_name||''} ${d.last_name||''}`.trim();
    document.getElementById('f_dob').value = d.dob || '';
    document.getElementById('f_address').value = d.address || '';
    document.getElementById('f_phone').value = d.phone || '';
    document.getElementById('f_email').value = d.email || '';
    document.getElementById('f_medical').value = d.medical_notes || '';

    const js = await api('meas_list', { params:{ member_id:String(id) } });
    const tbody = document.querySelector('#measTable tbody');
    tbody.innerHTML = '';
    (js.data||[]).forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r.measured_on??''}</td>
        <td>${r.weight_kg??''}</td>
        <td>${r.fat_percent??''}</td>
        <td>${r.twb??''}</td>
        <td>${r.mbw??''}</td>
        <td>${r.kcal??''}</td>
        <td>${r.bones??''}</td>
        <td>${r.visceral??''}</td>
        <td><button class="btn small ghost" data-del="${r.id}">Διαγραφή</button></td>`;
      tbody.appendChild(tr);
    });
  }

  document.getElementById('cardMemberSelect')?.addEventListener('change', e=>cardLoadCard(e.target.value));
  document.getElementById('btnPrintCard')?.addEventListener('click', ()=>window.print());

  document.getElementById('btnSaveMember')?.addEventListener('click', async ()=>{
    const id = document.getElementById('cardMemberSelect').value;
    const [first_name, ...rest] = (document.getElementById('f_fullname').value||'').trim().split(' ');
    const payload = {
      id,
      first_name: first_name || '',
      last_name: rest.join(' ') || '',
      dob: document.getElementById('f_dob').value || null,
      address: document.getElementById('f_address').value || null,
      phone: document.getElementById('f_phone').value || null,
      email: document.getElementById('f_email').value || null,
      medical_notes: document.getElementById('f_medical').value || null
    };
    try{
      await api('member_save', { method:'POST', body:payload });
      toast('✅ Αποθηκεύτηκε');
    }catch(e){ toast(e.message,'err',3500); }
  });

  document.getElementById('btnAddMeas')?.addEventListener('click', async ()=>{
    const member_id = document.getElementById('cardMemberSelect').value;
    const measured_on = document.getElementById('m_date').value;
    if(!measured_on){ toast('Βάλε ημερομηνία','err'); return; }
    const payload = {
      member_id,
      measured_on,
      weight_kg:  document.getElementById('m_weight').value || null,
      fat_percent:document.getElementById('m_fat').value   || null,
      twb:        document.getElementById('m_twb').value   || null,
      mbw:        document.getElementById('m_mbw').value   || null,
      kcal:       document.getElementById('m_kcal').value  || null,
      bones:      document.getElementById('m_bones').value || null,
      visceral:   document.getElementById('m_visc').value  || null
    };
    try{
      const js = await api('meas_add', { method:'POST', body:payload });
      toast('✅ Προστέθηκε μέτρηση');
      const r = js.data;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r.measured_on??''}</td>
        <td>${r.weight_kg??''}</td>
        <td>${r.fat_percent??''}</td>
        <td>${r.twb??''}</td>
        <td>${r.mbw??''}</td>
        <td>${r.kcal??''}</td>
        <td>${r.bones??''}</td>
        <td>${r.visceral??''}</td>
        <td><button class="btn small ghost" data-del="${r.id}">Διαγραφή</button></td>`;
      document.querySelector('#measTable tbody').prepend(tr);
      ['m_weight','m_fat','m_twb','m_mbw','m_kcal','m_bones','m_visc'].forEach(id=>document.getElementById(id).value='');
    }catch(e){ toast(e.message,'err',3500); }
  });

  document.querySelector('#measTable tbody')?.addEventListener('click', async (e)=>{
    const id = e.target?.dataset?.del;
    if(!id) return;
    if(!confirm('Διαγραφή μέτρησης;')) return;
    try{
      await api('meas_delete', { method:'POST', body:{ id:Number(id) } });
      e.target.closest('tr').remove();
    }catch(e2){ toast(e2.message,'err',3500); }
  });

  // ---------- Init ----------
  function setActive(page){
    Object.values(pages).forEach(p=>p.classList.remove('active'));
    const el = pages[page] || pages.members; el.classList.add('active');
    nav.querySelectorAll('a').forEach(a=>a.classList.toggle('active', a.dataset.page===page));
    if(page==='attendance' && !filterDate.value){ filterDate.value=todayISO(); }
  }
  function setActiveOnLoad(){ const page = (location.hash.replace('#/','')||'members').split('?')[0]; setActive(page); }
  async function initApp(){
    disableUI(true);
    setActiveOnLoad(); window.addEventListener('hashchange', handleHash);

    try{
      setStatus(null,'Έλεγχος σύνδεσης…');
      await api('ping');
      setStatus('ok','Συνδεδεμένο');
      disableUI(false);
    }catch(e){
      console.error(e);
      setStatus('err','Μη συνδεδεμένο');
    }

    if(!filterDate.value) filterDate.value = todayISO();
    wireReports();
    try { await loadMembers(); } catch(e){ console.warn(e); }
    try { await loadAttendance(filterDate.value); } catch(e){ console.warn(e); }
    try { await runReport(); } catch(e){ console.warn(e); }

    try { await sumPopulateMembers(); } catch(e){ console.warn(e); }
    if (location.hash.startsWith('#/clientele')) {
      try { await clLoadList(); } catch(e){ console.warn(e); }
    }
    if (location.hash.startsWith('#/card')) {
      try { await cardLoadMembers(); } catch(e){ console.warn(e); }
    }
  }
  document.addEventListener('DOMContentLoaded', initApp);
  </script>
</body>
</html>
