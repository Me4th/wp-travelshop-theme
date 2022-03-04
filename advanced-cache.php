<?php
error_reporting(-1);
ini_set('display_errors', 'on');
/**
 ** Create a symbolic link to this file from your wp-content directory and
 * on osx:
 * --
 * cd /var/www/vhosts/travelshop-theme.de/wp-content/
 * ln -s /var/www/vhosts/travelshop-theme.de/wp-content/themes/travelshop/advanced-cache.php wp-content/advanced-cache.php
 * --
 * enable page caching in your wp-config.php = WP_CACHE = true

 *
 * Put this constants to wp-config
 * define('PM_REDIS_HOST', '127.0.0.1');
 * define('PM_REDIS_PORT', '6379');
 * define('PM_REDIS_TTL', 48000); // seconds
 * define('PM_REDIS_BACKGROUND_KEY_REFRESH', 1000); // seconds
 * define('PM_REDIS_DEBUG', TRUE);
 * define('PM_REDIS_GZIP', TRUE);
 * define('PM_REDIS_KEY_PREFIX', 'fpc');
 * define('PM_REDIS_ACTIVATE', true);
 *
 * Redis CLI Commands:
 * Start Redis Server:  redis-server
 * Get all keys:        redis-cli keys "*"
 * Get key:             redis-cli get KEY
 * Delete key:          redis-cli del KEY
 * Flush all:           redis-cli flushall
 *
 * try redis-commander - a nice debugging gui
 *
 * Use this GET vars
 * cache-refresh=1 will renew the cache
 * no-cache=1 will display the page without cache
 */

if (!defined('ABSPATH')) {
    die();
}


class Redis_Page_Cache
{
    private static $redis;
    private static $request_hash = '';
    private static $current_url = ''; // complete url with request params
    private static $site_url = ''; // the host https://www.host.de
    private static $page_url = ''; // the page url without params https://www.host.de/foo/
    private static $blacklist_params = [ // all params that does not change the site rendering, @see https://github.com/mpchadwick/tracking-query-params-registry/blob/master/_data/params.csv
        'utm_', // Urchin
        'fbid', // Facebook
        'fbclid',
        'fbmid',
        'fbcid',
        'fbcmid',
        'fbcmlid',
        'cleverPushNotificationId',
        'mlnt', // Maleon
        'mlnm',
        'mlnc',
        'mlnch',
        'mlmsg',
        'mltest',
        'gclid',
        'gclsrc',
        '_ga',
        'mc_cid',
        'mc_eid',
        '_bta_tid',
        '_bta_c',
        'trk_contact',
        'trk_msg',
        'trk_module',
        'trk_sid',
        'gdfms',
        'gdftrk',
        'gdffi',
        '_ke',
        'redirect_log_mongo_id',
        'redirect_mongo_id',
        'sb_referer_host',
        'mkwid',
        'pcrid',
        'ef_id',
        's_kwcid',
        'msclkid',
        'dm_i',
        'epik',
        'pk_campaign',
        'pk_kwd',
        'pk_keyword',
        'piwik_campaign',
        'piwik_kwd',
        'piwik_keyword',
        'mtm_campaign',
        'mtm_keyword',
        'mtm_source',
        'mtm_medium',
        'mtm_content',
        'mtm_cid',
        'mtm_group',
        'mtm_placement',
        'matomo_campaign',
        'matomo_keyword',
        'matomo_source',
        'matomo_medium',
        'matomo_content',
        'matomo_cid',
        'matomo_group',
        'matomo_placement',
        'hsa_cam',
        'hsa_grp',
        'hsa_mt',
        'hsa_src',
        'hsa_ad',
        'hsa_acc',
        'hsa_net',
        'hsa_kw',
        'hsa_tgt',
        'hsa_ver',
        '_branch_match_id'
    ];

    private static $clean_get = [];


    public static function init()
    {
        self::$redis = new Redis();
        self::$redis->connect(PM_REDIS_HOST, PM_REDIS_PORT);
    }

