<?php

    namespace AloFramework\Tests\Log;

    use AloFramework\Log\Log;
    use PHPUnit_Framework_TestCase;

    class LogTest extends PHPUnit_Framework_TestCase {

        /** @dataProvider logPermissionsProvider */
        function testLogPermissions($permissionIndex, $permission, $methodIndex, $method) {
            $log = new Log(Log::DEBUG, 'PHPUNIT');

            $this->assertTrue($log->level($permission) instanceof Log,
                              'Logger did not return $this when permission was set to ' . $permission);

            $this->assertEquals($permission,
                                $log->level(),
                                'Permission set fail: ' . $permission . ' did not equal the one in the class: ' .
                                $log->level());

            $this->assertTrue($log->{$method}('Test message null') === null,
                              'Calling the method did not return NULL');

            $logged   = $log->log($method, 'Test message log');
            $expected = $methodIndex >= $permissionIndex;

            $this->assertTrue($logged == $expected,
                              'Write permission failed for permission ' . $permission . ' method ' . $method .
                              ': method returned ' . ($logged ? 'true' : 'false'));
        }

        function testLastMessage() {
            $log = new Log();
            $msg = 'testLastMessage' . mt_rand(~PHP_INT_MAX, PHP_INT_MAX);
            $log->emergency($msg);

            $this->assertEquals($msg, $log->getLastMessage());
        }

        function testReplaceContext() {
            $log = new Log();
            $rnd = mt_rand(~PHP_INT_MAX, PHP_INT_MAX);
            $log->emergency('the number is {anumber}', ['anumber' => $rnd]);

            $this->assertEquals('the number is ' . $rnd, $log->getLastMessage());
        }

        /**
         * @expectedException \Psr\Log\InvalidArgumentException
         */
        function testLogInvalidLevel() {
            $log = new Log();

            $log->log('invalidlevel', 'foo');
        }

        /**
         * @expectedException \Psr\Log\InvalidArgumentException
         */
        function testLogInvalidLevelWithContext() {
            $log = new Log();

            $log->log('invalidlevel', '{foo}', ['foo' => 'bar']);
        }

        /**
         * @expectedException \Psr\Log\InvalidArgumentException
         * @expectedExceptionCode 0
         */
        function testInvalidLabel() {
            $log = new Log(Log::DEBUG, 'PHPUNIT');
            $log->logLabel([]);
        }

        function testToString() {
            $log = new Log(Log::DEBUG, 'PHPUNIT', ALO_LOG_SAVE_PATH);

            $this->assertEquals('Label: PHPUNIT, ' . PHP_EOL . 'Level: ' . Log::DEBUG . ', ' . PHP_EOL . 'Save path: ' .
                                ALO_LOG_SAVE_PATH,
                                $log->__toString());
        }

        /**
         * @expectedException \Psr\Log\InvalidArgumentException
         * @expectedExceptionCode 0
         */
        function testInvalidLevel() {
            $log = new Log(Log::DEBUG, 'PHPUNIT');
            $log->level('foo');
        }

        /**
         * @expectedException \Psr\Log\InvalidArgumentException
         * @expectedExceptionCode 0
         */
        function testPathNonscalar() {
            $log = new Log(Log::DEBUG, 'PHPUNIT');
            $log->savePath([]);
        }

        /**
         * @expectedException \AloFramework\Log\LogException
         * @expectedExceptionCode 1
         */
        function testInvalidPath() {
            $log = new Log(Log::DEBUG, 'PHPUNIT');
            $log->savePath('/foo/bar/qux/foo.log');
        }

        function testValidPath() {
            $log  = new Log(Log::DEBUG, 'PHPUNIT');
            $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'logs' .
                    DIRECTORY_SEPARATOR . 'phpunit.log';

            $this->assertTrue($log->savePath($path) instanceof Log,
                              'Didn\'t return $this');

            $this->assertEquals($path, $log->savePath(), 'Didn\'t equal set path');
        }

        function logPermissionsProvider() {
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

            $ret = [];

            foreach ($permissions as $pi => $p) {
                foreach ($methods as $mi => $m) {
                    $ret[] = [$pi,
                              $p,
                              $mi,
                              $m];
                }
            }

            return $ret;
        }
    }
