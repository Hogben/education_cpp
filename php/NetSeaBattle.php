<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'NetworkManager.php';

if (isset($_POST['action']))
{
    switch ($_POST['action'])
    {
        case 'create_game':
            $gameID = uniqid();
            $playerID = session_id();
            NetworkManager::createGame($gameID, $playerID);
            $_SESSION['net_game'] = $gameID;
            $_SESSION['player_role'] = 'player1';
            header('Location:GameSeaBattle.php?mode=network');
            exit;
        case 'join_game':
            if (isset($_POST['game_id']))
            {
                $gameID = $_POST['game_id'];
                $playerID = session_id();
                if(NetworkManager::joinGame($gameID, $playerID))
                {
                    $_SESSION['net_game'] = $gameID;
                    $_SESSION['player_role'] = 'player2';
                    header('Location:GameSeaBattle.php?mode=network');
                    exit;
                }
            }
            break;
        case 'del_game':
            if (isset($_SESSION['net_game']))
            {
                $gameID = $_SESSION['net_game'];
                $playerID = session_id();
                NetworkManager::removeGame($gameID);
                unset($_SESSION['net_game']);
                unset($_SESSION['player_role']);
                header('Location:NetSeaBattle.php');
                exit;
            }
            break;
        case 'main_menu':
            header('Location:StartSeaBattle.php');
            exit;
    }
}

$rawGameList = NetworkManager::getGameList();
$curPlayerID = session_id();
$gameList = [];
foreach ($rawGameList as $gameID => $game)
{
    if ($game['player1'] !== $curPlayerID && $game['player2'] === null)
    {
        $gameList[$gameID] = $game;
    }
}

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
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #a1ade6ff 0%, #b69ccfff 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }        
        .actions {
            text-align: center;
            margin: 30px 0;
        }
        .game-list {
            margin: 30px 0;
        }
        .game-items {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            padding: 15px 30px;
            font-size: 1.1em;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 10px;
        }
        .btn-create {
            background: #007bff;
            color: white;
        }
        .btn-mainmenu {
            background: #6c757d;
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class = "actions">
        <!-- добавить запрет если уже есть созданная игра  -->
        <button class="btn btn-create" onclick="createGame()">Создать новую игру</button> 
        <button class="btn btn-mainmenu" onclick="mainMenu()">Предыдущее меню</button>
    </div>        
    <?php if ($myGame): ?> 
    <div class = "my-game">
        <h3>Ваша созданная игра</h3>
        <p><strong>Игра №<?php echo $myGame['id'] ?></strong></p>
        <form method="post">
            <button type="submit" name="action" value="del_game">
                Удалить
            </button>
        </form>
    </div>
    <?php endif ?>    
    <div class = "game-list">
        <h3>Список доступных игр</h3>
        <?php if (empty($gameList)): ?>
            <div>
                <p>Пока нет доступных игр.</p>
            </div>
        <?php else: 
            foreach ($gameList as $gameID => $game): ?>    
            <div class = "game-items">
                <div>
                    <strong>Игра №<?php echo $gameID ?></strong>
                    <br><small>создана <?php echo date('H:i:s', $game['create_time']) ?></small>
                </div>
                <form method="post">
                    <input type="hidden" name="game_id" value="<?php echo $gameID ?>">
                    <button type="submit" name="action" value="join_game">
                        Присоединиться
                    </button>
                </form>
            </div>
        <?php endforeach; 
            endif ?>    
    </div>        
    <!-- post BEGIN -->
    <form id="action-form" method="post" style="display: none;">
        <input type="hidden" id="form-action" name="action" value="">
    </form>
    <!-- post END -->
    <script>
        function createGame()
        {
            document.getElementById('form-action').value = 'create_game';
            document.getElementById('action-form').submit();
        }

        function mainMenu()
        {
            document.getElementById('form-action').value = 'main_menu';
            document.getElementById('action-form').submit();
        }

        setTimeout(function() {
            location.reload();
        }, 5000);
    </script>
</body>
</html>