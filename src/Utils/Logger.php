<?php
namespace Kipay\Utils;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;

/**
 * Logger Class for Kipay Payment Gateway
 * 
 * This class handles logging using Monolog.
 */
class Logger
{
    /**
     * @var \Monolog\Logger Monolog instance
     */
    protected $logger;
    
    /**
     * @var string Log channel name
     */
    protected $channel;
    
    /**
     * Logger constructor
     * 
     * @param string $channel Log channel name
     */
    public function __construct(string $channel = 'app')
    {
        $this->channel = $channel;
        $this->initLogger();
    }
    
    /**
     * Initialize the logger
     * 
     * @return void
     */
    protected function initLogger(): void
    {
        // Create logger instance
        $this->logger = new MonologLogger($this->channel);
        
        // Define log format
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        
        // Get log path
        $logPath = $this->getLogPath();
        
        // Check if debug mode is enabled
        $isDebug = isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true';
        
        // Add handlers based on environment
        if ($isDebug) {
            // In debug mode, log to daily files and output to stderr
            
            // Create rotating file handler (daily files)
            $fileHandler = new RotatingFileHandler(
                $logPath . '/' . $this->channel . '.log',
                30, // Keep 30 days of logs
                $isDebug ? MonologLogger::DEBUG : MonologLogger::INFO
            );
            $fileHandler->setFormatter($formatter);
            $this->logger->pushHandler($fileHandler);
            
            // Create stream handler for stderr
            $streamHandler = new StreamHandler('php://stderr', MonologLogger::DEBUG);
            $streamHandler->setFormatter($formatter);
            $this->logger->pushHandler($streamHandler);
        } else {
            // In production, only log to daily files
            $fileHandler = new RotatingFileHandler(
                $logPath . '/' . $this->channel . '.log',
                7, // Keep 7 days of logs in production
                MonologLogger::INFO
            );
            $fileHandler->setFormatter($formatter);
            $this->logger->pushHandler($fileHandler);
        }
        
        // Add processors
        $this->logger->pushProcessor(new WebProcessor());
        $this->logger->pushProcessor(new IntrospectionProcessor(MonologLogger::ERROR));
    }
    
    /**
     * Get the log path
     * 
     * @return string Log path
     */
    protected function getLogPath(): string
    {
        $logPath = defined('KIPAY_PATH') ? KIPAY_PATH . '/logs' : __DIR__ . '/../../logs';
        
        // Create directory if it doesn't exist
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        return $logPath;
    }
    
    /**
     * Log a debug message
     * 
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }
    
    /**
     * Log an info message
     * 
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }
    
    /**
     * Log a notice message
     * 
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }
    
    /**
     * Log an error message
     * 
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
    
    /**
     * Log a critical message
     * 
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }
    
    /**
     * Log an alert message
     * 
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }
    
    /**
     * Log an emergency message
     * 
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }
    
    /**
     * Log an exception
     * 
     * @param \Throwable $exception Exception to log
     * @param string $message Optional message
     * @return void
     */
    public function exception(\Throwable $exception, string $message = ''): void
    {
        $context = [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'previous' => $exception->getPrevious() ? get_class($exception->getPrevious()) : null
        ];
        
        $message = $message ?: $exception->getMessage();
        
        $this->error($message, $context);
    }
}