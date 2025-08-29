<?php

require_once 'matrix.php';
require_once 'BattleShip.php';

class SeaBattle 
{
    const seaSize = 10;

    private $playerBoard; // Matrix
    private $computerBoard; // Matrix
    private $playerShips = []; 
    private $computerShips = [];
   
    public function __construct()
    {
        $playerBoard = new Matrix($this->seaSize, $this->seaSize, 'numeric', 'alpha', ' ');
        $playerBoard->setShowColsLabel(true);
        $playerBoard->setShowRowsLabel(true);

        $computerBoard = new Matrix($this->seaSize, $this->seaSize, 'numeric', 'alpha', ' ');
        $computerBoard->setShowColsLabel(true);
        $computerBoard->setShowRowsLabel(true);

        $this->initShips();
        $this->placeComputerShips();
        //---- func растановки кораблей
    }

    private function initShips()
    {
        $ships = [
            ['size' => 4, 'name' => 'Линкор'],
            ['size' => 3, 'name' => 'Эсминец'],
            ['size' => 3, 'name' => 'Эсминец'],
            ['size' => 2, 'name' => 'Катер'],
            ['size' => 2, 'name' => 'Катер'],
            ['size' => 2, 'name' => 'Катер'],
            ['size' => 1, 'name' => 'Шлюпка'],
            ['size' => 1, 'name' => 'Шлюпка'],
            ['size' => 1, 'name' => 'Шлюпка'],
            ['size' => 1, 'name' => 'Шлюпка']
        ];

        foreach ($ships as $s)
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
                    }                    
                    $ship->setPosition($pos); 
                    if ($board === $playerBoard)
                    {
                       ($vertical) ? drawShip($row + $i, $col) : drawShip($row, $col + $i);    
                    }
                    $placed = true;
                }
            }
        }
    }

    private function drawShip($row, $col, $val = '@', $board = $playerBoard)
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
                                if ($pos['row'] === $r && $pos['col'] === $c) return false;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
}

?>

