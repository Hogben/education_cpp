<?php

class Logger 
{
    private $logDir;
    private $logName;
    private $fileSize;
    private $fileCount;
    private $sessionId;
    private $debug;

    public function __construct($logName, $logDir = 'log', $debug = false, $fileSize = 1048576, $fileCount = 5)
    {
        $this->logName = $logName;
        $this->logDir = $logDir;
        $this->fileSize = $fileSize;
        $this->fileCount = $fileCount;
        $this->sessionId = session_id() ? session_id() : 'no_session';
        $this->debug = $debug

        if (!is_dir($this->logDir))
        {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function log($msg, $type = "INFO")
    {
        $this->needRotate();

        $timestr = date('Y-m-d H:i:s');
        $logMessage = "[{$timestr}] [{$type}] {$msg}" . PHP_EOL;
        $logFile = $this->getLogName();
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public function info($msg)
    {
        $this->log($msg, 'INFO');
    }

    public function debug($msg)
    {
        if ($this->debug)   $this->log($msg, 'DEBUG');
    }

    public function error($msg)
    {
        $this->log($msg, 'ERROR');
    }

    public function warning($msg)
    {
        $this->log($msg, 'WARNING');
    }

    public function clearLog()
    {
        $logFile = $this->getLogName();        
        if (file_exists($logFile))  file_put_contents($logFile, '');
    }

    // ----- вернуть текстовый массив из файла лога с указанным количеством последних строк, если 0 - то вернуть весь лог
    public function getLogText($line = 0)
    {
        $logFile = $this->getLogName();
        if (!file_exists($logFile))
            return [];

        $text = file_get_contents($logFile);
        $context = explode(PHP_EOL, $text);
        $context = array_filter($context);

        if ($line > 0)
            $context = array_slice($context, -$line);

        return $context;
    }

    private function getLogName()
    {
        return $this->logDir . '/' . $this->logName . '_id_' . $this->sessionId . '.log';
    }

    private function getLogNameRotate($number)
    {
        return $this->logDir . '/' . $this->logName . '_id_' . $this->sessionId . '.log' . $number;
    }

    private function needRotate()
    {
        $logFile = $this->getLogName();
        
        if (!file_exists($logFile) || filesize($logFile) < $this->fileSize) return;

        for ($i = $this->fileCount - 1; $i >= 0; $i--)
        {
            $oldFile = $this->getLogNameRotate($i);
            $newFile = $this->getLogNameRotate($i + 1);

            if (file_exists($oldFile))
            {
                if ($i + 1 >= $this->fileCount)
                {
                    unlink($oldFile);
                }
                else
                {
                    rename($oldFile, $newFile);
                }
            }
        }

        rename ($logFile, $this->getLogNameRotate(0));
    }

}

?>