<!DOCTYPE html>
<html>
<head>
    <title>Супер Морской Бой</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #a1ade6ff 0%, #b69ccfff 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .btn {
            padding: 20px 30px;
            font-size: 1.2em;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            font-weight: bold;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .single-player {
            background: linear-gradient(45deg, #4CAF50, #45a049);
        }
        .multi-player {
            background: linear-gradient(45deg, #2196F3, #1976D2);
        }        
    </style>
</head>
<body>
    <div class = "game-mode">
        <button class = "btn single-player" onclick = "startSinglePlayer()">
            Игра с компьютером
        </button>
        <button class = "btn multi-player" onclick = "MultiPlayer()">
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