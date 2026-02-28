<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'SeaBattle.php';

session_start();

$gameMode = isset($_GET['mode']) ? $_GET['mode'] : 'single';

// Проверяем, нужно ли форсировать перезагрузку
$forceReload = isset($_GET['force_reload']) && $_GET['force_reload'] == 1;

if (!isset($_SESSION['game']) || (isset($_SESSION['game_mode']) && $_SESSION['game_mode'] !== $gameMode) || $forceReload) 
{
    // Если это форсированная перезагрузка, создаем новую игру с теми же параметрами
    if ($forceReload && isset($_SESSION['net_game']) && isset($_SESSION['player_role'])) {
        $gameId = $_SESSION['net_game'];
        $playerRole = $_SESSION['player_role'];
        $_SESSION['game'] = new SeaBattle($gameId, $playerRole);
        $_SESSION['game_mode'] = 'network';
        
        // Убираем параметр force_reload из URL после использования
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    } else {
        switch ($gameMode) 
        {
            case 'single':
                $_SESSION['game'] = new SeaBattle();
                $_SESSION['game_mode'] = 'single';
                break;
                
            case 'network':
                if (!isset($_SESSION['net_game'])) 
                {
                    header('Location: NetSeaBattle.php');
                    exit;
                }
                
                $gameId = $_SESSION['net_game'];
                $playerRole = $_SESSION['player_role'];

                if (!isset($_SESSION['game'])) 
                {
                    $_SESSION['game'] = new SeaBattle($gameId, $playerRole);
                    $_SESSION['game_mode'] = 'network';
                }
                break;
                
            default:
                $_SESSION['game'] = new SeaBattle();
                $_SESSION['game_mode'] = 'single';
                break;
        }
    }
}   

$game = $_SESSION['game'];
$gameMode = $_SESSION['game_mode'];
$message = 'Расстановка кораблей';

// Синхронизация сетевого состояния
$syncGame = $game->syncGameState();
if (!empty($syncGame))
{
    $message = $syncGame['message'];
}

// Флаг, определяющий, началась ли игра (оба игрока готовы)
$allReady = $game->Start();

