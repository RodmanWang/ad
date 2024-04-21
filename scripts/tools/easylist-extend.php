<?php
/**
 * easylist extend
 *
 * @file easylist-extend.php
 * @date 2021-05-01 23:14:30
 * @author gently
 *
 */
set_time_limit(0);

error_reporting(7);
date_default_timezone_set('Asia/Shanghai');
define('START_TIME', microtime(true));
define('ROOT_DIR', dirname(__DIR__) . '/');
const LIB_DIR = ROOT_DIR . 'lib/';

$black_domain_list = require_once LIB_DIR . 'black_domain_list.php';
require_once LIB_DIR . 'addressMaker.class.php';
const WILDCARD_SRC = ROOT_DIR . 'origin-files/wildcard-src-easylist.txt';
const WHITERULE_SRC = ROOT_DIR . 'origin-files/whiterule-src-easylist.txt';

$ARR_MERGED_WILD_LIST = array(
    'ad*.udn.com$dnstype=A|CNAME' => null,
    'p*-ad-sign.byteimg.com' => null, // #529
    '*.mgr.consensu.org' => null,
    'vs*.gzcu.u3.ucweb.com' => null,
    'ad*.goforandroid.com' => null,
    'bs*.9669.cn' => null,
    '*serror*.wo.com.cn' => ['m' => '$dnstype=A|CNAME'],
    '*mistat*.xiaomi.com' => null,
    'affrh20*.com' => null,
    'assoc-amazon.*' => null,
    'clkservice*.youdao.com' => null,
    'dsp*.youdao.com' => null,
    'pussl*.com' => null,
    'putrr*.com' => null,
    't*.a.market.xiaomi.com' => null,
    'ad*.bigmir.net' => null,
    'log*.molitv.cn' => null,
    'adm*.autoimg.cn' => null,
    'cloudservice*.kingsoft-office-service.com' => null,
    'gg*.51cto.com' => null,
    'log.*.hunantv.com' => null,
    'iflyad.*.openstorage.cn' => null,
    '*customstat*.51togic.com' => null,
//    'appcloud*.zhihu.com' => null, // #344
    'ad*.molitv.cn' => null,
    'ads*-adnow.com' => null,
    'aeros*.tk' => null,
    'analyzer*.fc2.com' => null,
    'admicro*.vcmedia.vn' => null,
    'xn--xhq9mt12cf5v.*' => null,
    'freecontent.*' => null,
    'hostingcloud.*' => null,
    'jshosting.*' => null,
    'flightzy.*' => null,
    'sunnimiq*.cf' => null,
    'admob.*' => null,
    '*log.droid4x.cn' => null,
    '*tsdk.vivo.com.cn' => null,
    '*.mmstat.com' => null,
//    'sf*-ttcdn-tos.pstatp.com' => null,
    'f-log*.grammarly.io' => null,
    '24log.*' => null,
    '24smi.*' => null,
    'ad-*.wikawika.xyz' => null,
    'ablen*.tk' => null,
    'darking*.tk' => null,
    'doubleclick*.xyz' => null,
    'adserver.*' => null,
    'clientlog*.music.163.com' => null,
    'brucelead*.com' => null,
    'gostats.*' => null,
    'gralfusnzpo*.top' => null,
    'oiwjcsh*.top' => null,
    '*-analytics*.huami.com' => null,
    'count*.pconline.com.cn' => null,
    'qchannel*.cn' => null,
    'sda*.xyz' => null,
    'ad-*.com' => null,
    'ad-*.net' => null,
    'webads.*' => null,
    'web-stat.*' => null,
    'waframedia*.*' => null,
    'wafmedia*.*' => null,
    'voluumtrk*.com' => null,
    'vmm-satellite*.com' => null,
    'vente-unique.*' => null,
    'vegaoo*.*' => null,
    'umtrack*.com' => null,
    'grjs0*.com' => null,
    'imglnk*.com' => null,
    'admarvel*.*' => null,
    'admaster*.*' => null,
    'adsage*.*' => null,
    'adsensor*.*' => null,
    'adservice*.*' => null,
    'adsh*.*' => null,
    'adsmogo*.*' => null,
    'adsrvmedia*.*' => null,
    'adsserving*.*' => null,
    'adsystem*.*' => null,
    'adwords*.*' => null,
    'analysis*.*' => null,
    'applovin*.*' => null,
    'appsflyer*.*' => null,
    'domob*.*' => null,
    'duomeng*.*' => null,
    'dwtrack*.*' => null,
    'guanggao*.*' => null,
//    'lianmeng*.*' => null,
    //'monitor*.*' => null,
    'omgmta*.*' => null,
    'omniture*.*' => null,
    'openx*.*' => null,
    'partnerad*.*' => null,
    'pingfore*.*' => null,
    'socdm*.*' => null,
    'supersonicads*.*' => null,
    'usage*.*' => null,
    'wlmonitor*.*' => null,
    'zjtoolbar*.*' => null,
    'engage.3m*' => null,
    '*.actonservice.com' => null,
//    '*-cor0*.api.p001.1drv.com' => null,
    '*33*-*.1drv.com' => null,
    '2cnjuh34j*.com' => null,
    'ssc.southpark*' => null,
    'tr.*.espmp-*fr.net' => null,
    'tdep.vacansoleil.*' => null,
    'da.hornbach.*' => null,
    '*us*watcab*.blob.core.windows.net' => null,
    'xn--wxtr9fwyxk9c.*' => null,
    'tuiguang.*' => null,
    '*.xsph.ru' => null,
    '*.page.link' => null,
);

