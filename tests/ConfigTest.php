<?php

    namespace AloFramework\Log\Tests;

    use PHPUnit_Framework_TestCase;
    use AloFramework\Log\Config as Cfg;
    use AloFramework\Log\InvalidArgumentException;
    use Psr\Log\LogLevel;

    class ConfigTest extends PHPUnit_Framework_TestCase {

        function testDefaults() {
            $cfg = new Cfg();

            $this->assertEquals(LogLevel::DEBUG, $cfg->logLevel);
            $this->assertEquals('SYSTEM', $cfg->logLabel);
        }

        function testGoodSavePath() {
            $cfg           = new Cfg();
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
            $c           = new Cfg();
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
            $c        = new Cfg();
            $c['foo'] = 'bar';

            $this->assertEquals('bar', $c['foo']);
        }

        function testCustomConstruct() {
            $c = new Cfg([Cfg::LOG_LABEL => 'foo', 'foo' => 'bar']);

            $this->assertEquals('foo', $c->logLabel);
            $this->assertEquals('bar', $c['foo']);
        }
    }
