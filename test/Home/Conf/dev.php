<?php 
return array(
    /* 数据库设置 */
    'DB_HOST'               =>  'bcg02.bwe.io', // 服务器地址
    'DB_NAME'               =>  'oi_base',          // 数据库名
    'DB_USER'               =>  'oi',      // 用户名
    'DB_PWD'                =>  'Qztw77Wd4uHApd3T',          // 密码
    'DB_DEBUG'              =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE'       =>  FALSE,        // 启用字段缓存
    'LOCAL_DB_STR'  => 'mysql://root:root@127.0.0.1:3306/pmi_base',
    'LOCAL_DB_TEST'  => 'mysql://payne:sh@BZ1hnC@192.168.1.242:3306/test_oi_base',
    'DATA_CACHE_TYPE'=>'Redis',//默认动态缓存为Redis
    'REDIS_RW_SEPARATE' => true, //Redis读写分离 true 开启
    'REDIS_HOST'=>'127.0.0.1', //redis服务器ip，多台用逗号隔开；读写分离开启时，第一台负责写，其它[随机]负责读；
    'REDIS_PORT'=>'6379',//端口号
    'REDIS_TIMEOUT'=>'300',//超时时间
    'REDIS_PERSISTENT'=>false,//是否长连接 false=短连接

    "YES"=>"590dd167d5414",
    "NO"=>"590dd1c350c37",
    'SITES' => [
        "PPUS" =>[
            'site_id'=>'59658c5c9aab9',
            'type'=>'merchant',
            'site_url' =>'https://www.promospro.com/',
            'country'=>'United States of America (USA)',
            'language'=>'en-us'
        ],
        "SL" => [
            'site_id'=>'595520f0256a3',
            'type'=>'merchant',
            'site_url' =>'https://www.savelution.com/',
            'country'=>'Great Britain (United Kingdom; England)',
            'language'=>'en-us'
        ],
        "PPAU" => [
            'site_id'=>'596756c06a230',
            'type'=>'merchant',
            'site_url' =>'https://www.ozdiscount.net/',
            'country'=>'Australia',
            'language'=>'en'
        ],
        "PPIN" => [
            'site_id'=>'596758300c3aa',
            'type'=>'merchant',
            'site_url' =>'http://in.promopro.com/',
            'country'=>'India',
            'language'=>'en'
        ],
        "PPCH" => [
            'site_id'=>'596758f029529',
            'type'=>'merchant',
            'site_url' =>'http://ch.promopro.com/',
            'country'=>'Switzerland',
            'language'=>'de-ch'
        ],
        "CSDE" => [
            'site_id'=>'596759919ab26',
            'type'=>'merchant',
            'site_url' =>'https://www.allecodes.de/',
            'country'=>'Germany',
            'language'=>'en'
        ],
        "CSFR" => [
            'site_id'=>'595e063da6914',
            'type'=>'merchant',
            'site_url' =>'https://www.codespromofr.com/',
            'country'=>'France',
            'language'=>'fr'
        ],
        "SSEN" => [
            'site_id'=>'59675b1665136',
            'type'=>'term',
            'site_url' =>'https://www.fyvor.com/',
            'country'=>'United States of America (USA)',
            'language'=>'en'
        ],
        "SSFR" => [
            'site_id'=>'59675bae49503',
            'type'=>'term',
            'site_url' =>'https://www.frcodespromo.com/',
            'country'=>'France',
            'language'=>'fr'
        ],
        "SSDE" => [
            'site_id'=>'59675b67c8133',
            'type'=>'term',
            'site_url' =>'http://de.fyvor.com/',
            'country'=>'Germany',
            'language'=>'en'
        ],
        "SSUK" => [
            'site_id'=>'59675bffc4026',
            'type'=>'term',
            'site_url' =>'http://www.couponwitme.com/',
            'country'=>'Great Britain (United Kingdom; England)',
            'language'=>'en'
        ],
        'VogYou' => [
            'site_id'=>'59675cb4594a2',
            'type'=>'merchant',
            'site_url' =>'',
            'country'=>'Germany',
            'language'=>'en'
        ],
        'CN' => [
            'site_id'=>'59675cfb9a555',
            'type'=>'merchant',
            'site_url' =>'',
            'country'=>'Germany',
            'language'=>'en'
        ],
    ],
    'SEO'=>[
        '598fb69987ea3'=>'URL',
        '598fb6a6d2e04'=>'Meta Title',
        '598fb6c19fdd4'=>'Meta Description',
        '598fb6d15e5b0'=>'Meta Keyword',
        '598fb6e1d1e15'=>'H1',
        '598fb6e717d3a'=>'H2',
        '598fb6ebb9ac1'=>'H3',
        '598fb6f48de63'=>'H4',
        '59ad664826ce0'=>'Active Promo Count',
        '59ad66920429b'=>'Active Code Count',
        '59ad67726eef0'=>'Last Update Time',
    ]

);