$ARR_REGEX_LIST = array(
    '/^(\S+\.)?9377[a-z0-9]{2}\.com$/' => ['m' => '$dnstype=A'],
    '/^(\S+\.)?ad(s?[\d]+|m|s)?\.[0-9\-a-z]+\./' => ['m' => '$denyallow=nucdn.net|azureedge.net|alibabacorp.com|alibabadns.com'],
    '/^(\S+\.)?advert/' => ['m' => '$denyallow=alibabacorp.com|alibabadns.com|sm.cn|tanx.com|alibaba-inc.com|tmall.com|taobao.com'],
    '/^(\S+\.)?affiliat(es?[0-9a-z]*?|ion[0-9\-a-z]*?|ly[0-9a-z\-]*?)\./' => null, // fixed #406
    '/^(\S+\.)?s?metrics\./' => null, // TODO 覆盖面很大
    '/^(\S+\.)?afgr[\d]{1,2}\.com$/' => null,
    '/^(\S+\.)?analytics(\-|\.)/' => null,
    '/^(\S+\.)?counter(\-|\.)/' => null,
    '/^(\S+\.)?pixels?\./' => null,
    '/^(\S+\.)?syma[a-z]\.cn$/' => null,
    '/^(\S+\.)?widgets?\./' => null,
    '/^(\S+\.)?(webstats?|swebstats?|mywebstats?)\./' => null,
    // '/^(\S+\.)?stat\..+?\.(com|cn|ru|it|de|cz|net|kr|ai|pl|th|fi|fr|jp|hu|bz|sk|se)$/' => null,
    '/^(\S+\.)?track(ing)?\./' => null,
    '/^(\S+\.)?tongji\./' => null,
    '/^(\S+\.)?toolbar\./' => null,
    '/^(\S+\.)?adservice\.google\./' => null,
    '/^(\S+\.)?d[\d]+\.sina(img)?(\.com)?\.cn/' => null,
    '/^(\S+\.)?sax[\dns]?\.sina\.com\.cn/' => null,
    '/^(\S+\.)?delivery([\d]{2}|dom|modo).com$/' => null,
    '/^(\S+\.)?[c-s]ads(abs|abz|ans|anz|ats|atz|del|ecs|ecz|ims|imz|ips|ipz|kis|kiz|oks|okz|one|pms|pmz)\.com/' => null,
    '/^(\S+\.)?11599[\da-z]{2,20}\.com$/' => null, //"澳门新葡京"系列
    '/^(\S+\.)?61677[\da-z]{0,20}\.com$/' => null, //"澳门新葡京"系列
    '/^(\S+\.)?[0-9a-f]{15,}\.com$/' => null, //15个字符以上的16进制域名
    '/^(\S+\.)?[0-9a-z]{16,}\.xyz$/' => null, //16个字符以上的.xyz域名
    '/^(\S+\.)?6699[0-9]\.top$/' => null, //连号
    '/^(\S+\.)?abie[0-9]+\.top$/' => null, //连号
    '/^(\S+\.)?ad[0-9]{3,}m.com$/' => null, //连号
    '/^(\S+\.)?aj[0-9]{4,}.online$/' => null, //连号
    '/^(\S+\.)?xpj[0-9]\.net$/' => null, //连号
    '/^(\S+\.)?ylx-[0-9].com$/' => null, //连号
    '/^(\S+\.)?ali2[a-z]\.xyz$/' => null, //连号
    '/^(\S+\.)?777\-?partners?\.(net|com)$/' => null, //组合
    '/^(\S+\.)?voyage-prive\.[a-z]+(\.uk)?$/' => null, //组合
    '/^(\S+\.)?e7[0-9]{2,4}\.(net|com)?$/' => null, //组合
    '/^(\S+\.)?g[1-4][0-9]{8,9}\.com?$/' => null, //批量组合
    '/^(\S+\.)?hg[0-9]{4,5}\.com?$/' => null, //批量组合
    '/^(\S+\.)?333[1-9]{2}[0-9]{2}\.com?$/' => null, //批量组合
    '/^(\S+\.)?5551[0-9]{3}\.com?$/' => null, //批量组合

    // '/^(\S+\.)?(?=.*[a-f].*\.com$)(?=.*\d.*\.com$)[a-f0-9]{15,}\.com$/' => null,
);

