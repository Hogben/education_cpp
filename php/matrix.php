<?php

class Matrix {
    private $rows;
    private $cols;
    private $data = [];
    private $showRowsLabel = false;
    private $showColsLabel = false;
    private $rowsLabel = [];
    private $colsLabel = [];
    private $rowsLabelType = 'numeric'; // 'alpha'
    private $colsLabelType = 'numeric'; // 'alpha'


    public function __construct($rows, $cols, $rowsLabelType='numeric', $colsLabelType = 'numeric', $val = 0)
    {
        $this->rows = $rows;
        $this->cols = $cols;
        $this->rowsLabelType = $rowsLabelType;
        $this->colsLabelType = $colsLabelType;
        $this->initData($val);
        $this->initLabel();
    }

    private function initLabel()
    {
        for ($i = 0; $i < $this->rows; $i++)
            $this->rowsLabel[$i] = $this->createLabel($this->rowsLabelType, $i);

        for ($i = 0; $i < $this->cols; $i++)
            $this->colsLabel[$i] = $this->createLabel($this->colsLabelType, $i);
    }


    //  A...Z AAABAC... AZ 
    private function createLabel($type, $idx)
    {
        // now only numeric
        if ($type === 'numeric')
            return $idx + 1;
    }

    private function initData($val)
    {
        for ($i = 0; $i < $this->rows; $i++)
            $this->data[$i] = array_fill(0, $this->cols, $val);
    }

    public function setValue($row, $col, $val) : bool
    {
        if (isset ($this->data[$row][$col])) {
            $this->data[$row][$col] = $val;
            return true;
        }
        return false;
    }

    public function getValue($row, $col) 
    {
        return $this->data[$row][$col] ?? null; // isset($this->data[$row][$col]) ? $this->data[$row][$col] : null
    }

    public function getRows() 
    {
        return $this->rows;
    }

    public function getCols() 
    {
        return $this->cols;
    }

    public setShowColsLabel($show = true)
    {
        $this->showColsLabel = $show;
    }

    public setShowRowsLabel($show = true)
    {
        $this->showRowsLabel = $show;
    }

    public function draw()
    {
        $html = '<table class="matrix" border="1">';
        
        if ($this->showColsLabel)
        {
            //---- нарисовать подписи для колонок
            $html .= '<tr>';
            $html .= $this->showRowsLabel ? '<th></th>' : '';
            foreach ($this->colsLabel as $colLabel)
                $html .= '<th>'.htmlspecialchars($colLabel).'</th>';
            $html .= '</tr>';
        }

        //--- нарисовать матрицу с учетом видимости подписей у строк
        
    }
}

?>