<?php

require_once 'seaBattleConfig.php';
require_once 'matrix.php';
require_once 'BattleShip.php';
require_once 'SmartShoot.php';
require_once 'Logger.php';
require_once 'NetworkManager.php';

class SeaBattle 
{
    //---------- для текущего игрока
    private $myBoard; 
    private $myShips = []; 
    private $enemyShips = []; // корабли противника (только для логики)
    private $enemyHits = []; // массив попаданий и промахов по противнику [row][col] => 'X' или '*'
    private $curPlayerShip = 0;
    private $gameOver = false;
    private $vertical = false;
    private $smartShoot = null;
    private $cyr_label = ['А','Б','В','Г','Д','Е','Ж','З','И','К'];
    
    private $quickPlacePerformed = false;

    public $log = null;

    //---------- netplay
    private $gameID;
    private $playerNumber; // 'player1' или 'player2'
    private $playerCurrent;
    private $multiPlayer = false;

    public function __construct($gameID = null, $playerNumber = null)
    {
        global $config;

        $this->log = new Logger('sea_battle', 'log', $config['debug']);
        $this->log->clearLog();

        $this->myBoard = new Matrix($config['sea']['size'], $config['sea']['size'], 'numeric', 'alpha', $config['sea']['board']['empty']);
        $this->myBoard->setShowColsLabel(true);
        $this->myBoard->setShowRowsLabel(true);
        $this->makeCyrLabel($this->myBoard);

        $this->initShips();

        if ($gameID && $playerNumber) 
        {
            $this->gameID = $gameID;
            $this->playerNumber = $playerNumber;
            $this->multiPlayer = true;
            
            // Загружаем состояние из сети
            $this->loadNetworkState();
        }
        else
        {
            $enemyBoardFull = new Matrix($config['sea']['size'], $config['sea']['size'], 'numeric', 'alpha', $config['sea']['board']['empty']);
            $enemyBoardFull->setShowColsLabel(true);
            $enemyBoardFull->setShowRowsLabel(true);
            $this->makeCyrLabel($enemyBoardFull);
            
            $this->placeShipsRandom($enemyBoardFull, $this->enemyShips, true);
            
            $this->enemyHits = [];
        }
    }

    public function isQuickPlacePerformed()
    {
        return $this->quickPlacePerformed;
    }
    
    public function resetQuickPlaceFlag()
    {
        $this->quickPlacePerformed = false;
    }

    private function loadNetworkState()
    {
        if (!$this->multiPlayer) return;

        $this->playerCurrent = NetworkManager::getCurrentPlayer($this->gameID);
        
        // Загружаем МОЮ доску (с моими кораблями)
        $myBoardData = NetworkManager::getGameBoard($this->gameID, $this->playerNumber);
        if ($myBoardData)
        {
            $this->loadMyBoardFromData($myBoardData);
            $this->log->debug("Загружена МОЯ доска из сети");
        }

        // Загружаем данные противника
        $enemyPlayer = ($this->playerNumber === 'player1') ? 'player2' : 'player1';
        $enemyBoardData = NetworkManager::getGameBoard($this->gameID, $enemyPlayer);

        if ($enemyBoardData)
        {
            $this->loadEnemyBoardFromData($enemyBoardData);
            $this->log->debug("Загружена доска противника из сети");
        }
        
        // После загрузки проверяем готовность
        $myReady = NetworkManager::getPlayerReady($this->gameID, $this->playerNumber);
        $opponentReady = NetworkManager::getPlayerReady($this->gameID, $enemyPlayer);
        
        $this->log->debug("Статус готовности - Я: " . ($myReady ? 'да' : 'нет') . ", Противник: " . ($opponentReady ? 'да' : 'нет'));
        
        // Если оба готовы, устанавливаем текущего игрока если еще не установлен
        if ($myReady && $opponentReady && !NetworkManager::getCurrentPlayer($this->gameID)) {
            $this->log->debug("Оба готовы - устанавливаем player1 как текущего");
            NetworkManager::setCurrentPlayer($this->gameID, 'player1');
            $this->playerCurrent = 'player1';
        }
    }

    private function saveNetworkState()
    {
        if (!$this->multiPlayer) return;

        $boardData = $this->getMyBoardData();
        NetworkManager::updateGameBoard($this->gameID, $this->playerNumber, $boardData);
        $this->log->debug("Сохранена МОЯ доска в сеть");
    }