    public static function cache_init()
    {
        self::init();
        self::cleanup_get();
        self::parse_request_uri();
        if (self::do_not_cache() === true) {
            header('X-PM-Cache-Status: do not cache');
            return;
        }
        if (self::$redis === false) {
            header('X-PM-Cache-Status: no server');
            return;
        }
        // set the request hash
        self::$request_hash = self::create_request_hash();

        if (PM_REDIS_DEBUG === true) {
            header('X-PM-Cache-Key: ' . self::$request_hash);
        }


        $cache = self::$redis->get(self::$request_hash);
        $cache = !empty($cache) ? unserialize($cache) : null;


        // Something is in cache.
        if (is_array($cache) && empty($cache) === false) {

            if (PM_REDIS_DEBUG === true) {
                header('X-PM-Cache-Time: ' . $cache['updated']);
            }

            // is this cache expired?
            $expired = ($cache['updated'] + PM_REDIS_BACKGROUND_KEY_REFRESH) < time();

            // Cache is outdated or set to expire, we're generating this page in the background!
            if ($expired && isset($_GET['cache-refresh']) === false) {

                header('X-PM-Cache-Expired: true');
                $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . (empty($_GET) ? '?' : '&') . "cache-refresh=1";
                $cmd = 'nohup curl "' . $url . '" </dev/null >/dev/null 2>&1 &';
                exec($cmd);
            }


            if ($cache['gzip'] === true) {
                if (PM_REDIS_DEBUG === true) {
                    header('X-PM-Cache-Gzip: true');
                }
                $cache['output'] = gzuncompress($cache['output']);
            }


            // Output the page from the cache
            header('X-PM-Cache-Status: hit');


            if (PM_REDIS_DEBUG === true) {
                header('X-PM-Cache-Expires: ' . (PM_REDIS_BACKGROUND_KEY_REFRESH + $cache['updated'] - time()));
            }

            // Output cached status code.
            if (!empty($cache['status'])) {
                http_response_code($cache['status']);
            }

            // Output cached headers.
            if (is_array($cache['headers']) && !empty($cache['headers'])) {
                foreach ($cache['headers'] as $header) {
                    header($header);
                }
            }
            echo $cache['output'];
            exit;
        } // cache data is available
        // Generate the page and cache it...
        header('X-PM-Cache-Status: miss');
        ob_start(array(__CLASS__, 'output_buffer'));
    }

    public static function cleanup_get(){
        self::$clean_get = $_GET;
        unset(self::$clean_get['cache-refresh']);
        ksort(self::$clean_get); // protect against unordered request
        foreach(self::$blacklist_params as $p){
            foreach ($_GET as $k => $v) {
                if($k == $p || strpos($k, $p) !== false){
                    unset(self::$clean_get[$k]);
                }
            }
        }
    }


