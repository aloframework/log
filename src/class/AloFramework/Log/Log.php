<?php

    namespace AloFramework\Log;

    use Psr\Log\LogLevel;
    use Psr\Log\LoggerTrait;
    use Psr\Log\InvalidArgumentException;

    /**
     * AloFramework logger
     * @author Art <a.molcanovas@gmail.com>
     */
    class Log extends LogLevel {

        use LoggerTrait;

        /**
         * Log identifier
         * @var string
         */
        protected $log;

        /**
         * Where the logs are stored
         * @var string
         */
        protected $savePath;

        /**
         * Log level set
         * @var string
         */
        protected $level;

        /**
         * Log element separator
         * @author Art <a.molcanovas@gmail.com>
         */
        const SEPARATOR = '|';

        /**
         * Log levels and their priorities
         * @var array
         */
        private static $priority = [self::DEBUG     => 0,
                                    self::INFO      => 1,
                                    self::NOTICE    => 2,
                                    self::WARNING   => 3,
                                    self::ERROR     => 4,
                                    self::CRITICAL  => 5,
                                    self::ALERT     => 6,
                                    self::EMERGENCY => 7];

        /**
         * Constructor
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $log      Log label, e.g. if you specify 'System' log entries will be prepended with SYSTEM
         * @param string $logLevel Minimum log level to log. See class constants.
         * @param string $savePath The file to save the logs in. If omitted, the logs directory in this package will
         *                         be used with the filenames being today's date in YYYY-mm-dd format.
         *
         * @throws LogException             When a save path is specified, but the directory does not exist
         * @throws InvalidArgumentException When $log isn't scalar or $logLevel is invalid
         */
        function __construct($log = 'SYSTEM', $logLevel = self::DEBUG, $savePath = null) {
            if (!$savePath) {
                $savePath =
                    __DIR__ .
                    DIRECTORY_SEPARATOR .
                    '..' .
                    DIRECTORY_SEPARATOR .
                    '..' .
                    DIRECTORY_SEPARATOR .
                    '..' .
                    DIRECTORY_SEPARATOR .
                    'logs' .
                    DIRECTORY_SEPARATOR .
                    date('Y-m-d') .
                    '.log';
            }

            $this->logLabel($log)->level($logLevel)->savePath($savePath);
        }

        /**
         * Gets or sets the log file path
         * @author Art <a.molcanovas@gmail.com>
         *
         * @throws LogException             When the directory doesn't exist
         * @throws InvalidArgumentException When the path isn't a valid string
         *
         * @param string|null $path Omit if using as a getter; The path to the log file otherwise
         *
         * @return Log|string
         */
        function savePath($path = null) {
            if ($path === null) {
                return $this->savePath;
            } elseif (!is_string($path)) {
                throw new InvalidArgumentException('The path must be a string');
            } else {
                $dir = dirname($path);

                if (!file_exists($dir)) {
                    throw new LogException('The directory does not exist: ' . $dir);
                } else {
                    $this->savePath = $path;
                }
            }

            return $this;
        }

        /**
         * Gets or sets the log label
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string|null $set Omit if using as a getter, the new log label if using as a setter.
         *
         * @throws InvalidArgumentException If attempting to set an invalid log level
         *
         * @return Log|string
         */
        function logLabel($set = null) {
            if ($set === null) {
                return $this->log;
            } elseif (!is_scalar($set)) {
                throw new InvalidArgumentException('The log label must be scalar. You tried to set (serialised): ' .
                                                   serialize($set));
            } else {
                $this->log = strtoupper($set);
            }

            return $this;
        }

        /**
         * Gets or sets the log level
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string|null $set Omit if using as a getter, the log level if using as a setter - see class constants
         *                         or refer to the PSR-3 standards.
         *
         * @throws InvalidArgumentException If attempting to set an invalid log level
         *
         * @return Log|string
         */
        function level($set = null) {
            if ($set === null) {
                return $this->level;
            } elseif (!array_key_exists($set, self::$priority)) {
                throw new InvalidArgumentException('An invalid log level was passed: ' .
                                                   (is_scalar($set) ? $set : serialize($set) . ' (serialised)'));
            } else {
                $this->level = $set;
            }

            return $this;
        }

        /**
         * Logs with an arbitrary level.
         *
         * @param mixed  $level
         * @param string $message
         * @param array  $context
         *
         * @return bool Whether the message has been written
         */
        function log($level, $message, array $context = []) {
            if (!array_key_exists($level, self::$priority)) {
                throw new InvalidArgumentException('Invalid log level supplied: ' .
                                                   (is_scalar($level) ? $level : serialize($level) . ' (serialised)'));
            } elseif (self::$priority[$level] < self::$priority[$this->level]) {
                return false;
            } else {
                return $this->doWrite($level, $this->replaceContext($message, $context));
            }
        }

        private function doWrite($level, $message) {
            $fp = fopen($this->savePath, 'ab');

            if ($fp) {
                $trace = debug_backtrace();
                $trace = isset($trace[1]) ? $trace[1] : [];

                $file    = isset($trace['file']) ? implode(DIRECTORY_SEPARATOR,
                                                           array_slice(explode(DIRECTORY_SEPARATOR,
                                                                               $trace['file']),
                                                                       -2)) : '<<unknown file>>';
                $line    = isset($trace['line']) ? implode(DIRECTORY_SEPARATOR,
                                                           array_slice(explode(DIRECTORY_SEPARATOR,
                                                                               $trace['line']),
                                                                       -2)) : '<<unknown line>>';
                $message =
                    $level .
                    ' ' .
                    self::SEPARATOR .
                    ' ' .
                    date('Y-m-d H:i:s') .
                    ' ' .
                    self::SEPARATOR .
                    ' ' .
                    $this->log .
                    ' ' .
                    self::SEPARATOR .
                    ' ' .
                    str_replace(self::SEPARATOR, '\\' . self::SEPARATOR, $message) .
                    ' ' .
                    self::SEPARATOR .
                    ' ' .
                    $file .
                    ' ' .
                    self::SEPARATOR .
                    ' ' .
                    $line .
                    PHP_EOL;

                $ok = [flock($fp, LOCK_EX),
                       fwrite($fp, $message),
                       flock($fp, LOCK_UN),
                       fclose($fp)];

                return !in_array(false, $ok, true);
            }

            return false;
        }

        /**
         * Replaces the placeholders in the message
         *
         * @param string $message The raw message
         * @param array  $context The context/placeholder assoc array
         *
         * @return string
         */
        protected static function replaceContext($message, array $context = []) {
            if (empty($context)) {
                return $message;
            } else {
                $search = $replace = [];

                foreach ($context as $k => $v) {
                    $search[]  = '{' . $k . '}';
                    $replace[] = $v;
                }

                return str_ireplace($search, $replace, $message);
            }
        }
    }
