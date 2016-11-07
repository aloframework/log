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

    namespace AloFramework\Log\Tests;

    use AloFramework\Log\Config as Cfg;
    use AloFramework\Log\InvalidArgumentException;
    use AloFramework\Log\Log;
    use PHPUnit_Framework_TestCase;

    class Extender extends Log {

        protected function buildMessage($level, $text) {
            return $level . '|' . $text . '|' . $this->time();
        }

        protected function time() {
            return date('Y');
        }
    }

    class LogTest extends PHPUnit_Framework_TestCase {

        function testDefaultConstruct() {
            $cfg = (new Cfg())->getAll();
            $class = (new Log())->getFullConfig();

            $this->assertEquals($cfg, $class);
        }

        function testCustomConstruct() {
            $my = ['foo', 'bar'];

            $cfg = new Cfg($my);
            $class = (new Log($cfg))->getFullConfig();

            $this->assertEquals($cfg->getAll(), $class);
        }

        function testGetPriority() {
            $prio = [Log::DEBUG     => 1,
                     Log::INFO      => 2,
                     Log::NOTICE    => 3,
                     Log::WARNING   => 4,
                     Log::ERROR     => 5,
                     Log::CRITICAL  => 6,
                     Log::ALERT     => 7,
                     Log::EMERGENCY => 8];

            $this->assertEquals($prio, Log::getPriority());
        }

        function testGetLastMessage() {
            $msg = mt_rand(~PHP_INT_MAX, PHP_INT_MAX) . 'testGetLastMessage';
            $time = date('Y-m-d H:i:s');
            $log = new Log();
            $log->debug($msg);

            //Get the line above
            $php = explode(PHP_EOL, file_get_contents(__FILE__));
            $line = 72;
            foreach ($php as $k => $v) {
                if (stripos($v, '') !== false) {
                    $line = $k;
                    break;
                }
            }

            $this->assertEquals($msg, $log->getLastMessage());

            $fullMsg = Log::DEBUG . ' ' . Log::SEPARATOR . ' ' . $time . ' ' . Log::SEPARATOR . ' ' .
                       $log->getConfig(Cfg::LOG_LABEL) . ' ' . Log::SEPARATOR . ' ' .
                       str_replace(Log::SEPARATOR, '\\' . Log::SEPARATOR, $msg) . ' ' . Log::SEPARATOR . ' ' . 'tests' .
                       DIRECTORY_SEPARATOR . 'LogTest.php' . ' ' . Log::SEPARATOR . ' ' . $line . PHP_EOL;

            $this->assertEquals($fullMsg, $log->getLastMessage(true));
        }

        function testToString() {
            $c = new Cfg();
            $l = new Log();
            $expect =
                'Label: ' . $c->logLabel . ', ' . PHP_EOL . 'Level: ' . $c->logLevel . ', ' . PHP_EOL . 'Save path: ' .
                $c->savePath;

            $this->assertEquals($expect, (string)$l);
        }

        /** @dataProvider logPrioProvider */
        function testLogPrios($setLevel, $logLevel, $ok) {
            $log = new Log();
            $log->addConfig(Cfg::LOG_LEVEL, $setLevel);

            $this->assertEquals($ok, $log->log($logLevel, 'msg'));
        }

        function testLogWithContext() {
            $log = new Log();
            $log->notice('foo {var}', ['var' => 'bar']);

            $this->assertEquals('foo bar', $log->getLastMessage());
        }

        function logPrioProvider() {
            $r = [];
            $prio = Log::getPriority();

            foreach ($prio as $level1 => $prio1) {
                foreach ($prio as $level2 => $prio2) {
                    $r[] = [$level1,
                            $level2,
                            $prio2 >= $prio1];
                }
            }

            return $r;
        }

        function testTimeAndBuildMessageOverride() {
            $log = new Extender();
            $log->debug('foo');

            $this->assertEquals(Log::DEBUG . '|foo|' . date('Y'), $log->getLastMessage(true));
        }

        /**
         * @expectedException InvalidArgumentException
         * @expectedExceptionCode    103
         */
        function testLogInvalidLevel() {
            (new Log())->log('foo', 'bar');
        }
    }
