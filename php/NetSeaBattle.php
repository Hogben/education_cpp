<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'NetworkManager.php';

// Получаем список игр
$games = NetworkManager::getGameList();

// Обработка действий
if (isset($_POST['action']))
{
    switch ($_POST['action'])
    {
        case 'create_game':
            // Полностью очищаем предыдущую сессию игры
            if (isset($_SESSION['net_game']))
            {
                NetworkManager::removeGame($_SESSION['net_game']);
                unset($_SESSION['net_game']);
                unset($_SESSION['player_role']);
            }
            if (isset($_SESSION['game']))
                unset($_SESSION['game']);
            if (isset($_SESSION['game_mode']))
                unset($_SESSION['game_mode']);
            
            // Создаем новую игру
            $gameID = uniqid();
            $playerID = session_id();
            NetworkManager::createGame($gameID, $playerID);
            
            $_SESSION['net_game'] = $gameID;
            $_SESSION['player_role'] = 'player1';
            
            // Перенаправляем на страницу игры
            header('Location: GameSeaBattle.php?mode=network');
            exit;
            
        case 'join_game':
            if (isset($_POST['game_id']))
            {
                $gameID = $_POST['game_id'];
                $playerID = session_id();
                
                $game = NetworkManager::getGame($gameID);

                // Проверяем, что игра существует и второй игрок еще не присоединился
                if ($game && $game['player2'] === null)
                {
                    // Очищаем предыдущую игру текущего игрока
                    if (isset($_SESSION['net_game']))
                    {
                        // Не удаляем старую игру, так как это может быть игра другого игрока
                        // Просто очищаем сессионные данные
                        unset($_SESSION['net_game']);
                        unset($_SESSION['player_role']);
                    }
                    if (isset($_SESSION['game']))
                        unset($_SESSION['game']);
                    if (isset($_SESSION['game_mode']))
                        unset($_SESSION['game_mode']);
                    
                    // Присоединяемся к игре
                    if (NetworkManager::joinGame($gameID, $playerID))
                    {
                        $_SESSION['net_game'] = $gameID;
                        $_SESSION['player_role'] = 'player2';
                        
                        // Перенаправляем на страницу игры
                        header('Location: GameSeaBattle.php?mode=network');
                        exit;
                    }
                }
            }
            break;
            
        case 'del_game':
            if (isset($_SESSION['net_game']))
            {
                $gameID = $_SESSION['net_game'];
                NetworkManager::removeGame($gameID);
                
                unset($_SESSION['net_game']);
                unset($_SESSION['player_role']);
            }
            if (isset($_SESSION['game']))
                unset($_SESSION['game']);
            if (isset($_SESSION['game_mode']))
                unset($_SESSION['game_mode']);
            
            header('Location: NetSeaBattle.php');
            exit;
            
        case 'main_menu':
            if (isset($_SESSION['net_game']))
            {
                $gameID = $_SESSION['net_game'];
                NetworkManager::removeGame($gameID);
                
                unset($_SESSION['net_game']);
                unset($_SESSION['player_role']);
            }
            if (isset($_SESSION['game']))
                unset($_SESSION['game']);
            if (isset($_SESSION['game_mode']))
                unset($_SESSION['game_mode']);
            
            header('Location: StartSeaBattle.php');
            exit;
            
        case 'refresh':
            // Просто обновляем страницу
            header('Location: NetSeaBattle.php');
            exit;
    }
}

// Получаем актуальный список игр после возможных изменений
$rawGameList = NetworkManager::getGameList();
$curPlayerID = session_id();

// Фильтруем игры для отображения:
// - не показываем игры, созданные текущим игроком
// - показываем только игры, где нет второго игрока
$gameList = [];
foreach ($rawGameList as $gameID => $game)
{
    if ($game['player1'] !== $curPlayerID && $game['player2'] === null)
    {
        $gameList[$gameID] = $game;
    }
}