// Обработка действий
if (isset($_POST['action']))
{
    switch ($_POST['action'])
    {
        case 'place_ship':
            if (isset($_POST['coord']))
            {
                $in_coord = htmlspecialchars(mb_strtoupper($_POST['coord']));    
                if (preg_match('/^([АБВГДЕЖЗИК])([1-9]|10)$/iu', $in_coord, $coord))
                {    
                    $row = $coord[2] - 1;
                    $col = $game->getColByName($coord[1]);
                    
                    if ($game->placePlayerShip($row, $col)) 
                    {
                        // После размещения корабля проверяем готовность
                        $allReady = $game->Start();
                        
                        if ($allReady) {
                            $message = 'Начало игры. Стреляй!';
                        } else {
                            if ($gameMode === 'network')    
                            {
                                if ($game->isPlayerReady())
                                    $message = 'Ждем соперника...';
                                else
                                    $message = 'Корабль размещен!';
                            }
                            else
                                $message = 'Корабль размещен!';
                        }
                    } 
                    else 
                    {
                        $message = 'Не удалось разместить корабль в этой позиции!';
                    }
                } 
                else 
                {
                    $message = 'Неверный формат координат!';
                }
            }
            break;
            
        case 'qiuck_place_ship':
            $game->quickPlaceShip();        
            
            // После быстрой расстановки проверяем готовность
            $allReady = $game->Start();
            
            if ($gameMode === 'network') {
                if ($allReady) {
                    $message = ($game->getPlayer() === $game->getCurrentPlayer()) ? 
                        "Ваш ход! Стреляйте!" : "Ход соперника...";
                } else {
                    $myReady = $game->isPlayerReady();
                    
                    if ($myReady) {
                        $message = 'Ждем соперника...';
                    } else {
                        $message = 'Корабли размещены!';
                    }
                }
            } else {
                $message = 'Корабли размещены!';
            }
            
            // Сохраняем игру и делаем редирект
            $_SESSION['game'] = $game;
            
            // Перенаправляем с параметром для обновления
            $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?');
            $redirectUrl .= '?mode=' . $gameMode . '&t=' . time();
            header('Location: ' . $redirectUrl);
            exit;
            break;
            
        case 'shiftOrientation':
            $game->shiftOrientation();
            break;
            
        case 'shoot':
            if (isset($_POST['coord']))
            {
                $in_coord = mb_strtoupper($_POST['coord']);    
                if (preg_match('/^([АБВГДЕЖЗИК])([1-9]|10)$/iu', $in_coord, $coord))
                {    
                    $row = $coord[2] - 1;
                    $col = $game->getColByName($coord[1]);

                    if ($gameMode === 'network')
                    {
                        // Сетевая игра
                        $player = $game->getPlayer(); // 'player1' или 'player2'
                        $res = $game->networkShoot($row, $col, $player);
                        if ($res['game_over'])
                        {
                            $message = ($player === 'player1') ? 'Поздравляем! Вы победили!!!' : 'Упс, Вы проиграли. :(';
                        }
                        else
                        {
                            $message = $res['result'];
                        }
                        
                        // Принудительно синхронизируем после выстрела
                        $game->syncGameState();
                    }
                    else
                    {
                        // Одиночная игра
                        $res = $game->playerShoot($row, $col);
                        if ($res['game_over'])
                        {
                            $message = 'Поздравляем! Вы победили!!!';
                        }
                        else
                        {
                            $message = $res['result'];
                            if (!$res['hit'])
                            {
                                $res = $game->computerShoot();
                                if ($res['game_over'])
                                {
                                    $message = 'Эх, Вы проиграли :`(';
                                }
                                else
                                {
                                    $message = 'Компьютер -> ' . $res['result'];
                                }
                            }    
                        }
                    }
                }
                else 
                {
                    $message = 'Неверный формат координат!';
                }
            }
            
            // После выстрела обновляем состояние и делаем редирект
            if ($gameMode === 'network') {
                $allReady = $game->Start();
                $_SESSION['game'] = $game;
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?mode=' . $gameMode . '&t=' . time());
                exit;
            }
            break;

        case 'restart':
            session_destroy();
            header('Location: ' . $_SERVER['PHP_SELF'] . '?mode=' . $gameMode);            
            exit;
            break;
    }

    // После обработки действия обновляем флаг готовности
    $allReady = $game->Start();
    $_SESSION['game'] = $game;
}

// Функция для определения, нужно ли автообновление
$autoRefresh = false;
if ($gameMode === 'network') {
    // Автообновление нужно, если:
    // 1. Игра не началась (не все готовы)
    // 2. Игра началась, но не ваш ход
    if (!$allReady || ($allReady && $game->getCurrentPlayer() !== $game->getPlayer())) {
        $autoRefresh = true;
    }
}

// Сбрасываем флаг быстрой расстановки после отображения
if ($game->isQuickPlacePerformed()) {
    $game->resetQuickPlaceFlag();
}

// Получаем доски для отображения ПОСЛЕ всей обработки
$myBoardHtml = $game->drawMyBoard();
$enemyBoardHtml = $game->drawEnemyBoard();

