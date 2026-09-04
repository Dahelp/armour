<?php

namespace ishop;

class ErrorHandler{

    public function __construct(){
        if(DEBUG){
            error_reporting(-1);
        }else{
            error_reporting(0);
        }
        set_exception_handler([$this, 'exceptionHandler']);
    }

    public function exceptionHandler(\Throwable $e): void{
        $this->logErrors($e->getMessage(), $e->getFile(), $e->getLine());
        $statusCode = self::normaliseStatusCode((int)$e->getCode());
        $this->displayError('Исключение', $e->getMessage(), $e->getFile(), $e->getLine(), $statusCode);
    }

    protected function logErrors($message = '', $file = '', $line = ''){
        error_log("[" . date('Y-m-d H:i:s') . "] Текст ошибки: {$message} | Файл: {$file} | Строка: {$line}\n=================\n", 3, ROOT . '/tmp/errors.log');
    }

    protected function displayError($errno, $errstr, $errfile, $errline, $response = 500){
        $response = self::normaliseStatusCode((int)$response);
        self::sendStatusCode($response);
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-store, max-age=0');
        }
        if($response === 404 && !DEBUG){
            require WWW . '/errors/404.php';
            die;
        }
        if(DEBUG){
            require WWW . '/errors/dev.php';
        }else{
            require WWW . '/errors/prod.php';
        }
        die;
    }

    public static function normaliseStatusCode(int $statusCode): int
    {
        return $statusCode >= 400 && $statusCode <= 599 ? $statusCode : 500;
    }

    public static function sendStatusCode(int $statusCode): void
    {
        $statusCode = self::normaliseStatusCode($statusCode);
        if ($statusCode === 419) {
            header('HTTP/1.1 419 Page Expired', true, 419);
            return;
        }

        http_response_code($statusCode);
    }

}
