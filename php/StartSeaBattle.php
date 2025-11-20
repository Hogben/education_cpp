<!DOCTYPE html>
<html>
<head>
    <title>Супер Морской Бой<title>
</head>
<body>
    <div class = "game-mode">
        <button class = "single-player" onclick = "startSinglePlayer()">
            Игра с компьюером
        </button>
        <button class = "multi-player" onclick = "MultiPlayer()">
            Игра с другим игроком
        </button>
    </div>    
    <script>
        function startSinglePlayer()
        {
            window.location.href = 'GameSeaBattle.php?mode=single';
        }
        
        function MultiPlayer()
        {
            window.location.href = 'NetSeaBattle.php';
        }
    </script>
</body>
</html>