    private function getMyBoardData()
    {
        $data = [
            'board' => [],
            'ships' => [],
            'curPlayerShip' => $this->curPlayerShip
        ];

        // Сохраняем МОЮ доску полностью
        for ($row = 0; $row < $this->myBoard->getRows(); $row++)
        {
            for ($col = 0; $col < $this->myBoard->getCols(); $col++)
            {
                $data['board'][$row][$col] = $this->myBoard->getValue($row, $col);
            }
        }
        
        // Сохраняем МОИ корабли
        foreach ($this->myShips as $index => $ship)
        {
            $shipData = $ship->getPosition();
            $data['ships'][$index] = $shipData;
        }

        return $data;
    }

    private function loadMyBoardFromData($boardData)
    {
        global $config;

        // Загружаем МОЮ доску полностью
        for ($row = 0; $row < $this->myBoard->getRows(); $row++)
        {
            for ($col = 0; $col < $this->myBoard->getCols(); $col++)
            {
                $this->myBoard->setValue($row, $col, $boardData['board'][$row][$col]);
            }
        }
        
        // Загружаем МОИ корабли
        if (isset($boardData['ships']))
        {
            foreach ($boardData['ships'] as $index => $shipData)
            {
                if (isset($this->myShips[$index]))
                {
                    if (isset($shipData['pos']))
                        $this->myShips[$index]->setPosition($shipData['pos'], $shipData['vertical']);

                    if (isset($shipData['hit']))
                        $this->myShips[$index]->setHit($shipData['hit']);
                }
            }
        }

        $this->curPlayerShip = $boardData['curPlayerShip'] ?? 0;
    }

    private function loadEnemyBoardFromData($boardData)
    {
        global $config;

        // Очищаем массив попаданий
        $this->enemyHits = [];
        
        // Загружаем ТОЛЬКО попадания и промахи в массив
        for ($row = 0; $row < 10; $row++)
        {
            for ($col = 0; $col < 10; $col++)
            {
                $val = $boardData['board'][$row][$col] ?? $config['sea']['board']['empty'];
                // Сохраняем только X и *, НИКАКИХ @
                if ($val === $config['sea']['board']['hit']) {
                    $this->enemyHits[$row][$col] = $config['sea']['board']['hit'];
                    $this->log->debug("Загружено попадание на [$row,$col]");
                } elseif ($val === $config['sea']['board']['miss']) {
                    $this->enemyHits[$row][$col] = $config['sea']['board']['miss'];
                    $this->log->debug("Загружен промах на [$row,$col]");
                }
                // Корабли (@) ИГНОРИРУЕМ ПОЛНОСТЬЮ - не сохраняем в enemyHits!
            }
        }
        
        // Загружаем корабли противника ТОЛЬКО для логики
        if (isset($boardData['ships']))
        {
            foreach ($boardData['ships'] as $index => $shipData)
            {
                if (isset($this->enemyShips[$index]))
                {
                    if (isset($shipData['pos']))
                        $this->enemyShips[$index]->setPosition($shipData['pos'], $shipData['vertical']);

                    if (isset($shipData['hit']))
                        $this->enemyShips[$index]->setHit($shipData['hit']);
                }
            }
        }
    }

    private function initShips()
    {
        global $config;
        foreach ($config['ships'] as $s)
        {
            $this->myShips[] = new BattleShip($s['size'], $s['name']);
            $this->enemyShips[] = new BattleShip($s['size'], $s['name']);
        }
    }

    public function quickPlaceShip()
    {
        global $config;

        $this->log->debug("=== НАЧАЛО БЫСТРОЙ РАССТАНОВКИ ===");

        // Очищаем МОЮ доску
        $this->myBoard->fillMatrix($config['sea']['board']['empty']);

        // Сбрасываем позиции МОИХ кораблей
        $this->curPlayerShip = 0;
        foreach ($this->myShips as $ship) 
        {
            $ship->setPosition([], false);
            $ship->setHit(0);
        }        

        // Расставляем МОИ корабли случайно на myBoard
        for ($i = 0; $i < count($this->myShips); $i++)
        {
            $ship = $this->myShips[$i];
            $placed = false;
            $shipSize = $ship->getSize();
            $attempts = 0;
            
            while (!$placed && $attempts < 1000)
            {
                $attempts++;
                $row = rand(0, $this->myBoard->getRows() - 1);
                $col = rand(0, $this->myBoard->getCols() - 1);
                $vertical = (rand(0,1) == 0);

                if ($this->canPlaceShip($ship, $row, $col, $vertical, $this->myShips))
                {
                    $pos = [];
                    for ($j = 0; $j < $shipSize; $j++)
                    {
                        if ($vertical)
                        {
                            $pos[] = ['row' => $row + $j, 'col' => $col];
                            $this->myBoard->setValue($row + $j, $col, $config['sea']['board']['ship']);
                            $this->log->debug("Размещен корабль на МОЕЙ доске [" . ($row + $j) . ",$col]");
                        }
                        else
                        {
                            $pos[] = ['row' => $row, 'col' => $col + $j];
                            $this->myBoard->setValue($row, $col + $j, $config['sea']['board']['ship']);
                            $this->log->debug("Размещен корабль на МОЕЙ доске [$row," . ($col + $j) . "]");
                        }
                    }                    
                    $ship->setPosition($pos, $vertical); 
                    $placed = true;
                }
            }
        }
        
        $this->curPlayerShip = count($config['ships']);
        
        if ($this->multiPlayer)            
        {
            $this->setPlayerReady();
            $this->saveNetworkState();
        }
        
        // Загружаем актуальное состояние противника
        if ($this->multiPlayer) {
            $this->loadNetworkState();
        }
        
        $this->quickPlacePerformed = true;
        
        $this->log->debug("=== КОНЕЦ БЫСТРОЙ РАССТАНОВКИ ===");
    }

