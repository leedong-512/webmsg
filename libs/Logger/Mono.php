<?php
/**
 * Created by PhpStorm.
 * User: rex
 * Email: caoliang@simpleedu.com.cn
 * Date: 2018/9/25
 * Time: 4:01 PM
 */

namespace Libs\Logger;


use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Mono
{

    private $_logger;

    /**
     * Mono constructor.
     * @param $log_file
     * @param $channel
     * @throws \Exception
     */
    public function __construct($log_file, $channel)
    {
        $stream = new StreamHandler($log_file, Logger::DEBUG);
        $firephp = new FirePHPHandler();

        $logger = new Logger($channel);
        $logger->pushHandler($stream);
        $logger->pushHandler($firephp);

        $this->_logger = $logger;
    }


    public function __call($name, $arguments)
    {
        try {
            $method = '_'.$name;
            if (method_exists($this, $method)) {
                return call_user_func_array([$this, $method], $arguments);
            }
        } catch (\Exception $e) {
            $err_msg = $e->getMessage();
            $code = $e->getCode();
            $line = $e->getLine();
            $file = $e->getFile();
            $trace_str = $e->getTraceAsString();

            $message = $code . ':' . $err_msg . PHP_EOL .
                $file . ': line ' . $line . PHP_EOL . $trace_str ;

            $this->_err($message);

            echo $e->getMessage();
        }
    }


    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _debug($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->debug($message, $context);
        }
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _info($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->info($message, $context);
        }
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _notice($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->notice($message, $context);
        }
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _warn($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->warn($message, $context);
        }
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _warning($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->warning($message, $context);
        }
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _err($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->err($message, $context);
        }
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _error($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->error($message, $context);
        }
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _crit($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->crit($message, $context);
        }
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _critical($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->critical($message, $context);
        }
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _alert($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->alert($message, $context);
        }
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _emerg($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->emerg($message, $context);
        }
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    private function _emergency($message, array $context = array())
    {
        if (ENV === 'debug') {
            return $this->_logger->emergency($message, $context);
        }
    }
}