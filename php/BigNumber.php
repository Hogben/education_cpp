<?php

class BigNumber 
{
    private $value;
    private $negative;
    private $absValue; //--- модуль числа

    public function __construct(string $val)
    {
        if (!preg_match('/^-?\d+$/', $val))
        {
            throw new InvalidArgumentException("No valid format: $val");
        }
        $this->value = $this->niceNumber($val);
        $this->negative = ($this->value[0] === '-') ? true : false;    
        $this->absValue = ($this->getNegative()) ? substr($this->value,1) : $this->value;

    }

    private function niceNumber (string $val) : string
    {
        if ($val == '0')    return '0';
        $tmp = ($val[0] === '-') ? substr($val,1) : $val;
        return ltrim($tmp, '0');
    }

    
    public function add(BigNumber $inNumber) : BigNumber
    {
        $val_1 = $this->getAbsValue();
        $val_2 = $inNumber->getAbsValue();

        $set_negative = false;

        $res;

        //-------- проверки
        if (($this->getNegative() && $inNumber->getNegative()) || (!$this->getNegative() && !$inNumber->getNegative()))
        {
            if ($this->getNegative() && $inNumber->getNegative())
            {
                $set_negative = true;
            }    
            $res = new BigNumber($this->addSameSing($val_1, $val_2, $set_negative));
        }
/*/        
        else
        {
            if ($this->getNegative())
            {
                $inNumber->sub($this);
            }
            else
            {
                $this->sub($inNumber);    
            }
        }
/*/           
        return $res;
    }

    private function addSameSing(string $val1, string $val2, bool $set_negative = false) : string
    {
        $i = strlen($val1) - 1;
        $j = strlen($val2) - 1;

        $cf = 0;
        
        $res = '';

        while ($i >= 0 || $j >= 0 || $cf > 0)
        {
            $num1 = ($i >= 0) ? (int) $val1[$i--] : 0;
            $num2 = ($j >= 0) ? (int) $val2[$j--] : 0;

            $sum = $num1 + $num2 + $cf; 
            $cf = intdiv($sum, 10); 
            $res = ($sum % 10).$res; 
        }

        return $set_negative ? '-'.$res : $res;
    }

    public function sub(BigNumber $inNumber) : BigNumber
    {
        $val_1 = $this->getAbsValue();
        $val_2 = $inNumber->getAbsValue();

        $set_negative = false;

        $res;

        //-------- проверки
        if (($this->getNegative() && !$inNumber->getNegative()) || ($this->getNegative() && !$inNumber->getNegative()))
        {
            if ($this->getNegative() && !$inNumber->getNegative())
            {
                $set_negative = true;
            }    
            $res = new BigNumber($this->addSameSing($val_1, $val_2, $set_negative));
        }
        return $res;
    }
/*
    private function subSameSing(string $val1, string $val2, bool $set_negative = false) : string
    {

    }

    public function mul(BigNumber $inNumber) : BigNumber
    {
        
    }

    public function div(BigNumber $inNumber) : BigNumber
    {
        
    }
*/
    public function getNegative() : bool
    {
        return $this->negative;
    }

    public function getAbsValue() : string
    {
        return $this->absValue;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}

$num1 = new BigNumber('111111111111111111111111111111111111111111111111');
$num2 = new BigNumber('11');
$num3 = $num1->add($num2);

echo $num3->getValue();

?>