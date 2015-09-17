<?php

    $composerJSON = json_decode(file_get_contents('composer.json'), true);

    foreach ($composerJSON['autoload']['files'] as $f) {
        include_once $f;
    }

    function log__aloframework__phpunitAutoloader($class) {
        $class = ltrim($class, '\\');
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $file  =
            __DIR__ .
            DIRECTORY_SEPARATOR .
            'src' .
            DIRECTORY_SEPARATOR .
            'class' .
            DIRECTORY_SEPARATOR .
            $class .
            '.php';

        if (file_exists($file)) {
            include_once $file;
        }
    }

    spl_autoload_register('log__aloframework__phpunitAutoloader');
