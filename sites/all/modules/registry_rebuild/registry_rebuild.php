<?php

/**
 * Root directory of Drupal installation. Note that you can change this
 * if you need to. It just has to point to the actual root. This assumes
 * that the php file is being run in sites/all/modules/registry_rebuild.
 */
define('DRUPAL_ROOT', realpath(getcwd() . '/../../../..'));
chdir(DRUPAL_ROOT);
print "DRUPAL_ROOT is " . DRUPAL_ROOT . ".<br/>\n";

global $_SERVER;
$_SERVER['REMOTE_ADDR'] = 'nothing';
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
require_once DRUPAL_ROOT . '/includes/common.inc';
require_once DRUPAL_ROOT . '/includes/registry.inc';
require_once DRUPAL_ROOT . '/includes/entity.inc';
require_once DRUPAL_ROOT . '/modules/system/system.module';

require_once DRUPAL_ROOT . '/includes/database/query.inc';
require_once DRUPAL_ROOT . '/includes/database/select.inc';

print "Bootstrapping to DRUPAL_BOOTSTRAP_SESSION<br/>\n";
drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);

// This section is not functionally important. It's just getting the
// registry_parsed_files() so that it can report the change.
$connection_info = Database::getConnectionInfo();
$driver = $connection_info['default']['driver'];
require_once DRUPAL_ROOT . '/includes/database/' . $driver . '/query.inc';

$parsed_before = registry_get_parsed_files();

cache_clear_all('lookup_cache', 'cache_bootstrap');
cache_clear_all('variables', 'cache_bootstrap');
cache_clear_all('module_implements', 'cache_bootstrap');

print "Doing registry_rebuild() in DRUPAL_BOOTSTRAP_SESSION<br/>\n";
registry_rebuild();   // At lower level

print "Bootstrapping to DRUPAL_BOOTSTRAP_FULL<br/>\n";
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
print "Doing registry_rebuild() in DRUPAL_BOOTSTRAP_FULL<br/>\n";
registry_rebuild();
$parsed_after = registry_get_parsed_files();

print "Flushing all caches<br/>\n";
drupal_flush_all_caches();

print "There were " . count($parsed_before) . " files in the registry before and " . count($parsed_after) . " files now.<br/>\n";
print "If you don't see any crazy fatal errors, your registry has been rebuilt.<br/>\n";
