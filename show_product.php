<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="global.css">
</head>
<body>
<?php

function line_break()
{
    echo '<br>';
}

function double_line_break()
{
    line_break();    
    line_break();    
}

function _echo($var)
{
    echo $var;
    line_break();
}

function nice_echo($var)
{
    echo $var;
    double_line_break();
}

$host = 'localhost'; 
$db = 'test';
$u_name = 'akim';
$u_pass = 'fGUA89o6q1ANoh5n';

try {
    //-- create connection
    $conn = new PDO("mysql:host=$host; dbname=$db; charset=utf8", $u_name, $u_pass);
    //-- create error hook
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    nice_echo('We have nice connection with database '.$db);

    $sql = $conn->query("select * from products");
    echo '<table>';
    echo    '<thaed>
                <tr>
                    <th>ID</th>
                    <th>Наименование</th>
                </tr>
            </thead><tbody>';
    while($data = $sql->fetch(PDO::FETCH_ASSOC)) 
    {
        echo '<tr>
            <td>'.htmlspecialchars($data['prod_id']).'</td>
            <td>'.htmlspecialchars($data['name']).'</td>
            <td>'.htmlspecialchars($data['price']).'</td>
        </tr>';
    }        
    echo '</tbody></table>';
}
catch (PDOException $e)
{
    echo 'Connection error: '.$e->getMessage();
}
?>
</body>
</html>

