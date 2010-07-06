<?php

require_once("include/logger.inc.php");

$cfg = new TWM_Logger_Config();
$cfg->output_dir = "/tmp";
$cfg->log_level = TWM_LOGLEVEL_TRACE;
$LOGGER = new TWM_Logger($cfg);

$LOGGER->log("test", TWM_LOGLEVEL_INFO, "This is an INFO level test");
$LOGGER->log("test", TWM_LOGLEVEL_WARN, "This is a WARN level test");

logtest();

function logtest() {
  global $LOGGER;
  $LOGGER->log("test", TWM_LOGLEVEL_ERROR, "This is an ERROR level test");
}

?>