//对通配符匹配或正则匹配增加的额外赦免规则
$ARR_WHITE_RULE_LIST = array(
    '@@||tongji.*kuwo.cn^' => 0,
    '@@||tracking.epicgames.com^' => 0,
    '@@||tracker.eu.org^' => 1, //强制加白，BT tracker，有形如2.tracker.eu.org的域
    '@@||stats.uptimerobot.com^' => 1, //uptimerobot监测相关 #38
    '@@||track.sendcloud.org^' => 0, //邮件退订域名
    '@@||log.mmstat.com^' => 0, //修复优酷视频显示禁用了cookie
    '@@||adm.10jqka.com.cn^' => 0, //同花顺
    '@@||center-h5api.m.taobao.com^' => 1, //h5页面
    '@@||app.adjust.com^' => 1, //https://github.com/AdguardTeam/AdGuardSDNSFilter/pull/186
    '@@||widget.weibo.com^' => 0, //微博外链
    '@@||uland.taobao.com^' => 1, //淘宝coupon #83
    '@@||advertisement.taobao.com^' => 1, //CNAME 被杀，导致s.click.taobao.com等服务异常
    '@@||baozhang.baidu.com^' => 1, //CNAME e.shifen.com
    '@@||tongji.edu.cn^' => 1, // 同济大学
    '@@||tongji.cn^' => 1, // 同济大学 #281
    '@@||ad.siemens.com.cn^' => 1, // 西门子下载中心
    '@@||sdkapi.sms.mob.com^' => 1, // 短信验证码 #127
    '@@||stats.gov.cn^' => 1, // 国家统计局 #144
    '@@||tj.gov.cn^' => 1,
    '@@||sax.sina.com.cn^' => 1, // #155
    '@@||api.ad-gone.com^' => 1, // #207
    '@@||news-app.abumedia.yql.yahoo.com^' => 1, // #206
    '@@||meizu.coapi.moji.com^' => 1, // #217
    '@@||track.cpau.info^' => 1, // #251
    '@@||passport.bobo.com^' => 1, // #265
    '@@||stat.jseea.cn^' => 1, // #279
    '@@||widget.intercom.io^' => 1, // #280
    '@@||track.toggl.com^' => 1, // #307
    '@@||www.msftconnecttest.com^' => 1, // #327
    '@@||storage.live.com^' => 1, // #333
    '@@||skyapi.onedrive.live.com^' => 1, // #333
    '@@||counter-strike.net^' => 1, // #332
    '@@||ftp.bmp.ovh^' => 1, // #353
    '@@||profile*.se.360.cn^' => 1, // #381
    '@@||pic.iask.cn^' => 1, // #397
    '@@||ad.jp^' => 1, // #399
    '@@||ad.azure.com^' => 1, // #399
    '@@||ad.cityu.edu.hk^' => 1, // #398
    '@@||edge-enterprise.activity.windows.com^' => 1, // #401
    '@@||edge.activity.windows.com^' => 1, // #401
    '@@||tracking-protection.cdn.mozilla.net^' => 1, // #407
    '@@||skydrivesync.policies.live.net^' => 1, // #409
    '@@||dxcloud.episerver.net^' => 1, // #418
    '@@||static3.iask.cn^' => 1, // #429
    '@@||login-ishare.iask.com.cn^' => 1, // #429
    '@@||wechat.ishare.iask.com.cn^' => 1, // #429
    '@@||dw.iask.com.cn^' => 1, // #429
    '@@||settings-win.data.microsoft.com^' => 1, // #426
    '@@||insideruser.microsoft.com^' => 1, // #426
    '@@||metrics.vrch.at^' => 1, // #440
    '@@||trackings.post.japanpost.jp^' => 1, // #441
    '@@||track.aliexpress.com^' => 1, // #446
    '@@||s.mvconf.f.360.cn^' => 1, // #462
    '@@||widget.1688.com^' => 1, // #469
    '@@||api.huangye.miui.com^' => 1, // #476
    '@@||ads.privacy.qq.com^' => 1, // #505
    '@@||future.biz.weibo.com^' => 1, // #527
    '@@||ad-putting.gw.zt-express.com^' => 1, // #534
    '@@||api.onedrive.com^' => 1, // #540
    '@@||files.1drv.com^' => 1, // #540
    '@@||skyapi.live.net^' => 1, // #540
    '@@||adm.crowdicity.com^' => 1, // #560
    '@@||iufostworldcongress-singapore.com^' => 1, // #563
    '@@||ad.ext.azure.com^' => 1, // #581
    '@@||ad.ext.azure.cn^' => 1, // #581
    '@@||torproject.org^' => 1, // #591
    '@@||api.browser.miui.com^' => 1, // #585
    '@@||pixel.prime.amazon.dev^' => 1, // #604
    '@@||ku.dk^' => 1, // #684
    '@@||track.landmarkglobal.com^' => 1, // #631
    '@@||microsoftazuresponsorships.com^' => 1, // #648
    '@@||metrics.icloud.com^' => 1, // #658
    '@@||adashx.ut.dingtalk.com^' => 1, // #662
    '@@||h-adashx.ut.dingtalk.com^' => 1, // #662
    '@@||ads.95516.com^' => 1, // #695
    '@@||track.bankcomm.com^' => 1, // #714
    '@@||tongji.koowo.cn^' => 1, // #742
    '@@||adverts.1foo.com^' => 1, // #782
    '@@||track.4px.com^' => 1, // #796
    '@@||ads.smartmidea.net^' => 1, // #807
    '@@||widget.ezidebit.com.au^' => 1, // #834
    '@@||widget.rave.office.net^' => 1, // #837
    '@@||code.sms.mob.com^' => 1, // #855
    '@@||widget.sndcdn.com^' => 1, // #839
    '@@||ad.nl^' => 1, // #841
    '@@||api.slightcommunicativeinterconnectedness.xyz^' => 1, // #873
    '@@||openxlab.org.cn^' => 1, // #876
    '@@||tracking.dpd.de^' => 1, // #877
    '@@||star.c10r.facebook.com^' => 1, // #892
    '@@||www.fbsbx.com^' => 1, // #892
    '@@||star.fallback.c10r.facebook.com^' => 1, // #892
    '@@||api.ads.tvb.com^' => 1, // #911
    '@@||img.ads.tvb.com^' => 1, // #911
    '@@||ads.cdn.tvb.com^' => 1, // #911
    '@@||ads.console.aliyun.com^' => 1, // #912
    
);

//针对上游赦免规则anti-AD不予赦免的规则，即赦免名单的黑名单
$ARR_WHITE_RULE_BLK_LIST = array(
    '@@||github.com^' => null,
    '@@||github.io^' => null,
    '@@||ads.nipr.ac.jp^' => null,
    '@@||10010.com^' => null,
    '@@||10086.cn^' => null,
    '@@||17173im.allyes.com^' => null,
    '@@||199it.com^' => null,
    '@@||1point3acres.com^' => null,
    '@@||3dpchip.com^' => null,
    '@@||4horlover.com^' => null,
    '@@||51job.com^' => null,
    '@@||520call.me^' => null,
    '@@||5278.cool^' => null,
    '@@||58b.tv^' => null,
    '@@||5qidgde.com^' => null,
    '@@||85po.com^' => null,
    '@@||85porn.net^' => null,
    '@@||99wb
