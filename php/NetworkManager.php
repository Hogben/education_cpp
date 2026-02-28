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
            'create_time' => time(),
            'ready_player1' => false,
            'ready_player2' => false,
            'board_player1' => null,
            'board_player2' => null
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
        
        $sessionFiles = glob($gameID . '_*.json');
        foreach ($sessionFiles as $file) 
        {
            @unlink($file);
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

    public static function setCurrentPlayer($gameID, $player)
    {
        $games = self::getGames();
        if (isset($games[$gameID])) 
        {
            $games[$gameID]['current_player'] = $player;
            $games[$gameID]['update_time'] = time();
            self::saveGames($games);
            return true;
        }
        return false;
    }

    public static function getCurrentPlayer($gameID)
    {
        $games = self::getGames();
        if (isset($games[$gameID])) 
        {
            return $games[$gameID]['current_player'] ?? 'player1';
        }
        return null;
    }

    public static function getGameBoard($gameID, $playerNumber)
    {
        $games = self::getGames();
        if (isset($games[$gameID])) 
        {
            return $games[$gameID]['board_'.$playerNumber] ?? null;
        }
        return null;
    }

    public static function updateGameBoard($gameID, $playerNumber, $board)
    {
        $games = self::getGames();
        if (isset($games[$gameID])) 
        {
            $games[$gameID]['board_'.$playerNumber] = $board;
            $games[$gameID]['update_time'] = time();
            self::saveGames($games);
            return true;
        }
        return false;
    }

    public static function setPlayerReady($gameID, $playerNumber, $ready = true)
    {
        $games = self::getGames();
        if (isset($games[$gameID])) 
        {
            $games[$gameID]['ready_'.$playerNumber] = $ready;
            self::saveGames($games);
            return true;
        }
        return false;
    }

    public static function getPlayerReady($gameID, $playerNumber)
    {
        $games = self::getGames();
        if (isset($games[$gameID])) 
        {
            return $games[$gameID]['ready_'.$playerNumber] ?? false;
        }
        return false;
    }

	public static function getOpponentReady($gameID, $playerNumber)
	{
		$games = self::getGames();
		if (isset($games[$gameID])) 
		{
			$opponent = ($playerNumber === 'player1') ? 'player2' : 'player1';
			return $games[$gameID]['ready_' . $opponent] ?? false;
		}
		return false;
	}	

	public static function isBothReady($gameID)
	{
		$games = self::getGames();
		if (isset($games[$gameID])) {
			$player1 = self::getPlayerReady($gameID, 'player1');
			$player2 = self::getPlayerReady($gameID, 'player2');
			$bothReady = $player1 && $player2;

			if ($bothReady) {
				// Если оба готовы, но нет текущего игрока - устанавливаем player1
				if (!isset($games[$gameID]['current_player']) || $games[$gameID]['current_player'] === null) {
					$games[$gameID]['current_player'] = 'player1';
					self::saveGames($games);
				}
				return true;
			}
			return false;
		}
		return false;
	}
	
	public static function forceSync($gameID)
	{
		$games = self::getGames();
		if (isset($games[$gameID])) {
			$games[$gameID]['last_sync'] = time();
			self::saveGames($games);
			return true;
		}
		return false;
	}	
}
?>