<?php
// save_answers.php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

// read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!isset($data['answers']) || !is_array($data['answers'])) {
    echo json_encode(['success'=>false, 'error'=>'Invalid payload']);
    exit;
}

$answers = $data['answers'];

// Validate question ids and choice ids are integers
$validPairs = [];
foreach ($answers as $a) {
    if (!isset($a['question_id'])) continue;
    $qid = (int)$a['question_id'];
    $cid = isset($a['choice_id']) && $a['choice_id'] !== null ? (int)$a['choice_id'] : null;
    $validPairs[] = ['q'=>$qid, 'c'=>$cid];
}

// Calculate score by checking choices.is_correct
$score = 0;
$total = 0;
$insertResponses = [];

foreach ($validPairs as $p) {
    $qid = $p['q'];
    // fetch choices for this question to determine total & correctness
    $stmt = $mysqli->prepare("SELECT id, is_correct FROM choices WHERE question_id = ?");
    $stmt->bind_param('i', $qid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $total++;
        $choiceWasCorrect = 0;
        while ($row = $res->fetch_assoc()) {
            if ($p['c'] !== null && (int)$row['id'] === $p['c'] && (int)$row['is_correct'] === 1) {
                $choiceWasCorrect = 1;
                break;
            }
        }
        $score += $choiceWasCorrect;
        $insertResponses[] = ['question_id'=>$qid, 'choice_id'=>$p['c']];
    }
    $stmt->close();
}

// Save into results and responses
$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare("INSERT INTO results (user_name, score, total) VALUES (?, ?, ?)");
    $tempName = null; // can be extended to accept a username
    $stmt->bind_param('sii', $tempName, $score, $total);
    $stmt->execute();
    $result_id = $stmt->insert_id;
    $stmt->close();

    $rstmt = $mysqli->prepare("INSERT INTO responses (result_id, question_id, choice_id) VALUES (?, ?, ?)");
    foreach ($insertResponses as $ir) {
        $cid = $ir['choice_id'] === null ? 0 : $ir['choice_id']; // store 0 to mark unanswered choice
        $rstmt->bind_param('iii', $result_id, $ir['question_id'], $cid);
        $rstmt->execute();
    }
    $rstmt->close();

    $mysqli->commit();

    echo json_encode(['success'=>true, 'result_id'=>$result_id, 'score'=>$score, 'total'=>$total]);
    exit;
} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success'=>false, 'error'=>'Database error: '.$e->getMessage()]);
    exit;
}
?>
