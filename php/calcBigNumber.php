<?php

require_once 'BigNumber.php';

$res = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $Number1 = $_POST["Number1"] ?? '';
    $Number2 = $_POST["Number2"] ?? '';
    $op = $_POST["operation"] ?? '';

    if ($Number1 === '' || $Number2 === '')
        $res = 'Введите чилсла';
    else
    {
        //----------- обрабочик операций
        try
        {
            $n1 = new BigNumber($Number1);
            $n2 = new BigNumber($Number2);

            switch($op)
            {
                case 'add':
                    $res = (string)$n1->add($n2);
                    break;
                case 'sub':
                    $res = (string)$n1->sub($n2);
                    break;
                case 'mul':
                    $res = (string)$n1->mul($n2);
                    break;
                case 'div':
                    $res = (string)$n1->div($n2);
                    break;
                default:
                    $res = "Неизвеcтная операция";                    
            }
        }
        catch(DivisionByZeroError $e)
        {
            $res = $e->getMessage();
            $isError = true;
        }        
        catch(Exception $e)
        {
            $res = $e->getMessage();
            $isError = true;
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet   " href="global.css"> <!-- в стили добавить для input -->
    <title>Калькулятор больших чисел</title>
</head>
<body>
    <h2>Калькулятор больших чисел</h2>
    <form method="post">
        <input type="text" name="Number1" placeholder="Первое число" value="<?=htmlspecialchars($_POST["Number1"] ?? '')  ?>"><br>
        <input type="text" name="Number2" placeholder="Второе число" value="<?=htmlspecialchars($_POST["Number2"] ?? '')  ?>"><br>

        <!-- добавить кнопки -->
        <button type="submit" name="operation" value="add">Сложение</button>     
        <button type="submit" name="operation" value="sub">Вычитание</button>     
        <button type="submit" name="operation" value="mul">Умножение</button>     
        <button type="submit" name="operation" value="div">Деление</button>     

    </form>    
    <?php if ($res !== ''): ?>
    <div class="<?= $isError ? 'error' : 'res' ?>">
        Результат: <?= htmlspecialchars($res) ?>
    </div>
    <?php endif; ?>
</body>
</html>
