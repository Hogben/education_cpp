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
    #private $shootsPlayer = [];
    #private $shootsComputer = [];
    private $gameOver = false;

    public function __construct()
    {
        $playerBoard = new Matrix($config['sea']['size'], $config['sea']['size'], 'numeric', 'alpha', $config['sea']['board']['empty']);
        $playerBoard->setShowColsLabel(true);
        $playerBoard->setShowRowsLabel(true);

        $computerBoard = new Matrix($config['sea']['size'], $config['sea']['size'], 'numeric', 'alpha', $config['sea']['board']['empty']);
        $computerBoard->setShowColsLabel(true);
        $computerBoard->setShowRowsLabel(true);

        $this->initShips();
        $this->placeComputerShips();
        //---- func растановки кораблей
    }

    private function initShips()
    {
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

    private function placeShipsRandom($board, $ships)  //--- всегда комп и по желанию игрок
    {
        foreach ($ships as $ship)        
        {
            $placed = false;
            $shipSize = $ship->getSize();
            while (!$placed)
            {
                $row = rand(0, $board->seaSize - 1);
                $col = rand(0, $board->seaSize - 1);

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
                        if ($board === $playerBoard)
                        {
                            ($vertical) ? drawShip($row + $i, $col) : drawShip($row, $col + $i);    
                        }
                    }                    
                    $ship->setPosition($pos, $vertical); 
                    $placed = true;
                }
            }
        }
    }

    private function drawShip($row, $col, $val = $config['sea']['board']['ship'], $board = $playerBoard)
    {
        $board->setValue($row, $col, $val);
    }

    private function canPlaceShip($ship, $row, $col, $vertical, $ships) : bool
    {
        $shipSize = $ship->getSize();
        if ($vertical)
        {
            if ($row + $shipSize > $board->seaSize) return false;
        }
        else
        {
            if ($col + $shipSize > $board->seaSize) return false;
        }    

        // ------- перебрать действующие корабли и проверить пересечение координат
        for ($i = 0; $i < $shipSize; $i++)
        {
            $checkRow = $vertical ? $row + $i : $row;
            $checkCol = $vertical ? $col : $col + $i;
            foreach ($ships as $realShip)
            {
                if ($realShip === $ship)    break;
                for ($r = max(0, $checkRow - 1); $r < min($board->seaSize - 1, $checkRow + 1); $r++)
                {
                    for ($c = max(0, $checkCol - 1); $c < min($board->seaSize - 1, $checkCol + 1); $c++)
                    {
                        for ($i = 0; $i < $shipSize; $i++)
                        {
                            foreach($realShip->getPosition() as $pos)
                            {
                                if ($pos['pos']['row'] === $r && $pos['pos']['col'] === $c) return false;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    public function placePlayerShip($row, $col, $vertical) : bool
    {
        $ship = $this->playerShips[$curPlayerShip];
        $shipSize = $ship->getSize();

        if ($this->canPlaceShip($ship, $row, $col, $vertical, $this->playerShips))
        {
            $pos = [];
            for ($i = 0; $i < $shipSize; $i++)
            {
                if ($vertical)
                    $pos[] = ['row' => $row + $i, 'col' => $col];
                else
                    $pos[] = ['row' => $row, 'col' => $col + $i];
                ($vertical) ? drawShip($row + $i, $col) : drawShip($row, $col + $i);    
            }                    
            $ship->setPosition($pos, $vertical); 
            $this->curPlayerShip++;
            return true;
        }
        return false;
    }

    public function playerShoot($row, $col)
    {
        //---------- оптимизировать через getValue
        if ($config['sea']['board']['empty'] !== $computerBoard->getValue($row, $col))
               return ['result' = 'Уже сюда стрелял!', 'hit' = false, 'game_over' = false];
        $hit = false;
        $dead = false;
        $check_ship = null;
        foreach($this->computerShips as $ship)
        {
            if ($ship->checkHit($row, $col))
            {
                $this->computerBoard->setVlalue($row, $col, $config['sea']['board']['hit']);
                $hit = true;
                $dead = $ship->isDead(); 
                $check_ship = $ship;
                break;
            }
        }
        if ($hit)
        {
            $this->computerBoard->setVlalue($row, $col, $config['sea']['board']['hit']);
            if ($dead)
            {
                //-------- проверить весь флот на живучесть
                if ($this->checkShips($this->computerShips) === true)
                    return ['result' = 'Игрок победил!', 'hit' = true, 'game_over' = true];
                //--------- нала отметить вокруг корябля как обстреляно
                $this->markDeadShip($this->computerBoard, $check_ship)
                return ['result' = 'Убил!', 'hit' = true, 'game_over' = false];
            }
            else
                return ['result' = 'Ранил!', 'hit' = true, 'game_over' = false];
        }
        else
        {
            $this->computerBoard->setVlalue($row, $col, $config['sea']['board']['miss']);
            return ['result' = 'Мимо!', 'hit' = false, 'game_over' = false];
        }
    }

    private function checkShips($Ships)
    {
        foreach ($Ships as $ship)
        {
            if (!$ship->isDead()) return false;
        }
        return true;
    }

    private function markDeadShip($Board, $ship)
    {
        $ship_position = $ship->getPosition();
        for ($i = 0; $i < $ship->getSize(); $i++)
        {
            $checkRow = $ship_position['vertical'] ? $row + $i : $row;
            $checkCol = $ship_position['vertical'] ? $col : $col + $i;
            for ($r = max(0, $checkRow - 1); $r < min($board->seaSize - 1, $checkRow + 1); $r++)
            {
                for ($c = max(0, $checkCol - 1); $c < min($board->seaSize - 1, $checkCol + 1); $c++)
                {
                    if ($Board->getValue($r,$c) === $config['sea']['board']['hit']) continue;
                    $Board->setValue($r,$c,$config['sea']['board']['miss']);
                }
            }        
        }
    }
}

?>

