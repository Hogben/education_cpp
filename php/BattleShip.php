<?php

class BattleShip {
    private $size; // 1-4
    private $name;
    private $hit = 0;
    private $positon = []; // row,col
    
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

    public function setPosition($pos)
    {
        return $this->position = $pos;
    }

    public function getPosition()
    {
        return $this->position;
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

