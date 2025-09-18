// api.supabase.js
(() => {
  // ---- ΡΥΘΜΙΣΗ SUPABASE ----
  const SUPABASE_URL = "https://wjsapjgmuplhpjzhilin.supabase.co";
  const SUPABASE_ANON_KEY =  "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Indqc2FwamdtdXBsaHBqemhpbGluIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTU0NDY1MTUsImV4cCI6MjA3MTAyMjUxNX0.7gJFVRoGfCrVVwyIAB_GWf_Xy85wBT4behIm73zQoZY"


  const T = {
    members: 'members',
    attendance: 'attendance',
    weights: 'weights',
    measurements: 'measurements',
  };

  const supa = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

  const todayISO = () => {
    const d = new Date();
    const pad = (n) => String(n).padStart(2,'0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  };
  const must = ({ data, error }) => { if (error) throw error; return data ?? null; };
  const num = (v, def=0) => (typeof v === 'number' && Number.isFinite(v)) ? v : def;

  async function countAttendance(member_id, fromISO=null, toISO=null){
    let q = supa.from(T.attendance).select('id', { count:'exact', head:false })
      .eq('member_id', Number(member_id)).eq('present', true);
    if (fromISO) q = q.gte('attended_on', fromISO);
    if (toISO)   q = q.lte('attended_on', toISO);
    const { count, error } = await q;
    if (error) throw error;
    return count || 0;
  }

  async function api(action, { method='GET', body=null, params=null } = {}) {

    if (action === 'ping') {
      const { error } = await supa.from(T.members).select('id', { head:true }).limit(1);
      if (error) throw error;
      return { ok:true };
    }

    // ---- MEMBERS ----
    if (action === 'add_member' && method === 'POST') {
      const p = body || {};
      const { data, error } = await supa.from(T.members).insert({
        first_name: p.first_name?.trim() || '',
        last_name:  p.last_name?.trim()  || '',
        counter_epoch: todayISO()
      }).select('id').single();
      if (error) throw error;
      return { id: data.id };
    }

    if (action === 'search_members') {
      const q = (params?.q || '').trim();
      let sel = supa.from(T.members).select('id, first_name, last_name, counter_epoch')
                   .order('id', { ascending:false });
      if (q) sel = sel.or(`first_name.ilike.%${q}%,last_name.ilike.%${q}%`);
      const data = must(await sel);
      const withCounts = await Promise.all(data.map(async m => {
        let c = 0;
        if (m.counter_epoch) { try { c = await countAttendance(m.id, m.counter_epoch, todayISO()); } catch {} }
        return { ...m, total_since_reset: c };
      }));
      return withCounts;
    }

    if (action === 'list_members') {
      const data = must(await supa.from(T.members).select('id, first_name, last_name, counter_epoch')
                            .order('id', { ascending:false }));
      const withCounts = await Promise.all(data.map(async m => {
        let c = 0;
        if (m.counter_epoch) { try { c = await countAttendance(m.id, m.counter_epoch, todayISO()); } catch {} }
        return { ...m, total_since_reset: c };
      }));
      return withCounts;
    }

    if (action === 'get_member') {
      const id = Number(params?.id);
      const { data, error } = await supa.from(T.members).select('*').eq('id', id).single();
      if (error) throw error;
      let c = 0;
      if (data.counter_epoch) { try { c = await countAttendance(id, data.counter_epoch, todayISO()); } catch {} }
      return { ...data, total_since_reset: c };
    }

    if (action === 'update_member' && method === 'POST') {
      const p = body || {};
      const upd = {
        first_name: p.first_name ?? undefined,
        last_name:  p.last_name  ?? undefined,
        counter_epoch: p.counter_epoch ?? undefined,
      };
      const { error } = await supa.from(T.members).update(upd).eq('id', Number(p.id));
      if (error) throw error;
      return { ok:true };
    }

    if (action === 'member_save' && method === 'POST') {
      const p = body || {};
      const upd = {
        first_name: p.first_name ?? undefined,
        last_name:  p.last_name  ?? undefined,
        dob: p.dob ?? undefined,
        address: p.address ?? undefined,
        phone: p.phone ?? undefined,
        email: p.email ?? undefined,
        medical_notes: p.medical_notes ?? undefined,
      };
      const { error } = await supa.from(T.members).update(upd).eq('id', Number(p.id));
      if (error) throw error;
      return { ok:true };
    }

    if (action === 'members_basic') {
      return must(await supa.from(T.members).select('id, first_name, last_name')
        .order('last_name').order('first_name'));
    }

    if (action === 'members_with_latest_weight') {
      const base = must(await supa.from(T.members).select('id, first_name, last_name')
        .order('last_name').order('first_name'));
      const out = [];
      for (const m of base) {
        const w = must(await supa.from(T.weights).select('measured_on, weight_kg')
          .eq('member_id', m.id).order('measured_on', { ascending:false }).limit(1));
        out.push({ ...m, weights: w || [] });
      }
      return out;
    }

    if (action === 'reset_counter' && method === 'POST') {
      const id = Number(body?.id);
      const { error } = await supa.from(T.members).update({ counter_epoch: todayISO() }).eq('id', id);
      if (error) throw error;
      return { ok:true };
    }

    // ---- ATTENDANCE ----
    if (action === 'upsert_attendance' && method === 'POST') {
      const p = body || {};
      const rec = {
        member_id: Number(p.member_id),
        attended_on: p.attended_on || todayISO(),
        note: p.note ?? null,
        present: true,
      };
      const { error } = await supa.from(T.attendance).upsert(rec, { onConflict: 'member_id,attended_on' });
      if (error) throw error;
      return { ok:true };
    }

    if (action === 'list_attendance') {
      const date = params?.date || todayISO();
      const { data, error } = await supa
        .from(T.attendance)
        .select('id, attended_on, note, present, members:member_id ( id, first_name, last_name )')
        .eq('attended_on', date)
        .order('id', { ascending:false });
      if (error) throw error;
      return data;
    }

    if (action === 'toggle_attendance' && method === 'POST') {
      const id = Number(body?.id);
      const cur = !!body?.present;
      const { error } = await supa.from(T.attendance).update({ present: !cur }).eq('id', id);
      if (error) throw error;
      return { ok:true };
    }

    if (action === 'update_attendance_note' && method === 'POST') {
      const id = Number(body?.id);
      const note = body?.note ?? null;
      const { error } = await supa.from(T.attendance).update({ note }).eq('id', id);
      if (error) throw error;
      return { ok:true };
    }

    if (action === 'count_attendance') {
      const id = Number(params?.member_id);
      const fromISO = params?.from || null;
      const toISO   = params?.to   || null;
      const count = await countAttendance(id, fromISO, toISO);
      return { count };
    }

    if (action === 'year_totals') {
      const fromISO = params?.from;
      const toISO   = params?.to;
      const { data, error } = await supa.from(T.attendance).select('member_id')
        .gte('attended_on', fromISO).lte('attended_on', toISO).eq('present', true);
      if (error) throw error;
      const agg = {};
      for (const r of data) agg[r.member_id] = (agg[r.member_id]||0)+1;
      return agg;
    }

    if (action === 'report_attendance') {
      const fromISO = params?.from;
      const toISO   = params?.to;
      const { data, error } = await supa.from(T.attendance).select('attended_on')
        .gte('attended_on', fromISO).lte('attended_on', toISO)
        .eq('present', true).order('attended_on');
      if (error) throw error;
      return data;
    }

    // ---- WEIGHTS / MEASUREMENTS ----
    if (action === 'list_weights') {
      const id = Number(params?.member_id);
      let sel = supa.from(T.weights).select('id, measured_on, weight_kg')
        .eq('member_id', id).order('measured_on', { ascending:false });
      if (params?.from) sel = sel.gte('measured_on', params.from);
      if (params?.to)   sel = sel.lte('measured_on', params.to);
      return must(await sel);
    }

    if (action === 'add_weight' && method === 'POST') {
      const p = body || {};
      const rec = {
        member_id: Number(p.member_id),
        measured_on: p.measured_on || todayISO(),
        weight_kg: num(p.weight_kg, null)
      };
      const { error } = await supa.from(T.weights).insert(rec);
      if (error) throw error;
      return { ok:true };
    }

    if (action === 'meas_list') {
      const id = Number(params?.member_id);
      const data = must(await supa
        .from(T.measurements)
        .select('id, measured_on, weight_kg, fat_percent, twb, mbw, kcal, bones, visceral')
        .eq('member_id', id)
        .order('measured_on', { ascending:false }));
      return { data };
    }

    if (action === 'meas_add' && method === 'POST') {
      const p = body || {};
      const rec = {
        member_id: Number(p.member_id),
        measured_on: p.measured_on || todayISO(),
        weight_kg: p.weight_kg ?? null,
        fat_percent: p.fat_percent ?? null,
        twb: p.twb ?? null,
        mbw: p.mbw ?? null,
        kcal: p.kcal ?? null,
        bones: p.bones ?? null,
        visceral: p.visceral ?? null,
      };
      const { data, error } = await supa.from(T.measurements).insert(rec)
        .select('id, measured_on, weight_kg, fat_percent, twb, mbw, kcal, bones, visceral').single();
      if (error) throw error;
      return data;
    }

    if (action === 'meas_delete' && method === 'POST') {
      const id = Number(body?.id);
      const { error } = await supa.from(T.measurements).delete().eq('id', id);
      if (error) throw error;
      return { ok:true };
    }

    throw new Error('Unsupported action: '+action);
  }

  window.api = api;
  window.__supabaseApi = { supa, tables: T, todayISO, countAttendance };
})();
