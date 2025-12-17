<?php
/**
 * 根据下载的原始文件，生成 dnsmasq 的屏蔽广告用途的配置
 *
 * @file make-addr.php
 * @author gently
 * @date 2017.12.31
 */

define('ROOT_DIR', __DIR__ . '/');
define('ORIG_DIR', ROOT_DIR . 'origin-files/');

set_time_limit(600);
error_reporting(0);

if (PHP_SAPI != 'cli') {
    die('nothing.');
}

date_default_timezone_set('Asia/Shanghai');

/* =========================
 * 远程黑白名单配置
 * ========================= */

// GitHub raw 远程地址
define(
    'REMOTE_BLACKLIST_URL',
    'https://raw.githubusercontent.com/privacy-protection-tools/anti-AD/refs/heads/adlist-maker/scripts/lib/black_domain_list.php'
);
define(
    'REMOTE_WHITELIST_URL',
    'https://raw.githubusercontent.com/privacy-protection-tools/anti-AD/refs/heads/adlist-maker/scripts/lib/white_domain_list.php'
);

// 本地缓存目录 & 文件
define('LIST_CACHE_DIR', ROOT_DIR . 'cache/');
define('LOCAL_BLACKLIST_CACHE', LIST_CACHE_DIR . 'black_domain_list.php');
define('LOCAL_WHITELIST_CACHE', LIST_CACHE_DIR . 'white_domain_list.php');

// 确保缓存目录存在
if (!is_dir(LIST_CACHE_DIR)) {
    mkdir(LIST_CACHE_DIR, 0755, true);
}

/**
 * 从远程加载 PHP 数组（return [...]），并缓存到本地
 *
 * @param string $remoteUrl
 * @param string $localCache
 * @param int    $ttl        缓存有效期（秒），默认 6 小时
 * @return array
 */
function load_remote_php_list($remoteUrl, $localCache, $ttl = 21600)
{
    // 缓存不存在或过期，才更新
    if (!file_exists($localCache) || filemtime($localCache) < time() - $ttl) {
        $code = file_get_contents($remoteUrl);
        if ($code !== false && strpos($code, '<?php') === 0) {
            file_put_contents($localCache, $code);
        }
    }

    if (!file_exists($localCache)) {
        die("List file not available: {$localCache}");
    }

    $data = require $localCache;
    return is_array($data) ? $data : [];
}

/* =========================
 * 加载黑白名单（远程）
 * ========================= */

$ARR_BLACKLIST = load_remote_php_list(
    REMOTE_BLACKLIST_URL,
    LOCAL_BLACKLIST_CACHE
);

$ARR_WHITELIST = load_remote_php_list(
    REMOTE_WHITELIST_URL,
    LOCAL_WHITELIST_CACHE
);

/* =========================
 * 原有业务逻辑（未改动）
 * ========================= */

require ROOT_DIR . 'lib/writerFormat.class.php';
require ROOT_DIR . 'lib/addressMaker.class.php';

$arr_input_cache = [];
$arr_whitelist_cache = [];
$arr_output = [];

$reflect = new ReflectionClass('writerFormat');
$formatterList = $reflect->getConstants();

foreach ($formatterList as $name => $formatObj) {

    if (!is_array($formatObj['src'])) {
        continue;
    }

    $arr_src_domains = [];
    $arr_tmp_whitelist = []; // 单次白名单

    // 附加白名单
    if (
        is_array($formatObj['whitelist_attached'])
        && count($formatObj['whitelist_attached']) > 0
    ) {
        foreach ($formatObj['whitelist_attached'] as $white_file => $white_attr) {

            $cacheKey = "{$white_file}_{$white_attr['merge_mode']}";

            if (!array_key_exists($cacheKey, $arr_whitelist_cache)) {
                $arr_attached = file(
                    ORIG_DIR . $white_file,
                    FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES
                );
                $arr_attached = array_fill_keys($arr_attached, $white_attr['merge_mode']);
                $arr_whitelist_cache[$cacheKey] = $arr_attached;
            }

            $arr_tmp_whitelist = array_merge(
                $arr_tmp_whitelist,
                $arr_whitelist_cache[$cacheKey]
            );
        }
    }

    // 合并全局白名单
    $arr_tmp_whitelist = array_merge($arr_tmp_whitelist, $ARR_WHITELIST);

    // 处理源文件
    foreach ($formatObj['src'] as $src_file => $src_attr) {

        if (!array_key_exists($src_file, $arr_input_cache)) {

            $src_content = file_get_contents(ORIG_DIR . $src_file);

            if ($src_attr['type'] === 'easylist') {
                $src_content = addressMaker::get_domain_from_easylist(
                    $src_content,
                    $src_attr['strict_mode'],
                    $arr_tmp_whitelist
                );
            } elseif ($src_attr['type'] === 'hosts') {
                $src_content = addressMaker::get_domain_list(
                    $src_content,
                    $src_attr['strict_mode'],
                    $arr_tmp_whitelist
                );
            }

            $arr_input_cache[$src_file] = $src_content;
        }

        $arr_src_domains = array_merge_recursive(
            $arr_src_domains,
            $arr_input_cache[$src_file]
        );
    }

    // 合并黑名单
    $arr_src_domains = array_merge_recursive($arr_src_domains, $ARR_BLACKLIST);
    ksort($arr_src_domains);

    $arr_output[] = '[' . $name . ']:' .
        addressMaker::write_to_file(
            $arr_src_domains,
            $formatObj,
            $arr_tmp_whitelist
        );
}

echo join(',', $arr_output);
