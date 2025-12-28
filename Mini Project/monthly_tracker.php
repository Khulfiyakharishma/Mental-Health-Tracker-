<?php
// monthly_tracker.php
require 'config.php';
/** @var mysqli $mysqli */

// avoid "session already active" notice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: home.php'); exit;
}
$user_id = (int)$_SESSION['user_id'];

/* ---------- helpers (reused) ---------- */
function load_wordlist($path) {
    $out = [];
    if (!file_exists($path)) return $out;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $w) {
        $w = trim(mb_strtolower($w));
        if ($w !== '') $out[$w] = true;
    }
    return $out;
}
function analyze_sentiment($text) {
    static $pos=null,$neg=null,$neu=null;
    if ($pos === null) {
        $pos = load_wordlist(__DIR__.'/wordlists/positive.txt');
        $neg = load_wordlist(__DIR__.'/wordlists/negative.txt');
        $neu = load_wordlist(__DIR__.'/wordlists/neutral.txt');
    }
    $t = mb_strtolower($text);
    $tokens = preg_split('/[^\p{L}\p{N}\']+/u', $t, -1, PREG_SPLIT_NO_EMPTY);
    if (empty($tokens)) return ['score'=>0,'label'=>'neutral'];
    $pc=0;$nc=0;$nuc=0;
    foreach($tokens as $tk){
        if(isset($pos[$tk])) $pc++;
        elseif(isset($neg[$tk])) $nc++;
        elseif(isset($neu[$tk])) $nuc++;
    }
    $matched = $pc+$nc+$nuc;
    if ($matched===0) return ['score'=>0,'label'=>'neutral'];
    $score = ($pc - $nc)/$matched;
    $label = $score >= 0.25 ? 'positive' : ($score <= -0.25 ? 'negative' : 'neutral');
    return ['score'=>$score,'label'=>$label];
}

/* ---------- fetch month entries ---------- */
$year = date('Y'); $month = date('m');
$start = "{$year}-{$month}-01";
$end = date('Y-m-t', strtotime($start)); // last day of month

$q = $mysqli->prepare("SELECT entry_date, mood, mood_score, text, sentiment FROM journals WHERE user_id=? AND entry_date BETWEEN ? AND ? ORDER BY entry_date ASC");
$q->bind_param('iss',$user_id,$start,$end);
$q->execute();
$res = $q->get_result();
$month_entries = $res->fetch_all(MYSQLI_ASSOC);
$q->close();

/* ---------- compute stats for month ---------- */
$total = count($month_entries);
$counts = ['happy'=>0,'neutral'=>0,'sad'=>0];
$sent_counts = ['positive'=>0,'neutral'=>0,'negative'=>0];
foreach ($month_entries as $e) {
    if (isset($counts[$e['mood']])) $counts[$e['mood']]++;
    if (!empty($e['sentiment']) && isset($sent_counts[$e['sentiment']])) $sent_counts[$e['sentiment']]++;
}
$happy_pct = $total ? ($counts['happy']/$total)*100 : 0;
$neutral_pct = $total ? ($counts['neutral']/$total)*100 : 0;
$sad_pct = $total ? ($counts['sad']/$total)*100 : 0;

/* ---------- fetch user and friend ---------- */
$u = $mysqli->prepare("SELECT name, friend_phone FROM users WHERE id = ?");
$u->bind_param('i',$user_id); $u->execute(); $u->bind_result($db_name,$friend_phone); $u->fetch(); $u->close();
$first = explode(' ', $db_name ?? ($_SESSION['user_name'] ?? 'there'))[0];

/* ---------- HANDLE manual alert (Send SMS) ----------
   This must come after we fetched $friend_phone and $db_name so they are available.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['__send_alert'])) {
    // only attempt to send if friend phone exists
    if (!empty($friend_phone)) {
        $message = "Alert: " . ($db_name ?? $_SESSION['user_name']) . " may need support. Please check on them.";
        // send_sms should be defined in config.php or an included helper
        if (function_exists('send_sms')) {
            $sms = send_sms($friend_phone, $message);
        } else {
            $sms = false;
            error_log("send_sms() not defined - cannot send SMS");
        }
        if ($sms !== false) {
            // log the alert into alerts table
            $f = $mysqli->real_escape_string($friend_phone);
            $d = $mysqli->real_escape_string($message);
            $mysqli->query("INSERT INTO alerts (user_id, alert_type, sent_to, details) VALUES ({$user_id}, 'manual_alert', '{$f}', '{$d}')");
        } else {
            // optional: you may want to log failures for debugging
            error_log("send_sms() returned false for user_id {$user_id}");
        }
    }
    // redirect back to avoid form resubmission and refresh UI
    header('Location: monthly_tracker.php');
    exit;
}
/* ---------- end manual alert handler ---------- */

