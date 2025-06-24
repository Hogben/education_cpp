<?php

define ('def_cellspacing', '0');
define ('def_cellpadding', '5');
define ('def_table_width', '100%');

$host = 'localhost'; 
$db = 'test';
$u_name = 'akim';
$u_pass = 'fGUA89o6q1ANoh5n';

$_cellspacing = isset($_GET['c_spacing']) ?  $_GET['c_spacing'] : def_cellspacing;
$_cellpadding = isset($_GET['c_padding']) ?  $_GET['c_padding'] : def_cellpadding;
$_table_width = isset($_GET['t_width']) ?  $_GET['t_width'] : def_table_width;

try {
    //-- create connection
    $conn = new PDO("mysql:host=$host; dbname=$db; charset=utf8", $u_name, $u_pass);
    //-- create error hook
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
/*/
    $sql = $conn->query("select * from categories");
    echo '<table border="1" cellspacing="'.$_cellspacing.'" cellpadding="'.$_cellpadding.'" style="width:'.$_table_width.'">';
    echo    '<thaed>
                <tr>
                    <th>ID</th>
                    <th>Наименование</th>
                </tr>
            </thead><tbody>';
    while($data = $sql->fetch(PDO::FETCH_ASSOC)) 
    {
        echo '<tr>
            <td>'.htmlspecialchars($data['cat_id']).'</td>
            <td>'.htmlspecialchars($data['cat_name']).'</td>
        </tr>';
    }        
    echo '</tbody></table>';
/*/
    $text_sql = isset($_GET['sql']) ?  $_GET['sql'] : "select * from categories";

    if (mb_convert_case(strtok($text_sql, " "), MB_CASE_UPPER, "UTF-8") === 'SELECT' && strpos($text_sql, ';') === false)
        echo 'Ok';
    else
        echo 'No OK';

    /*/
    $sql = $conn->query($text_sql);
    echo '<table border="1" cellspacing="'.$_cellspacing.'" cellpadding="'.$_cellpadding.'" style="width:'.$_table_width.'">';
    echo    '<thaed>
                <tr>
                    <th>ID</th>
                    <th>Наименование</th>
                </tr>
            </thead><tbody>';
    while($data = $sql->fetch(PDO::FETCH_ASSOC)) 
    {
        echo '<tr>
            <td>'.htmlspecialchars($data['cat_id']).'</td>
            <td>'.htmlspecialchars($data['cat_name']).'</td>
        </tr>';
    }        
    echo '</tbody></table>';
    /*/
}
catch (PDOException $e)
{
    echo 'Connection error: '.$e->getMessage();
}


?>