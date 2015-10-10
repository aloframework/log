<?php

   namespace AloFramework\Log;

   use AloFramework\Common\Alo;
   use Psr\Log\InvalidArgumentException;
   use Psr\Log\LoggerInterface;
   use Psr\Log\LoggerTrait;
   use Psr\Log\LogLevel;

   require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.default.php';

   /**
    * AloFramework logger.
    *
    * @author Art <a.molcanovas@gmail.com>
    * @since  1.4 buildMessage() added<br/>
    *        1.3 getBacktrace(), self::$ignoredFiles added<br/>
    *        1.2 time() added, the separator is now a constant<br/>
    *        1.1 getLastMessage() added
    */
   class Log extends LogLevel implements LoggerInterface {

      use LoggerTrait;

      /**
       * Log identifier.
       *
       * @var string
       */
      protected $label;

      /**
       * Where the logs are stored.
       *
       * @var string
       */
      protected $savePath;

      /**
       * Log level set.
       *
       * @var string
       */
      protected $level;

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
       * @param string $logLevel Minimum log level to log. See class constants.
       * @param string $label    Log label, e.g. if you specify 'System' log entries will be prepended with SYSTEM
       * @param string $savePath The file to save the logs in. If omitted, the logs directory in this package will
       *                         be used with the filenames being today's date in YYYY-mm-dd format.
       *
       * @throws LogException             When a save path is specified, but the directory does not exist
       * @throws InvalidArgumentException When $log isn't scalar or $logLevel is invalid
       */
      public function __construct($logLevel = self::DEBUG, $label = ALO_LOG_LABEL, $savePath = ALO_LOG_SAVE_PATH) {
         $this->logLabel($label)
              ->level($logLevel)
              ->savePath($savePath);
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
         return 'Label: ' . $this->label . ', ' . PHP_EOL . 'Level: ' . $this->level . ', ' . PHP_EOL .
                'Save path: ' . $this->savePath;
      }

      /**
       * Gets or sets the log file path.
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @throws LogException             When the directory doesn't exist or is not writeable
       * @throws InvalidArgumentException When the path isn't a valid string
       *
       * @param string|null $path Omit if using as a getter; The path to the log file otherwise
       *
       * @return self|string
       */
      public function savePath($path = null) {
         if($path === null) {
            return $this->savePath;
         } elseif(!is_string($path)) {
            throw new InvalidArgumentException('The path must be a string');
         } else {
            $dir = dirname($path);

            if(!file_exists($dir)) {
               throw new LogException('The directory does not exist: ' . $dir, LogException::E_INVALID_PATH);
            } elseif(!is_writeable($dir)) {
               throw new LogException('The directory is not writeable: ' . $dir, LogException::E_NOT_WRITEABLE);
            } else {
               $this->savePath = $path;
            }
         }

         return $this;
      }

      /**
       * Gets or sets the log label.
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string|null $set Omit if using as a getter, the new log label if using as a setter.
       *
       * @throws InvalidArgumentException If attempting to set an invalid log level
       *
       * @return self|string
       */
      public function logLabel($set = null) {
         if($set === null) {
            return $this->label;
         } elseif(!is_scalar($set)) {
            throw new InvalidArgumentException('The log label must be scalar. You tried to set (serialised): ' .
                                               serialize($set));
         } else {
            $this->label = strtoupper($set);
         }

         return $this;
      }

      /**
       * Gets or sets the log level.
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string|null $set Omit if using as a getter, the log level if using as a setter - see class constants
       *                         or refer to the PSR-3 standards.
       *
       * @throws InvalidArgumentException If attempting to set an invalid log level
       *
       * @return self|string
       */
      public function level($set = null) {
         if($set === null) {
            return $this->level;
         } elseif(!array_key_exists($set, self::$priority)) {
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
       * @param string $level   The message's log level
       * @param string $message The message
       * @param array  $context The message context/placeholder asociative array
       *
       * @throws InvalidArgumentException When the log level is invalid
       *
       * @return bool Whether the message has been written
       */
      public function log($level, $message, array $context = []) {
         if(!array_key_exists($level, self::$priority)) {
            throw new InvalidArgumentException('Invalid log level supplied: ' .
                                               (is_scalar($level) ? $level : serialize($level) . ' (serialised)'));
         } elseif(self::$priority[$level] < self::$priority[$this->level]) {
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

         if(empty($trace)) {
            return [];
         } else {
            foreach($trace as $k => $v) {
               foreach(self::$ignoredFiles as $i) {
                  if(stripos(Alo::ifnull($v['file'], ''), $i) !== false) {
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
         $fp                = fopen($this->savePath, 'ab');

         if($fp) {
            $message = $this->buildMessage($level, $message);
            $ok      = [flock($fp, LOCK_EX),
                        fwrite($fp, $message),
                        flock($fp, LOCK_UN),
                        fclose($fp)];

            return !in_array(false, $ok, true);
         } else {
            trigger_error('Failed to open log file ' . $this->savePath, E_USER_WARNING);
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

         return $level . ' ' . self::SEPARATOR . ' ' . $this->time() . ' ' . self::SEPARATOR . ' ' . $this->label .
                ' ' . self::SEPARATOR . ' ' . str_replace(self::SEPARATOR, '\\' . self::SEPARATOR, $text) . ' ' .
                self::SEPARATOR . ' ' . $file . ' ' . self::SEPARATOR . ' ' . $line . PHP_EOL;
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
         if(empty($context)) {
            return $message;
         } else {
            $search = $replace = [];

            foreach($context as $k => $v) {
               $search[]  = '{' . $k . '}';
               $replace[] = $v;
            }

            return str_ireplace($search, $replace, $message);
         }
      }
   }
