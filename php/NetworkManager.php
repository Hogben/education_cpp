<?php

require_once 'NetworkStorage.php';

class NetworkManager
{
    private static function getGames()
    {
        return NetworkStorage::loadGames();
    }
    
    private static function saveGames($games)
    {
        NetworkStorage::saveGames($games);
    }
    
    public static function createGame($gameID, $parentID)
    {
        $games = self::getGames();
        
        $games[$gameID] = [
            'player1' => $parentID,
            'player2' => null,
            'state' => 'waiting',
            'create_time' => time()
        ];

        self::saveGames($games);
        return $gameID;
    }

    public static function getGame($gameID)
    {
        $games = self::getGames();
        return $games[$gameID] ?? null;
    }

    public static function getGameByPlayer($playerID)
    {
        $games = self::getGames();
        foreach ($games as $gameID => $game) 
        {
            if ($game['player1'] === $playerID || $game['player2'] === $playerID) 
            {
                return $game;
            }
        }
        return null;
    }

    public static function joinGame($gameID, $playerID)
    {
        $games = self::getGames();
        
        if (isset($games[$gameID]) && $games[$gameID]['player2'] === null) 
        {
            $games[$gameID]['player2'] = $playerID;
            $games[$gameID]['state'] = 'start_game';
            self::saveGames($games);
            return true;
        }
        return false;
    }

    public static function getGameList()
    {
        $games = self::getGames();
        $list = [];
        
        foreach ($games as $gameID => $game) 
        {
            if ($game['player2'] === null) 
            {
                $list[$gameID] = $game;
            }
        }
        return $list;
    }

    public static function removeGame($gameID)
    {
        $games = self::getGames();
        if (isset($games[$gameID])) 
        {
            unset($games[$gameID]);
            self::saveGames($games);
        }
    }
    
    public static function updateGame($gameID, $gameData)
    {
        $games = self::getGames();
        if (isset($games[$gameID])) 
        {
            $games[$gameID] = array_merge($games[$gameID], $gameData);
            self::saveGames($games);
        }
    }
}
?>