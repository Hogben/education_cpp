<?php

class NetworkStorage
{
    private static $fileSession = 'network_games.json';
    
    public static function saveGames($games)
    {
        file_put_contents(self::$fileSession, json_encode($games, JSON_PRETTY_PRINT));
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
        $hasChanges = false;
        foreach ($games as $gameID => $game) 
        {
            if ($currentTime - $game['create_time'] > 900) // 15 мин
            {
                unset($games[$gameID]);
                $hasChanges = true;
            }
        }
        
        if ($hasChanges) 
        {
            self::saveGames($games);
        }
        
        return $games;
    }
}
?>