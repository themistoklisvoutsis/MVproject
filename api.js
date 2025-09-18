
// app.js — Κεντρικό JS που μιλά με το api.php μέσω fetch (AJAX)
// ΣΗΜΕΙΩΣΗ: Δεν εκθέτουμε ποτέ Supabase keys στο front-end. Όλα περνούν από το api.php.

const API_BASE = "../api.php"; // προσαρμόστε ανάλογα με το που θα ανεβάσετε τα αρχεία

// ---- Βασικά helpers fetch ----
async function httpGet(action, params = {}){
  const url = new URL(API_BASE, window.location.origin);
  url.searchParams.set("action", action);
  for (const [k,v] of Object.entries(params)) if (v !== undefined && v !== null) url.searchParams.set(k, v);
  const res = await fetch(url, { method: "GET" });
  return parseJSON(res);
}

async function httpPost(action, body = {}){
  const url = new URL(API_BASE, window.location.origin);
  url.searchParams.set("action", action);
  const res = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body)
  });
  return parseJSON(res);
}

async function httpPatch(action, body = {}){
  const url = new URL(API_BASE, window.location.origin);
  url.searchParams.set("action", action);
  const res = await fetch(url, {
    method: "PATCH",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body)
  });
  return parseJSON(res);
}

async function httpDelete(action, form = {}){
  const url = new URL(API_BASE, window.location.origin);
  url.searchParams.set("action", action);
  // για DELETE στο api.php -> χρησιμοποιεί $_POST, άρα στέλνουμε form-urlencoded
  const data = new URLSearchParams(form);
  const res = await fetch(url, { method: "POST", body: data });
  return parseJSON(res);
}

async function parseJSON(res){
  let data = null;
  try { data = await res.json(); } catch(e){ /* empty */ }
  if (!res.ok) {
    const msg = (data && (data.error || data.message)) || `HTTP ${res.status}`;
    throw new Error(msg);
  }
  return data;
}

// ---- API wrappers (ένα-προς-ένα με το api.php) ----
export const Api = {
  // health
  ping: () => httpGet("ping"),

  // μέλη
  addMember: ({ first_name, last_name }) => httpPost("add_member", { first_name, last_name }),
  searchMembers: (q = "") => httpGet("search_members", { q }),
  membersBasic: (q = "") => httpGet("members_basic", { q }),
  getMember: (id) => httpGet("get_member", { id }),
  updateMember: (payload) => httpPatch("update_member", payload),
  resetCounter: (id) => httpPatch("reset_counter", { id }),
  yearTotals: ({ from, to } = {}) => httpGet("year_totals", { from, to }),

  // παρουσίες
  upsertAttendance: (payload) => httpPost("upsert_attendance", payload),
  listAttendance: (date) => httpGet("list_attendance", { date }),
  updateAttendanceNote: (payload) => httpPatch("update_attendance_note", payload),
  toggleAttendance: (payload) => httpPatch("toggle_attendance", payload),
  countAttendance: ({ member_id, from, to }) => httpGet("count_attendance", { member_id, from, to }),
  reportAttendance: ({ from, to }) => httpGet("report_attendance", { from, to }),

  // βάρη
  addWeight: (payload) => httpPost("add_weight", payload),
  listWeights: ({ member_id, from, to }) => httpGet("list_weights", { member_id, from, to }),
  membersWithLatestWeight: (q = "") => httpGet("members_with_latest_weight", { q }),

  // καρτέλα αθλητή
  listMembers: () => httpGet("list_members"),
  memberGet: (id) => httpGet("member_get", { id }),
  memberSave: (payload) => httpPatch("member_save", payload),
  measList: (member_id) => httpGet("meas_list", { member_id }),
  measAdd: (payload) => httpPost("meas_add", payload),
  measDelete: (id) => httpDelete("meas_delete", { id }),
};

// ---- DEMO wiring για τα κουμπάκια του index.html ----
const $ = (sel) => document.querySelector(sel);

$("#btnPing").addEventListener("click", async () => {
  const out = $("#outPing");
  out.textContent = "τρέχει...";
  try {
    const r = await Api.ping();
    out.textContent = JSON.stringify(r, null, 2);
  } catch(err){ out.textContent = `Σφάλμα: ${err.message}`; }
});

$("#btnMembers").addEventListener("click", async () => {
  const out = $("#outMembers");
  out.textContent = "αναζήτηση...";
  try {
    const q = $("#qMembers").value.trim();
    const rows = await Api.searchMembers(q);
    out.textContent = JSON.stringify(rows, null, 2);
  } catch(err){ out.textContent = `Σφάλμα: ${err.message}`; }
});

$("#btnAdd").addEventListener("click", async () => {
  const out = $("#outAdd");
  out.textContent = "προσθήκη...";
  try {
    const first = $("#first").value.trim();
    const last  = $("#last").value.trim();
    const r = await Api.addMember({ first_name:first, last_name:last });
    out.textContent = JSON.stringify(r, null, 2);
  } catch(err){ out.textContent = `Σφάλμα: ${err.message}`; }
});

$("#btnAttendance").addEventListener("click", async () => {
  const out = $("#outAttendance");
  out.textContent = "φόρτωση...";
  try {
    const today = new Date().toISOString().slice(0,10);
    const rows = await Api.listAttendance(today);
    out.textContent = JSON.stringify(rows, null, 2);
  } catch(err){ out.textContent = `Σφάλμα: ${err.message}`; }
});
