<?php

    if (!defined('ALO_LOG_LABEL')) {
        /** The default label to use for logs */
        define('ALO_LOG_LABEL', 'SYSTEM');
    }

    if (!defined('ALO_LOG_SAVE_PATH')) {
        /** The default path to the log file */
        define('ALO_LOG_SAVE_PATH', __DIR__ .
                                    DIRECTORY_SEPARATOR .
                                    'logs' .
                                    DIRECTORY_SEPARATOR .
                                    date('Y-m-d') .
                                    '.log');
    }
