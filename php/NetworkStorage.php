<?php

class NetworkStorage
{
    private static $fileSession = 'network_games.json';
    private static $maxAgePerSecond = 1800; 
    
    public static function saveGames($games)
    {
        $currentTime = time();
        foreach ($games as $gameID => $game) 
        {
            if ($currentTime - ($game['create_time'] ?? 0) > self::$maxAgePerSecond)
            {
                unset($games[$gameID]);
            }
        }
       
        file_put_contents(self::$fileSession, json_encode($games, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    public static function loadGames()
    {
        if (!file_exists(self::$fileSession)) 
        {
            return [];
        }
        
        $data = file_get_contents(self::$fileSession);
        $games = json_decode($data, true) ?: [];
        
        $currentTime = time();
        $changed = false;
        foreach ($games as $gameID => $game) 
        {
            if ($currentTime - ($game['create_time'] ?? 0) > self::$maxAgePerSecond)
            {
                unset($games[$gameID]);
                $changed = true;
            }
        }
        
        if ($changed) {
            self::saveGames($games);
        }
        
        return $games;
    }
    
    public static function cleanGame($gameID)
    {
        $games = self::loadGames();
        if (isset($games[$gameID])) 
        {
            unset($games[$gameID]);
            self::saveGames($games);
        }
    }
}
?>