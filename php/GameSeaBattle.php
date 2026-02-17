<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'SeaBattle.php';

session_start();

$gameMode = isset($_GET['mode']) ? $_GET['mode'] : 'single';

if (!isset($_SESSION['game']) || (isset($_SESSION['game_mode']) && $_SESSION['game_mode'] !== $gameMode)) 
{
    switch ($gameMode) 
    {
        case 'single':
            $_SESSION['game'] = new SeaBattle();
            $_SESSION['game_mode'] = 'single';
            break;
            
        case 'network':
            if (!isset($_SESSION['net_game'])) 
            {
                header('Location:NetSeaBattle.php');
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

$game = $_SESSION['game'];
$gameMode = $_SESSION['game_mode'];
$message = 'Расстановка кораблей';

$syncGame = $game->syncGameState();
if ($syncGame !== [])
{
    $message = $syncGame['message'];
}


if(isset($_POST['action']))
{
    switch($_POST['action'])
    {
        case 'place_ship':
            if (isset($_POST['coord']))
            {
                $in_coord = htmlspecialchars(mb_strtoupper($_POST['coord']));    
                if (preg_match('/^([АБВГДЕЖЗИК])([1-9]|10)$/iu', $in_coord, $coord))
                {    
                    $row = $coord[2] - 1;
                    $col = $game->getColByName($coord[1]);
                    
                    // ДОБАВЛЕНО: вызов метода размещения корабля
                    if ($game->placePlayerShip($row, $col)) 
                    {
                      if ($game->Start())
                        $message = 'Начало игры. Стреляй!';
                      else
                      {
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
            if ($game->Start())
            {
                $res = $game->syncGameState();
                $message = $res['message'];
            }
            else
                $message = 'Ждем соперника...';
            break;
        case 'shiftOrientation':
            $game->shiftOrientation();
            break;
        case 'shoot':
            if ($gameMode !== 'network')
            {
                if (isset($_POST['coord']))
                {
                    $in_coord = mb_strtoupper($_POST['coord']);    
                    if (preg_match('/^([АБВГДЕЖЗИК])([1-9]|10)$/iu', $in_coord, $coord))
                    {    
                        $row = $coord[2] - 1;
                        $col = $game->getColByName($coord[1]);
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
                                    $message = 'Компьютер -> '.$res['result'];
                                }
                            }    
                        }
                    }
                    else 
                    {
                        $message = 'Неверный формат координат!';
                    }
                }
            }
            break;
        case 'neworkShoot':
            if ($gameMode === 'network')
            {
                if (isset($_POST['coord']))
                {
                    $in_coord = mb_strtoupper($_POST['coord']);    
                    if (preg_match('/^([АБВГДЕЖЗИК])([1-9]|10)$/iu', $in_coord, $coord))
                    {
                        $row = $coord[2] - 1;
                        $col = $game->getColByName($coord[1]);
                        $player = $game->getPlayer();
                        $res = $game->networkShoot($row, $col, $player);
                        //--- разборчик ---- 
                        if ($res['game_over'])
                        {
                            $message = ($playerRole === 'player1') ? 'Поздравляем! Вы победили!!!' : 'Упс, Вы проиграли. :(';
                        }
                        else
                        {
                            $message = $res['result'];
                            if (!$res['hit'])
                            {
                                $message = 'Ход соперникка...';
                            }    
                        }
                    }
                    else 
                    {
                        $message = 'Неверный формат координат!';
                    }
                }
            }
            break;
        case 'restart':
            session_destroy();
            header('Location: ' . $_SERVER['PHP_SELF']);            
            break;
    }

    if ($gameMode === 'network')    
        $allReady = $game->Start(); // ЗАМЕНИТЬ !!!
    $_SESSION['game'] = $game;
}
?>
<!DOCTYPE html> 
<html>
<head>    
  <title>Морской бой</title>
    <style>
        .game_main { display: flex; flex-direction: column; align-items: center; font-family: Arial, sans-serif; }
        .battle_board { display: flex; gap: 50px; margin: 20px; }
        .board { margin: 10px; }
        .matrix { border-collapse: collapse; background-color: #e6f7ff; }
        .matrix td, .matrix th { width: 35px; height: 35px; text-align: center; vertical-align: middle; border: 1px solid #999; }
        .matrix th { background-color: #f0f0f0; font-weight: bold; }
        .message { padding: 15px; margin: 10px; border-radius: 5px; background-color: #e7f3fe; border-left: 6px solid #2196F3; }
        .control { margin: 20px; }
        /* раскраска состояний игрового поля */
        .cell-empty {background-color: #e6f7ff;}
        .cell-miss  {background-color: #6db0cfff;}
        .cell-hit   {background-color: #ff9165ff;}
        .cell-ship  {background-color: #88ff9cff;}
    </style>  
</head>    
<body>
    <div class="game_main">
        <h2>Морской бой</h2>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message) ?></div>
        <?php endif ?>
    </div>      
    <div class="battle_board">
        <div class="board">
            <div>Мой флот</div>
            <?php echo $game->drawPlayerBoard() ?>
        </div>      
        <div class="board">
            <div>Компьютерный флот</div>
            <?php echo $game->drawComputerBoard() ?>
        </div>      
    </div>      
    <div class="control">
        <?php if(!$game->Start()): ?>
            <!-- если осталось больше 0 -->
            <?php if($game->getUnPlaceCount() > 0): ?>
                <form method="POST">
                    <div>Осталось разместить: <?php echo $game->getUnPlaceCount() ?> кораблей</div>
                    <?php $ship = $game->getCurPlaceShip() ?>
                    <div>Текущий корабль: <?php echo $ship->getName() ?> (размер: <?php echo $ship->getSize() ?>, <?php echo $game->getCurOrientation() ?>)</div>
                    <div>
                        <label>Введите координату (А1-К10, можно с маленькими буквами): </label>
                        <input type="text" name="coord" required  style="width: 50px;">
                        <button type="submit" name="action" value='place_ship'>Разместить</button>
                    </div>
                </form>    
                <form method="POST">
                    <div>
                        <button type="submit" name="action" value='shiftOrientation'>Повернуть</button>
                    <div>
                    <br>
                    <div>
                        <button type="submit" name="action" value='qiuck_place_ship'>Быстрая расстановка</button>                
                    <div>
                </form>    
            <?php endif ?>
        <?php elseif (!$game->isGameOver()) : ?>    
            <form method="POST">
                <div>
                    <label>Введите координату (А1-К10, можно с маленькими буквами): </label>
                    <input type="text" name="coord" id="coord_input" required style="width: 50px;">
                    <button type="submit" name="action" value='shoot' id="shoot_btn">Огонь!</button>
                </div>
            </form>    
        <?php else: ?>    
        <?php endif ?>
        <br><br>    
        <form method="POST">
            <button type="submit" name="action" value='restart'>Начать заново...</button>
        </form>    
        <br>
        <textarea id="shoot_log" readonly style="width:80%; height: 200px; resize:vertical; font-family: monospace; font-size: 12px; padding: 10px"><?php
echo htmlspecialchars(implode(PHP_EOL, $game->log->getLogText()));
        ?>
        </textarea>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () 
    {
        const config = 
        {
            empty:  '<?php echo $config['sea']['board']['empty'] ?>',
            miss:   '<?php echo $config['sea']['board']['miss'] ?>',
            hit:    '<?php echo $config['sea']['board']['hit'] ?>',
            ship:   '<?php echo $config['sea']['board']['ship'] ?>'
        };

        function setCellColor() 
        {
            function getCellState(cellValue)
            {
                switch (cellValue)
                {
                    case config.miss:   return 'cell-miss';
                    case config.hit:    return 'cell-hit';
                    case config.ship:   return 'cell-ship';
                    default:            return 'cell-empty';
                }
            }

            document.querySelectorAll('.matrix td').forEach(function(cell) 
            {
                const cellValue = cell.textContent.trim();
                const cellClass = getCellState(cellValue);
                cell.classList.remove('cell-empty', 'cell-miss', 'cell-hit', 'cell-ship');
                cell.classList.add(cellClass);
            });
        }

        setCellColor();

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
            const cInput = coorInput.value.trim();
            const isEnable = coorValidate(cInput);

            btnShoot.disabled = !isEnable;
        }

        if (coorInput) {
            coorInput.addEventListener('input', updateShootState);
            updateShootState();
            coorInput.focus();
        }

        <?php if ($gameMode === 'network' && $allReady): ?>
        setTimeout(function() {
            location.reload();
        }, 5000);
        <?php endif ?>

    });        
    </script>

</body>
</html>    