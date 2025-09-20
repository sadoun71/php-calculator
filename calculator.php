<?php
header('Content-Type: application/json; charset=utf-8');

$expression = $_POST['expression'] ?? '';
$expression = trim($expression);

if ($expression === '') {
  echo json_encode(['ok' => false, 'error' => 'لا يوجد مدخلات']); exit;
}
if (strlen($expression) > 200) {
  echo json_encode(['ok' => false, 'error' => 'تعبير طويل جداً']); exit;
}
if (!preg_match('/^[0-9+\-*\/().\s]+$/', $expression)) {
  echo json_encode(['ok' => false, 'error' => 'محارف غير مسموحة']); exit;
}

try {
  $result = evaluate_expression($expression);
  if (!is_finite($result)) throw new Exception('نتيجة غير صالحة');
  $result = rtrim(rtrim(sprintf('%.12F', $result), '0'), '.');
  echo json_encode(['ok' => true, 'result' => $result]);
} catch (Exception $ex) {
  echo json_encode(['ok' => false, 'error' => $ex->getMessage()]);
}

function evaluate_expression(string $expr): float {
  $tokens = tokenize($expr);
  $rpn = shunting_yard($tokens);
  return rpn_eval($rpn);
}

function tokenize(string $expr): array {
  $tokens = []; $num = '';
  for ($i = 0; $i < strlen($expr); $i++) {
    $ch = $expr[$i];
    if (ctype_space($ch)) continue;
    if (ctype_digit($ch) || $ch === '.') { $num .= $ch; continue; }
    if ($num !== '') { $tokens[] = $num; $num = ''; }
    if (in_array($ch, ['+','-','*','/','(',')'])) $tokens[] = $ch;
    else throw new Exception('رمز غير متوقع');
  }
  if ($num !== '') $tokens[] = $num;
  return $tokens;
}

function shunting_yard(array $tokens): array {
  $out = []; $ops = [];
  $prec = ['+' => 1, '-' => 1, '*' => 2, '/' => 2];
  foreach ($tokens as $t) {
    if (is_numeric($t)) { $out[] = $t; }
    elseif (in_array($t, ['+','-','*','/'])) {
      while ($ops && end($ops) !== '(' && $prec[end($ops)] >= $prec[$t]) {
        $out[] = array_pop($ops);
      }
      $ops[] = $t;
    }
    elseif ($t === '(') { $ops[] = $t; }
    elseif ($t === ')') {
      while ($ops && end($ops) !== '(') { $out[] = array_pop($ops); }
      if (!$ops) throw new Exception('أقواس غير متطابقة');
      array_pop($ops);
    }
  }
  while ($ops) {
    $op = array_pop($ops);
    if ($op === '(') throw new Exception('أقواس غير متطابقة');
    $out[] = $op;
  }
  return $out;
}

function rpn_eval(array $rpn): float {
  $st = [];
  foreach ($rpn as $t) {
    if (is_numeric($t)) { $st[] = (float)$t; }
    elseif (in_array($t, ['+','-','*','/'])) {
      if (count($st) < 2) throw new Exception('صيغة غير صحيحة');
      $b = array_pop($st); $a = array_pop($st);
      switch ($t) {
        case '+': $st[] = $a + $b; break;
        case '-': $st[] = $a - $b; break;
        case '*': $st[] = $a * $b; break;
        case '/': if ($b == 0.0) throw new Exception('قسمة على صفر'); $st[] = $a / $b; break;
      }
    }
  }
  if (count($st) !== 1) throw new Exception('صيغة غير صحيحة');
  return $st[0];
}
