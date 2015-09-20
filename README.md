# AloFramework | Log #

A simple, configurable logger implementing the PSR-3 standards interface.

[![License](https://poser.pugx.org/aloframework/log/license?format=plastic)](LICENSE)
[![Latest Stable Version](https://poser.pugx.org/aloframework/log/v/stable?format=plastic)](https://packagist.org/packages/aloframework/log)
[![Total Downloads](https://poser.pugx.org/aloframework/log/downloads?format=plastic)](https://packagist.org/packages/aloframework/log)


|                                                                                          dev-master                                                                                         |                                                             Latest release                                                            |
|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|:-------------------------------------------------------------------------------------------------------------------------------------:|
| [![Dev Build Status](https://travis-ci.org/aloframework/log.svg?branch=master)](https://travis-ci.org/aloframework/log)                                                                     | [![Release Build Status](https://travis-ci.org/aloframework/log.svg?branch=0.1.6)](https://travis-ci.org/aloframework/log)            |
| [![SensioLabsInsight](https://insight.sensiolabs.com/projects/c3500bba-d9af-4734-9dc7-31fddc7f8abe/mini.png)](https://insight.sensiolabs.com/projects/c3500bba-d9af-4734-9dc7-31fddc7f8abe) | [![SensioLabsInsight](https://i.imgur.com/KygqLtf.png)](https://insight.sensiolabs.com/projects/c3500bba-d9af-4734-9dc7-31fddc7f8abe) |


## Installation ##
Installation is available via Composer:

    composer require aloframework/log

## Usage ##

    <?php
	
		use AloFramework\Log\Log;
		
		// $logLevel = one of the class' constants, the standard debug to emergency levels. Messages below this level will not be logged.
		// $logLabel = however you want to identify entries from this specific log, e.g. [SYSTEM]
		// $pathToLogFile = The path to where the log file will be located
		$log = new Log($logLevel, $logLabel, $pathToLogFile);
		
		$log->notice('My notice message');
		$log->error('An error message');

## Configuration ##

Default settings can be overwritten by defining the following constants before calling the composer autoload file:

 - **ALO_LOG_LABEL** - the label to use for log entries, defaults to **SYSTEM**.
	 - Example: `define('ALO_LOG_LABEL', 'MyLog');`
 - **ALO_LOG_SAVE_PATH** - the default path to the log file, defaults to **vendor/aloframework/log/src/logs/YYYY-mm-dd.log**.
	 - Example: `define('ALO_LOG_SAVE_PATH', '/var/log/my-app/' . date('Y-m-d') . '.log');`