// Проверяем, есть ли у текущего игрока созданная игра
$myGame = null;
foreach ($rawGameList as $gameID => $game)
{
    if ($game['player1'] === $curPlayerID && $game['player2'] === null)
    {
        $myGame = $game;
        $myGame['id'] = $gameID;
        break;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Сетевой Морской Бой</title>
    <meta charset="utf-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #a1ade6ff 0%, #b69ccfff 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        h3 {
            color: #555;
            border-bottom: 2px solid #a1ade6;
            padding-bottom: 5px;
        }
        .actions {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            padding: 15px 30px;
            font-size: 1.1em;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 10px;
            font-weight: bold;
        }
        .btn-create {
            background: #4CAF50;
            color: white;
        }
        .btn-mainmenu {
            background: #6c757d;
            color: white;
        }
        .btn-refresh {
            background: #ffc107;
            color: #333;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .my-game {
            background: #e8f5e9;
            border: 2px solid #4CAF50;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .game-items {
            background: #f8f9fa;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .game-items:hover {
            background: #e9ecef;
        }
        .empty-list {
            text-align: center;
            color: #666;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background: #c82333;
        }
        .join-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .join-btn:hover {
            background: #0056b3;
        }
        .game-info {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Сетевой Морской Бой</h1>
        
        <div class="actions">
            <button class="btn btn-create" onclick="createGame()">➕ Создать новую игру</button>
            <button class="btn btn-refresh" onclick="refresh()">🔄 Обновить список</button>
            <button class="btn btn-mainmenu" onclick="mainMenu()">🏠 Главное меню</button>
        </div>

        <?php if ($myGame): ?> 
            <div class="my-game">
                <div>
                    <strong>🎮 Ваша созданная игра</strong>
                    <br>
                    <span class="game-info">ID: <?php echo htmlspecialchars($myGame['id']) ?></span>
                    <br>
                    <span class="game-info">Создана: <?php echo date('H:i:s', $myGame['create_time']) ?></span>
                </div>
                <form method="post" style="margin: 0;">
                    <button type="submit" name="action" value="del_game" class="delete-btn">
                        ❌ Удалить
                    </button>
                </form>
            </div>
        <?php endif ?>

        <h3>📋 Доступные игры (<?php echo count($gameList); ?>)</h3>
        
        <div class="game-list">
            <?php if (empty($gameList)): ?>
                <div class="empty-list">
                    <p>😴 Пока нет доступных игр.</p>
                    <p>Создайте новую игру или подождите, пока кто-то создаст.</p>
                </div>
            <?php else: 
                foreach ($gameList as $gameID => $game): ?>    
                    <div class="game-items">
                        <div>
                            <strong>🎯 Игра #<?php echo htmlspecialchars(substr($gameID, -6)) ?></strong>
                            <br>
                            <span class="game-info">Создана: <?php echo date('H:i:s', $game['create_time']) ?></span>
                        </div>
                        <form method="post" style="margin: 0;">
                            <input type="hidden" name="game_id" value="<?php echo htmlspecialchars($gameID) ?>">
                            <button type="submit" name="action" value="join_game" class="join-btn">
                                🔑 Присоединиться
                            </button>
                        </form>
                    </div>
            <?php endforeach; 
                endif ?>    
        </div>

        <!-- Скрытая форма для действий -->
        <form id="action-form" method="post" style="display: none;">
            <input type="hidden" id="form-action" name="action" value="">
        </form>
    </div>

    <script>
        function createGame() {
            document.getElementById('form-action').value = 'create_game';
            document.getElementById('action-form').submit();
        }

        function mainMenu() {
            document.getElementById('form-action').value = 'main_menu';
            document.getElementById('action-form').submit();
        }

        function refresh() {
            document.getElementById('form-action').value = 'refresh';
            document.getElementById('action-form').submit();
        }

        // Автообновление страницы каждые 5 секунд
        setTimeout(function() {
            location.reload();
        }, 5000);
    </script>
</body>
</html>