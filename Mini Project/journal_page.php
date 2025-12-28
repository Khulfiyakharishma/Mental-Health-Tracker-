<?php
// journal_page.php (improved UI + session_start fix)
// Place this file in the same folder as config.php and wordlists/
require 'config.php';
/** @var mysqli $mysqli */

// guard session_start to avoid "already active" notice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: home.php'); exit;
}
$user_id = (int)$_SESSION['user_id'];

/* ---------- sentiment helpers (same as before) ---------- */
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
    static $pos=null, $neg=null, $neu=null;
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

/* ---------- handle POST (keeps previous behavior: save and redirect to monthly_tracker.php) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mood'])) {
    $mood = (string) ($_POST['mood']);
    $text = trim((string)($_POST['text'] ?? ''));
    $score = ($mood === 'happy') ? 2 : (($mood === 'neutral') ? 1 : 0);
    $date = date('Y-m-d');

    $sent = analyze_sentiment($text);
    $sent_label = $sent['label'];
    $sent_score = ($sent['score'] + 1) * 1.0;

    // update or insert
    $sel = $mysqli->prepare("SELECT id FROM journals WHERE user_id=? AND entry_date=?");
    $sel->bind_param('is',$user_id,$date);
    $sel->execute();
    $sel->store_result();
    if ($sel->num_rows>0) {
        $sel->bind_result($jid); $sel->fetch(); $sel->close();
        $t = $mysqli->real_escape_string($text);
        $mm = $mysqli->real_escape_string($mood);
        $sl = $mysqli->real_escape_string($sent_label);
        $mysqli->query("UPDATE journals SET mood='{$mm}', mood_score={$score}, text='{$t}', sentiment='{$sl}', sentiment_score={$sent_score}, created_at=NOW() WHERE id={$jid}");
    } else {
        $t = $mysqli->real_escape_string($text);
        $mm = $mysqli->real_escape_string($mood);
        $sl = $mysqli->real_escape_string($sent_label);
        $mysqli->query("INSERT INTO journals (user_id,entry_date,mood,mood_score,text,sentiment,sentiment_score) VALUES ({$user_id},'{$date}','{$mm}',{$score},'{$t}','{$sl}',{$sent_score})");
    }

    // redirect to monthly tracker (or back to journal with saved flag)
    header('Location: monthly_tracker.php'); exit;
}

/* ---------- fetch recent journals to show on this page ---------- */
$q = $mysqli->prepare("SELECT entry_date, mood, mood_score, text, sentiment FROM journals WHERE user_id = ? ORDER BY entry_date DESC LIMIT 12");
$q->bind_param('i',$user_id);
$q->execute();
$res = $q->get_result();
$recent = $res->fetch_all(MYSQLI_ASSOC);
$q->close();

