<?php

require_once 'seaBattleConfig.php';
require_once 'matrix.php';
require_once 'BattleShip.php';
require_once 'SmartShoot.php';
require_once 'Logger.php';
require_once 'NetworkManager.php';

class SeaBattle 
{
    //---------- one player
    private $playerBoard; // Matrix
    private $computerBoard; // Matrix
    private $playerShips = []; 
    private $computerShips = [];
    private $curPlayerShip = 0;
    private $gameOver = false;
    private $vertical = false;
    private $smartShoot = null;
    private $cyr_label = ['А','Б','В','Г','Д','Е','Ж','З','И','К'];

    public $log = null;

    //---------- netplay
    private $gameID;
    private $playerNumber;
    private $playerCurrent;
    private $multiPlayer = false;
    private $enemyBoard = null;

    public function __construct($gameID = null, $playerNumber = null)
    {
        global $config;

        $this->log = new Logger('sea_battle');
        $this->log->clearLog();

        $this->playerBoard = new Matrix($config['sea']['size'], $config['sea']['size'], 'numeric', 'alpha', $config['sea']['board']['empty']);
        $this->playerBoard->setShowColsLabel(true);
        $this->playerBoard->setShowRowsLabel(true);
        $this->makeCyrLabel($this->playerBoard);

        $this->computerBoard = new Matrix($config['sea']['size'], $config['sea']['size'], 'numeric', 'alpha', $config['sea']['board']['empty']);
        $this->computerBoard->setShowColsLabel(true);
        $this->computerBoard->setShowRowsLabel(true);
        $this->makeCyrLabel($this->computerBoard);

        $this->initShips();

        if ($gameID && $playerNumber) 
        {
            $this->gameID = $gameID;
            $this->playerNumber = $playerNumber;
            $this->multiPlayer = true;
            $this->loadNetworkState();
        }
        else
            $this->placeComputerShips();
    }

    private function loadNetworkState()
    {
        if (!$this->multiPlayer) return;

        $this->playerCurrent = NetworkManager::getCurrentPlayer($this->gameID);
        $boardData = NetworkManager::getGameBoard($this->gameID, $this->playerNumber);
        if ($boardData)
        {
            $this->loadBoardFromData($boardData);
        }

        $enemyPlayer = ($this->playerNumber == 'player1') ? 'player2' : 'player1';
        $enemyBoardData = NetworkManager::getGameBoard($this->gameID, $enemyPlayer);

        if ($enemyBoardData)
        {
            $this->loadEnemyBoard($enemyBoardData);
        }


    }

    private function saveNetworkState()
    {
        if (!$this->multiPlayer) return;

        $boardData = $this->getBoardData();
        NetworkManager::updateGameBoard($this->gameID, $this->playerNumber, $boardData);

    }

    private function getBoardData()
    {
        $data = [
            'board' => [],
            'ships' => [],
            'curPlayerShip' => $this->curPlayerShip
        ];

        for ($row = 0; $row < $this->playerBoard->getRows(); $row++)
        {
            for ($col = 0; $col < $this->playerBoard->getCols(); $col++)
            {
                $data['board'][$row][$col] = $this->playerBoard->getValue($row, $col);
            }
        }
        //-------- restore ships
        foreach ($this->playerShips as $index => $ship)
        {
            $shipData = $ship->getPosition();
            $data['ships'][$index] = $shipData;
        }

        return $data;
    }

    private function loadBoardFromData($boardData)
    {
        global $config;


        for ($row = 0; $row < $this->playerBoard->getRows(); $row++)
        {
            for ($col = 0; $col < $this->playerBoard->getCols(); $col++)
            {
                $this->playerBoard->setValue($row, $col, $boardData['board'][$row][$col]);
            }
        }
        //-------- restore ships
        if (isset($boardData['ships']))
        {
            foreach ($boardData['ships'] as $index => $shipData)
            {
                if (isset($this->playerShips[$index]))
                {
                    if (isset($shipData['pos']))
                        $this->playerShips[$index]->setPosition($shipData['pos'], $shipData['vertical']);

                    if (isset($shipData['hit']))
                        $this->playerShips[$index]->setHit($shipData['hit']);
                }
            }
        }

        $this->curPlayerShip = $boardData['curPlayerShip'] ?? 0;
    }


