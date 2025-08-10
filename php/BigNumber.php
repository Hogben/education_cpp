<?php

class BigNumber 
{
    private $value;
    private $negative;
    private $absValue; //--- модуль числа
    private $remainder;

    public function __construct(string $val, string $remiand = '0')
    {
        if (!preg_match('/^-?\d+$/', $val))
        {
            throw new InvalidArgumentException("No valid format: $val");
        }
        $this->value = $this->niceNumber($val);
        $this->negative = ($this->value[0] === '-') ? true : false;    
        $this->absValue = ($this->getNegative()) ? substr($this->value,1) : $this->value;
        $this->remainder = $remiand;
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

        // Одинаковые знаки: складываем
        if ($this->getNegative() === $inNumber->getNegative()) {
            $resultValue = $this->addSameSign($val_1, $val_2, $this->getNegative());
            return new BigNumber($resultValue);
        }

        // Разные знаки: вычитаем
        $cmp = $this->cmpAbs($val_1, $val_2);
        if ($cmp === 0) {
            return new BigNumber('0');
        }

        if ($cmp > 0) {
            $resultValue = $this->subSameSign($val_1, $val_2, $this->getNegative());
        } else {
            $resultValue = $this->subSameSign($val_2, $val_1, $inNumber->getNegative());
        }

        return new BigNumber($resultValue);
    }

    private function addSameSign(string $val1, string $val2, bool $set_negative = false) : string
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
        $neg_in_value = $inNumber->getNegative() ? $inNumber->getAbsValue() : '-' . $inNumber->getAbsValue(); 
        return $this->add(new BigNumber($neg_in_value));
    }

    private function subSameSign(string $val1, string $val2, bool $set_negative = false) : string
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

    public function mulDigit(string $str, int $num) : string
    {
        $carry = 0;
        $res = '';

        for ($i = strlen($str)-1; $i >= 0; $i--)
        {
            $t_num = (int)$str[$i]*$num + $carry;
            $carry = intdiv($t_num, 10);
            $res = ($t_num % 10).$res;    
        }

        if ($carry > 0)
            $res = $carry.$res;    

        return $res;
    }

    public function mul(BigNumber $inNumber) : BigNumber
    {
        $res = new BigNumber('0');

        $set_negative = $this->getNegative() ^ $inNumber->getNegative();

        $a = $this->getAbsValue();
        $b = $inNumber->getAbsValue();

        if ($this->cmpAbs($a, $b) > 0)
        {
            $b = $this->getAbsValue();
            $a = $inNumber->getAbsValue();
        }

        if ($a === '0')
            return $res;

        if ($a === '1')
            return new BigNumber($set_negative ? '-'.$inNumber->getValue() : $inNumber->getValue());

        $res = '0';

        for ($i = 0; $i < strlen($a); $i++)
        {
            $num = (int)$a[strlen($a)-$i-1];
            $t_res = $this->mulDigit($b, $num);

            $t_res .= str_repeat('0', $i);
            $res = $this->addSameSign($res, $t_res);
        }

        return ($set_negative) ? new BigNumber('-'.$res) : new BigNumber($res);
    }

    public function div(BigNumber $inNumber) : BigNumber
    {
        $res = new BigNumber('0');

        $set_negative = $this->getNegative() ^ $inNumber->getNegative();

        $a = $this->getAbsValue();
        $b = $inNumber->getAbsValue();

        if ($b === '0')
        {
            throw new DivisionByZeroError("Alarm!!! Division by 0");
        }

        if ($b === '1')
        {
            return ($set_negative) ? new BigNumber('-'.$a) : new BigNumber($a);
        }

        if ($this->cmpAbs($a, $b) < 0)
        {
            return ($set_negative) ? new BigNumber('-0', $a) : new BigNumber('0', $a);
        }

        while ($this->cmpAbs($a, $b) >= 0)
        {
            $res = $res->add(new BigNumber('1'));
            $t_num = new BigNumber($a);   
            $a = (string)$t_num->sub(new BigNumber($b));  
        }

        return ($set_negative) ? new BigNumber((string)$res, $a) : new BigNumber((string)$res, $a);
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

    public function getNegative() : bool
    {
        return $this->negative;
    }

    public function getRemainder() : string
    {
        return $this->remainder;
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

?>