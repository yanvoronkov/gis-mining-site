<?php

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
  header('Access-Control-Allow-Headers: token, Content-Type');
  header('Access-Control-Max-Age: 1728000');
  header('Content-Length: 0');
  header('Content-Type: text/plain');
  exit;
}
header('Access-Control-Allow-Origin: *');

try {
  require __DIR__.'/config.php';
  $c = $app1760940257Config;

  $d = json_decode(file_get_contents('php://input'), true);
  if (empty($d)) {
    die(json_encode(['success' => false]));
  }

  $message = '';
  if (!empty($d['name'])) $message .= "<b>Имя:</b> {$d['name']}<br>";
  if (!empty($d['phone'])) $message .= "<b>Телефон:</b> {$d['phone']}<br>";
  if (!empty($d['comment'])) $message .= "<b>Комментарий:</b> ".nl2br(trim($d['comment'])).'<br>';

  if (!empty($d['data'])) {
    $message .= '<br>';

    // Безопасная функция экранирования
    $esc = function($v) {
      if (is_null($v)) return '—';
      if (is_bool($v)) return $v ? 'true' : 'false';
      if (is_array($v) || is_object($v)) return htmlspecialchars(json_encode($v, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
      return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    };

    $data = $d['data'];

    if (!empty($data['coin'])) {
      $coin = $data['coin'];
      $message .= "<b>Монета:</b> {$esc($coin['code'])}<br><br>";
    }

    if (!empty($data['device'])) {
      $dev = $data['device'];
      $message .= '<b>Устройство:</b><br>';
      $message .= 'ID: ' . $esc($dev['id']) . '<br>';
      $message .= 'Бренд: ' . $esc($dev['brand']) . '<br>';
      $message .= 'Название: ' . $esc($dev['name']) . '<br>';
      $message .= 'Количество: ' . $esc($dev['quantity']) . '<br>';
      $message .= 'Цена за единицу: ' . $esc($dev['customPrice']) . ' ₽<br>';
      $message .= 'Потребление: ' . $esc($dev['customPower']) . ' ' . $esc($dev['powerUnit'] ?? '') . '<br>';
      $message .= 'Хэшрейт: ' . $esc($dev['hashrate']) . ' ' . $esc($dev['hashrateUnit'] ?? '') . '<br>';
      $message .= 'Итого: ' . $esc($dev['total']) . ' ₽<br><br>';
    }

    if (!empty($data['settings'])) {
      $s = $data['settings'];
      $message .= '<b>Настройки:</b><br>';
      $message .= 'Стоимость электроэнергии: ' . $esc($s['customPowerPrice']) . ' ₽<br>';
      $message .= "Курс {$esc($coin['code'])}: " . $esc($s['exchangeRateCoinUsed']) . '<br>';
      $message .= 'Курс USD: ' . $esc($s['exchangeRateUsdUsed']) . '<br><br>';
    }

    if (!empty($data['result'])) {
      $r = $data['result'];
      $message .= '<b>Результаты расчета за месяц:</b><br>';
      $message .= 'Прибыль: ' . $esc($r['profit']) . ' ₽<br>';
      $message .= 'Доходность: ' . $esc($r['income']) . ' ₽, ' . $esc($r['incomePercent']) . '%/год<br>';
      $message .= 'Окупаемость: ' . $esc($r['paybackPeriod']) . ' мес<br>';
      $message .= 'Оборудование: ' . $esc($r['equipmentPrice']) . ' ₽<br>';
      $message .= 'Электроэнергия: ' . $esc($r['powerPrice']) . ' ₽, ' . $esc($r['power']) . ' кВт<br>';
    }
  }

  require __DIR__.'/vendor/autoload.php';

  $mail = new \PHPMailer\PHPMailer\PHPMailer;

  if (isset($c['mailer']['smtp']) && $c['mailer']['smtp'] === true) {
    $mail->isSMTP();
    $mail->Host = $c['mailer']['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $c['mailer']['username'];
    $mail->Password = $c['mailer']['password'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = $c['mailer']['port'];
  }

  $mail->CharSet = 'UTF-8';
  $mail->setFrom($c['mailer']['from'][0], $c['mailer']['from'][1]);
  $mail->addAddress($c['mailer']['to']);
  $mail->isHTML(true);
  $mail->Subject = $c['mailer']['subject'];
  $mail->msgHTML($message);

  $res = $mail->send();

  echo json_encode([
    'success' => $res ? true : false,
    'message' => $res ? $c['mailer']['successMessage'] : $c['mailer']['errorMessage'],
    'phpmailer' => [
      'result' => $res,
      'error' => $mail->ErrorInfo,
    ],
  ]);
} catch (Exception $err) {
  die(json_encode(['success' => false]));
}