/* ---------- suggestions generator (simple) ---------- */
function get_suggestions_for_text($text) {
    $text = mb_strtolower($text);
    if (strpos($text,'school') !== false || strpos($text,'exam') !== false) {
        return ['title'=>'School stress? Try a short break', 'action' => 'Take a 10-minute walk', 'spotify'=>'focus playlist'];
    }
    if (strpos($text,'sleep') !== false || strpos($text,'tired') !== false) {
        return ['title'=>'Sleep support', 'action'=>'Try a 5-minute breathing exercise', 'spotify'=>'sleep sounds'];
    }
    if (strpos($text,'friend') !== false) {
        return ['title'=>'Connect with a friend', 'action'=>'Message or call a close friend', 'spotify'=>'happy vibes'];
    }
    return ['title'=>'Small mindful break','action'=>'Do a 5-minute breathing exercise','spotify'=>'calm playlist'];
}

/* ---------- decide if we should show the emergency prompt ---------- */
$SAD_THRESHOLD_PERCENT = 40; // if >= 40% of month entries are 'sad' then prompt
$show_alert_prompt = ($total > 0 && $sad_pct >= $SAD_THRESHOLD_PERCENT);

/* ---------- choose quote/suggestion for month (simple) ---------- */
$summary_text = '';
foreach ($month_entries as $e) {
    if (!empty($e['text'])) $summary_text .= ' '.$e['text'];
}
$sugg = get_suggestions_for_text($summary_text);

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Monthly Tracker — <?php echo htmlspecialchars($first); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
:root{--bg:linear-gradient(180deg,#f2f2ff,#faf8ff);--vio:#7b61ff;--card:#fff;--muted:#666}
body{font-family:Inter,system-ui,Arial;background:var(--bg);margin:0;padding:20px;color:#222}
.container{max-width:1100px;margin:0 auto}
.header{display:flex;justify-content:space-between;align-items:center}
.avatar{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#c7b2ff,#8ea2ff);color:white;font-weight:700}
.card{background:var(--card);padding:16px;border-radius:12px;box-shadow:0 12px 36px rgba(103,78,255,0.06);margin-top:16px}
.stat-row{display:flex;gap:12px;align-items:center;margin-top:12px}
.stat{flex:1;padding:12px;border-radius:10px;background:linear-gradient(180deg,#fff,#fbfaff);text-align:center}
.spark{height:56px;background:linear-gradient(90deg,#e9e7ff,#fff);border-radius:6px;margin-top:8px}
.small{color:var(--muted);font-size:0.95rem}
.view-list{margin-top:12px}
.entry{padding:12px;border-radius:10px;background:linear-gradient(180deg,#ffffff,#fafbff);border:1px solid #f0efff;margin-bottom:10px}
.modal-back{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center}
.modal{background:white;padding:18px;border-radius:12px;max-width:720px;width:94%}
.alert-img{width:100%;max-width:300px;border-radius:8px;display:block;margin-top:12px}
.action{display:inline-block;padding:10px 12px;border-radius:10px;background:var(--vio);color:white;text-decoration:none;font-weight:700;margin-top:8px}
@media(max-width:900px){.stat-row{flex-direction:column}}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div style="display:flex;gap:12px;align-items:center">
      <div class="avatar"><?php echo htmlspecialchars(mb_substr($first,0,1));?></div>
      <div>
        <div style="font-weight:800;font-size:1.1rem">Monthly tracker — <?php echo htmlspecialchars($first); ?></div>
        <div class="small"><?php echo date('F Y'); ?> • <?php echo $total; ?> entries</div>
      </div>
    </div>
    <div><a href="journal_page.php" class="small" style="color:var(--vio);text-decoration:none;font-weight:700">Write new</a></div>
  </div>

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div><strong>Emotion breakdown</strong><div class="small">This month</div></div>
      <div style="text-align:right">
        <div style="font-size:14px;color:#444">Sad: <?php echo round($sad_pct,1); ?>% • Neutral: <?php echo round($neutral_pct,1); ?>% • Happy: <?php echo round($happy_pct,1); ?>%</div>
      </div>
    </div>

    <div class="stat-row">
      <div class="stat">
        <div style="font-weight:800;font-size:20px"><?php echo $counts['happy']; ?></div>
        <div class="small">Happy</div>
        <div class="spark" aria-hidden="true"></div>
      </div>
      <div class="stat">
        <div style="font-weight:800;font-size:20px"><?php echo $counts['neutral']; ?></div>
        <div class="small">Neutral</div>
        <div class="spark" aria-hidden="true"></div>
      </div>
      <div class="stat">
        <div style="font-weight:800;font-size:20px"><?php echo $counts['sad']; ?></div>
        <div class="small">Sad</div>
        <div class="spark" aria-hidden="true"></div>
      </div>
    </div>

    <div style="margin-top:12px">
      <strong>Monthly suggestion</strong>
      <div class="small" style="margin-top:6px"><?php echo htmlspecialchars($sugg['title']); ?></div>
      <div style="margin-top:10px">
        <a class="action" href="https://open.spotify.com/search/<?php echo urlencode($sugg['spotify']); ?>" target="_blank">Play on Spotify</a>
        <a class="action" href="#" onclick="startWalkTimer(10);return false;">Start 10-min walk</a>
        <a class="action" href="#" onclick="showBreathe();return false;">Guided breathing</a>
      </div>
    </div>
  </div>

  <div class="card" style="margin-top:12px">
    <strong>Journal entries this month</strong>
    <div class="view-list">
      <?php if(empty($month_entries)): ?>
        <div class="entry">No entries for this month yet.</div>
      <?php else: foreach($month_entries as $me): $d = DateTime::createFromFormat('Y-m-d',$me['entry_date']);?>
        <div class="entry">
          <div style="display:flex;justify-content:space-between">
            <div style="font-weight:700"><?php echo $d? $d->format('j M Y'): htmlspecialchars($me['entry_date']); ?></div>
            <div class="small"><?php echo ucfirst(htmlspecialchars($me['mood'])); ?> • <?php echo $me['sentiment'] ?? 'neutral'; ?></div>
          </div>
          <div style="margin-top:8px"><?php echo nl2br(htmlspecialchars(mb_strimwidth($me['text'],0,600,'...'))); ?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- breathing modal -->
<div id="breatheModal" class="modal-back" style="display:none;align-items:center;justify-content:center">
  <div class="modal">
    <h3>Guided Breathing</h3>
    <p class="small">Follow: breathe in 4s — hold 4s — breathe out 6s. Repeat 5 times.</p>
    <!-- replace the video src with your own path if needed --> 
    <video width="100%" style="max-width:360px;border-radius:8px;margin-top:10px;">
      <source src="breathing .mp4" type="video/mp4">
      Your browser does not support the video tag.
    </video>
    <div style="margin-top:12px"><button onclick="document.getElementById('breatheModal').style.display='none'" class="action">Close</button></div>
  </div>
</div>

<!-- emergency modal: shown when sad% high -->
<div id="emModal" class="modal-back" style="<?php echo $show_alert_prompt? 'display:flex':'display:none'; ?>;align-items:center;justify-content:center">
  <div class="modal">
    <h3 style="margin:0">We noticed you’ve had several low days recently</h3>
    <p class="small">If you want, we can call your emergency contact. Otherwise, try a guided breathing or a short walk.</p>
    <!-- IMAGE: replace 'images/support.png' with your own image path -->
    <img src="alert.jpg" alt="support" class="alert-img">
    <div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end">
      <?php if(!empty($friend_phone)): ?>
        <a class="action" href="tel:<?php echo htmlspecialchars($friend_phone); ?>">Call friend</a>
        <form method="post" style="display:inline">
  <button type="button" class="action" 
          style="background:#25D366"
          onclick="window.open('https://web.whatsapp.com/', '_blank')">
      Send WhatsApp Alert
  </button>
</form>

      <?php else: ?>
        <button class="action" onclick="alert('No emergency contact found on your profile. Add one in profile.')">No contact</button>
      <?php endif; ?>
      <button class="action" onclick="document.getElementById('emModal').style.display='none'">Dismiss</button>
    </div>
  </div>
</div>

<script>
function startWalkTimer(mins){
  if (!confirm('Start a ' + mins + '-minute walk timer?')) return;
  const seconds = mins*60;
  const win = window.open('', '_blank', 'width=320,height=140');
  win.document.write('<p style="font-family:Inter,Arial;padding:10px">Walk timer: <span id="t"></span></p>');
  let s = seconds;
  const ti = setInterval(()=> {
    const m = Math.floor(s/60); const sec = s%60;
    win.document.getElementById('t').innerText = `${m}:${sec.toString().padStart(2,'0')}`;
    s--; if (s<0) { clearInterval(ti); win.document.getElementById('t').innerText='Done!'; }
  },1000);
}
function showBreathe(){ document.getElementById('breatheModal').style.display='flex'; }
</script>
<a href="logout.php" style="
    position: fixed;
    top: 20px;
    right: 20px;
    background:#7b5cff;
    color:#fff;
    padding:10px 20px;
    border-radius:12px;
    text-decoration:none;
    font-weight:600;
    font-family:Arial, sans-serif;
    box-shadow:0 4px 10px rgba(0,0,0,0.15);
    transition:0.3s;
    z-index:1000;
"
onmouseover="this.style.background='#6a4ae0'"
onmouseout="this.style.background='#7b5cff'">
    Logout
</a>

</body>
</html>
