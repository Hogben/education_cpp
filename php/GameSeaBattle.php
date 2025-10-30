<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'SeaBattle.php';

session_start();
if(!isset($_SESSION['game']))
{
    $_SESSION['game'] = new SeaBattle();
}

$game = $_SESSION['game'];
$message = 'Расстановка кораблей';

if(isset($_POST['action']))
{
    switch($_POST['action'])
    {
        case 'place_ship':
            if (isset($_POST['coord']))
            {
                $in_coord = mb_strtoupper($_POST['coord']);    
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
                        $message = 'Корабль размещен!';
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
            $message = 'Начало игры. Стреляй!';
            break;
        case 'shiftOrintation':
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
            break;
        case 'restart':
            session_destroy();
            header('Location: ' . $_SERVER['PHP_SELF']);            
            break;
    }
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
            <form method="POST">
                <div>Осталось разместить: <?php echo $game->getUnPlaceCount() ?> кораблей</div>
                <?php $ship = $game->getCurPlaceShip() ?>
                <div>Текущий корабль: <?php echo $ship->getName() ?> (размер: <?php echo $ship->getSize() ?>, <?php echo $game->getCurOrientation() ?>)</div>
                <div>
                    <label>Введите координату (А1-К10, можно с маленькими буквами): </label>
                    <input type="text" name="coord" required>
                    <button type="submit" name="action" value='place_ship'>Разместить</button>
                </div>
            </form>    
            <form method="POST">
                <div>
                    <button type="submit" name="action" value='shiftOrintation'>Повернуть</button>
                <div>
                <br>
                <div>
                    <button type="submit" name="action" value='qiuck_place_ship'>Быстрая расстановка</button>                
                <div>
            </form>    
        <?php elseif (!$game->isGameOver()) : ?>    
            <form method="POST">
                <div>
                    <label>Введите координату (А1-К10, можно с маленькими буквами): </label>
                    <input type="text" name="coord" required>
                    <button type="submit" name="action" value='shoot'>Огонь!</button>
                </div>
            </form>    
        <?php else: ?>    
        <?php endif ?>
        <br><br>    
        <form method="POST">
            <button type="submit" name="action" value='restart'>Начать заново...</button>
        </form>    
        <textarea id="shoot_log" readonly style="width:100%; height: 200px; resize:vertical; font-famaly: monospace; font-size: 12px; padding: 10px">
        <?php
           echo htmlspecialchars($game->getLogInfo());
        ?>
        </textarea>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const coordInput = document.querySelector('input[name="coord"]');
            if (coordInput) {
                coordInput.focus();
                coordInput.select();
            }
        });
    </script>

</body>
</html>    