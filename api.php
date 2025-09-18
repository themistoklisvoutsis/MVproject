<?php
// api.php — PHP proxy προς Supabase REST

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS'); // +DELETE
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ===== ΣΤΟΙΧΕΙΑ ΣΥΝΔΕΣΗΣ (όπως μου τα έδωσες) =====
$SB_URL = "https://wjsapjgmuplhpjzhilin.supabase.co";
$SB_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Indqc2FwamdtdXBsaHBqemhpbGluIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTU0NDY1MTUsImV4cCI6MjA3MTAyMjUxNX0.7gJFVRoGfCrVVwyIAB_GWf_Xy85wBT4behIm73zQoZY";

// ===== Helpers =====
function build_query($params){
  $parts=[];
  foreach($params as $k=>$v){
    if (is_array($v)){
      foreach($v as $vv){ $parts[] = rawurlencode($k) . '=' . rawurlencode($vv); }
    } else if ($v !== null) {
      $parts[] = rawurlencode($k) . '=' . rawurlencode($v);
    }
  }
  return implode('&', $parts);
}

function sbreq($method, $path, $q = [], $body = null, $prefer = []) {
  global $SB_URL, $SB_KEY;
  $url = rtrim($SB_URL, '/') . '/rest/v1' . $path;
  if (!empty($q)) { $url .= '?' . build_query($q); }

  $ch = curl_init($url);
  $headers = [
    'apikey: ' . $SB_KEY,
    'Authorization: Bearer ' . $SB_KEY,
    'Content-Type: application/json',
    'Accept: application/json'
  ];
  if (!empty($prefer)) $headers[] = 'Prefer: ' . implode(',', $prefer);

  curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST   => $method,
    CURLOPT_HTTPHEADER      => $headers,
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_CONNECTTIMEOUT  => 6,
    CURLOPT_TIMEOUT         => 12,
    CURLOPT_FOLLOWLOCATION  => true,
    CURLOPT_USERAGENT       => 'php-proxy/1.0'
  ]);
  if (!is_null($body)) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

  $resp  = curl_exec($ch);
  $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err   = curl_error($ch);
  curl_close($ch);

  if ($resp === false) { http_response_code(502); echo json_encode(['error' => $err]); exit; }
  $data = json_decode($resp, true);
  if ($code < 200 || $code >= 300) {
    http_response_code($code);
    echo json_encode(['error' => $data['message'] ?? 'Supabase error', 'details' => $data]); exit;
  }
  return $data;
}

function injson() {
  $raw = file_get_contents('php://input');
  $d = json_decode($raw, true);
  return is_array($d) ? $d : [];
}

// ===== Router =====
$act = $_GET['action'] ?? '';

