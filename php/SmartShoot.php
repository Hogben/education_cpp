<?php

class SmartShoot
{
    private $vertical;
    private $position = []; // row,col
    private $incr; // добавлять или отминать от предыдущего
    private $niceShoot = null;

    public function __construct($row, $col)
    {
        $this->incr = null;
        $this->vertical = null;
        $this->position[] = ['row' => $row, 'col' => $col];
    }

    public function addPos($row, $col)
    {
        $this->position[] = ['row' => $row, 'col' => $col];
        $this->niceShoot = null;
        $this->vertical = ($col === $this->position[0]['col']);
        if ($this->vertical)
        {
            $this->incr = ($row > $this->position[0]['row']);
        }
        else
        {
            $this->incr = ($col > $this->position[0]['col']);
        }
    }

    public function calcNiceShoot($row, $col) // --- при промахе
    {
        if ($this->vertical !== null)
        {
            if ($this->vertical)    
            {
                if ($this->position[0]['row'] < $row)
                {
                    $this->incr = false;
                    $this->niceShoot = ['row' => min(array_column($this->position, 'row')) - 1, 'col' => $col];
                }
                else
                {
                    $this->incr = true;
                    $this->niceShoot = ['row' => max(array_column($this->position, 'row')) + 1, 'col' => $col];
                }
            }
            else
            {
                if ($this->position[0]['col'] < $col)
                {
                    $this->incr = false;
                    $this->niceShoot = ['row' => $row, 'col' => min(array_column($this->position, 'col')) - 1];
                }
                else
                {
                    $this->incr = true;
                    $this->niceShoot = ['row' => $row, 'col' => max(array_column($this->position, 'col')) + 1];
                }
            }
        }
    }

    public function getInfo()
    {
        return ['pos' => $this->position, 'vertical' => $this->vertical, 'niceShoot' => $this->niceShoot, 'inc' => $this->incr];
    }
}

?>