/* ---------- fetch user's display name ---------- */
$u = $mysqli->prepare("SELECT name FROM users WHERE id = ?");
$u->bind_param('i',$user_id); $u->execute(); $u->bind_result($db_name); $u->fetch(); $u->close();
$display_name = $db_name ?? ($_SESSION['user_name'] ?? 'there');
$first = explode(' ', $display_name)[0];

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Journal — Hello <?php echo htmlspecialchars($first); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:linear-gradient(180deg,#f3f6ff,#faf8ff);
  --vio:#7b61ff;
  --vio-2:#9b86ff;
  --card:#fff;
  --muted:#6b7280;
  --accent:#8ac6d1;
}
*{box-sizing:border-box}
body{font-family:Inter,system-ui,Arial;background:var(--bg);margin:0;padding:26px;color:#111}
.container{max-width:1100px;margin:0 auto}
.header{display:flex;justify-content:space-between;align-items:center;gap:18px}
.user{display:flex;gap:14px;align-items:center}
.avatar{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--vio),var(--vio-2));color:white;font-weight:700;font-size:20px;box-shadow:0 10px 30px rgba(107,91,255,0.12)}
.title{font-weight:700;font-size:1.15rem}
.sub{color:var(--muted);font-size:.95rem}
.card{background:var(--card);padding:18px;border-radius:14px;box-shadow:0 12px 40px rgba(100,80,220,0.06);margin-top:18px}
.mood-row{display:flex;gap:14px;justify-content:center;margin-top:12px}
.emo{width:120px;height:120px;border-radius:16px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;border:0;font-size:34px;transition:transform .18s,box-shadow .18s,background .18s}
.emo span{margin-top:8px;font-weight:700}
.emo:hover{transform:translateY(-8px);box-shadow:0 22px 48px rgba(96,72,200,0.12)}
.happy{background:linear-gradient(135deg,#fff8ec,#fff0c8)}
.neutral{background:linear-gradient(135deg,#f4f9ff,#eef6ff)}
.sad{background:linear-gradient(135deg,#eef7ff,#e6f1ff)}
.journal-area{margin-top:16px}
textarea{width:100%;min-height:140px;padding:12px;border-radius:12px;border:1px solid #eef2ff;outline:none;font-size:0.95rem}
.actions{display:flex;gap:10px;align-items:center;margin-top:10px}
.btn{background:var(--vio);color:white;padding:10px 14px;border-radius:10px;border:0;cursor:pointer;font-weight:700}
.btn-ghost{background:transparent;border:1px solid #eef2ff;padding:10px 12px;border-radius:10px;color:#444}
.recent-list{margin-top:14px;display:grid;grid-template-columns:1fr 1fr;gap:12px}
.entry{padding:12px;border-radius:10px;background:linear-gradient(180deg,#fff,#fbfbff);border:1px solid #f0efff}
.small{color:var(--muted);font-size:0.95rem}
.toast{position:fixed;right:20px;bottom:20px;background:linear-gradient(90deg,var(--vio),var(--vio-2));color:white;padding:12px 16px;border-radius:10px;box-shadow:0 12px 30px rgba(107,91,255,0.18);display:none}
.modal-back{display:none;position:fixed;inset:0;background:rgba(8,6,22,0.45);align-items:center;justify-content:center;z-index:60}
.modal{width:96%;max-width:720px;background:white;border-radius:12px;padding:18px}
@media(max-width:900px){.recent-list{grid-template-columns:1fr} .mood-row{flex-direction:column;align-items:center}}
/* subtle focus ring for keyboard users */
.emo:focus{outline:3px solid rgba(123,97,255,0.14);}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="user">
      <div class="avatar" aria-hidden="true"><?php echo htmlspecialchars(mb_substr($first,0,1)); ?></div>
      <div>
        <div class="title">Hi <?php echo htmlspecialchars($first); ?> <span style="font-size:20px">👋</span></div>
        <div class="sub">Tap an emoji to start a quick journal — we'll save it with today's date.</div>
      </div>
    </div>

    <div>
      <a href="monthly_tracker.php" style="color:var(--vio);text-decoration:none;font-weight:700">See monthly tracker</a>
    </div>
  </div>

  <!-- mood picker card -->
  <div class="card" role="region" aria-label="Quick mood">
    <div style="font-weight:800">Quick mood</div>
    <div class="small" style="margin-top:6px">Choose how you feel — a short modal will help you write a few lines.</div>

    <div class="mood-row" role="list">
      <div class="emo happy" role="button" tabindex="0" data-mood="happy" aria-label="Happy">
        <div style="font-size:44px">😊</div><span>Happy</span>
      </div>

      <div class="emo neutral" role="button" tabindex="0" data-mood="neutral" aria-label="Neutral">
        <div style="font-size:44px">😐</div><span>Neutral</span>
      </div>

      <div class="emo sad" role="button" tabindex="0" data-mood="sad" aria-label="Sad">
        <div style="font-size:44px">😔</div><span>Sad</span>
      </div>
    </div>

    <!-- inline journal (hidden until mood chosen) -->
    <div class="journal-area" id="journalArea" style="display:none">
      <form method="post" action="journal_page.php" id="journalForm">
        <input type="hidden" name="mood" value="" id="moodField">
        <label class="small" for="text">Write a short journal (today will be saved automatically)</label>
        <textarea name="text" id="text" placeholder="Write a short journal..."></textarea>
        <div class="actions">
          <button type="submit" class="btn">Save Journal</button>
          <button type="button" id="cancel" class="btn-ghost">Cancel</button>
          <div style="margin-left:auto" class="small" id="hint">Tip: press <strong>Space</strong> anytime to quick-check.</div>
        </div>
      </form>
    </div>
  </div>

  <!-- recent journals -->
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div style="font-weight:800">Recent journals</div>
      <div class="small">Latest entries (most recent first)</div>
    </div>

    <div class="recent-list" style="margin-top:12px">
      <?php if(empty($recent)): ?>
        <div class="entry">No entries yet — write your first journal above.</div>
      <?php else: foreach($recent as $r): 
          $d = DateTime::createFromFormat('Y-m-d', $r['entry_date']);
      ?>
        <div class="entry" aria-live="polite">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div style="font-weight:700"><?php echo $d? $d->format('j M Y'): htmlspecialchars($r['entry_date']); ?></div>
            <div class="small"><?php echo ucfirst(htmlspecialchars($r['mood'])); ?> • <?php echo $r['sentiment'] ?? 'neutral'; ?></div>
          </div>
          <div style="margin-top:8px"><?php echo nl2br(htmlspecialchars(mb_strimwidth($r['text'],0,400,'...'))); ?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- modal (for accessibility we use modal to enter more text) -->
<div id="modalBack" class="modal-back" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div id="modalTitle" style="font-weight:800">Write a quick journal</div>
      <button id="modalClose" style="background:transparent;border:0;font-weight:700;cursor:pointer">Close ✕</button>
    </div>
    <p class="small" id="modalSub">Share a short note — what happened, how you feel, or a small win.</p>
    <form id="modalForm" method="post" action="journal_page.php">
      <input type="hidden" name="mood" id="modalMood" value="">
      <textarea id="modalText" name="text" style="width:100%;min-height:160px;padding:12px;border-radius:10px;border:1px solid #eef2ff"></textarea>
      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px">
        <button type="button" id="modalCancel" class="btn-ghost">Cancel</button>
        <button type="submit" class="btn">Save</button>
      </div>
    </form>
  </div>
</div>

<div id="toast" class="toast">Saved ✓</div>

<script>
// UI interactions
(function(){
  const emos = document.querySelectorAll('.emo');
  const journalArea = document.getElementById('journalArea');
  const moodField = document.getElementById('moodField');
  const textArea = document.getElementById('text');
  const cancelBtn = document.getElementById('cancel');

  const modalBack = document.getElementById('modalBack');
  const modalMood = document.getElementById('modalMood');
  const modalText = document.getElementById('modalText');
  const modalClose = document.getElementById('modalClose');
  const modalCancel = document.getElementById('modalCancel');
  const modalForm = document.getElementById('modalForm');

  // open inline or modal journal when mood clicked
  emos.forEach(e => {
    e.addEventListener('click', () => openJournal(e.dataset.mood));
    e.addEventListener('keydown', (ev)=> { if(ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); openJournal(e.dataset.mood); } });
  });

  function openJournal(mood) {
    // prefer modal for bigger writing experience
    modalMood.value = mood;
    modalText.value = mood === 'happy' ? 'Today I felt...' : '';
    document.getElementById('modalTitle').textContent = mood === 'happy' ? 'Share a bright moment' : (mood === 'neutral' ? 'Note your day' : 'I am here — tell me more');
    document.getElementById('modalSub').textContent = mood === 'sad' ? 'If it feels heavy, write what happened. Small steps help.' : 'A short note helps us track patterns.';
    modalBack.style.display = 'flex';
    modalText.focus();
  }

  modalClose.addEventListener('click', ()=> { modalBack.style.display='none'; });
  modalCancel.addEventListener('click', ()=> { modalBack.style.display='none'; });

  // cancel inline area (not used currently)
  cancelBtn.addEventListener('click', ()=> {
    journalArea.style.display='none';
    moodField.value=''; textArea.value='';
  });

  // quick spacebar open (neutral quick-check)
  document.addEventListener('keydown', function(ev){
    if (ev.code === 'Space' && (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA')) {
      ev.preventDefault();
      openJournal('neutral');
    }
  });

  // toast if ?saved=1
  (function checkSaved(){
    const url = new URL(window.location.href);
    if (url.searchParams.get('saved') === '1') {
      const t = document.getElementById('toast');
      t.style.display = 'block';
      setTimeout(()=> t.style.display = 'none', 3000);
      // remove param from URL for cleanliness
      url.searchParams.delete('saved');
      history.replaceState(null,'',url.toString());
    }
  })();

  // for modal submit, copy modal values into form fields (so server receives them)
  modalForm.addEventListener('submit', function(ev){
    // let the form submit normally to server (it POSTS to journal_page.php)
    // If you want to use AJAX instead, preventDefault and send fetch()
  });

})();
</script>
</body>
</html>
