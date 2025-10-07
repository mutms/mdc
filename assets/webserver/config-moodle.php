<?php  // Moodle configuration file

// NOTE: the simplest way is to use this file directly,
// that is to include this from main config.php file, for example:
//     <?php require('/var/www/config-moodle.php');

if (!getenv('MDC_RUNNING', true)) {
    die('Not a MDC container!');
}

unset($CFG);
global $CFG;
$CFG = new stdClass();

ini_set('error_log', '/var/www/php_error.log');
ini_set('log_errors', '1');
ini_set('zend.exception_ignore_args', true);

$CFG->dbtype    = getenv('MDC_DB_TYPE');
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'db';
$CFG->dbname    = getenv('MDC_DBNAME');
$CFG->dbuser    = getenv('MDC_DBUSER');
$CFG->dbpass    = getenv('MDC_DBPASS');
$CFG->prefix    = 'm_';
$CFG->dboptions = [];
if (getenv('MDC_DB_COLLATION')) {
    $CFG->dboptions['dbcollation'] = getenv('MDC_DB_COLLATION');
}
if ($CFG->dbtype === 'sqlsrv') {
    // Disable Encryption for now on sqlsrv, it is on by default from msodbcsql18.
    $CFG->dboptions['extrainfo'] = ['Encrypt' => false];
}

$CFG->wwwroot   = 'https://webserver.' . getenv('COMPOSE_PROJECT_NAME') . '.orb.local';
if (getenv('MDC_USE_WWWROOT_SUBDIR')) {
    // Test compatibility with subdirectories.
    $CFG->wwwroot .= '/subdir';
}
$CFG->sslproxy  = true;

$CFG->dataroot  = '/var/www/moodledata';
$CFG->admin     = 'admin';
$CFG->directorypermissions = 0777;
$CFG->smtphosts = 'mailpit:1025';
$CFG->noreplyaddress = 'noreply@example.com';
$CFG->site_is_public = false;

// In case somebody is still using port redirection to localhost, make port instances separate.
$CFG->sessioncookie = getenv('COMPOSE_PROJECT_NAME');

if (strpos($CFG->wwwroot, 'https://') !== 0) {
    $CFG->cookiesecure = false;
}

// Very basic NGROK support, do not access the site via other URLs.
if (isset($_SERVER['HTTP_HOST'])) {
    if (strpos($_SERVER['HTTP_HOST'], '.ngrok-free.app') !== false
        || strpos($_SERVER['HTTP_HOST'], '.ngrok.app') !== false
    ) {
        if (file_exists('/usr/local/bin/ngrok')) {
            $CFG->wwwroot = 'https://' . $_SERVER['HTTP_HOST'];
            $CFG->sslproxy  = true;
        }
    }
}

$CFG->debug = (E_ALL | 2048); // DEBUG_DEVELOPER - note that since Moodle 5.0 it is just E_ALL, but higher value here is ok.
$CFG->debugdisplay = 1;
$CFG->debug_developer_use_pretty_exceptions = 0; // Do not leak environment and source code!
$CFG->allowthemechangeonurl = 1;
$CFG->passwordpolicy = 0;
$CFG->cronclionly = 0;
$CFG->pathtophp = '/usr/local/bin/php';

if (file_exists('/var/www/html/public/lib/setuplib.php')) {
    // This is Moodle 5.1 or later.
    $CFG->routerconfigured = true;
}

$CFG->behat_wwwroot   = 'http://webserver';
$CFG->behat_dataroot  = '/var/www/behatdata';
$CFG->behat_prefix = 'b_';
$CFG->behat_profiles = array(
    'default' => array(
        'browser' => getenv('MDC_BEHAT_BROWSER'),
        'wd_host' => 'http://selenium:4444/wd/hub',
    ),
);
if (getenv('MDC_BEHAT_BROWSER') === 'chromium' || getenv('MDC_BEHAT_BROWSER') === 'chrome') {
    $CFG->behat_profiles['default']['browser'] = 'chrome';
    $CFG->behat_profiles['default']['capabilities']['extra_capabilities']['goog:chromeOptions']['args'][] = 'remote-debugging-port=9222';
//    $CFG->behat_profiles['default']['capabilities']['extra_capabilities']['goog:chromeOptions']['args'][] = 'disable-dev-shm-usage';
    if (getenv('MDC_BEHAT_BROWSER_HEADLESS')) {
        $CFG->behat_profiles['default']['capabilities']['extra_capabilities']['goog:chromeOptions']['args'][] = 'headless=new';
    }
}
$CFG->behat_faildump_path = '/var/www/behatfaildumps';


$CFG->phpunit_dataroot  = '/var/www/phpunitdata';
$CFG->phpunit_prefix = 't_';
define('TEST_EXTERNAL_FILES_HTTP_URL', 'http://exttests:9000');
define('TEST_EXTERNAL_FILES_HTTPS_URL', 'http://exttests:9000');
define('PHPUNIT_LONGTEST', true);

if (getenv('MDC_PHPUNIT_EXTRAS')) {
    define('TEST_SEARCH_SOLR_HOSTNAME', 'solr');
    define('TEST_SEARCH_SOLR_INDEXNAME', 'test');
    define('TEST_SEARCH_SOLR_PORT', 8983);

    define('TEST_SESSION_REDIS_HOST', 'redis');
    define('TEST_CACHESTORE_REDIS_TESTSERVERS', 'redis');

    define('TEST_CACHESTORE_MONGODB_TESTSERVER', 'mongodb://mongo:27017');

    define('TEST_CACHESTORE_MEMCACHED_TESTSERVERS', "memcached0:11211\nmemcached1:11211");
    define('TEST_CACHESTORE_MEMCACHE_TESTSERVERS', "memcached0:11211\nmemcached1:11211");

    define('TEST_LDAPLIB_HOST_URL', 'ldap://ldap:389');
    define('TEST_LDAPLIB_BIND_DN', 'cn=admin,dc=example,dc=org');
    define('TEST_LDAPLIB_BIND_PW', 'admin');
    define('TEST_LDAPLIB_DOMAIN', 'dc=example,dc=org');

    define('TEST_AUTH_LDAP_HOST_URL', 'ldap://ldap:389');
    define('TEST_AUTH_LDAP_BIND_DN', 'cn=admin,dc=example,dc=org');
    define('TEST_AUTH_LDAP_BIND_PW', 'admin');
    define('TEST_AUTH_LDAP_DOMAIN', 'dc=example,dc=org');

    define('TEST_ENROL_LDAP_HOST_URL', 'ldap://ldap:389');
    define('TEST_ENROL_LDAP_BIND_DN', 'cn=admin,dc=example,dc=org');
    define('TEST_ENROL_LDAP_BIND_PW', 'admin');
    define('TEST_ENROL_LDAP_DOMAIN', 'dc=example,dc=org');
}

if (getenv('MDC_BBB_MOCK')) {
    if (property_exists($CFG, 'behat_wwwroot')) {
        $mockhash = sha1($CFG->behat_wwwroot);
    } else {
        $mockhash = sha1($CFG->wwwroot);
    }
    define('TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER', "http://bbbmock/{$mockhash}");
}

// Include shared extra config.
if (file_exists('/var/www/mdc-config-shared.php')) {
    require('/var/www/mdc-config-shared.php');
}

// Include extra config from current directory.
if (file_exists('/var/www/mdc-config-project.php')) {
    require('/var/www/mdc-config-project.php');
}

require('/var/www/html/lib/setup.php');
