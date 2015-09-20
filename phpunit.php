<?php

    $composerJSON = json_decode(file_get_contents('composer.json'), true);

    foreach ($composerJSON['autoload']['files'] as $f) {
        include_once $f;
    }
