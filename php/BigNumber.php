<?php

class BigNumber 
{
    private $value;
    public  $negative;

    public function __construct(string $val)
    {
        if (!preg_match('/^-?\d+$/', $val))
        {
            throw new InvalidArgumentException("No valid format: $val");
        }
        $this->value = $this->niceNumber($val);
        $negative = ($this->value[0] === '-') ? true : false;    
    }

    private function niceNumber (string $val) : string
    {
        if ($val == '0')    return '0';
        $tmp = ($val[0] === '-') ? substr($val,1) : $val;
        return ltrim($tmp, '0');
    }

    
    public function add(BigNumber $inNumber) : BigNumber
    {
        $val_1 = $this->value;
        $val_2 = $inNumber->getValue();

        //-------- проверки


    }

    private function addPositive(string $val1, string $val2) : string
    {
        
    }

    public function sub(BigNumber $inNumber) : BigNumber
    {

    }

    public function mul(BigNumber $inNumber) : BigNumber
    {
        
    }

    public function div(BigNumber $inNumber) : BigNumber
    {
        
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



echo new BigNumber("12222222231231231212312123")."<br>";
echo new BigNumber("-0000000000000")."<br>";
echo new BigNumber("00000000000000000000000000000000003")."<br>";

?>