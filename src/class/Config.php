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

    use AloFramework\Config\AbstractConfig;
    use Psr\Log\LogLevel;

    /**
     * Log configuration
     *
     * @author  Art <a.molcanovas@gmail.com>
     * @since   3.0 Contains the lock setting
     *          2.0
     * @property string          $logLevel The log level
     * @property string|resource $savePath The save path
     * @property string          $logLabel The log label
     * @property bool            $lock     Whether to perform file locking
     */
    class Config extends AbstractConfig {

        /**
         * The default log label to use [SYSTEM]
         *
         * @var string
         */
        const LOG_LABEL = 'logLabel';
        /**
         * The default path where to save the log file. [src/logs/YYYY-mm-dd.log]. Can be given a resource or a file
         * path.
         *
         * @var string
         */
        const SAVE_PATH = 'savePath';
        /**
         * The default logging level
         *
         * @var string
         */
        const LOG_LEVEL = 'logLevel';

        /**
         * Whether to perform file locking [true]
         *
         * @var string
         * @since 3.0
         */
        const LOCK_FILE = 'lock';
        /**
         * Default settings
         *
         * @var array
         */
        private static $default;

        /**
         * Constructor
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param array $cfg Your custom configuration parameters
         */
        function __construct(array $cfg = []) {
            parent::__construct(self::$default);

            foreach ($cfg as $k => $v) {
                //Perform all the checks while adding
                $this->set($k, $v);
            }
        }

        /**
         * Sets a configuration key
         *
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
                    $e = InvalidArgumentException::E_LABEL;
                    break;
                case self::LOG_LEVEL:
                    $ok = $this->checkLogLevel($v);
                    $e = InvalidArgumentException::E_LEVEL;
                    break;
                case self::SAVE_PATH:
                    $ok = $this->checkSavePath($v);
                    $e = InvalidArgumentException::E_PATH;
                    break;
                default:
                    $e = 0;
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
         *
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
         *
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
         *
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
            } elseif (is_resource($path)) {
                return true;
            }

            return false;
        }
    }

    $defaults = new \ReflectionProperty(Config::class, 'default');
    $defaults->setAccessible(true);
    $defaults->setValue([Config::LOG_LABEL => 'SYSTEM',
                         Config::LOG_LEVEL => LogLevel::DEBUG,
                         Config::LOCK_FILE => true,
                         Config::SAVE_PATH => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
                                              'logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log']);