    private function placeShipsRandom($board, $ships, $drawShip = true)
    {
        // Этот метод используется только для компьютера
        global $config;

        foreach ($ships as $ship)        
        {
            $placed = false;
            $shipSize = $ship->getSize();
            while (!$placed)
            {
                $row = rand(0, $board->getRows() - 1);
                $col = rand(0, $board->getCols() - 1);

                $vertical = (rand(0,1) == 0);

                if ($this->canPlaceShip($ship, $row, $col, $vertical, $ships))
                {
                    $pos = [];
                    for ($i = 0; $i < $shipSize; $i++)
                    {
                        if ($vertical)
                            $pos[] = ['row' => $row + $i, 'col' => $col];
                        else
                            $pos[] = ['row' => $row, 'col' => $col + $i];
                        
                        // Рисуем корабль на доске
                        if ($drawShip) {
                            ($vertical) ? $this->drawShip($row + $i, $col, $config['sea']['board']['ship'], $board) : $this->drawShip($row, $col + $i, $config['sea']['board']['ship'], $board);    
                        }
                    }                    
                    $ship->setPosition($pos, $vertical); 
                    $placed = true;
                }
            }
        }
    }

    private function drawShip($row, $col, $val, $board)
    {
        $board->setValue($row, $col, $val);
    }

