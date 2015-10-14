<?php

    namespace AloFramework\Log;

    use AloFramework\Common\Alo;
    use Psr\Log\InvalidArgumentException;
    use Psr\Log\LoggerInterface;
    use Psr\Log\LoggerTrait;
    use Psr\Log\LogLevel;
    use AloFramework\Config\Configurable;
    use AloFramework\Config\ConfigurableTrait;

    /**
     * AloFramework logger.
     *
     * @author Art <a.molcanovas@gmail.com>
     * @property Config $config Configuration object
     * @since  2.0 Is now configured via the Configurable interface<br/>
     *         1.4 buildMessage() added<br/>
     *         1.3 getBacktrace(), self::$ignoredFiles added<br/>
     *         1.2 time() added, the separator is now a constant<br/>
     *         1.1 getLastMessage() added
     */
    class Log extends LogLevel implements LoggerInterface, Configurable {

        use LoggerTrait;
        use ConfigurableTrait;

        /**
         * Log element separator.
         *
         * @var string
         * @since 1.2 is a constant, not private static
         */
        const SEPARATOR = '|';

        /**
         * The previously logged message text
         *
         * @var string
         */
        private $lastMessage;

        /**
         * File name fragments to ignore when generating the backtrace
         *
         * @var array
         * @since 1.3
         */
        protected static $ignoredFiles = ['Log.php'];

        /**
         * Log levels and their priorities.
         *
         * @var array
         */
        private static $priority = [self::DEBUG     => 1,
                                    self::INFO      => 2,
                                    self::NOTICE    => 3,
                                    self::WARNING   => 4,
                                    self::ERROR     => 5,
                                    self::CRITICAL  => 6,
                                    self::ALERT     => 7,
                                    self::EMERGENCY => 8,];

        /**
         * Constructor.
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param Config $cfg The configuration object
         *
         * @throws InvalidArgumentException When $log isn't scalar or $logLevel is invalid
         */
        public function __construct(Config $cfg = null) {
            $this->config = Alo::ifnull($cfg, new Config());
        }

        /**
         * Returns the log level priority
         * @author Art <a.molcanovas@gmail.com>
         * @return array
         */
        static function getPriority() {
            return self::$priority;
        }

        /**
         * Returns the last logged message
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return string
         * @since  1.1
         */
        function getLastMessage() {
            return $this->lastMessage;
        }

        /**
         * Returns a string representation of the class.
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @return string
         */
        public function __toString() {
            return 'Label: ' . $this->config->logLabel . ', ' . PHP_EOL . 'Level: ' . $this->config->logLevel . ', ' .
                   PHP_EOL . 'Save path: ' . $this->config->savePath;
        }

        /**
         * Logs with an arbitrary level.
         *
         * @param string $level   The message's log level
         * @param string $message The message
         * @param array  $context The message context/placeholder asociative array
         *
         * @throws InvalidArgumentException When the log level is invalid
         *
         * @return bool Whether the message has been written
         */
        public function log($level, $message, array $context = []) {
            if (!array_key_exists($level, self::$priority)) {
                throw new InvalidArgumentException('Invalid log level supplied: ' .
                                                   (is_scalar($level) ? $level : serialize($level) . ' (serialised)'));
            } elseif (self::$priority[$level] < self::$priority[$this->config->logLevel]) {
                return false;
            } else {
                return $this->doWrite($level, $this->replaceContext($message, $context));
            }
        }

        /**
         * Returns the debug backtrace
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return array
         * @since  1.3
         */
        protected function getBacktrace() {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            if (empty($trace)) {
                return [];
            } else {
                foreach ($trace as $k => $v) {
                    foreach (self::$ignoredFiles as $i) {
                        if (stripos(Alo::ifnull($v['file'], ''), $i) !== false) {
                            unset($trace[$k]);
                            break;
                        }
                    }
                }
            }

            return array_values($trace);
        }

        /**
         * Performs the actual log operation.
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $level   Log level
         * @param string $message Raw message
         *
         * @return bool
         */
        private function doWrite($level, $message) {
            $this->lastMessage = $message;
            $fp                = fopen($this->config->savePath, 'ab');

            if ($fp) {
                $message = $this->buildMessage($level, $message);
                $ok      = [flock($fp, LOCK_EX),
                            fwrite($fp, $message),
                            flock($fp, LOCK_UN),
                            fclose($fp)];

                return !in_array(false, $ok, true);
            } else {
                trigger_error('Failed to open log file ' . $this->config->savePath, E_USER_WARNING);
            }

            return false;
        }

        /**
         * Builds the message as it will appear in the log file
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $level Message level
         * @param string $text  User-supplied message text
         *
         * @return string The built message
         * @since  1.4
         */
        protected function buildMessage($level, $text) {
            $trace = Alo::ifnull($this->getBacktrace()[1], [], true);

            $file = isset($trace['file']) ? implode(DIRECTORY_SEPARATOR,
                                                    array_slice(explode(DIRECTORY_SEPARATOR,
                                                                        $trace['file']),
                                                                -2)) : '<<unknown file>>';
            $line = isset($trace['line']) ? implode(DIRECTORY_SEPARATOR,
                                                    array_slice(explode(DIRECTORY_SEPARATOR,
                                                                        $trace['line']),
                                                                -2)) : '<<unknown line>>';

            return $level . ' ' . self::SEPARATOR . ' ' . $this->time() . ' ' . self::SEPARATOR . ' ' .
                   $this->config->logLabel . ' ' . self::SEPARATOR . ' ' .
                   str_replace(self::SEPARATOR, '\\' . self::SEPARATOR, $text) . ' ' . self::SEPARATOR . ' ' . $file .
                   ' ' . self::SEPARATOR . ' ' . $line . PHP_EOL;
        }

        /**
         * Returns the current time for log messages. Added as a method so you can overwrite it to the format you want
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return string
         * @since  1.2
         */
        protected function time() {
            return date('Y-m-d H:i:s');
        }

        /**
         * Replaces the placeholders in the message.
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
