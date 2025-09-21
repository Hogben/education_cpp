<?php

require_once 'SeaBattle.php';

session_start();
if(isset($_SESSION['game']))
{
    $_SESSION['game'] = new SeaBattle();
}
$game = $_SESSION['game'];
$message = '';
if(isset($_POST['action']))
{
    switch($_POST['action'])
    {
        case 'place_ship':
            break;
        case 'start':
            if ($game->Start())
                $message = 'Начало игры, удачи!';
            else
                $message = 'Игрок не расставил весь флот!';
            break;
        case 'shoot':
            break;
        case 'restart':
            session_destroy();
            break;
    }
}
?>