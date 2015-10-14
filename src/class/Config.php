<?php

    namespace AloFramework\Log;

    use AloFramework\Config\AbstractConfig;
    use Psr\Log\LogLevel;

    /**
     * Log configuration
     * @author Art <a.molcanovas@gmail.com>
     * @since  2.0
     * @property string $logLevel The log level
     * @property string $savePath The save path
     * @property string $logLabel The log label
     */
    class Config extends AbstractConfig {

        /**
         * Default settings
         * @var array
         */
        private static $default;

        /**
         * The default log label to use [SYSTEM]
         * @var string
         */
        const LOG_LABEL = 'logLabel';

        /**
         * The default path where to save the log file. [src/logs/YYYY-mm-dd.log]
         * @var string
         */
        const SAVE_PATH = 'savePath';

        /**
         * The default logging level
         * @author Art <a.molcanovas@gmail.com>
         */
        const DEFAULT_LEVEL = 'logLevel';

        /**
         * Constructor
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param array $cfg Your custom configuration parameters
         */
        function __construct(array $cfg = []) {
            self::setDefaults();
            parent::__construct(self::$default, $cfg);
        }

        /**
         * Sets a configuration key
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $k The config key
         * @param mixed  $v The config value
         *
         * @return self
         * @throws InvalidArgumentException if a parameter is... invalid.
         */
        function set($k, $v) {
            switch ($k) {
                case self::LOG_LABEL:
                    $ok = $this->checkLogLabel($v);
                    $e  = InvalidArgumentException::E_LABEL;
                    break;
                case self::DEFAULT_LEVEL:
                    $ok = $this->checkLogLevel($v);
                    $e  = InvalidArgumentException::E_LEVEL;
                    break;
                case self::SAVE_PATH:
                    $ok = $this->checkSavePath($v);
                    $e  = InvalidArgumentException::E_PATH;
                    break;
                default:
                    $e  = 0;
                    $ok = true;
            }

            if ($ok) {
                parent::set($k, $v);
            } else {
                throw new InvalidArgumentException('The ' . $k . ' setting is invalid.', $e);
            }

            return $this;
        }

        /**
         * Checks if the log label is acceptable
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param mixed $label The log label
         *
         * @return bool
         */
        private static function checkLogLabel($label) {
            return is_scalar($label);
        }

        /**
         * Checks if the log level is acceptable
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param mixed $lvl The log level
         *
         * @return bool
         */
        private static function checkLogLevel($lvl) {
            return array_key_exists($lvl, Log::getPriority());
        }

        /**
         * Checks if the log save path is acceptable
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param mixed $path The log save path
         *
         * @return bool
         */
        private static function checkSavePath($path) {
            if (is_string($path)) {
                $dir = dirname($path);

                return file_exists($dir) && is_writeable($dir);
            }

            return false;
        }

        /**
         * Sets the default configuration array if required
         * @author Art <a.molcanovas@gmail.com>
         */
        private static function setDefaults() {
            if (!self::$default) {
                self::$default = [self::LOG_LABEL     => 'SYSTEM',
                                  self::DEFAULT_LEVEL => LogLevel::DEBUG,
                                  self::SAVE_PATH     => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
                                                         'logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log'];
            }
        }
    }
