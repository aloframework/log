<?php

    use AloFramework\Log\Log;

    Log::WARNING; //Load constants

    class ConstantsTest extends PHPUnit_Framework_TestCase {

        /** @dataProvider testDefinedProvider */
        function testDefined($d) {
            $this->assertTrue(defined($d), $d . ' wasn\'t defined');
        }

        function testDefinedProvider() {
            return [['ALO_LOG_LABEL'],
                    ['ALO_LOG_SAVE_PATH']];
        }
    }
