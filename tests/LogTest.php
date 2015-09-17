<?php

    namespace AloFramework\Tests\Log;

    use AloFramework\Log\Log;
    use PHPUnit_Framework_TestCase;

    class LogTest extends PHPUnit_Framework_TestCase {

        /**
         * @var Log
         */
        private $log;

        function __construct($name = null, $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->log = new Log('PHPUNIT');
        }

        function testLogPermissions() {
            $permissions = [Log::DEBUG,
                            Log::INFO,
                            Log::NOTICE,
                            Log::WARNING,
                            Log::ERROR,
                            Log::CRITICAL,
                            Log::ALERT,
                            Log::EMERGENCY];
            $methods     = ['debug',
                            'info',
                            'notice',
                            'warning',
                            'error',
                            'critical',
                            'alert',
                            'emergency'];

            foreach ($permissions as $permissionIndex => $permission) {
                $this->assertTrue($this->log->level($permission) instanceof Log,
                                  'Logger did not return $this when permission was set to ' . $permission);

                $this->assertEquals($permission,
                                    $this->log->level(),
                                    'Permission set fail: ' .
                                    $permission .
                                    ' did not equal the one in the class: ' .
                                    $this->log->level());

                foreach ($methods as $methodIndex => $method) {
                    $this->assertTrue(call_user_func([$this->log, $method], 'Test message null') === null,
                                      'Calling the method did not return NULL');

                    $logged   = $this->log->log($method, 'Test message log');
                    $expected = $methodIndex >= $permissionIndex;

                    $this->assertTrue($logged == $expected,
                                      'Write permission failed for permission ' .
                                      $permission .
                                      ' method ' .
                                      $method .
                                      ': method returned ' .
                                      ($logged ? 'true' : 'false'));
                }
            }
        }

        /**
         * @expectedException \Psr\Log\InvalidArgumentException
         * @expectedExceptionCode 0
         */
        function testInvalidLabel() {
            $this->log->logLabel([]);
        }

        /**
         * @expectedException \Psr\Log\InvalidArgumentException
         * @expectedExceptionCode 0
         */
        function testInvalidLevel() {
            $this->log->level('foo');
        }

        /**
         * @expectedException \Psr\Log\InvalidArgumentException
         * @expectedExceptionCode 0
         */
        function testPathNonscalar() {
            $this->log->savePath([]);
        }

        /**
         * @expectedException \AloFramework\Log\LogException
         * @expectedExceptionCode 1
         */
        function testInvalidPath() {
            $this->log->savePath('/foo/bar/qux/foo.log');
        }
    }
