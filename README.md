# AloFramework | Log #

A simple, configurable logger implementing the [PSR-3 standards interface](https://packagist.org/packages/psr/log).

![License](https://poser.pugx.org/aloframework/log/license?format=plastic)
[![Latest Stable Version](https://poser.pugx.org/aloframework/log/v/stable?format=plastic)](https://packagist.org/packages/aloframework/log)
[![Total Downloads](https://poser.pugx.org/aloframework/log/downloads?format=plastic)](https://packagist.org/packages/aloframework/log)

Latest release API documentation: [https://aloframework.github.io/log/](https://aloframework.github.io/log/)

|                                                                                         dev-develop                                                                                         |                                                                                Release                                                                               |
|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|:--------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
|                                   [![Dev Build Status](https://travis-ci.org/aloframework/log.svg?branch=develop)](https://travis-ci.org/aloframework/log)                                  |                      [![Release Build Status](https://travis-ci.org/aloframework/log.svg?branch=master)](https://travis-ci.org/aloframework/log)                     |
| [![Coverage Status](https://coveralls.io/repos/aloframework/log/badge.svg?branch=develop&service=github)](https://coveralls.io/github/aloframework/log?branch=develop)                      | [![Coverage Status](https://coveralls.io/repos/aloframework/log/badge.svg?branch=master&service=github)](https://coveralls.io/github/aloframework/log?branch=master) |


## Installation ##
Installation is available via Composer:

    composer require aloframework/log

## Usage ##

    <?php
	
		use AloFramework\Log\Log;
		
		$log = new Log();
		$log->notice('My notice message');
		$log->error('An error message');

## Configuration ##
General configuration guidelines can be found [here](https://github.com/aloframework/config).

There are 3 configuration keys available:

 - `Config::LOG_LABEL`: How the log entries will get labelled (default: `SYSTEM`)
 - `Config::LOG_LEVEL`: Minimum log level to log (default: `LogLevel::DEBUG`)
 - `Config::SAVE_PATH`: The log file's location (default: `src/logs/YYYY-mm-dd.log`). Alternatively, you can pass a file handle (opened by `fopen()`)
 - `Config::LOCK_FILE`: Controls whether file locking should take place while writing log entries (default: `true`)
