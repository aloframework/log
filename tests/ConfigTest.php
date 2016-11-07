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
    use PHPUnit_Framework_TestCase;
    use Psr\Log\LogLevel;

    class ConfigTest extends PHPUnit_Framework_TestCase {

        function testDefaults() {
            $cfg = new Cfg();

            $this->assertEquals(LogLevel::DEBUG, $cfg->logLevel);
            $this->assertEquals('SYSTEM', $cfg->logLabel);
            $this->assertTrue($cfg->lock);
        }

        function testGoodSavePath() {
            $cfg = new Cfg();
            $cfg->savePath = 'foo.log';

            $this->assertEquals('foo.log', $cfg->savePath);
        }

        /**
         * @expectedException InvalidArgumentException
         * @expectedExceptionCode    101
         * @expectedExceptionMessage The savePath setting is invalid.
         */
        function testBadSavePathString() {
            $cfg = new Cfg();

            $cfg->savePath = '/foo/bar/qux/' . mt_rand(~PHP_INT_MAX, PHP_INT_MAX) . '/foo.log';
        }

        /**
         * @expectedException InvalidArgumentException
         * @expectedExceptionCode    101
         * @expectedExceptionMessage The savePath setting is invalid.
         */
        function testBadSavePathNonString() {
            $cfg = new Cfg();

            $cfg->savePath = 5;
        }

        function testGoodLevel() {
            $c = new Cfg();
            $c->logLevel = LogLevel::ALERT;

            $this->assertEquals(LogLevel::ALERT, $c->logLevel);
        }

        /**
         * @expectedException InvalidArgumentException
         * @expectedExceptionCode    103
         * @expectedExceptionMessage The logLevel setting is invalid.
         */
        function testBadLevel() {
            $cfg = new Cfg();

            $cfg->logLevel = 'EVERYTHING! LOG EVERYTHING! PLEASE!';
        }

        function testGoodLabel() {
            $c = new Cfg();

            $c->logLabel = 'foo';
            $this->assertEquals('foo', $c->logLabel);
        }

        /**
         * @expectedException InvalidArgumentException
         * @expectedExceptionCode    102
         * @expectedExceptionMessage The logLabel setting is invalid.
         */
        function testBadLabel() {
            $cfg = new Cfg();

            $cfg->logLabel = new \stdClass();
        }

        function testCustomSetting() {
            $c = new Cfg();
            $c['foo'] = 'bar';

            $this->assertEquals('bar', $c['foo']);
        }

        function testCustomConstruct() {
            $c = new Cfg([Cfg::LOG_LABEL => 'foo', 'foo' => 'bar']);

            $this->assertEquals('foo', $c->logLabel);
            $this->assertEquals('bar', $c['foo']);
        }
    }
