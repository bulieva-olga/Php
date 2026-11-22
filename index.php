<?php
session_start();

$question = "Кто ты из Винкс?";
$options = [
    "Блум",
    "Стелла",
    "Муза",
    "Флора",
    "Текна"
];

$file = 'poll.json';

// Обработка нажатия на кнопку
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote'])) {
    $voteIndex = (int)$_POST['vote'];
    
    if (isset($options[$voteIndex])) {
        $data = json_decode(file_get_contents($file), true);
        $data['votes'][$voteIndex]++; 
        $data['total']++;             
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        
        $_SESSION['just_voted'] = true;
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Логика показа
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['just_voted'])) {
        $showResults = true;
        unset($_SESSION['just_voted']); 
    } else {
        $showResults = false;
    }
} else {
    $showResults = false;
}

$data = json_decode(file_get_contents($file), true);
$totalVotes = $data['total'];

function getPercent($count, $total) {
    if ($total == 0) return 0;
    return round(($count / $total) * 100, 1);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Опрос: Винкс</title>
    <style>
        /* Базовые стили */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #e7ebf0; display: flex; justify-content: center; padding-top: 50px; }
        
        /* Контейнер (добавил overflow: hidden, чтобы ничего не вылезало) */
        .poll-container { background: #fff; border-radius: 12px; padding: 20px; width: 350px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; box-sizing: border-box; }
        
        h3 { margin-top: 0; font-size: 18px; color: #000; }
        .option { margin-bottom: 8px; }
        
        /* Кнопки голосования */
        .vote-btn { 
            width: 100%; text-align: left; background: #f1f3f5; border: 1px solid #e1e4e8; padding: 12px 15px; 
            border-radius: 8px; cursor: pointer; font-size: 15px; transition: 0.2s; color: #000;
            box-sizing: border-box; /* Чтобы padding не увеличивал ширину */
        }
        .vote-btn:hover { background: #e2e6ea; }
        
        /* Блоки статистики */
        .result-item { 
            position: relative; /* Нужно для позиционирования заливки */
            width: 100%; 
            background: #f8f9fa; /* Цвет фона блока */
            border: 1px solid #dee2e6; 
            padding: 12px 15px; 
            border-radius: 8px; 
            font-size: 15px; 
            color: #000; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-sizing: border-box; /* Чтобы padding не ломал ширину */
            overflow: hidden; /* Чтобы заливка не вылезала за скругленные углы */
        }
        
        /* Розовая заливка (как в ТГ) */
        .progress-fill {
            position: absolute;
            left: 0; 
            top: 0; 
            bottom: 0;
            background-color: #ffb3e6; /* Светло-розовый цвет */
            z-index: 1; /* Под текстом */
            transition: width 0.5s ease-in-out; /* Плавная анимация */
        }
        
        /* Текст поверх заливки */
        .result-text, .percent { 
            position: relative; 
            z-index: 2; /* Над заливкой */
        }
        
        .percent { font-weight: bold; color: #333; }
        .stats { margin-top: 15px; font-size: 13px; color: #707579; text-align: center; }
    </style>
</head>
<body>

<div class="poll-container">
    <h3><?= htmlspecialchars($question) ?></h3>
    <div style="font-size: 13px; color: #707579; margin-bottom: 15px;">Анонимный опрос</div>

    <?php if (!$showResults): ?>
        <!-- ЭКРАН 1: Кнопки -->
        <form method="POST" action="">
            <?php foreach ($options as $index => $option): ?>
                <div class="option">
                    <button type="submit" name="vote" value="<?= $index ?>" class="vote-btn">
                        <?= htmlspecialchars($option) ?>
                    </button>
                </div>
            <?php endforeach; ?>
        </form>

    <?php else: ?>
        <!-- ЭКРАН 2: Статистика с розовой заливкой -->
        <?php foreach ($options as $index => $option): 
            $count = $data['votes'][$index];
            $percent = getPercent($count, $totalVotes);
        ?>
            <div class="option">
                <div class="result-item">
                    <!-- Розовая полоса (ширина = проценту) -->
                    <div class="progress-fill" style="width: <?= $percent ?>%;"></div>
                    
                    <!-- Текст -->
                    <span class="result-text"><?= htmlspecialchars($option) ?></span>
                    <span class="percent"><?= $percent ?>%</span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Всего голосов -->
    <div class="stats">
        Всего голосов: <?= $totalVotes ?>
    </div>
</div>

</body>
</html>