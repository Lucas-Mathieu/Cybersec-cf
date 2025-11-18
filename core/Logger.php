<?php

class Logger
{
    private static $logFile;

    private static function ensureInitialized(): void
    {
        if (self::$logFile !== null) {
            return;
        }

        $baseDir = dirname(__DIR__) . '/storage/logs';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        self::$logFile = $baseDir . '/app.log';
    }

    public static function log(string $action, ?string $user, string $result, array $context = []): void
    {
        self::ensureInitialized();
        $timestamp = date('Y-m-d H:i:s');
        $sanitizedContext = array_map(function ($value) {
            if (is_scalar($value) || $value === null) {
                return $value;
            }
            return json_encode($value);
        }, $context);
        $entry = sprintf(
            "[%s] action=%s user=%s result=%s context=%s%s",
            $timestamp,
            $action,
            $user ?? 'anonymous',
            $result,
            json_encode($sanitizedContext, JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );
        file_put_contents(self::$logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
?>