    /**
     * Create the unique request hash!
     * @return string
     */
    public static function create_request_hash()
    {
        unset($_SERVER['HTTP_IF_NONE_MATCH']);
        unset($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        $request_hash = array(
            'request' => self::$current_url,
            'host' => !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
            'https' => !empty($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : '',
            'method' => $_SERVER['REQUEST_METHOD'],
            'get' => self::$clean_get,
        );
        // Make sure requests with Authorization: headers are unique.
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $request_hash['http-auth'] = $_SERVER['HTTP_AUTHORIZATION'];
        }
        return self::get_key_path_from_url(self::$page_url,). ':' . md5(serialize($request_hash));
    }

    /**
     * @param $url
     * @return string
     */
    public static function get_key_path_from_url($url){
        $path = str_replace(['https://', 'http://', '/'], ['', '', ':'], trim($url, '/'));
        return PM_REDIS_KEY_PREFIX.':'.$path;
    }

    public static function do_not_cache()
    {
        $doing_ajax = defined('DOING_AJAX') && DOING_AJAX;
        $xmlrpc_request = defined('XMLRPC_REQUEST') && XMLRPC_REQUEST;
        $rest_request = defined('REST_REQUEST') && REST_REQUEST;
        $no_cache_c = defined('DONOTCACHE') && DONOTCACHE;
        $robots_request = strpos($_SERVER['REQUEST_URI'], 'robots.txt') != false;
        $wp_admin = strpos($_SERVER['REQUEST_URI'], 'wp-admin') != false;
        $is_post = (strtolower($_SERVER['REQUEST_METHOD']) == 'post') ? true : false;
        $no_cache_g = isset($_GET['no-cache']);
        $is_logged_in = ((function_exists('is_user_logged_in') && is_user_logged_in()));
        $no_ttl = (PM_REDIS_TTL < 1) ? true : false;
        $no_bgcrf = (PM_REDIS_BACKGROUND_KEY_REFRESH < 1) ? true : false;
        $result = ($no_bgcrf || $is_post || $no_ttl  || $doing_ajax  || $xmlrpc_request || $rest_request || $robots_request || $wp_admin ||
            $no_cache_c || $no_cache_g || $is_logged_in);
        return $result;
    }

    /**
     * Take a request uri and remove ignored request keys.
     */
    private static function parse_request_uri()
    {
        $query = http_build_query(self::$clean_get);
        $parsed = parse_url($_SERVER['REQUEST_URI']);
        self::$site_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'];
        // the page url without request params
        self::$page_url = self::$site_url;
        self::$page_url .= !empty($parsed['path']) ? $parsed['path'] : '';
        // the full request with params
        self::$current_url = self::$page_url;
        if (!empty($query)) {
            self::$current_url .= '?' . $query;
        }
        return true;
    }

    /**
     * Runs when the output buffer stops.
     */
    public static function output_buffer($output)
    {
        if (self::$redis === false) {
            return $output;
        }
        if (self::do_not_cache() === true) {
            self::$redis->del(self::$request_hash);
            return $output;
        }
        $log = array();
        $cache = true;
        $data = array(
            'url' => self::$current_url,
            'output' => $output,
            'headers' => array(),
            'status' => http_response_code(),
            'gzip' => false,
        );
        // Don't cache 5xx errors.
        if ($data['status'] >= 500) {
            $log[] = 'error: response code is: ' . $data['status'] . "\r\n";
            $cache = false;
        }
        // Compression.
        if (PM_REDIS_GZIP && function_exists('gzcompress')) {
            $data['output'] = gzcompress($data['output']);
            $data['gzip'] = true;
        }
        // Clean up headers he don't want to store.
        foreach (headers_list() as $header) {
            list($key, $value) = explode(':', $header, 2);
            $value = trim($value);
            // do not cache set-cookies
            if (strtolower($key) == 'set-cookie') {
                continue;
            }
            // Never store X-PM-Cache-* headers in cache.
            if (strpos(strtolower($key), strtolower('X-PM-Cache')) !== false) {
                continue;
            }
            $data['headers'][] = $header;
        }
        $data['updated'] = time();
        if ($cache === true) {
            self::$redis->set(self::$request_hash, serialize($data), PM_REDIS_TTL);
            #$redis->expire(sprintf('tt-%s', self::$request_hash),(!defined('PM_REDIS_EXPIRE') ? 172800 : PM_REDIS_EXPIRE ));
            if (PM_REDIS_DEBUG === true) {
                $str = '<!-- pm-cache: request_hash: ' . self::$request_hash . ' -->';
                $output = str_replace('</html>', "\r\n" . $str . "\r\n</html>", $output);
            }
        } else {
            if (PM_REDIS_DEBUG === true) {
                $str = '<!-- pm-cache: error can not cache -->';
                $str .= implode("\r\n", $log);
                $output = str_replace('</html>', "\r\n" . $str . "\r\n</html>", $output);
            }
            // Not okay, so delete any stale entry.
            self::$redis->del(self::$request_hash);
        }
        return $output;
    }
    public static function get_by_pattern($pattern)
    {
        $iterator = null;
        $keys = [];
        // Retry when we get no keys back
        self::$redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        while ($scanned_keys = self::$redis->scan($iterator, $pattern)) {
            $keys += $scanned_keys;
        }
        return $keys;
    }
    public static function del_by_pattern($redis_pattern, $level = null){
        $keys = self::get_by_pattern($redis_pattern);
        $c = 0;
        foreach($keys as $key){
            $path = explode(':', $key);
            $depth = count($path);
            if($level == null  || $depth == $level){
                self::del($key);
                $c++;
            }
        }
        return $c;
    }

}
if(defined('PM_REDIS_ACTIVATE') === true && PM_REDIS_ACTIVATE === true) {
    Redis_Page_Cache::cache_init();
}