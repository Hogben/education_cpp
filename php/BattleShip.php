<?php

class BattleShip {
    private $size; // 1-4
    private $name;
    private $hit = 0;
    private $positon = []; // row,col
    private $vertical = false;
    
    public function __construct($size, $name)
    {
        $this->name = $name; 
        $this->size = $size; 
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPosition($pos, $vertical)
    {
        $this->vertical = $vertical;
        $this->position = $pos;
        return true;
    }

    public function getPosition()
    {
        return ['pos' = $this->position, 'vertical' = $this->vertical];
    }

    public function isDead()
    {
        return $this->hit === $this->size;
    }

    public function checkHit($row, $col) : bool
    {
        foreach ($this->position as $pos)
        {
            if ($pos['row'] === $row && $pos['col'] === $col)
            {   
                $this->hit++;
                return true;
            }
        }
        return false;
    }

}

?>

