<?php
// quiz.php
require_once 'db.php';

// Fetch questions and choices dynamically
$qSql = "SELECT q.id as qid, q.qtext, q.topic, c.id as cid, c.ctext
         FROM questions q
         JOIN choices c ON c.question_id = q.id
         ORDER BY q.id ASC, c.id ASC";
$res = $mysqli->query($qSql);
if (!$res) {
    die("Database error: " . $mysqli->error);
}

$questions = [];
while ($row = $res->fetch_assoc()) {
    $qid = $row['qid'];
    if (!isset($questions[$qid])) {
        $questions[$qid] = [
            'id' => (int)$qid,
            'qtext' => $row['qtext'],
            'topic' => $row['topic'],
            'choices' => []
        ];
    }
    $questions[$qid]['choices'][] = ['id'=> (int)$row['cid'], 'ctext'=> $row['ctext']];
}
$res->free();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>IQ Test — Quiz</title>
<style>
/* Internal CSS — stylish quiz UI */
:root{--bg:#071022; --card:#071a2a; --accent:#7dd3fc; --muted:#9fb0c8}
body{margin:0; font-family:Inter,system-ui,Arial; background:linear-gradient(180deg,#041022,#071022); color:#e9f7ff; padding:18px}
.container{max-width:900px; margin:0 auto}
.card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); padding:20px; border-radius:12px; border:1px solid rgba(255,255,255,0.03)}
h2{margin:0 0 8px 0}
.q{padding:14px; border-radius:10px; margin-bottom:12px; background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.02)}
.choices{display:flex; flex-direction:column; gap:8px; margin-top:8px}
.choice{display:flex; gap:10px; align-items:center; padding:10px; border-radius:8px; cursor:pointer; border:1px solid transparent}
.choice input{transform:scale(1.1)}
.choice:hover{background:rgba(125,211,252,0.03); border-color:rgba(125,211,252,0.06)}
.progress{margin-top:14px; color:var(--muted); font-size:14px}
.footer{display:flex; justify-content:space-between; align-items:center; margin-top:16px}
.btn{padding:10px 14px; border-radius:10px; border:none; cursor:pointer; font-weight:700}
.btn-primary{background:linear-gradient(90deg,var(--accent),#60a5fa); color:#04293a}
.btn-ghost{background:transparent; border:1px solid rgba(255,255,255,0.04); color:var(--muted)}
small{color:var(--muted)}
@media (max-width:520px){
  .choices{gap:6px}
}
</style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h2>IQ Test — Answer all questions</h2>
      <p style="color:var(--muted); margin-top:8px">This test has <strong><?php echo count($questions); ?></strong> questions. Choose the answer you think is correct.</p>

      <form id="quizForm" onsubmit="return false;">
        <?php foreach ($questions as $idx => $q): ?>
          <div class="q" data-qid="<?php echo $q['id']; ?>">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
              <div><strong>Q<?php echo ($idx+1); ?>.</strong> <?php echo htmlspecialchars($q['qtext']); ?></div>
              <div style="font-size:12px;color:#9fb0c8"><?php echo htmlspecialchars(ucfirst($q['topic'])); ?></div>
            </div>
            <div class="choices">
              <?php foreach ($q['choices'] as $choice): ?>
                <label class="choice">
                  <input type="radio" name="q_<?php echo $q['id']; ?>" value="<?php echo $choice['id']; ?>" />
                  <span><?php echo htmlspecialchars($choice['ctext']); ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="footer">
          <div>
            <small>Take your time. You can change answers before submitting.</small>
          </div>
          <div style="display:flex; gap:10px;">
            <button class="btn btn-ghost" type="button" id="resetBtn">Reset</button>
            <button class="btn btn-primary" id="submitBtn">Submit Answers</button>
          </div>
        </div>
      </form>
    </div>
  </div>

<script>
// JS to gather answers, post to save_answers.php and then redirect to result.php using window.location
document.getElementById('resetBtn').addEventListener('click', function(){
  document.getElementById('quizForm').reset();
});

document.getElementById('submitBtn').addEventListener('click', async function(){
  const form = document.getElementById('quizForm');
  const qElems = document.querySelectorAll('.q');
  const answers = [];

  qElems.forEach(qEl => {
    const qid = qEl.getAttribute('data-qid');
    const sel = form['q_' + qid];
    let val = null;
    if (sel) {
      if (sel.length === undefined) { // single radio
        val = sel.checked ? sel.value : null;
      } else {
        for (let r of sel) { if (r.checked) { val = r.value; break; } }
      }
    }
    answers.push({ question_id: Number(qid), choice_id: val ? Number(val) : null });
  });

  // Basic client-side completeness check
  const unanswered = answers.filter(a => a.choice_id === null);
  if (unanswered.length > 0) {
    if (!confirm("You have unanswered questions. Submit anyway?")) {
      return;
    }
  }

  // Send to server for saving and scoring
  try {
    const resp = await fetch('save_answers.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ answers })
    });

    const json = await resp.json();
    if (json.success) {
      // redirect using JS (no PHP redirect)
      window.location.href = 'result.php?result_id=' + encodeURIComponent(json.result_id);
    } else {
      alert('Error: ' + (json.error || 'Unknown error'));
    }
  } catch (e) {
    alert('Network error: ' + e.message);
  }
});
</script>
</body>
</html>
