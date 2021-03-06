<?php
    /**
 *    Copyright (c) Arturas Molcanovas <a.molcanovas@gmail.com> 2016.
 *    https://github.com/aloframework/log
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

    namespace AloFramework\Log;

    use AloFramework\Common\Alo;
    use AloFramework\Config\Configurable;
    use AloFramework\Config\ConfigurableTrait;
    use Psr\Log\LoggerInterface;
    use Psr\Log\LoggerTrait;
    use Psr\Log\LogLevel;

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
                                    self::EMERGENCY => 8];
        /**
         * The previously logged message text
         *
         * @var string
         */
        private $lastMessage;
        /**
         * The last message including any formatting/extra params
         *
         * @var string
         */
        private $lastMessageFull;
        /**
         * fopen() resouce
         *
         * @var resource
         */
        private $fp;

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
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return array
         */
        public static function getPriority() {
            return self::$priority;
        }

        /**
         * Cleanup operations
         *
         * @author Art <a.molcanovas@gmail.com>
         */
        public function __destruct() {
            $this->fclose();
        }

        /**
         * Closes the handler
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return self
         */
        private function fclose() {
            if ($this->fp && is_resource($this->fp) && !is_resource($this->config->savePath)) {
                fclose($this->fp);
            }

            return $this;
        }

        /**
         * Returns the last logged message
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param bool $full If set to true, will return the message as it appears in the log
         *
         * @return string
         * @since  2.0 $full added<br/>
         *         1.1
         */
        public function getLastMessage($full = false) {
            return $full ? $this->lastMessageFull : $this->lastMessage;
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
                                                   (is_scalar($level) ? $level : serialize($level) . ' (serialised)'),
                                                   InvalidArgumentException::E_LEVEL);
            } elseif (self::$priority[$level] < self::$priority[$this->config->logLevel]) {
                return false;
            } else {
                return $this->doWrite($level, $this->replaceContext($message, $context));
            }
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
            $this->fopen();

            if ($this->fp && is_resource($this->fp)) {
                $message = $this->buildMessage($level, $message);
                $this->lastMessageFull = $message;
                $ok = $this->config->lock ? [flock($this->fp, LOCK_EX),
                                             fwrite($this->fp, $message),
                                             flock($this->fp, LOCK_UN)] : [fwrite($this->fp, $message)];

                return !in_array(false, $ok, true);
            }

            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        /**
         * Opens the file handler
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param bool $force Whether to force it
         *
         * @return self
         */
        private function fopen($force = false) {
            if ($force || !$this->fp) {
                $this->fclose();
                $this->fp = is_resource($this->config->savePath) ? $this->config->savePath
                    : fopen($this->config->savePath, 'ab');
            }

            return $this;
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
         * Returns the debug backtrace
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return array
         * @since  1.3
         */
        protected function getBacktrace() {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            if (empty($trace)) {
                // @codeCoverageIgnoreStart
                return [];
                // @codeCoverageIgnoreEnd
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
                    $search[] = '{' . $k . '}';
                    $replace[] = $v;
                }

                return str_ireplace($search, $replace, $message);
            }
        }
    }
