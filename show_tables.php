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
    $conn = new PDO("mysql:host=$host; dbname=$db", $u_name, $u_pass);
    //-- create error hook
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    nice_echo('We have nice connection with database '.$db);

    $tbl = $conn->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    if(count($tbl))
    {
        _echo('Database have some tables:');
        foreach($tbl as $table)
        {
            _echo($table);
        }
    }
    else
    {
        nice_echo('Database is empty. :(');
    }

}
catch (PDOException $e)
{
    echo 'Connection error: '.$e->getMessage();
}


?>