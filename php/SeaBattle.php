<?php

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
        foreach ($this->computerShips as $compShip)        
        {
            $placed = false;
            while (!$placed)
            {
                
            }
        }
    }

}

?>

