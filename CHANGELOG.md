# 1.4 #
The message as it would appear in the log is now built in the protected method `buildMessage()`

# 1.3 #

 - `getBacktrace()` added
 - `self::$ignoredFiles` added

# 1.2 #

 - time() added
 - The separator is now a constant

# 1.1 #

 - getLastMessage() added
 - cleanup
 - more extensive tests
 - Bugfix: __toString() path is now returned correctly

# 1.0.5 #

Removed some doc clutter and var-dumper as a dependency

# 1.0.4 #

 - Removed some unnecessary files from composer's autoloader
 - The default configuration file is now loaded on class file load instead of composer load
 - Slight update to source documentation