    private function canPlaceShip($ship, $row, $col, $vertical, $ships) : bool
    {
        $shipSize = $ship->getSize();

        if ($vertical)
        {
            if ($row + $shipSize > $this->myBoard->getRows()) return false;
        }
        else
        {
            if ($col + $shipSize > $this->myBoard->getCols()) return false;
        }    
        
        for ($i = 0; $i < $shipSize; $i++)
        {
            $checkRow = $vertical ? $row + $i : $row;
            $checkCol = $vertical ? $col : $col + $i;
            
            foreach ($ships as $realShip)
            {
                if ($realShip === $ship) continue; 
                
                $pos_ship = $realShip->getPosition();
                if (!isset($pos_ship['pos'])) continue; 
                
                for ($r = max(0, $checkRow - 1); $r <= min($this->myBoard->getRows() - 1, $checkRow + 1); $r++)
                {
                    for ($c = max(0, $checkCol - 1); $c <= min($this->myBoard->getCols() - 1, $checkCol + 1); $c++)
                    {
                        foreach ($pos_ship['pos'] as $pos)
                        {
                            if ($pos['row'] === $r && $pos['col'] === $c) 
                            {
                                return false; 
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    public function placePlayerShip($row, $col) : bool
    {
        global $config;

        if ($this->curPlayerShip >= count($this->myShips)) {
            return false;
        }

        $ship = $this->myShips[$this->curPlayerShip];
        $shipSize = $ship->getSize();

        if ($this->canPlaceShip($ship, $row, $col, $this->vertical, $this->myShips))
        {
            $pos = [];
            for ($i = 0; $i < $shipSize; $i++)
            {
                if ($this->vertical)
                    $pos[] = ['row' => $row + $i, 'col' => $col];
                else
                    $pos[] = ['row' => $row, 'col' => $col + $i];
                
                ($this->vertical) ? $this->drawShip($row + $i, $col, $config['sea']['board']['ship'], $this->myBoard) : $this->drawShip($row, $col + $i, $config['sea']['board']['ship'], $this->myBoard);    
            }                    
            $ship->setPosition($pos, $this->vertical); 
            
            $this->curPlayerShip++;
            
            if ($this->multiPlayer)          
            {
                if ($this->curPlayerShip === count($config['ships'])) {
                    $this->setPlayerReady();
                }
                $this->saveNetworkState();
            }
            return true;
        }
        return false;
    }

    public function syncGameState()
    {
        if (!$this->multiPlayer) return [];

        $this->loadNetworkState();
        
        // Обновляем текущего игрока
        $this->playerCurrent = NetworkManager::getCurrentPlayer($this->gameID);
        
        $myReady = NetworkManager::getPlayerReady($this->gameID, $this->playerNumber);
        $opponentReady = NetworkManager::getPlayerReady(
            $this->gameID, 
            ($this->playerNumber === 'player1') ? 'player2' : 'player1'
        );

        if ($this->Start())
        {
            $currentPlayer = $this->playerCurrent;
            $yourTurn = ($currentPlayer === $this->playerNumber);
            $mes = $yourTurn ? "Ваш ход! Стреляйте!" : "Ход соперника...";
            $this->log->info($mes);
            return ['message' => $mes];
        }
        else
        {
            if ($myReady && !$opponentReady) {
                return ['message' => "Ждем соперника..."];
            } elseif (!$myReady) {
                return ['message' => "Расставьте корабли"];
            } elseif ($myReady && $opponentReady) {
                // Оба готовы, игра начинается
                $this->log->debug("Оба готовы! Устанавливаем current_player если нужно");
                if (!NetworkManager::getCurrentPlayer($this->gameID)) {
                    NetworkManager::setCurrentPlayer($this->gameID, 'player1');
                }
                $this->playerCurrent = NetworkManager::getCurrentPlayer($this->gameID);
                $mes = ($this->playerCurrent === $this->playerNumber) ? "Ваш ход! Стреляйте!" : "Ход соперника...";
                $this->log->info("Игра началась! " . $mes);
                return ['message' => $mes];
            }
        }
        return [];
    }
	
    public function computerShoot($smart = true)
    {
        // ... (оставляем без изменений, как в предыдущей версии)
        global $config;

        $maxShots = 30;
        $shotsMade = 0;
        
        do 
        {
            $row = null;
            $col = null;

            if ($this->smartShoot === null || !$smart)
            {
                $attempts = 0;
                do 
                {
                    $row = rand(0,$this->myBoard->getRows() - 1);
                    $col = rand(0,$this->myBoard->getCols() - 1);
                    $attempts++;
                    
                    if ($attempts > 200) {
                        $this->log->error("Не удалось найти свободную клетку для выстрела");
                        return ['result' => 'Ошибка: не удалось найти цель', 'hit' => false, 'game_over' => false, 'dead' => false];
                    }

                } while ($this->myBoard->getValue($row, $col) === $config['sea']['board']['hit'] || $this->myBoard->getValue($row, $col) === $config['sea']['board']['miss']);

                $res = $this->shootResult($this->myBoard, $this->myShips, $row, $col);
                $shotsMade++;
                
                if ($res['game_over']) {
                    return $res;
                } 
                elseif ($res['hit']) 
                {
                    if (!$res['dead']) {
                        $this->smartShoot = new SmartShoot($row, $col, $this->log);
                    } else {
                        $this->smartShoot = null;
                    }
                    continue;
                } else {
                    return $res;
                }
            }
            else
            {
                // ... (оставляем без изменений)
                $shootInfo = $this->smartShoot->getInfo();

                $direction = ['left' => true, 'right' => true, 'up' => true, 'down' => true];
            
                if ($shootInfo['niceShoot'] !== null)
                {
                    $row = $shootInfo['niceShoot']['row'];
                    $col = $shootInfo['niceShoot']['col'];
                    if ($config['debug'])
                        $this->log->debug("следующий выстрел: ".$this->getPrintCoord($this->myBoard, $row, $col));
                }
                else
                {
                    $attempts = 0;
                    do 
                    {
                        $pos = end($shootInfo['pos']);
                        if ($shootInfo['vertical'] !== null)
                        {
                            if ($shootInfo['vertical'])
                            {
                                $direction['left'] = false;
                                $direction['right'] = false;
                            }
                            else
                            {
                                $direction['up'] = false;
                                $direction['down'] = false;
                            }
                            if (
                                ($shootInfo['vertical'] && $shootInfo['inc'] && $pos['row'] === $this->myBoard->getRows() - 1) || 
                                ($shootInfo['vertical'] && !$shootInfo['inc'] && $pos['row'] === 0) ||
                                (!$shootInfo['vertical'] && $shootInfo['inc'] && $pos['col'] === $this->myBoard->getCols() - 1) || 
                                (!$shootInfo['vertical'] && !$shootInfo['inc'] && $pos['col'] === 0) 
                            )
                            {
                                $pos = $shootInfo['pos'][0];
                                $shootInfo['inc'] = !$shootInfo['inc'];
                            }
                            if ($shootInfo['vertical'])
                            {
                                $row = $pos['row'] + ($shootInfo['inc'] ? 1 : -1);
                                $col = $pos['col'];
                            }
                            else
                            {
                                $col = $pos['col'] + ($shootInfo['inc'] ? 1 : -1);
                                $row = $pos['row'];
                            }
                        }
                        else
                        {
                            if ($pos['row'] === 0 || $this->myBoard->getValue($pos['row'] - 1, $pos['col']) === $config['sea']['board']['miss'])  
                                $direction['up'] = false;
                            if ($pos['row'] === $this->myBoard->getRows() - 1 || $this->myBoard->getValue($pos['row'] + 1, $pos['col']) === $config['sea']['board']['miss'])
                                $direction['down'] = false;
                            if ($pos['col'] === 0 || $this->myBoard->getValue($pos['row'], $pos['col'] - 1) === $config['sea']['board']['miss'])
                                $direction['left'] = false;
                            if ($pos['col'] === $this->myBoard->getCols() - 1 || $this->myBoard->getValue($pos['row'], $pos['col'] + 1) === $config['sea']['board']['miss'])
                                $direction['right'] = false;

                            $move_vert = (rand(0,1) == 0);    

                            if ($config['debug'])
                                $this->log->debug(
                                    "можно стрелять вверх: ".($direction['up'] ? 'да' : 'нет')
                                    ."  вниз: ".($direction['down'] ? 'да' : 'нет')
                                    ."  влево: ".($direction['left'] ? 'да' : 'нет')
                                    ."  вправо: ".($direction['right'] ? 'да' : 'нет')
                                );

                            if ($move_vert && ($direction['up'] || $direction['down']))
                            {
                                if (!$direction['up'] && !$direction['down'])
                                    $row = $pos['row'] + (rand(0,1) == 0 ? 1 : -1);
                                else
                                {
                                    if ($direction['up'])
                                        $row = $pos['row'] - 1;
                                    else    
                                        $row = $pos['row'] + 1;
                                }
                                $col = $pos['col'];
                            }
                            else
                            {
                                if ($direction['left'] && $direction['right'])
                                    $col = $pos['col'] + (rand(0,1) == 0 ? 1 : -1);
                                else
                                {
                                    if ($direction['left'])
                                        $col = $pos['col'] - 1;
                                    else    
                                        $col = $pos['col'] + 1;
                                }
                                $row = $pos['row'];
                            }
                        }
                        $attempts++;
                        
                        if ($attempts > 50) {
                            $this->log->error("Не удалось найти цель для умного выстрела");
                            $this->smartShoot = null;
                            return ['result' => 'Ошибка поиска цели', 'hit' => false, 'game_over' => false, 'dead' => false];
                        }
                    } while ($this->myBoard->getValue($row, $col) === $config['sea']['board']['hit'] || $this->myBoard->getValue($row, $col) === $config['sea']['board']['miss']);
                }

                $res = $this->shootResult($this->myBoard, $this->myShips, $row, $col);
                $shotsMade++;
                
                if ($res['game_over']) {
                    return $res;
                } elseif ($res['hit']) 
                {
                    if ($res['dead']) {
                        $this->smartShoot = null;
                    } else {
                        $this->smartShoot->addPos($row, $col);
                    }
                    continue;
                } else 
                {
                    $this->smartShoot->calcNiceShoot($row, $col);
                    return $res;
                }
            }
            
            if ($shotsMade >= $maxShots) {
                $this->log->warning("Достигнут лимит выстрелов за ход: $maxShots");
                return ['result' => 'Лимит выстрелов', 'hit' => false, 'game_over' => false, 'dead' => false];
            }
            
        } while (true);
    }
    
    private function shootResult($board, $Ships, $row, $col)
    {
        global $config;

        if ($board === $this->myBoard)
            $this->log->info("Компьютер стреляет: ".$this->getPrintCoord($this->myBoard, $row, $col));
        else
            $this->log->info("Игрок стреляет: ".$this->getPrintCoord($this->myBoard, $row, $col));

        $hit = false;
        $dead = false;
        $check_ship = null;
        foreach($Ships as $ship)
        {
            if ($ship->checkHit($row, $col))
            {
                $board->setValue($row, $col, $config['sea']['board']['hit']);
                $hit = true;
                $dead = $ship->isDead(); 
                $check_ship = $ship;
    
                if ($config['debug'])
                    $this->log->debug("Попали в размер: ".$ship->getSize().", корабль: ".$ship->getName().", убит: ".($dead ? 'да' : 'нет'));

                break;
            }
        }
        if ($hit)
        {
            $board->setValue($row, $col, $config['sea']['board']['hit']);
            if ($dead)
            {
                $this->log->info("Убил!!!");
                if ($this->checkDeadShips($Ships) === true)
                {
                    $this->log->info("Конец игры!!!");
                    $msg = ($board === $this->computerBoard) ? 'Игрок победил!' : 'Компьюер думает что победил...';
                    return ['result' => $msg, 'hit' => true, 'game_over' => true, 'dead' => true];
                }
                $this->markDeadShip($board, $check_ship);
                return ['result' => 'Убил!', 'hit' => true, 'game_over' => false, 'dead' => true];
            }
            else
            {
                $this->log->info("Попал...");
                return ['result' => 'Ранил!', 'hit' => true, 'game_over' => false, 'dead' => false];
            }
        }
        else
        {
            $this->log->info("Мимо.");
            $board->setValue($row, $col, $config['sea']['board']['miss']);
            return ['result' => 'Мимо!', 'hit' => false, 'game_over' => false, 'dead' => false];
        }
    }	
	
	public function networkShoot($row, $col, $shooter)
    {
        global $config;

        if (!$this->Start()) {
            return ['result' => 'Игра еще не началась!', 'hit' => false, 'game_over' => false, 'dead' => false];
        }

        $currentPlayer = NetworkManager::getCurrentPlayer($this->gameID);
        
        if ($currentPlayer !== $shooter)
        {
            return ['result' => 'Не Ваш ход...', 'hit' => false, 'game_over' => false, 'dead' => false];
        }

        // Определяем, по кому стреляем (всегда по противнику)
        $targetPlayer = ($shooter === 'player1') ? 'player2' : 'player1';
        
        // Получаем данные противника
        $targetBoardData = NetworkManager::getGameBoard($this->gameID, $targetPlayer);
        
        if (!$targetBoardData) {
            return ['result' => 'Ошибка: нет данных о противнике', 'hit' => false, 'game_over' => false, 'dead' => false];
        }

        // Проверяем, не стреляли ли уже сюда
        if (isset($targetBoardData['board'][$row][$col])) {
            $currentVal = $targetBoardData['board'][$row][$col];
            if ($currentVal === $config['sea']['board']['miss'] || $currentVal === $config['sea']['board']['hit']) {
                return ['result' => 'Уже сюда стрелял!', 'hit' => false, 'game_over' => false, 'dead' => false];
            }
        }

        // Проверяем попадание по кораблям противника
        $hit = false;
        $dead = false;
        
        if (isset($targetBoardData['ships'])) {
            foreach ($targetBoardData['ships'] as $index => $shipData) {
                if (isset($shipData['pos'])) {
                    foreach ($shipData['pos'] as $posKey => $pos) {
                        if ($pos['row'] == $row && $pos['col'] == $col) {
                            $hit = true;
                            
                            // Отмечаем попадание в данных
                            $targetBoardData['board'][$row][$col] = $config['sea']['board']['hit'];
                            
                            // Проверяем, убит ли корабль
                            $allHit = true;
                            foreach ($shipData['pos'] as $p) {
                                if ($targetBoardData['board'][$p['row']][$p['col']] != $config['sea']['board']['hit']) {
                                    $allHit = false;
                                    break;
                                }
                            }
                            $dead = $allHit;
                            if ($dead)
                            {
                                // Создаем матрицу из данных доски
                                $tempBoard = new Matrix($config['sea']['size'], $config['sea']['size']);
                                for ($r = 0; $r < $config['sea']['size']; $r++) {
                                    for ($c = 0; $c < $config['sea']['size']; $c++) {
                                        $tempBoard->setValue($r, $c, $targetBoardData['board'][$r][$c]);
                                    }
                                }
                                
                                $this->markDeadShip($tempBoard, $this->enemyShips[$index]);
                                
                                // Обновляем оригинальный массив данными из временной матрицы
                                for ($r = 0; $r < $config['sea']['size']; $r++) {
                                    for ($c = 0; $c < $config['sea']['size']; $c++) {
                                        $targetBoardData['board'][$r][$c] = $tempBoard->getValue($r, $c);
                                    }
                                }
                            }
                            break 2;
                        }
                    }
                }
            }
        }

        if ($hit) {
            $targetBoardData['board'][$row][$col] = $config['sea']['board']['hit'];
            $result_message = $dead ? 'Убил!' : 'Попал!';
        } else {
            $targetBoardData['board'][$row][$col] = $config['sea']['board']['miss'];
            $result_message = 'Мимо!';
        }

        NetworkManager::updateGameBoard($this->gameID, $targetPlayer, $targetBoardData);

        if (!$hit)
        {
            $nextPlayer = ($shooter === 'player1') ? 'player2' : 'player1';
            NetworkManager::setCurrentPlayer($this->gameID, $nextPlayer);
        }

        // Загружаем обновленное состояние
        $this->loadNetworkState();
        
        // Проверяем, не закончилась ли игра
        $gameOver = $this->checkNetworkGameOver($targetPlayer, $targetBoardData);
        
        return [
            'result' => $result_message, 
            'hit' => $hit, 
            'game_over' => $gameOver, 
            'dead' => $dead
        ];
    }

    private function markDeadShip($Board, $ship)
    {
        global $config;

        $this->log->debug("Попали в размер: ".$ship->getSize().", корабль: ".$ship->getName().", убит: да");
        
        $maxRows = $Board->getRows() - 1;
        $maxCols = $Board->getCols() - 1;

        $ship_position = $ship->getPosition();
        foreach ($ship_position['pos'] as $pos)
        {
            $checkRow = $pos['row'];
            $checkCol = $pos['col'];

            for ($r = max(0, $checkRow - 1); $r <= min($maxRows, $checkRow + 1); $r++)
            {
                for ($c = max(0, $checkCol - 1); $c <= min($maxCols, $checkCol + 1); $c++)
                {
                    $currentValue = $Board->getValue($r, $c);
                    if ($currentValue === $config['sea']['board']['hit']) continue;
                    $Board->setValue($r, $c, $config['sea']['board']['miss']);
                }
            }  
        }
    }

    private function checkNetworkGameOver($player, $boardData)
    {
        if (!isset($boardData['ships'])) return false;
        
        foreach ($boardData['ships'] as $shipData) {
            if (isset($shipData['pos'])) {
                foreach ($shipData['pos'] as $pos) {
                    if ($boardData['board'][$pos['row']][$pos['col']] != 'X') {
                        return false;
                    }
                }
            }
        }
        $this->gameOver = true;
        return true;
    }

    public function getPlayer()
    {
        return $this->playerNumber;
    }

    public function isPlayerReady()
    {
        return NetworkManager::getPlayerReady($this->gameID, $this->playerNumber);
    }

    public function playerShoot($row, $col)
    {
        global $config;

        // Проверяем, не стреляли ли уже сюда
        if (isset($this->enemyHits[$row][$col])) {
            return ['result' => 'Уже сюда стрелял!', 'hit' => false, 'game_over' => false, 'dead' => false];
        }
        
        // Проверяем попадание по кораблям противника
        $hit = false;
        $dead = false;
        $hitShip = null;
        
        foreach($this->enemyShips as $ship)
        {
            if ($ship->checkHit($row, $col))
            {
                $hit = true;
                $dead = $ship->isDead();
                $hitShip = $ship;
                break;
            }
        }
        
        if ($hit) {
            $this->enemyHits[$row][$col] = $config['sea']['board']['hit'];
            
            if ($dead) {
                // Создаем временную доску для отметки промахов вокруг убитого корабля
                $tempBoard = new Matrix($config['sea']['size'], $config['sea']['size']);
                
                // Копируем текущие попадания/промахи во временную доску
                for ($r = 0; $r < $config['sea']['size']; $r++) {
                    for ($c = 0; $c < $config['sea']['size']; $c++) {
                        if (isset($this->enemyHits[$r][$c])) {
                            $tempBoard->setValue($r, $c, $this->enemyHits[$r][$c]);
                        } else {
                            $tempBoard->setValue($r, $c, $config['sea']['board']['empty']);
                        }
                    }
                }
                
                // Отмечаем промахи вокруг убитого корабля
                $this->markDeadShip($tempBoard, $hitShip);
                
                // Обновляем enemyHits из временной доски
                for ($r = 0; $r < $config['sea']['size']; $r++) {
                    for ($c = 0; $c < $config['sea']['size']; $c++) {
                        $val = $tempBoard->getValue($r, $c);
                        if ($val === $config['sea']['board']['miss'] || $val === $config['sea']['board']['hit']) {
                            $this->enemyHits[$r][$c] = $val;
                        }
                    }
                }
                
                return ['result' => 'Убил!', 'hit' => true, 'game_over' => false, 'dead' => true];
            } else {
                return ['result' => 'Попал!', 'hit' => true, 'game_over' => false, 'dead' => false];
            }
        } else {
            $this->enemyHits[$row][$col] = $config['sea']['board']['miss'];
            return ['result' => 'Мимо!', 'hit' => false, 'game_over' => false, 'dead' => false];
        }
    }

    private function checkDeadShips($Ships)
    {
        foreach ($Ships as $ship)
        {
            if (!$ship->isDead()) return false;
        }
        $this->gameOver = true;
        return true;
    }

    public function isGameOver()
    {
        return $this->gameOver;
    }

    public function drawMyBoard()
    {
        $this->log->debug("Рисуем МОЮ доску");
        $html = $this->myBoard->make();
        return $html;
    }

	public function drawEnemyBoard()
	{
		global $config;
		
		$this->log->debug("Рисуем ВРАЖЕСКУЮ доску");
		
		$displayBoard = new Matrix($config['sea']['size'], $config['sea']['size'], 'numeric', 'alpha', $config['sea']['board']['empty']);
		$displayBoard->setShowColsLabel(true);
		$displayBoard->setShowRowsLabel(true);
		$this->makeCyrLabel($displayBoard);
		
		if ($this->multiPlayer) {
			foreach ($this->enemyHits as $row => $cols) {
				foreach ($cols as $col => $val) {
					$displayBoard->setValue($row, $col, $val);
				}
			}
		} else {
			foreach ($this->enemyHits as $row => $cols) {
				foreach ($cols as $col => $val) {
					$displayBoard->setValue($row, $col, $val);
				}
			}
		}
		
		$html = $displayBoard->make();
		return $html;
	}

    private function setPlayerReady()
    {
        global $config;
        if (count($config['ships']) === $this->curPlayerShip)
        {
            $this->log->debug("Игрок готов! curPlayerShip = " . $this->curPlayerShip);
            NetworkManager::setPlayerReady($this->gameID, $this->playerNumber);
        }
    }

    public function Start()
    {
        global $config;

        $this->log->debug("Start() - curPlayerShip: " . $this->curPlayerShip . ", всего кораблей: " . count($config['ships']));
        
        if (count($config['ships']) === $this->curPlayerShip)
        {
            $this->log->debug("Все корабли размещены!");
            if ($this->multiPlayer)            
            {
                $bothReady = NetworkManager::isBothReady($this->gameID);
                $this->log->debug("Оба игрока готовы: " . ($bothReady ? 'да' : 'нет'));
                
                if ($bothReady && !NetworkManager::getCurrentPlayer($this->gameID)) {
                    $this->log->debug("Устанавливаем player1 как текущего");
                    NetworkManager::setCurrentPlayer($this->gameID, 'player1');
                }
                
                return $bothReady;
            }
            else
            {
                $this->log->debug("Одиночная игра - можно начинать");
                return true;
            }
        }
        
        $this->log->debug("Не все корабли размещены");
        return false;
    }

    public function getUnPlaceCount()
    {
        global $config;
        return count($config['ships']) - $this->curPlayerShip;
    }

    public function getCurPlaceShip()
    {
        global $config;
        $idx = min($this->curPlayerShip, count($this->myShips) - 1);
        return $this->myShips[$idx];
    }

    public function getCurOrientation()
    {
        return ($this->vertical ? 'вертикально' : 'горизонтально');
    }

    public function shiftOrientation()
    {
        $this->vertical = !$this->vertical;
    }

    private function makeCyrLabel($board)
    {
        for ($i = 0; $i < count($this->cyr_label); $i++)
            $board->setColLabel($i, $this->cyr_label[$i]);
    }

    public function getColByName($char)
    {
        return array_search($char, $this->cyr_label);
    }

    private function getPrintCoord($board, $row, $col)
    {
        return $board->getColLabel($col).($row+1);
    }

    public function getColLabel($index)
    {
        return $this->cyr_label[$index] ?? $index;
    }
    
    public function getGameId()
    {
        return $this->gameID;
    }

    public function getCurrentPlayer()
    {
        if ($this->multiPlayer) {
            return NetworkManager::getCurrentPlayer($this->gameID);
        }
        return null;
    }
}
?>