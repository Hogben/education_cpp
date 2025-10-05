<?php

require_once 'seaBattleConfig.php';
require_once 'matrix.php';
require_once 'BattleShip.php';

class SeaBattle 
{
    private $playerBoard; // Matrix
    private $computerBoard; // Matrix
    private $playerShips = []; 
    private $computerShips = [];
    private $curPlayerShip = 0;
    private $gameOver = false;
    private $vertical = false;

    public function __construct()
    {
        global $config;

        $this->playerBoard = new Matrix($config['sea']['size'], $config['sea']['size'], 'numeric', 'alpha', $config['sea']['board']['empty']);
        $this->playerBoard->setShowColsLabel(true);
        $this->playerBoard->setShowRowsLabel(true);
        $this->makeCyrLabel($this->playerBoard);

        $this->computerBoard = new Matrix($config['sea']['size'], $config['sea']['size'], 'numeric', 'alpha', $config['sea']['board']['empty']);
        $this->computerBoard->setShowColsLabel(true);
        $this->computerBoard->setShowRowsLabel(true);
        $this->makeCyrLabel($this->computerBoard);

        $this->initShips();
        $this->placeComputerShips();
        //---- func растановки кораблей
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
        $this->placeShipsRandom($this->playerBoard, $this->playerShips);
        $this->curPlayerShip = count($config['ships']);
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
//                        if ($board === $this->playerBoard)    // закоментить для читерства
                        {
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
            return true;
        }
        return false;
    }

    public function playerShoot($row, $col)
    {
        global $config;

        //---------- оптимизировать через getValue
        if ($config['sea']['board']['miss'] === $this->computerBoard->getValue($row, $col) || $config['sea']['board']['hit'] === $this->computerBoard->getValue($row, $col))
            return ['result' => 'Уже сюда стрелял!', 'hit' => false, 'game_over' => false];
        return shootResult($this->computerBoard, $this->computerShips, $row, $col);
    }

    private function shootResult($board, $Ships, $row, $col)
    {
        global $config;

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
                break;
            }
        }
        if ($hit)
        {
            $board->setValue($row, $col, $config['sea']['board']['hit']);
            if ($dead)
            {
                $msg = ($board === $this->computerBoard) ? 'Игрок победил!' : 'Компьюер думает что победил...';
                //-------- проверить весь флот на живучесть
                if ($this->checkGameOver($Ships) === true)
                    return ['result' => $msg, 'hit' => true, 'game_over' => true];
                //--------- нала отметить вокруг корябля как обстреляно
                $this->markDeadShip($board, $check_ship);
                return ['result' => 'Убил!', 'hit' => true, 'game_over' => false];
            }
            else
                return ['result' => 'Ранил!', 'hit' => true, 'game_over' => false];
        }
        else
        {
            $board->setValue($row, $col, $config['sea']['board']['miss']);
            return ['result' => 'Мимо!', 'hit' => false, 'game_over' => false];
        }
    }

    private function checkGameOver($Ships)
    {
        foreach ($Ships as $ship)
        {
            if (!$ship->isDead()) return false;
        }
        return true;
    }

    public function isGameOver()
    {
        foreach ($this->playerShips as $ship)
        {
            if (!$ship->isDead()) return false;
        }
        foreach ($this->computerShips as $ship)
        {
            if (!$ship->isDead()) return false;
        }
        return true;
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

    public function computerShoot($smart = false)
    {
        global $config;

        $row = null;
        $col = null;
       if ($smart)
        {
            return ['result' => 'Надо поумнеть!', 'hit' => false, 'game_over' => false];
        }
        else
        {
            do 
            {
                $row = rand(0,$board->getRows() - 1);
                $col = rand(0,$board->getCols() - 1);

            } while ($this->playerBoard->getValue($row, $col) != $config['sea']['board']['empty']);

            return shootResult($this->playerBoard, $this->playerShips, $row, $col);
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

    public function Start()
    {
        global $config;

        return (count($config['ships']) === $this->curPlayerShip);
    }

    public function getUnPlaceCount()
    {
        global $config;
        return count($config['ships']) - $this->curPlayerShip;
    }

    public function getCurPlaceShip()
    {
        $idx = $this->curPlayerShip;
        if ($this->Start()) $idx--;
        return $this->playerShips[$idx];
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
        $cyr_label = ['А','Б','В','Г','Д','Е','Ж','З','И','К'];
        for ($i = 0; $i < count($cyr_label); $i++)
            $board->setColLabel($i, $cyr_label[$i]);
    }


    public function getColByName($char)
    {
        $cyr_label = ['А','Б','В','Г','Д','Е','Ж','З','И','К'];
        return array_search($char, $cyr_label);
    }
}

?>

