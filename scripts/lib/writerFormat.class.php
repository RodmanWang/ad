<?php
/**
 * 定义输出格式
 *
 *
 */

!defined('ROOT_DIR') && die('Access Denied.');

class writerFormat{
    /*dnsmasq支持格式的屏蔽广告列表*/
    const DNSMASQ = array(
        'format' => 'address=/{DOMAIN}/',
        'header' => "#VER={DATE}\n#TOTAL_LINES={COUNT}\n",
        'full_domain' => 0,
        'name' => 'dnsmasq',
        'filename' => '../ad.conf',
        'whitelist_attached' => array(
            'base-dead-hosts.txt' =>array(
                'merge_mode' => 2, //0=单条，1=单条+子域名，2=根域名相当于1，非根域名相当于0
            ),
        ),
        'src' => array(
            'base-src-easylist.txt' => array(
                'type' => 'easylist',
                'strict_mode' => false,
            ),
            'base-src-hosts.txt' => array(
                'type' => 'hosts',
                'strict_mode' => false,
            ),
            'base-src-strict-hosts.txt' => array(
                'type' => 'hosts',
                'strict_mode' => true,
            ),
        ),
    );

        /*easylist 兼容格式的屏蔽广告列表*/	
    const EASYLIST = array(	
        'format' => '||{DOMAIN}^',	
        'header' => "Version: {DATE}\n!Total lines: 00000\n",	
        'full_domain' => 0,	
        'name' => 'easylist',	
        'filename' => '../ad-easylist.txt',	
        'whitelist_attached' => array(	
            'base-dead-hosts.txt' =>array(	
                'merge_mode' => 2, //0=单条，1=单条+子域名，2=根域名相当于1，非根域名相当于0	
            ),	
        ),	
        'src' => array(	
            'base-src-easylist.txt' => array(	
                'type' => 'easylist',	
                'strict_mode' => false,	
            ),	
            'base-src-hosts.txt' => array(	
                'type' => 'hosts',	
                'strict_mode' => false,	
            ),	
            'base-src-strict-hosts.txt' => array(	
                'type' => 'hosts',	
                'strict_mode' => true,	
            ),	
        ),	
    );
    
    /*clash 兼容格式的屏蔽广告规则集*/
    const SURGE = array(
        'format' => '  - \'+.{DOMAIN}\'',
        'header' => "payload:\n# > Time: {DATE}\n# > lines: {COUNT}\n", 
        'full_domain' => 0,
        'name' => 'clash',
        'filename' => '../ad.yaml',
        'whitelist_attached' => array(
            'base-dead-hosts.txt' =>array(
                'merge_mode' => 2, //0=单条，1=单条+子域名，2=根域名相当于1，非根域名相当于0
            ),
        ),
        'src' => array(
            'base-src-easylist.txt' => array(
                'type' => 'easylist',
                'strict_mode' => false,
            ),
            'base-src-hosts.txt' => array(
                'type' => 'hosts',
                'strict_mode' => false,
            ),
            'base-src-strict-hosts.txt' => array(
                'type' => 'hosts',
                'strict_mode' => true,
            ),
        ),
    );

    /*Surge 兼容格式的屏蔽广告列表*/
    const SURGE3 = array(
        'format' => 'DOMAIN-SUFFIX,{DOMAIN}',
        'header' => "#VER={DATE}\n#TOTAL_LINES={COUNT}\n",
        'full_domain' => 0,
        'name' => 'surge3',
        'filename' => '../ad.list',
        'whitelist_attached' => array(
            'base-dead-hosts.txt' =>array(
                'merge_mode' => 2, //0=单条，1=单条+子域名，2=根域名相当于1，非根域名相当于0
            ),
        ),
        'src' => array(
            'base-src-easylist.txt' => array(
                'type' => 'easylist',
                'strict_mode' => false,
            ),
            'base-src-hosts.txt' => array(
                'type' => 'hosts',
                'strict_mode' => false,
            ),
            'base-src-strict-hosts.txt' => array(
                'type' => 'hosts',
                'strict_mode' => true,
            ),
        ),
    );
    
    /*smartdns支持格式的屏蔽广告列表*/
    const SMARTDNS = array(
        'format' => 'address /{DOMAIN}/#',
        'full_domain' => 0,
        'name' => 'dnsmasq',
        'filename' => '../ad-smartdns.conf',
        'whitelist_attached' => array(
            'base-dead-hosts.txt' =>array(
                'merge_mode' => 2, //0=单条，1=单条+子域名，2=根域名相当于1，非根域名相当于0
            ),
        ),
        'src' => array(
            'base-src-easylist.txt' => array(
                'type' => 'easylist',
                'strict_mode' => false,
            ),
            'base-src-hosts.txt' => array(
                'type' => 'hosts',
                'strict_mode' => false,
            ),
            'base-src-strict-hosts.txt' => array(
                'type' => 'hosts',
                'strict_mode' => true,
            ),
        ),
    );

    /*and etc...*/

}
