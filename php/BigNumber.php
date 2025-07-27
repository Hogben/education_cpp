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
        return ($val[0] === '-') ? '-'.ltrim($tmp, '0') : ltrim($tmp, '0');
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

        $t_cmp = $this->cmp($this->value, $inNumber->getValue());

        if ($t_cmp === 0)    return new BigNumber('0');

        $res;

        //-------- проверки
        if (($this->getNegative() && !$inNumber->getNegative()) || (!$this->getNegative() && $inNumber->getNegative()))
        {
            if ($this->getNegative() && !$inNumber->getNegative())
            {
                $set_negative = true;
            }    
            $res = new BigNumber($this->addSameSing($val_1, $val_2, $set_negative));
        }
        else
        {
            if ($this->getNegative())   $t_cmp *= -1; 
            
            if ($t_cmp > 0) 
                $res = new BigNumber($this->subSameSing($val_1, $val_2, $this->getNegative()));
            else
                $res = new BigNumber($this->subSameSing($val_2, $val_1, $inNumber->getNegative()));
        }
        return $res;
    }

    private function subSameSing(string $val1, string $val2, bool $set_negative = false) : string
    {
        $i = strlen($val1) - 1;
        $j = strlen($val2) - 1;

        $res = '';
        $carry = 0;

        while ($i >=0 )
        {
            $num1 = ($i >= 0) ? (int) $val1[$i--] : 0;            
            $num2 = ($j >= 0) ? (int) $val2[$j--] : 0;   

            if ($carry === 1)
            {
                if ($num1 === 0)
                {
                    $num1 = 9;
                }
                else
                {
                    $num1--;
                    $carry = 0;
                }
            }   
            
            $num1 -= $num2;

            if ($num1 < 0)
            {
                $carry = 1;
                $num1 = 10 + $num1; 
            }
            $res = $num1.$res; 
        }
        return ($set_negative === true) ? '-'.ltrim($res, '0') : ltrim($res, '0');
    }

    private function cmp(string $val1, string $val2) : int
    {
        if ($val1[0] === '-' && $val2[0] === '-')
        {
            return $this->cmpAbs(substr($val1,1), substr($val2,1)) * -1;
        }
        if ($val1[0] !== '-' && $val2[0] !== '-')
        {
            return $this->cmpAbs($val1, $val2);
        }
        return ($val1[0] === '-') ? -1 : 1;

    }

    private function cmpAbs(string $val1, string $val2) : int
    {
        if (strlen($val1) > strlen($val2)) return 1;
        if (strlen($val2) > strlen($val1)) return -1;
        return strcmp($val1, $val2);
    }
/*
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

$num1 = new BigNumber('-2');
$num2 = new BigNumber('-11');
$num3 = new BigNumber('2');
$num4 = new BigNumber('11');
$num12 = $num1->sub($num2);
$num14 = $num1->sub($num4);
$num32 = $num3->sub($num2);
$num34 = $num3->sub($num4);

echo $num12->getValue().'<br>';
echo $num14->getValue().'<br>';
echo $num32->getValue().'<br>';
echo $num34->getValue().'<br>';

?>