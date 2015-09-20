<?php

    namespace AloFramework\Log;

    use Exception;

    /**
     * Log-related exceptions
     * @author Art <a.molcanovas@gmail.com>
     */
    class LogException extends Exception {

        /**
         * Code when the log save path is invalid
         * @var int
         */
        const E_INVALID_PATH = 1;
    }
