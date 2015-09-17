<?php

    $composerJSON = json_decode(file_get_contents('composer.json'), true);

    foreach ($composerJSON['autoload']['files'] as $f) {
        include_once $f;
    }

    function core__aloframework__phpunitAutoloader($class) {
        $class = ltrim($class, '\\');
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        return \AloFramework\Alo::includeonceifexists(__DIR__ .
                                                      DIRECTORY_SEPARATOR .
                                                      'src' .
                                                      DIRECTORY_SEPARATOR .
                                                      'class' .
                                                      DIRECTORY_SEPARATOR .
                                                      $class .
                                                      '.php');
    }

    spl_autoload_register('core__aloframework__phpunitAutoloader');