// Дополнительная проверка - если оба готовы, но сообщение все еще "Расстановка кораблей"
if ($gameMode === 'network' && $allReady && $message === 'Расстановка кораблей') {
    $currentPlayer = $game->getCurrentPlayer();
    $myNumber = $game->getPlayer();
    $message = ($currentPlayer === $myNumber) ? "Ваш ход! Стреляйте!" : "Ход соперника...";
}
?>
<!DOCTYPE html> 
<html>
<head>    
    <title>Морской бой</title>
    <meta charset="utf-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <?php if ($autoRefresh): ?>
    <meta http-equiv="refresh" content="2">
    <?php endif; ?>
    <style>
        .game_main { display: flex; flex-direction: column; align-items: center; font-family: Arial, sans-serif; }
        .battle_board { display: flex; gap: 50px; margin: 20px; flex-wrap: wrap; justify-content: center; }
        .board { margin: 10px; }
        .matrix { border-collapse: collapse; background-color: #e6f7ff; }
        .matrix td, .matrix th { width: 35px; height: 35px; text-align: center; vertical-align: middle; border: 1px solid #999; }
        .matrix th { background-color: #f0f0f0; font-weight: bold; }
        .message { padding: 15px; margin: 10px; border-radius: 5px; background-color: #e7f3fe; border-left: 6px solid #2196F3; }
        .control { margin: 20px; }
        /* раскраска состояний игрового поля */
        .cell-empty { background-color: #e6f7ff; }
        .cell-miss  { background-color: #6db0cf; }
        .cell-hit   { background-color: #ff9165; }
        .cell-ship  { background-color: #88ff9c; }
        .disabled-form { opacity: 0.5; pointer-events: none; }
        .turn-indicator { font-weight: bold; color: #2196F3; }
        .auto-refresh-indicator { 
            font-size: 0.8em; 
            color: #666; 
            margin-top: 5px;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { opacity: 0.5; }
            50% { opacity: 1; }
            100% { opacity: 0.5; }
        }
    </style>  
</head>    
<body>
    <div class="game_main">
        <h2>Морской бой</h2>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message) ?></div>
        <?php endif ?>
        <?php if ($autoRefresh): ?>
            <div class="auto-refresh-indicator">⏳ Ожидание соперника... Страница обновится автоматически</div>
        <?php endif; ?>
    </div>      
    <div class="battle_board">
        <div class="board">
            <div>Мой флот</div>
            <?php echo $myBoardHtml ?>
        </div>      
        <div class="board">
            <div>Флот противника</div>
            <?php echo $enemyBoardHtml ?>
        </div>      
    </div>
    <div class="control">
        <?php if (!$allReady): ?>
            <!-- Режим расстановки кораблей -->
            <?php if ($game->getUnPlaceCount() > 0): ?>
                <form method="POST">
                    <div>Осталось разместить: <?php echo $game->getUnPlaceCount() ?> кораблей</div>
                    <?php $ship = $game->getCurPlaceShip() ?>
                    <div>Текущий корабль: <?php echo $ship->getName() ?> (размер: <?php echo $ship->getSize() ?>, <?php echo $game->getCurOrientation() ?>)</div>
                    <div>
                        <label>Введите координату (А1-К10): </label>
                        <input type="text" name="coord" required style="width: 50px;">
                        <button type="submit" name="action" value="place_ship">Разместить</button>
                    </div>
                </form>    
                <form method="POST">
                    <button type="submit" name="action" value="shiftOrientation">Повернуть</button>
                    <button type="submit" name="action" value="qiuck_place_ship">Быстрая расстановка</button>                
                </form>    
            <?php endif; ?>
        <?php elseif (!$game->isGameOver()): ?>
            <!-- Режим стрельбы -->
            <?php 
            $canShoot = true;
            if ($gameMode === 'network') {
                $currentPlayer = $game->getCurrentPlayer();
                $myNumber = $game->getPlayer();
                $canShoot = ($currentPlayer === $myNumber);
            }
            ?>
            <form method="POST" <?php echo !$canShoot ? 'class="disabled-form"' : ''; ?>>
                <div>
                    <label>Введите координату (А1-К10): </label>
                    <input type="text" name="coord" id="coord_input" required style="width: 50px;" 
                           <?php echo !$canShoot ? 'disabled' : ''; ?>>
                    <button type="submit" name="action" value="shoot" id="shoot_btn" 
                            <?php echo !$canShoot ? 'disabled' : ''; ?>>
                        Огонь!
                    </button>
                </div>
                <?php if (!$canShoot): ?>
                    <div class="turn-indicator">Сейчас не ваш ход! Страница обновится автоматически когда соперник сделает ход</div>
                <?php endif; ?>
            </form>    
        <?php else: ?>
            <!-- Игра окончена -->
            <div class="message">Игра завершена. Начните новую.</div>
        <?php endif; ?>

        <br><br>    
        <form method="POST">
            <button type="submit" name="action" value="restart">Начать заново...</button>
        </form>    
        <br>
        <textarea id="shoot_log" readonly style="width:80%; height: 200px; resize:vertical; font-family: monospace; font-size: 12px; padding: 10px"><?php
            echo htmlspecialchars(implode(PHP_EOL, $game->log->getLogText()));
        ?></textarea>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () 
    {
        const config = {
            empty:  '<?php echo $config['sea']['board']['empty'] ?>',
            miss:   '<?php echo $config['sea']['board']['miss'] ?>',
            hit:    '<?php echo $config['sea']['board']['hit'] ?>',
            ship:   '<?php echo $config['sea']['board']['ship'] ?>'
        };

        function setCellColor() 
        {
            document.querySelectorAll('.matrix td').forEach(function(cell) 
            {
                const cellValue = cell.textContent.trim();
                
                // Удаляем все классы состояния
                cell.classList.remove('cell-empty', 'cell-miss', 'cell-hit', 'cell-ship');
                
                // Добавляем соответствующий класс
                if (cellValue === config.miss) {
                    cell.classList.add('cell-miss');
                } else if (cellValue === config.hit) {
                    cell.classList.add('cell-hit');
                } else if (cellValue === config.ship) {
                    cell.classList.add('cell-ship');
                } else {
                    cell.classList.add('cell-empty');
                }
            });
        }

        // Вызываем функцию при загрузке
        setCellColor();

        // Для сетевой игры добавляем наблюдатель за изменениями DOM
        <?php if ($gameMode === 'network'): ?>
        const observer = new MutationObserver(function(mutations) {
            setCellColor();
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        <?php endif ?>

        const coordInput = document.querySelector('input[name="coord"]');
        if (coordInput) {
            coordInput.focus();
            coordInput.select();
        }

        const textarea = document.getElementById('shoot_log');
        const savedHeight = localStorage.getItem('shoot_log_height');
        if (savedHeight) {
            textarea.style.height = savedHeight;
        }
        
        textarea.addEventListener('input', function() {
            localStorage.setItem('shoot_log_height', textarea.style.height);
        });
        
        textarea.addEventListener('mouseup', function() {
            localStorage.setItem('shoot_log_height', textarea.style.height);
        });

        textarea.scrollTop = textarea.scrollHeight;

        const coorInput = document.getElementById('coord_input');
        const btnShoot = document.getElementById('shoot_btn');

        function coorValidate(coor)
        {
            return /^[АБВГДЕЖЗИКабвгдежзик]([1-9]|10)$/i.test(coor);
        }

        function updateShootState()
        {
            if (coorInput) {
                const cInput = coorInput.value.trim();
                btnShoot.disabled = !coorValidate(cInput);
            }
        }

        if (coorInput) {
            coorInput.addEventListener('input', updateShootState);
            updateShootState();
            coorInput.focus();
        }

        // Сохраняем введенные координаты в localStorage
        const placeCoordInput = document.querySelector('input[name="coord"]');
        if (placeCoordInput) {
            const savedCoord = localStorage.getItem('last_coord');
            if (savedCoord) {
                placeCoordInput.value = savedCoord;
            }
            
            placeCoordInput.addEventListener('input', function() {
                localStorage.setItem('last_coord', this.value);
            });
        }

        if (coorInput) {
            const savedCoord = localStorage.getItem('last_shoot_coord');
            if (savedCoord) {
                coorInput.value = savedCoord;
            }
            
            coorInput.addEventListener('input', function() {
                localStorage.setItem('last_shoot_coord', this.value);
            });
        }

        <?php if ($gameMode === 'network'): ?>
        // Добавляем обработчик для ручного обновления по клавише F5
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F5') {
                e.preventDefault();
                location.reload();
            }
        });
        
        // Дополнительная проверка каждые 2 секунды
        setInterval(function() {
            // Проверяем, изменилось ли сообщение
            const messageDiv = document.querySelector('.message');
            if (messageDiv) {
                const currentMessage = messageDiv.textContent;
                // Если сообщение все еще "Расстановка кораблей", но игра должна начаться
                if (currentMessage === 'Расстановка кораблей' && <?php echo $allReady ? 'true' : 'false'; ?>) {
                    location.reload();
                }
            }
        }, 2000);
        <?php endif ?>
    });        
    </script>
</body>
</html>