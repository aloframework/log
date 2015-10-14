<?php

    namespace AloFramework\Log;

    /**
     * Invalid argument exception codes
     * @author Art <a.molcanovas@gmail.com>
     */
    class InvalidArgumentException extends \InvalidArgumentException {

        /**
         * Code for an invalid log save path
         * @var int
         */
        const E_PATH = 101;

        /**
         * Code for an invalid log label
         * @var int
         */
        const E_LABEL = 102;

        /**
         * Code for an invalid log level
         * @var int
         */
        const E_LEVEL = 103;
    }
