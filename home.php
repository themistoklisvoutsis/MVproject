<?php /* home.php — Αρχική χωρίς sidebar, 4 κάρτες (ομοιόμορφη 2x2 διάταξη) */ ?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Αρχική</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ====== Topbar (χωρίς sidebar) ====== */
    html, body { height: 100%; }
    .topbar{
      position: sticky; top:0; z-index:10;
      display:flex; align-items:center; justify-content:space-between; gap:12px;
      padding:14px 18px;
      background: linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.005));
      border-bottom: 1px solid rgba(255,255,255,.06);
      backdrop-filter: blur(6px);
    }
    .brand-line{ display:flex; align-items:center; gap:10px }
    .brand-line .logo{ width:28px; height:28px; border-radius:8px; background: linear-gradient(135deg, var(--accent2), var(--accent)); box-shadow: 0 6px 18px rgba(255,209,0,.25) }
    .brand-line h1{
      margin:0; font-size:18px; font-weight:900; letter-spacing:.2px;
      background: linear-gradient(90deg, #fff, var(--accent2));
      -webkit-background-clip: text; background-clip:text; color: transparent;
    }
    .status{ display:flex; align-items:center; gap:8px; color:var(--muted); font-size:12px }
    .dot{ width:10px; height:10px; border-radius:50%; background:#555; box-shadow:0 0 0 3px rgba(255,255,255,.06) }
    .dot.ok{ background: var(--ok) }
    .dot.err{ background: var(--danger) }

    .container{ max-width: 980px; margin: 0 auto; padding: 22px 18px 28px; }

    /* ====== Επικεφαλίδα/τίτλος ====== */
    .hero{ text-align:center; padding: 8px 12px 4px; }
    .hero h2{ margin:0 0 6px 0; font-size:24px; font-weight:900; letter-spacing:.3px; }
    .hero .sub{ color:var(--muted); font-size:14px }

    /* ====== Πλέγμα 2×2 ====== */
    .tiles{
      display:grid;
      grid-template-columns: 1fr;
      grid-auto-rows: 1fr;
      gap: 18px;
      margin-top: 18px;
      align-items: stretch;
      justify-items: stretch;
    }
    @media (min-width: 720px){
      .tiles{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* ====== Κάρτες ====== */
    .tile{
      position: relative;
      display:grid;
      grid-template-columns: 56px 1fr;
      align-items:center; gap:14px;
      padding:18px;
      min-height: 132px;
      border-radius:20px;
      background: linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.01));
      border: 1px solid var(--border);
      box-shadow: 0 14px 40px rgba(0,0,0,.6), inset 0 1px 0 rgba(255,255,255,.03);
      color: inherit; text-decoration:none;
      transition: transform .12s ease, box-shadow .2s ease, border-color .2s ease, background .2s ease;
      outline: none;
    }
    .tile::before{
      content:""; position:absolute; left:0; top:0; bottom:0; width:3px;
      border-radius:20px 0 0 20px; background: rgba(255, 209, 0, .22);
      transition: background .2s ease, width .12s ease;
    }
    .tile .ic{
      width:56px; height:56px; border-radius:14px; display:grid; place-items:center;
      background: #0b0b0b; border:1px solid rgba(255,255,255,.12);
      box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
    }
    .tile h3{ margin:0; font-size:16px; font-weight:800; letter-spacing:.2px; }
    .tile p{ margin:6px 0 0 0; font-size:13px; color: var(--muted); }

    .tile:hover{
      transform: translateY(-2px);
      box-shadow: 0 20px 50px rgba(0,0,0,.75);
      border-color: rgba(255, 209, 0, .28);
      background: linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.012));
    }
    .tile:hover::before{ background: linear-gradient(180deg, var(--accent2), var(--accent)); width:4px; }

    .tile:active{ transform: translateY(0) scale(.99); }

    /* Focus προσβάσιμο */
    .tile:focus-visible{
      box-shadow: 0 0 0 3px rgba(255,209,0,.18), 0 20px 50px rgba(0,0,0,.75);
      border-color: rgba(255, 209, 0, .35);
    }

    /* μικρότερες οθόνες */
    @media (max-width:560px){
      .tile{ grid-template-columns:42px 1fr; padding:16px; min-height:118px; }
      .tile .ic{ width:42px; height:42px; border-radius:10px; }
    }

    /* Tooltip μικρό για shortcuts */
    .hint{
      position:absolute; right:12px; top:12px;
      font-size:11px; color:#fff27a; opacity:.9;
      padding:3px 7px; border-radius:999px;
      background: rgba(255, 209, 0, .12);
      border: 1px solid rgba(255, 209, 0, .35);
    }

    /* Προαιρετική απαλή αιώρηση pointer */
    @media (hover:hover) and (prefers-reduced-motion:no-preference){
      .tile[data-tilt="1"]:hover{ transform: translateY(-2px) perspective(800px) rotateX(1deg) rotateY(-1deg); }
    }
  </style>
