<?php
class NetworkStorage
{
    private static $fileSession = 'network_games.json';
    private static $maxAgePerSecond = 1800; // 30 минут
    
    public static function saveGames($games)
    {
        // Очистка старых игр
        $currentTime = time();
        foreach ($games as $gameID => $game) 
        {
            if ($currentTime - ($game['create_time'] ?? 0) > self::$maxAgePerSecond)
            {
                unset($games[$gameID]);
            }
        }
        
        // Сохраняем с блокировкой файла
        $json = json_encode($games, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents(self::$fileSession, $json, LOCK_EX);
    }
    
    public static function loadGames()
    {
        if (!file_exists(self::$fileSession)) 
        {
            return [];
        }
        
        // Читаем с блокировкой
        $data = file_get_contents(self::$fileSession);
        if ($data === false) {
            return [];
        }
        
        $games = json_decode($data, true) ?: [];
        
        // Очистка старых игр при загрузке
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