    private function loadEnemyBoard($boardData)
    {
        global $config;

        for ($row = 0; $row < $this->computerBoard->getRows(); $row++)
        {
            for ($col = 0; $col < $this->computerBoard->getCols(); $col++)
            {
                $this->computerBoard->setValue($row, $col, $boardData['board'][$row][$col]);
            }
        }
        //-------- restore ships
        if (isset($boardData['ships']))
        {
            foreach ($boardData['ships'] as $index => $shipData)
            {
                if (isset($this->computerShips[$index]))
                {
                    if (isset($shipData['pos']))
                        $this->computerShips[$index]->setPosition($shipData['pos'], $shipData['vertical']);

                    if (isset($shipData['hit']))
                        $this->computerShips[$index]->setHit($shipData['hit']);
                }
            }
        }
    }

    private function initShips()
    {
        global $config;
        foreach ($config['ships'] as $s)
        {
            $this->playerShips[] = new BattleShip($s['size'], $s['name']);
            $this->computerShips[] = new BattleShip($s['size'], $s['name']);
        }
    }

    private function placeComputerShips()
    {
        $this->placeShipsRandom($this->computerBoard, $this->computerShips);
    }

    public function quickPlaceShip()
    {
        global $config;

        $this->playerBoard->fillMatrix($config['sea']['board']['empty']);

        $this->curPlayerShip = 0;
        foreach ($this->playerShips as $ship) 
        {
            $ship->setPosition([], false);
        }        

        $this->placeShipsRandom($this->playerBoard, $this->playerShips);
        $this->curPlayerShip = count($config['ships']);
         //       $this->log->info("Начало игры");
        if ($this->multiPlayer)            
        {
            $this->setPlayerReady();
            $this->saveNetworkState();
        }
        //        $this->Start();
    }