</head>
<body>
  <!-- Top bar χωρίς sidebar -->
  <header class="topbar" role="banner">
    <div class="brand-line" aria-label="Επωνυμία">
      <div class="logo" aria-hidden="true"></div>
      <h1>Dashboard</h1>
    </div>
    <div class="status" aria-live="polite">
      <span id="connDot" class="dot" aria-hidden="true"></span>
      <span id="connText">Έλεγχος…</span>
    </div>
  </header>

  <main class="container" role="main">
    <div class="hero">
      <h2>Επιλογές</h2>
      <div class="sub">Διάλεξε ενότητα για διαχείριση</div>
    </div>

    <!-- 4 κάρτες/κουμπιά (2×2 σε desktop) -->
    <nav class="tiles" role="navigation" aria-label="Κύριες επιλογές">
      <a class="tile" href="members.php" data-tilt="1" role="link" aria-label="Μέλη — Προσθήκη, αναζήτηση και επεξεργασία">
        <span class="hint" aria-hidden="true">1</span>
        <div class="ic" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm6 8v-1a5 5 0 0 0-5-5H11a5 5 0 0 0-5 5v1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </div>
        <div>
          <h3>Μέλη</h3>
          <p>Προσθήκη, αναζήτηση & επεξεργασία μελών</p>
        </div>
      </a>

      <a class="tile" href="attendance.php" data-tilt="1" role="link" aria-label="Παρουσίες — Καταχώριση και προβολή ανά ημέρα">
        <span class="hint" aria-hidden="true">2</span>
        <div class="ic" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 2v4M16 2v4M4 9h16M5 6h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </div>
        <div>
          <h3>Παρουσίες</h3>
          <p>Καταχώριση & προβολή παρουσιών ανά ημέρα</p>
        </div>
      </a>

      <a class="tile" href="reports.php" data-tilt="1" role="link" aria-label="Αναφορές — Σύνολα και γραφήματα">
        <span class="hint" aria-hidden="true">3</span>
        <div class="ic" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 20V6M8 20v-6M12 20V4M16 20v-9M20 20v-3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </div>
        <div>
          <h3>Αναφορές</h3>
          <p>Σύνολα & γραφήματα σε εύρος ημερομηνιών</p>
        </div>
      </a>

      <a class="tile" href="card.php" data-tilt="1" role="link" aria-label="Καρτέλα Αθλητή — Στοιχεία & σωματομετρήσεις">
        <span class="hint" aria-hidden="true">4</span>
        <div class="ic" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/>
            <circle cx="9" cy="12" r="2.4" stroke="currentColor" stroke-width="1.6"/>
            <path d="M6.5 16.5c1-1.4 3-1.6 4.9-1.6 1.8 0 3.7.2 4.9 1.6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            <path d="M14.5 9.5h4M14.5 12h4M14.5 14.5h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </div>
        <div>
          <h3>Καρτέλα Αθλητή</h3>
          <p>Στοιχεία μέλους & σωματομετρήσεις (εκτύπωση)</p>
        </div>
      </a>
    </nav>
  </main>

  <!-- Toasts container (ήδη υποστηρίζεται από το CSS σου) -->
  <div class="toasts" id="toasts" aria-live="polite" aria-atomic="true"></div>

  <script>
    // —— Helpers ——
    const $ = s => document.querySelector(s);
    const $$ = s => Array.from(document.querySelectorAll(s));

    // Toasts
    function toast(msg, kind='ok', timeout=3500){
      const wrap = $('#toasts'); if(!wrap) return;
      const el = document.createElement('div');
      el.className = `toast ${kind}`;
      el.textContent = msg;
      wrap.appendChild(el);
      setTimeout(()=>{ el.remove(); }, timeout);
    }

    // Ένδειξη σύνδεσης με ελαφρύ backoff
    const connDot = $('#connDot'), connText = $('#connText');
    function setStatus(kind, text){
      connDot.classList.remove('ok','err');
      if(kind==='ok') connDot.classList.add('ok');
      if(kind==='err') connDot.classList.add('err');
      if(text) connText.textContent = text;
    }

    async function pingLoop(){
      let delay = 0, step = 4000, max = 20000;
      while(true){
        await new Promise(r => setTimeout(r, delay || 0));
        try{
          const u = new URL('api.php', location.href); u.searchParams.set('action','ping');
          const r = await fetch(u, {cache:'no-store'});
          if(!r.ok) throw new Error('bad');
          if(delay>0) toast('Επανασυνδέθηκε', 'ok', 2200);
          setStatus('ok','Συνδεδεμένο');
          delay = step; // ping ανά 4s όταν είναι οκ
        }catch(e){
          setStatus('err','Μη συνδεδεμένο');
          toast('Απώλεια σύνδεσης', 'err', 2200);
          delay = Math.min((delay||step) * 1.5, max);
        }
      }
    }
    pingLoop(); // fire-and-forget

    // —— Keyboard UX: 1–4 ανοίγουν κάρτες, βελάκια περιήγηση, Enter/Space ενεργοποίηση ——
    const tiles = $$('.tile');
    function focusTile(i){
      if(!tiles.length) return;
      const idx = (i + tiles.length) % tiles.length;
      tiles[idx].focus();
      tiles[idx].scrollIntoView({block:'nearest', behavior:'smooth'});
      tiles.current = idx;
    }
    tiles.forEach((t,i)=> t.tabIndex = 0);
    tiles.current = 0;

    document.addEventListener('keydown', (e)=>{
      // shortcuts 1-4
      if(e.key>='1' && e.key<='4'){
        const idx = Number(e.key)-1;
        const t = tiles[idx];
        if(t){ t.click(); e.preventDefault(); }
        return;
      }
      // arrows for navigation
      const cols = (matchMedia('(min-width: 720px)').matches) ? 2 : 1;
      if(['ArrowRight','ArrowDown','ArrowLeft','ArrowUp'].includes(e.key)){
        let n = tiles.current ?? 0;
        if(e.key==='ArrowRight') n += 1;
        if(e.key==='ArrowLeft')  n -= 1;
        if(e.key==='ArrowDown')  n += cols;
        if(e.key==='ArrowUp')    n -= cols;
        focusTile(n);
        e.preventDefault();
      }
      // activate
      if(e.key==='Enter' || e.key===' '){
        const t = tiles[tiles.current ?? 0];
        if(t){ t.click(); e.preventDefault(); }
      }
    });

    // μικρό hover-tilt που απενεργοποιείται αν ο χρήστης προτιμά reduced motion
    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    if(!reduced){
      tiles.forEach(tile=>{
        tile.addEventListener('pointermove', (e)=>{
          if(tile.dataset.tilt!=='1') return;
          const r = tile.getBoundingClientRect();
          const x = (e.clientX - r.left) / r.width - .5;
          const y = (e.clientY - r.top)  / r.height - .5;
          tile.style.transform = `translateY(-2px) perspective(800px) rotateX(${y*-2}deg) rotateY(${x*2}deg)`;
        });
        tile.addEventListener('pointerleave', ()=>{
          tile.style.transform = '';
        }, {passive:true});
      });
    }
    // —— Staggered fade-in στις κάρτες (μια-μια) ——
(function(){
  const tiles = Array.from(document.querySelectorAll('.tiles .tile'));
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!tiles.length || reduced) return;

  // Αρχική κρυφή κατάσταση
  tiles.forEach(t => t.classList.add('_hidden'));

  // Όταν «μπει» το grid στο viewport, κάνε stagger τις κάρτες
  const grid = document.querySelector('.tiles');
  const reveal = () => {
    tiles.forEach((t, i) => {
      setTimeout(() => {
        t.classList.add('_show');
        t.classList.remove('_hidden');
      }, i * 120); // διάστημα μεταξύ καρτών (ms)
    });
    io.disconnect();
  };

  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) reveal();
    });
  }, { threshold: 0.15 });

  if (grid) io.observe(grid);
})();

  </script>
</body>
</html>
