<?php

/**
 *
 * Generic Logging Framework for PHP Scripts
 * Copyright (C) 2004-2005, Three Wise Men Software Development and Consulting
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * <b>General Usage Information</b>
 *
 * Use the TWM_Logger_Config class to configure the logger, and
 * then pass that to the TWM_Logger constructor.
 *
 * For example:
 *
 * <code>
 * $cfg = new TWM_Logger_Config();
 * $cfg->output_dir = "/";
 * $cfg->log_level = TWM_LOGLEVEL_WARN;
 * $LOGGER = new TWM_Logger($cfg);
 *
 * $LOGGER->log("test", TWM_LOGLEVEL_INFO, "This is an INFO level test");
 * $LOGGER->log("test", TWM_LOGLEVEL_WARN, "This is a WARN level test");
 * </code>
 *
 * Typically, you will create a single global $LOGGER variable and reference
 * this one variable throughout your code. This saves juggling configurations.
 *
 * Note that the first argument to the ->log function is a name used to
 * identify a particular type of entry. These are separated into different
 * files in the output_dir you specify - in the example above, the entries
 * will get logged to a file called "test.log". You can choose your own names,
 * and the logger will create and append to those log files as required.
 *
 * @version 1.0.1
 * @copyright 2004-2005, Three Wise Men Software Development and Consulting
 *
 */

/**
 * Constants that define log levels
 */
define("TWM_LOGLEVEL_TRACE", 0);
define("TWM_LOGLEVEL_DEBUG", 1);
define("TWM_LOGLEVEL_INFO", 2);
define("TWM_LOGLEVEL_WARN", 3);
define("TWM_LOGLEVEL_ERROR", 4);
define("TWM_LOGLEVEL_CRITICAL", 5);

/**
 * Configuration class for the logger system
 *
 * Available settings:
 *
 * <ul>
 *  <li> output_dir       - output folder for log files (REQUIRED FIELD) </li>
 *  <li> log_level        - log level output threshold (messages lower than this won't be logged), defaults to TWM_LOGLEVEL_ERROR </li>
 *  <li> show_on_console  - useful for console PHP scripts, outputs log information to the console in addition to the file, defaults to false </li>
 *  <li> log_format       - format of log file using log format markers below (defaults to "tsnlfiem" </li>
 *  <li> log_separator    - field separator used in log, defaults to "|" </li>
 *  <li> timestamp_format - date/time format for timestamp field, defaults to "Y/m/d H:m:s" </li>
 * </ul>
 *
 * Log format markers:
 * <ul>
 *  <li> t - timestamp </li>
 *  <li> s - severity (log level) of entry </li>
 *  <li> f - class and method (if called from class) or function name </li>
 *  <li> n - name of file that called the logger </li>
 *  <li> l - line number of file that called the logger </li>
 *  <li> i - IP address of visitor </li>
 *  <li> e - PHP session ID </li>
 *  <li> m - message </li>
 * </ul>
 */
class TWM_Logger_Config {
  var $output_dir;  // Output folder for log files
  var $log_level;   // Output log level threshold
  var $show_on_console; // Output to console as well as file
  var $log_format;
  var $log_separator;
  var $timestamp_format;

  /**
   * Constructor, sets up default values for configuration
   */
  function TWM_Logger_Config() {
    $this->log_level = TWM_LOGLEVEL_ERROR;
    $this->show_on_console = false;
    $this->separate_trace_file = null;
    $this->log_format = "tsnlfiem";
    $this->log_separator = "|";
    $this->timestamp_format = "Y/m/d H:m:s";
  }
}

/**
 * Main class for logger framework
 */
class TWM_Logger {

  var $config;	// Configuration

  /**
   * Create a new TWM_Logger from the provided configuration object.
   * @param TWM_Logger_Config configuration
   */
  function TWM_Logger($config) {
    $this->config = $config;
  }

  /**
   * Log activity
   * @param string log name to write to
   * @param int specify detail level for this message
   * @param string the message to log
   */
  function log($name, $level, $data) {
    if ($level >= $this->config->log_level) {
      $trace = $this->stacktrace();
      $parts = array();
      for ($i=0; $i<strlen($this->config->log_format); $i++) {
        $part = substr($this->config->log_format, $i, 1);
        if ($part == 't') $parts[] = date($this->config->timestamp_format);
        if ($part == 's') $parts[] = $level;
        if ($part == 'n') $parts[] = $trace[0]['name'];
        if ($part == 'l') $parts[] = $trace[0]['line'];
        if ($part == 'f') $parts[] = $trace[0]['file'];
        if ($part == 'i') $parts[] = $_SERVER['REMOTE_ADDR'];
        if ($part == 'e') $parts[] = session_id();
        if ($part == 'm') $parts[] = $data;
      }
      $logfile = $this->config->output_dir.$name.".log";
      $line = implode($this->config->log_separator, $parts);
      $of = fopen($logfile, 'a');
      fwrite($of, $line."\n");
      fclose($of);
      if ($this->config->show_on_console) print "$line\n";
    }
  }

  /**
   * Obtain a stack trace that doesn't include the logger functions
   */
  function stacktrace() {
    if (phpversion() < "4.3") {
      return array("Trace unavailable (php<4.3)");
    }
    $data = debug_backtrace();
    array_shift($data); // We don't need to report ourself
    $trace = array();
    foreach ($data as $d) {
      if (strtolower($d['class']) != strtolower(__CLASS__)) {
        $data = array();
        if ($d['class']) {
          $data['name'] = $d['class'] . $d['type'] . $d['function'];
        } else {
          $data['name'] = $d['function'];
        }
        if ($data['name'] != "SKIP") {
          $data['line'] = $d['line'];
          $data['file'] = $d['file'];
        }
        $trace[] = $data;
      }
    }
    return $trace;
  }
}

?>
