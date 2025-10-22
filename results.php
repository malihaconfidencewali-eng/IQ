<?php
// result.php
require_once 'db.php';

$result_id = isset($_GET['result_id']) ? (int)$_GET['result_id'] : 0;
if (!$result_id) {
    // show latest result if no id provided
    $r = $mysqli->query("SELECT id FROM results ORDER BY created_at DESC LIMIT 1");
    if ($r && $r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $result_id = (int)$row['id'];
    } else {
        // no results yet: redirect to home
        header('Location: index.php');
        exit;
    }
}

// load result
$stmt = $mysqli->prepare("SELECT id, user_name, score, total, created_at FROM results WHERE id = ?");
$stmt->bind_param('i', $result_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die('Result not found.');
}
$result = $res->fetch_assoc();
$stmt->close();

// fetch responses and question texts for breakdown
$stmt = $mysqli->prepare("
    SELECT r.question_id, r.choice_id, q.qtext, c.ctext AS chosen_text, co.ctext AS correct_text
    FROM responses r
    LEFT JOIN questions q ON q.id = r.question_id
    LEFT JOIN choices c ON c.id = r.choice_id
    LEFT JOIN choices co ON co.question_id = q.id AND co.is_correct = 1
    WHERE r.result_id = ?
    ORDER BY q.id ASC
");
$stmt->bind_param('i', $result_id);
$stmt->execute();
$resp = $stmt->get_result();
$breakdown = [];
while ($row = $resp->fetch_assoc()) {
    $breakdown[] = $row;
}
$stmt->close();

// derive IQ estimate mapping (simple mapping)
$score = (int)$result['score'];
$total = (int)$result['total'];
$percent = $total>0 ? round(($score/$total)*100) : 0;

function iq_estimate($percent) {
    if ($percent >= 90) return ['iq'=>135, 'level'=>'Very Superior'];
    if ($percent >= 75) return ['iq'=>120, 'level'=>'Superior'];
    if ($percent >= 50) return ['iq'=>100, 'level'=>'Average'];
    if ($percent >= 35) return ['iq'=>85, 'level'=>'Below Average'];
    return ['iq'=>75, 'level'=>'Low'];
}
$iq = iq_estimate($percent);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>IQ Test — Result</title>
<style>
:root{--bg:#071022; --card:#071a2a; --accent:#7dd3fc; --muted:#9fb0c8}
body{margin:0; font-family:Inter,system-ui,Arial; background:linear-gradient(180deg,#041022,#071022); color:#eaf6ff; padding:16px}
.container{max-width:1000px; margin:0 auto}
.card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); padding:20px; border-radius:12px; border:1px solid rgba(255,255,255,0.03)}
.header{display:flex; justify-content:space-between; align-items:center}
.score{font-size:44px; font-weight:800; color:var(--accent)}
.sub{color:var(--muted)}
.breakdown{margin-top:18px}
.item{padding:12px; border-radius:10px; background:rgba(255,255,255,0.01); margin-bottom:10px; border:1px solid rgba(255,255,255,0.02)}
.good{color:#9ef7b3}
.bad{color:#ffb3b3}
.actions{display:flex; gap:10px; margin-top:14px}
.btn{padding:10px 14px; border-radius:10px; border:none; cursor:pointer; font-weight:700}
.btn-primary{background:linear-gradient(90deg,var(--accent),#60a5fa); color:#04293a}
.btn-ghost{background:transparent; border:1px solid rgba(255,255,255,0.04); color:var(--muted)}
@media (max-width:520px){ .header{flex-direction:column; align-items:flex-start} .score{font-size:34px} }
</style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="header">
        <div>
          <h3 style="margin:0">Your IQ Test Result</h3>
          <div class="sub">Taken on <?php echo htmlspecialchars($result['created_at']); ?></div>
        </div>
        <div style="text-align:right">
          <div class="score"><?php echo $score . '/' . $total; ?></div>
          <div class="sub">Percent: <?php echo $percent; ?>% — Estimate: <?php echo $iq['level']; ?> (≈ IQ <?php echo $iq['iq']; ?>)</div>
        </div>
      </div>

      <div style="margin-top:12px">
        <strong>Feedback</strong>
        <p class="sub">
          <?php
            echo "Your approximate IQ category is <strong>".$iq['level']."</strong> (approx. IQ " . $iq['iq'] . "). ";
            if ($percent >= 90) {
                echo "Excellent performance! You show strong reasoning and pattern skills.";
            } elseif ($percent >= 75) {
                echo "Very good — solid strengths in reasoning. Work on timed practice to improve further.";
            } elseif ($percent >= 50) {
                echo "Average. Practice logic puzzles and number series to boost performance.";
            } elseif ($percent >= 35) {
                echo "Below average — try targeted practice on pattern recognition and basic arithmetic.";
            } else {
                echo "Low result — consider foundational exercises: puzzles, pattern tests, and basic arithmetic practice.";
            }
          ?>
        </p>
      </div>

      <div class="actions">
        <button class="btn btn-primary" onclick="retake()">Retake Test</button>
        <button class="btn btn-ghost" onclick="share()">Share Result</button>
        <a class="btn btn-ghost" href="index.php">Home</a>
      </div>

      <div class="breakdown">
        <h4 style="margin-top:18px">Question-by-question breakdown</h4>
        <?php foreach ($breakdown as $b): ?>
          <div class="item">
            <div><strong><?php echo htmlspecialchars($b['qtext']); ?></strong></div>
            <div style="margin-top:6px; color:var(--muted)">
              Your answer: <?php echo htmlspecialchars($b['chosen_text']?:'No answer'); ?>
              <span style="margin-left:12px">Correct answer: <?php echo htmlspecialchars($b['correct_text']); ?></span>
              <?php
                $you = $b['chosen_text'] ? trim($b['chosen_text']) : '';
                $correct = trim($b['correct_text']);
                $isRight = ($you !== '' && $you === $correct);
              ?>
            </div>
            <div style="margin-top:8px">
              <span class="<?php echo $isRight ? 'good' : 'bad'; ?>">
                <?php echo $isRight ? 'Correct' : 'Incorrect'; ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

<script>
function retake(){
  // JS redirect to quiz
  window.location.href = 'quiz.php';
}
function share(){
  // simple share: copy URL
  const url = location.href;
  navigator.clipboard?.writeText(url).then(() => {
    alert('Result link copied. You can share it with others.');
  }).catch(()=> {
    prompt('Copy this link:', url);
  });
}
</script>
</body>
</html>