    private function placeShipsRandom($board, $ships)  //--- всегда комп и по желанию игрок
    {
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
                        if ($board === $this->playerBoard)    // закоментить для читерства
                        {
                            ($vertical) ? $this->drawShip($row + $i, $col, $config['sea']['board']['ship'], $board) : $this->drawShip($row, $col + $i, $config['sea']['board']['ship'], $board);    
                        }
                    }                    
                    $ship->setPosition($pos, $vertical); 
                    $placed = true;
                }
            }
        }
        if ($this->multiPlayer)            
            $this->saveNetworkState();
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
            if ($row + $shipSize > $this->playerBoard->getRows()) return false;
        }
        else
        {
            if ($col + $shipSize > $this->playerBoard->getCols()) return false;
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
                
                for ($r = max(0, $checkRow - 1); $r <= min($this->playerBoard->getRows() - 1, $checkRow + 1); $r++)
                {
                    for ($c = max(0, $checkCol - 1); $c <= min($this->playerBoard->getCols() - 1, $checkCol + 1); $c++)
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

        $ship = $this->playerShips[$this->curPlayerShip];
        $shipSize = $ship->getSize();

        if ($this->canPlaceShip($ship, $row, $col, $this->vertical, $this->playerShips))
        {
            $pos = [];
            for ($i = 0; $i < $shipSize; $i++)
            {
                if ($this->vertical)
                    $pos[] = ['row' => $row + $i, 'col' => $col];
                else
                    $pos[] = ['row' => $row, 'col' => $col + $i];
                ($this->vertical) ? $this->drawShip($row + $i, $col, $config['sea']['board']['ship'], $this->playerBoard) : $this->drawShip($row, $col + $i, $config['sea']['board']['ship'], $this->playerBoard);    
            }                    
            $ship->setPosition($pos, $this->vertical); 
            
            $this->curPlayerShip++;
            if ($this->curPlayerShip === count($config['ships']))   
            {
                if ($this->multiPlayer)  $this->setPlayerReady();
//                $this->curPlayerShip--;
            }

            if ($this->multiPlayer)          
            {
                $this->saveNetworkState();
            }
            return true;
        }
        return false;
    }

//---------- синхронизация состояния игры ----
    public function syncGameState()
    {
        $mes = '';
        if ($this->multiPlayer)
        {
            $this->loadNetworkState();

            if ($this->Start())
            {
                $mes = "Стреляет ".$this->playerCurrent;
            }
            else
            {
                $mes = NetworkManager::getPlayerReady($this->gameID, $this->playerNumber) ? "Ждем соперника" : "Расставьте корабли";
            }
            return['message' => $mes];
        }
        else
            return [];
    }

    public function networkShoot($row, $col, $shooter)
    {
        global $config;

        if ($this->playerCurrent !== $shooter)
        {
            return ['result' => 'Не Ваш ход...', 'hit' => false, 'game_over' => false, 'dead' => false];
        }

        $targetBoard = ($shooter === 'player1') ? $this->computerBoard : $this->playerBoard;
        $targetShips = ($shooter === 'player1') ? $this->computerShips : $this->playerShips;

        if ($config['sea']['board']['miss'] === $targetBoard->getValue($row, $col) || $config['sea']['board']['hit'] === $targetBoard->getValue($row, $col))
            return ['result' => 'Уже сюда стрелял!', 'hit' => false, 'game_over' => false, 'dead' => false];

        $res = $this->shootResult($targetBoard, $targetShips, $row, $col);

        if (!$res['hit'])
        {
            $this->playerCurrent = ($shooter === 'player1') ? 'player2' : 'player1';
        }

        $this->saveNetworkState();
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

        //---------- оптимизировать через getValue
        if ($config['sea']['board']['miss'] === $this->computerBoard->getValue($row, $col) || $config['sea']['board']['hit'] === $this->computerBoard->getValue($row, $col))
            return ['result' => 'Уже сюда стрелял!', 'hit' => false, 'game_over' => false, 'dead' => false];
        return $this->shootResult($this->computerBoard, $this->computerShips, $row, $col);
    }

    private function shootResult($board, $Ships, $row, $col)
    {
        global $config;

        if ($board === $this->playerBoard)
            $this->log->info("Компьютер стреляет: ".$this->getPrintCoord($this->playerBoard, $row, $col));
        else
            $this->log->info("Игрок стреляет: ".$this->getPrintCoord($this->playerBoard, $row, $col));

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
                //-------- проверить весь флот на живучесть
                if ($this->checkDeadShips($Ships) === true)
                {
                    $this->log->info("Конец игры!!!");
                    $msg = ($board === $this->computerBoard) ? 'Игрок победил!' : 'Компьюер думает что победил...';
                    return ['result' => $msg, 'hit' => true, 'game_over' => true, 'dead' => true];
                }
                //--------- нала отметить вокруг корябля как обстреляно
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

    public function computerShoot($smart = true)
    {
        global $config;

        $maxShots = 30; // Максимальное количество выстрелов за ход
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
                    $row = rand(0,$this->playerBoard->getRows() - 1);
                    $col = rand(0,$this->playerBoard->getCols() - 1);
                    $attempts++;
                    
                    // Защита от бесконечного цикла
                    if ($attempts > 200) {
                        $this->log->error("Не удалось найти свободную клетку для выстрела");
                        return ['result' => 'Ошибка: не удалось найти цель', 'hit' => false, 'game_over' => false, 'dead' => false];
                    }

                } while ($this->playerBoard->getValue($row, $col) === $config['sea']['board']['hit'] || $this->playerBoard->getValue($row, $col) === $config['sea']['board']['miss']);

                $res = $this->shootResult($this->playerBoard, $this->playerShips, $row, $col);
                $shotsMade++;
                
                if ($res['game_over']) {
                    return $res;
                } 
                elseif ($res['hit']) 
                {
                    if (!$res['dead']) 
                    {
                        $this->smartShoot = new SmartShoot($row, $col, $this->log);
                        // Продолжаем стрельбу в следующей итерации цикла
                    }
                    else 
                    {
                        $this->smartShoot = null;
                        //                        return $res;
                    }
                    continue;
                } 
                else 
                {
                    return $res;
                }
            }
            else
            {
                $shootInfo = $this->smartShoot->getInfo();

                $direction = ['left' => true, 'right' => true, 'up' => true, 'down' => true];
            
                if ($shootInfo['niceShoot'] !== null)
                {
                    $row = $shootInfo['niceShoot']['row'];
                    $col = $shootInfo['niceShoot']['col'];
                    if ($config['debug'])
                        $this->log->debug("следующий выстрел: ".$this->getPrintCoord($this->playerBoard, $row, $col));
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
                                ($shootInfo['vertical'] && $shootInfo['inc'] && $pos['row'] === $this->playerBoard->getRows() - 1) || 
                                ($shootInfo['vertical'] && !$shootInfo['inc'] && $pos['row'] === 0) ||
                                (!$shootInfo['vertical'] && $shootInfo['inc'] && $pos['col'] === $this->playerBoard->getCols() - 1) || 
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
                        else  //----- было только 1 попадание  
                        {
                            if ($pos['row'] === 0 || $this->playerBoard->getValue($pos['row'] - 1, $pos['col']) === $config['sea']['board']['miss'])  
                                $direction['up'] = false;
                            if ($pos['row'] === $this->playerBoard->getRows() - 1 || $this->playerBoard->getValue($pos['row'] + 1, $pos['col']) === $config['sea']['board']['miss'])
                                $direction['down'] = false;
                            if ($pos['col'] === 0 || $this->playerBoard->getValue($pos['row'], $pos['col'] - 1) === $config['sea']['board']['miss'])
                                $direction['left'] = false;
                            if ($pos['col'] === $this->playerBoard->getCols() - 1 || $this->playerBoard->getValue($pos['row'], $pos['col'] + 1) === $config['sea']['board']['miss'])
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
                        
                        // Защита от бесконечного цикла
                        if ($attempts > 50) {
                            $this->log->error("Не удалось найти цель для умного выстрела");
                            $this->smartShoot = null;
                            return ['result' => 'Ошибка поиска цели', 'hit' => false, 'game_over' => false, 'dead' => false];
                        }
                    } while ($this->playerBoard->getValue($row, $col) === $config['sea']['board']['hit'] || $this->playerBoard->getValue($row, $col) === $config['sea']['board']['miss']);
                }

                $res = $this->shootResult($this->playerBoard, $this->playerShips, $row, $col);
                $shotsMade++;
                
                if ($res['game_over']) {
                    return $res;
                } elseif ($res['hit']) 
                {
                    if ($res['dead']) {
                        $this->smartShoot = null;
                        //                        return $res;
                    } 
                    else {
                        $this->smartShoot->addPos($row, $col);
                        // Продолжаем стрельбу в следующей итерации
                    }
                    continue;
                } else 
                {
                    $this->smartShoot->calcNiceShoot($row, $col);
                    return $res;
                }
            }
            
            // Защита от бесконечного цикла
            if ($shotsMade >= $maxShots) {
                $this->log->warning("Достигнут лимит выстрелов за ход: $maxShots");
                return ['result' => 'Лимит выстрелов', 'hit' => false, 'game_over' => false, 'dead' => false];
            }
            
        } while (true);
    }

    private function checkDeadShips($Ships)
    {
        foreach ($Ships as $ship)
        {
            if (!$ship->isDead()) return false;
        }
        $this->gameOver = true;
        return $this->gameOver;
    }

    public function isGameOver()
    {
        return $this->gameOver;
    }

    private function markDeadShip($Board, $ship)
    {
        global $config;

        $ship_position = $ship->getPosition();
        foreach ($ship_position['pos'] as $pos)
        {
            $checkRow = $pos['row'];
            $checkCol = $pos['col'];
            for ($r = max(0, $checkRow - 1); $r <= min($Board->getRows() - 1, $checkRow + 1); $r++)
            {
                for ($c = max(0, $checkCol - 1); $c <= min($Board->getCols() - 1, $checkCol + 1); $c++)
                {
                    if ($Board->getValue($r,$c) === $config['sea']['board']['hit']) continue;
                    $Board->setValue($r,$c,$config['sea']['board']['miss']);
                }
            }        
        }
    }

    public function drawPlayerBoard()
    {
        return $this->playerBoard->make();
    }

    public function drawComputerBoard()
    {
        return $this->computerBoard->make();
    }

    private function setPlayerReady()
    {
        global $config;
        if (count($config['ships']) === $this->curPlayerShip)
        {
            NetworkManager::setPlayerReady($this->gameID, $this->playerNumber);
        }
    }

    public function Start()
    {
        global $config;

        if (count($config['ships']) === $this->curPlayerShip)
        {
            if ($this->multiPlayer)            
            {
                return NetworkManager::isBothReady($this->gameID);
            }
            else
            {
                return true;
            }
        }
        else
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
/*/        
        $idx = $this->curPlayerShip;
        if ($this->multiPlayer)            
        {
            if ($this->isPlayerReady())    $idx--;
        }
        else
        {
            if ($this->Start()) $idx--;
        }
        return $this->playerShips[$idx];
/*/        
        return (count($config['ships']) === $this->curPlayerShip) ?  $this->playerShips[$this->curPlayerShip - 1] : $this->playerShips[$this->curPlayerShip];
    }

    public function getCurOrientation()
    {
        return ($this->vertical ? 'вертикально' : 'горизотально');
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
}

?>

