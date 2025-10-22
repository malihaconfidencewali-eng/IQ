<?php
// index.php
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>IQ Test - Home</title>
<style>
/* Internal CSS — stylish, modern, responsive */
:root {
  --bg:#0f1724; --card:#0b1220; --accent:#7dd3fc; --muted:#94a3b8;
  --glass: rgba(255,255,255,0.03);
  font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
}
*{box-sizing:border-box}
body{margin:0; background:linear-gradient(180deg,#071022,#0b1220); color:#e6f0ff; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px}
.container{max-width:900px; width:100%}
.card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border:1px solid rgba(255,255,255,0.03); padding:28px; border-radius:14px; box-shadow:0 6px 30px rgba(3,7,18,0.6)}
.header{display:flex; gap:16px; align-items:center}
.logo{width:76px;height:76px;border-radius:12px;background:linear-gradient(135deg,var(--accent),#60a5fa); display:flex;align-items:center;justify-content:center; font-weight:700; color:#04293a; font-size:26px}
h1{margin:0;font-size:24px}
.lead{color:var(--muted); margin-top:10px}
.start-btn{margin-top:20px;background:linear-gradient(90deg,var(--accent),#60a5fa); border:none; color:#04293a; padding:12px 18px; border-radius:10px; font-weight:700; cursor:pointer; font-size:16px; box-shadow:0 6px 18px rgba(125,211,252,0.12)}
.features{display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-top:18px}
.feature{background:var(--glass); padding:12px; border-radius:10px; border:1px solid rgba(255,255,255,0.02); color:var(--muted)}
.footer{margin-top:18px; color:var(--muted); font-size:13px}
@media (max-width:520px){
  .header{flex-direction:column; align-items:flex-start}
  .logo{width:60px;height:60px;font-size:20px}
}
</style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="header">
        <div class="logo">IQ</div>
        <div>
          <h1>Online IQ Test</h1>
          <div class="lead">Short, reliable multiple-choice test to measure reasoning, pattern recognition, and numeric problem solving.</div>
        </div>
      </div>

      <div class="features">
        <div class="feature"><strong>10 questions</strong><div style="font-size:13px">Quick 5–10 minute test</div></div>
        <div class="feature"><strong>Instant results</strong><div style="font-size:13px">Score & feedback shown right away</div></div>
        <div class="feature"><strong>Responsive</strong><div style="font-size:13px">Works on mobile & desktop</div></div>
        <div class="feature"><strong>Secure</strong><div style="font-size:13px">Stored safely in DB</div></div>
      </div>

      <div style="display:flex; gap:12px; margin-top:18px; align-items:center;">
        <button class="start-btn" id="start">Start Test</button>
        <a href="result.php" style="color:var(--muted); font-size:13px; text-decoration:none">View latest results</a>
      </div>

      <div class="footer">
        <p><strong>How it works:</strong> Click <em>Start Test</em>, answer 10 multiple-choice questions, submit — you'll get a score and tailored feedback.</p>
      </div>
    </div>
  </div>

<script>
// JS-based redirection to quiz.php (no PHP redirect)
document.getElementById('start').addEventListener('click', function(){
  // pass through to quiz page
  window.location.href = 'quiz.php';
});
</script>
</body>
</html>
