<?php

require_once 'seaBattleConfig.php';

class SmartShoot
{
    private $vertical;
    private $position = []; // row,col
    private $incr; // добавлять или отминать от предыдущего
    private $niceShoot = null;
    private $log;

    private $miss_count = 0;

    public function __construct($row, $col, $log = null)
    {
        $this->incr = null;
        $this->vertical = null;
        $this->position[] = ['row' => $row, 'col' => $col];
        $this->log = $log;
    }

    public function addPos($row, $col)
    {
        global $config;

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
        if ($config['debug'])
            $this->log->debug("Smart set vertical: ".$this->vertical." and add x = ".($col + 1)." y = ".($row+1));
    }

    public function calcNiceShoot($row, $col) // --- при промахе
    {
        global $config;

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
            if ($config['debug'])
                $this->log->debug("Calc nice shoot at x = ".($this->niceShoot['col'] + 1)." y = ".($this->niceShoot['row'] + 1));
        }
    }

    public function getInfo()
    {
        return ['pos' => $this->position, 'vertical' => $this->vertical, 'niceShoot' => $this->niceShoot, 'inc' => $this->incr];
    }
}

?>