try {
  switch ($act) {

    /* ---------- Healthcheck: ΑΠΕΥΘΕΙΑΣ στο PostgREST ---------- */
    case 'ping': {
      try {
        sbreq('GET','/members', ['select'=>'id', 'limit'=>1]);
        echo json_encode(['ok'=>true]);
      } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
      }
      break;
    }

    /* -------------------- ΜΕΛΗ -------------------- */
    case 'add_member': {
      $in = injson();
      $first = trim($in['first_name'] ?? '');
      $last  = trim($in['last_name']  ?? '');
      if (!$first || !$last) { http_response_code(400); echo json_encode(['error'=>'first_name & last_name required']); break; }
      $r = sbreq('POST','/members', [], ['first_name'=>$first,'last_name'=>$last], ['return=representation']);
      echo json_encode(['id' => $r[0]['id'] ?? null]); break;
    }

    case 'search_members': {
      $q = trim($_GET['q'] ?? '');
      $params = [
        'select' => 'id,first_name,last_name,counter_epoch,total_since_reset',
        'order'  => 'id.desc',
        'limit'  => 500,
      ];
      if ($q !== '') {
        // PostgREST: or=(first_name.ilike.*q*,last_name.ilike.*q*)
        $params['or'] = sprintf('(first_name.ilike.*%1$s*,last_name.ilike.*%1$s*)', $q);
      }
      $rows = sbreq('GET','/v_member_stats', $params);
      echo json_encode($rows); break;
    }

    // ---- ΒΑΣΙΚΗ ΛΙΣΤΑ ΜΕΛΩΝ (χωρίς spread, χωρίς διπλό case)
    case 'members_basic': {
      $q = trim($_GET['q'] ?? '');
      try {
        // Προτίμηση στο view για να έχει και total_since_reset
        $params = [
          'select' => 'id,first_name,last_name,counter_epoch,total_since_reset',
          'order'  => 'id.asc',
          'limit'  => 10000
        ];
        if ($q !== '') {
          $params['or'] = sprintf('(first_name.ilike.*%1$s*,last_name.ilike.*%1$s*)', $q);
        }
        $rows = sbreq('GET', '/v_member_stats', $params);
      } catch (Throwable $e) {
        // Fallback στον πίνακα members
        $params = [
          'select' => 'id,first_name,last_name,counter_epoch',
          'order'  => 'id.asc',
          'limit'  => 10000
        ];
        if ($q !== '') {
          $params['or'] = sprintf('(first_name.ilike.*%1$s*,last_name.ilike.*%1$s*)', $q);
        }
        $rows = sbreq('GET', '/members', $params);
      }
      echo json_encode($rows); break;
    }

    // ---- ΣΥΝΟΛΑ ΕΤΟΥΣ ΓΙΑ ΟΛΑ ΤΑ ΜΕΛΗ ΣΕ ΕΝΑ CALL
    case 'year_totals': {
      $from = $_GET['from'] ?? date('Y-01-01');
      $to   = $_GET['to']   ?? date('Y-12-31');

      // Παίρνουμε μόνο member_id για παρούσες εγγραφές και αθροίζουμε σε PHP
      $p = [
        'select'      => 'member_id',
        'present'     => 'eq.true',
        'limit'       => 200000
      ];
      if ($from) $p['attended_on'][] = 'gte.'.$from;
      if ($to)   $p['attended_on'][] = 'lte.'.$to;

      $rows = sbreq('GET','/attendance',$p);
      $out = [];
      foreach ($rows as $r) {
        $mid = $r['member_id'] ?? null;
        if ($mid===null) continue;
        $out[$mid] = ($out[$mid] ?? 0) + 1;
      }
      echo json_encode($out); break;
    }

    case 'get_member': {
      $id = (int)($_GET['id'] ?? 0);
      $data = sbreq('GET','/v_member_stats', [
        'id' => 'eq.'.$id,
        'select' => 'id,first_name,last_name,counter_epoch,total_since_reset',
        'limit' => 1
      ]);
      echo json_encode($data); break;
    }

    case 'update_member': {
      $in = injson();
      $id = (int)($in['id'] ?? 0);
      if (!$id) { http_response_code(400); echo json_encode(['error'=>'id required']); break; }
      $payload = [];
      foreach (['first_name','last_name','counter_epoch'] as $k) if (isset($in[$k])) $payload[$k] = $in[$k];
      sbreq('PATCH','/members', [ 'id' => 'eq.'.$id ], $payload, ['return=representation']);
      echo json_encode(['ok'=>true]); break;
    }

    case 'reset_counter': {
      $in = injson();
      $id = (int)($in['id'] ?? 0);
      sbreq('PATCH','/members', [ 'id' => 'eq.'.$id ], [ 'counter_epoch' => date('Y-m-d') ], ['return=representation']);
      echo json_encode(['ok'=>true]); break;
    }

    /* -------------------- ΠΑΡΟΥΣΙΕΣ -------------------- */
    case 'upsert_attendance': {
      $in = injson();
      $payload = [
        'member_id'   => (int)($in['member_id'] ?? 0),
        'attended_on' => $in['attended_on'] ?? date('Y-m-d'),
        'present'     => true,
        'note'        => $in['note'] ?? null,
      ];
      $r = sbreq('POST','/attendance', [ 'on_conflict' => 'member_id,attended_on' ], $payload, ['resolution=merge-duplicates','return=representation']);
      echo json_encode($r[0] ?? ['ok'=>true]); break;
    }

    case 'list_attendance': {
      $date = $_GET['date'] ?? date('Y-m-d');
      $params = [
        'select' => 'id,attended_on,present,note,members:member_id(id,first_name,last_name)',
        'attended_on' => 'eq.'.$date,
        'order' => 'id.desc',
        'limit' => 300
      ];
      $rows = sbreq('GET','/attendance', $params);
      echo json_encode($rows); break;
    }

    case 'update_attendance_note': {
      $in = injson();
      $id = (int)($in['id'] ?? 0);
      $note = $in['note'] ?? null;
      sbreq('PATCH','/attendance', [ 'id' => 'eq.'.$id ], [ 'note' => $note ], ['return=representation']);
      echo json_encode(['ok'=>true]); break;
    }

    case 'toggle_attendance': {
      $in = injson();
      $id = (int)($in['id'] ?? 0);
      $present = !empty($in['present']);
      sbreq('PATCH','/attendance', [ 'id' => 'eq.'.$id ], [ 'present' => !$present ], ['return=representation']);
      echo json_encode(['ok'=>true]); break;
    }

    /* -------------------- REPORTS / COUNTS -------------------- */
    case 'count_attendance': {
      $mid  = (int)($_GET['member_id'] ?? 0);
      $from = $_GET['from'] ?? null;
      $to   = $_GET['to']   ?? null;

      $p = [
        'select'    => 'id',
        'member_id' => 'eq.'.$mid,
        'present'   => 'eq.true',
        'limit'     => 20000
      ];
      if ($from) $p['attended_on'][] = 'gte.'.$from;
      if ($to)   $p['attended_on'][] = 'lte.'.$to;

      $arr = sbreq('GET','/attendance', $p);
      echo json_encode(['count' => is_array($arr) ? count($arr) : 0]); break;
    }

    case 'report_attendance': {
      $from = $_GET['from'] ?? date('Y-m-01');
      $to   = $_GET['to']   ?? date('Y-m-t');

      $p = [
        'select'      => 'id,attended_on,present',
        'present'     => 'eq.true',
        'order'       => 'attended_on.asc',
        'limit'       => 20000
      ];
      if ($from) $p['attended_on'][] = 'gte.'.$from;
      if ($to)   $p['attended_on'][] = 'lte.'.$to;

      $rows = sbreq('GET','/attendance', $p);
      echo json_encode($rows); break;
    }

    /* -------------------- ΒΑΡΗ (weights) -------------------- */
    case 'add_weight': {
      $in   = injson();
      $mid  = (int)($in['member_id'] ?? 0);
      $wkg  = $in['weight_kg'] ?? null;
      $date = $in['measured_on'] ?? date('Y-m-d');
      if ($mid<=0 || $wkg===null) { http_response_code(400); echo json_encode(['error'=>'member_id & weight_kg required']); break; }
      $payload = ['member_id'=>$mid, 'measured_on'=>$date, 'weight_kg'=>$wkg];
      $r = sbreq('POST','/weights', ['on_conflict'=>'member_id,measured_on'], $payload, ['resolution=merge-duplicates','return=representation']);
      echo json_encode($r[0] ?? ['ok'=>true]); break;
    }

    case 'list_weights': {
      $mid  = (int)($_GET['member_id'] ?? 0);
      if ($mid<=0) { http_response_code(400); echo json_encode(['error'=>'member_id required']); break; }
      $from = $_GET['from'] ?? null;
      $to   = $_GET['to']   ?? null;
      $p = [
        'select'    => 'id,member_id,measured_on,weight_kg',
        'member_id' => 'eq.'.$mid,
        'order'     => 'measured_on.desc',
        'limit'     => 3650
      ];
      if ($from) $p['measured_on'][] = 'gte.'.$from;
      if ($to)   $p['measured_on'][] = 'lte.'.$to;
      $rows = sbreq('GET','/weights', $p);
      echo json_encode($rows); break;
    }

    case 'members_with_latest_weight': {
      $q = trim($_GET['q'] ?? '');
      $params = [
        'select'          => 'id,first_name,last_name,weights:weights!left(member_id,weight_kg,measured_on)',
        'order'           => 'id.asc',
        'weights.order'   => 'measured_on.desc',
        'weights.limit'   => 1,
        'limit'           => 10000
      ];
      if ($q !== '') {
        $params['or'] = sprintf('(first_name.ilike.*%1$s*,last_name.ilike.*%1$s*)', $q);
      }
      $rows = sbreq('GET','/members', $params);
      echo json_encode($rows); break;
    }

    /* ==================== ΝΕΑ ΓΙΑ "ΚΑΡΤΕΛΑ ΑΘΛΗΤΗ" ==================== */

    // Απλή λίστα μελών για dropdown (id, first_name, last_name)
    case 'list_members': {
      $params = [
        'select' => 'id,first_name,last_name',
        'order'  => 'id.asc',
        'limit'  => 10000
      ];
      echo json_encode(sbreq('GET','/members',$params)); break;
    }

    // Πλήρες προφίλ μέλους (με πεδία καρτέλας: dob, address, phone, email, medical_notes)
    case 'member_get': {
      $id = (int)($_GET['id'] ?? 0);
      $params = [
        'id'     => 'eq.'.$id,
        'select' => 'id,first_name,last_name,counter_epoch,'
                  . 'dob,address,phone,email,medical_notes',
        'limit'  => 1
      ];
      $rows = sbreq('GET','/members',$params);
      echo json_encode(['ok'=>true,'data'=>$rows[0] ?? null]); break;
    }

    // Αποθήκευση στοιχείων μέλους (μόνο τα σχετικά πεδία)
    case 'member_save': {
      $in = injson();
      $id = (int)($in['id'] ?? 0);
      if (!$id) { http_response_code(400); echo json_encode(['error'=>'id required']); break; }
      $payload = [];
      foreach (['first_name','last_name','dob','address','phone','email','medical_notes'] as $k) {
        if (array_key_exists($k,$in)) $payload[$k] = $in[$k];
      }
      sbreq('PATCH','/members', [ 'id' => 'eq.'.$id ], $payload, ['return=representation']);
      echo json_encode(['ok'=>true]); break;
    }

    // Λίστα σωματομετρήσεων
    case 'meas_list': {
      $mid = (int)($_GET['member_id'] ?? 0);
      if ($mid<=0){ http_response_code(400); echo json_encode(['error'=>'member_id required']); break; }
      $params = [
        'select'    => 'id,member_id,measured_on,weight_kg,fat_percent,twb,mbw,kcal,bones,visceral,created_at',
        'member_id' => 'eq.'.$mid,
        'order'     => 'measured_on.desc,id.desc',
        'limit'     => 5000
      ];
      echo json_encode(['ok'=>true,'data'=>sbreq('GET','/measurements',$params)]); break;
    }

    // Προσθήκη σωματομέτρησης
    case 'meas_add': {
      $in = injson();
      $payload = [
        'member_id'   => (int)($in['member_id'] ?? 0),
        'measured_on' => $in['measured_on'] ?? date('Y-m-d'),
        'weight_kg'   => $in['weight_kg']   ?? null,
        'fat_percent' => $in['fat_percent'] ?? null,
        'twb'         => $in['twb']         ?? null,
        'mbw'         => $in['mbw']         ?? null,
        'kcal'        => $in['kcal']        ?? null,
        'bones'       => $in['bones']       ?? null,
        'visceral'    => $in['visceral']    ?? null,
      ];
      $r = sbreq('POST','/measurements', [], $payload, ['return=representation']);
      echo json_encode(['ok'=>true,'data'=>$r[0] ?? null]); break;
    }

    // Διαγραφή σωματομέτρησης
    case 'meas_delete': {
      $id = (int)($_POST['id'] ?? 0);
      if ($id<=0){ http_response_code(400); echo json_encode(['error'=>'id required']); break; }
      sbreq('DELETE','/measurements', [ 'id' => 'eq.'.$id ], null, []);
      echo json_encode(['ok'=>true]); break;
    }

    /* ================== ΤΕΛΟΣ «ΚΑΡΤΕΛΑ ΑΘΛΗΤΗ» ================== */

    default:
      http_response_code(400);
      echo json_encode(['error'=>'Unknown action']);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
