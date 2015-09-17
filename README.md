# AloFramework | Log #

The logging component, implementing the PSR-3 logging interface.

[![License](https://poser.pugx.org/aloframework/log/license?format=plastic)](LICENSE)
[![Latest Stable Version](https://poser.pugx.org/aloframework/log/v/stable?format=plastic)](https://packagist.org/packages/aloframework/log)
[![Total Downloads](https://poser.pugx.org/aloframework/log/downloads?format=plastic)](https://packagist.org/packages/aloframework/log)

Development code quality: [![SensioLabsInsight](https://insight.sensiolabs.com/projects/2b373436-ce4e-4d46-a217-a99645ffa5a0/small.png)](https://insight.sensiolabs.com/projects/2b373436-ce4e-4d46-a217-a99645ffa5a0)

Dev: [![Dev Build Status](https://travis-ci.org/aloframework/log.svg?branch=master)](https://travis-ci.org/aloframework/log)
Release: [![Release Build Status](https://travis-ci.org/aloframework/log.svg?branch=0.1)](https://travis-ci.org/aloframework/log)

## Installation ##
Installation is available via Composer:

    composer require aloframework/log

## Usage ##

    <?php
	
		use AloFramework\Log\Log;
		
		// $logLabel = however you want to identify entries from this specific log, e.g. [SYSTEM]
		// $logLevel = one of the class' constants, the standard debug to emergency levels. Messages below this level will not be logged.
		// $pathToLogFile = The path to where the log file will be located
		$log = new Log($logLabel, $logLevel, $pathToLogFile);
		
		$log->notice('My notice message');
		$log->error('An error message');
