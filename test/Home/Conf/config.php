<?php
return array(
    /* 站点设置 */
    'SITE_NAME' => 'PMI',
    'SITE_VERSION' => 'v1.1.6',
    'STATIC_VERSION' => '2017091601',
    /* 数据库设置 */
    'DB_TYPE'               =>  'MYSQL',     // 数据库类型
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PARAMS'    =>    array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),
    /* URL */
    'URL_HTML_SUFFIX'       =>  '',  // URL伪静态后缀设置
    /* Site Config */
    'SITE_LIST' => [
        [
            'SITE_NAME' => 'PPUS',
            'SITE_UUID' => '59658c5c9aab9'
        ],
        [
            'SITE_NAME' => 'SL',
            'SITE_UUID' => '595520f0256a3'
        ],
        [
            'SITE_NAME' => 'PPAU',
            'SITE_UUID' => '596756c06a230'
        ],
        [
            'SITE_NAME' => 'PPIN',
            'SITE_UUID' => '596758300c3aa'
        ],
        [
            'SITE_NAME' => 'PPCH',
            'SITE_UUID' => '596758f029529'
        ],
        [
            'SITE_NAME' => 'CSDE',
            'SITE_UUID' => '596759919ab26'
        ],
        [
            'SITE_NAME' => 'CSFR',
            'SITE_UUID' => '595e063da6914'
        ],
        [
            'SITE_NAME' => 'SSEN',
            'SITE_UUID' => '59675b1665136'
        ],
        [
            'SITE_NAME' => 'SSFR',
            'SITE_UUID' => '59675bae49503'
        ],
        [
            'SITE_NAME' => 'SSDE',
            'SITE_UUID' => '59675b67c8133'
        ],
        [
            'SITE_NAME' => 'SSUK',
            'SITE_UUID' => '59675bffc4026'
        ],
        [
            'SITE_NAME' => 'VogYou',
            'SITE_UUID' =>'59675cb4594a2'
        ],
        [
            'SITE_NAME' => 'CN',
            'SITE_UUID' => '59675cfb9a555'
        ],
    ],
    'TERM_LIST' => ['SSEN', 'SSFR', 'SSUK', 'SSDE'],
    'DEFAUTL_SITE' => 'PPUS',
    'DEFAUTL_DATE' => '- 2 days',

    'SITES' => [
        "PPUS" =>[
            'site_id'=>'59658c5c9aab9',
            'type'=>'merchant',
            'site_url' =>'https://www.promospro.com/',
        ],
        "SL" => [
            'site_id'=>'595520f0256a3',
            'type'=>'merchant',
            'site_url' =>'https://www.savelution.com/',
        ],
        "PPAU" => [
            'site_id'=>'596756c06a230',
            'type'=>'merchant',
            'site_url' =>'https://www.ozdiscount.net/',
        ],
        "PPIN" => [
            'site_id'=>'596758300c3aa',
            'type'=>'merchant',
            'site_url' =>'http://in.promopro.com/',
        ],
        "PPCH" => [
            'site_id'=>'596758f029529',
            'type'=>'merchant',
            'site_url' =>'http://ch.promopro.com/',
        ],
        "CSDE" => [
            'site_id'=>'596759919ab26',
            'type'=>'merchant',
            'site_url' =>'https://www.allecodes.de/',
        ],
        "CSFR" => [
            'site_id'=>'595e063da6914',
            'type'=>'merchant',
            'site_url' =>'https://www.codespromofr.com/',
        ],
        "SSEN" => [
            'site_id'=>'59675b1665136',
            'type'=>'term',
            'site_url' =>'https://www.fyvor.com/',
        ],
        "SSFR" => [
            'site_id'=>'59675bae49503',
            'type'=>'term',
            'site_url' =>'https://www.frcodespromo.com/',
        ],
        "SSDE" => [
            'site_id'=>'59675b67c8133',
            'type'=>'term',
            'site_url' =>'http://de.fyvor.com/',
        ],
        "SSUK" => [
            'site_id'=>'59675bffc4026',
            'type'=>'term',
            'site_url' =>'http://www.couponwitme.com/',
        ],
        'VogYou' => [
            'site_id'=>'59675cb4594a2',
            'type'=>'merchant',
            'site_url' =>'',
        ],
        'CN' => [
            'site_id'=>'59675cfb9a555',
            'type'=>'merchant',
            'site_url' =>'',
        ],
    ],
    'PANEL_LIST' => [
        'Content Basic Status' => '内容基本状态',
        'Coupon Content Status' => '促销内容状态',
        'GA Traffic Status' => 'GA流量基本状态',
        'BR Page Count' => 'BR的页面分布',
        'BR Session Count' => 'BR的流量分布',
        'Google Performance' => 'Google表现',
        'Content Requirement Rate' => '内容需求达标率',
        'Aff_Status' => '联盟状态',
        'SEO_REVENUE' => 'SEO收入情况'
    ],
    'SUB_PANEL_LIST' => [
        'sample_content_p1' => '内容基本状态日报',
        'sample_content_p2' => '促销内容状态日报',
        'sample_gsc' => 'Google表现日报',
        'sample_ga' => 'GA流量基本状态日报',
        'sample_ga_br_page' => 'BR的页面分布日报',
        'sample_ga_br_session' => 'BR的流量分布',
        'sample_requirement_1' => 'Top 10% 内容需求达标率日报',
        'REQUIREMENT_1' => 'Top 10% 内容需求达标率-未达标商家列表',
        'sample_requirement_2' => '10% - 30% 内容需求达标率日报',
        'REQUIREMENT_2' => '10% - 30% 内容需求达标率-未达标商家列表',
        'sample_requirement_3' => '30% - 60% 内容需求达标率日报',
        'REQUIREMENT_3' => '30% - 60% 内容需求达标率-未达标商家列表',
        'sample_requirement_4' => '30% - 60% 内容需求达标率日报',
        'REQUIREMENT_4' => '30% - 60% 内容需求达标率-未达标商家列表'
    ],
    'SITEDAILY_METRIC_LIST' => [
        'PAGE_COUNT' => '页面数(商家/Term)',
        'PAGE_COUNT_NEW' => '新增页面数',
        'PAGE_COUNT_DELETE' => '删除页面数',
        'PAGE_COUNT_NOUPD_IN_30D' => '30天内未更新的页面数（>30)',
        'PAGE_COUNT_NOUPD_IN_15D' => '15天内未更新的页面数（>15）',
        'PAGE_COUNT_NOUPD_IN_7D' => '7天内未更新的页面数（>7)',
        'PAGE_COUNT_PROMOCNT_LE_3' => '促销数量少于3条的页面数',
        'PAGE_COUNT_PROMOCNT_LE_5' => '促销数量少于5条的页面数',
        'PAGE_COUNT_PROMOCNT_LE_10' => '促销数量少于10条的页面数',
        'PAGE_COUNT_NOCODE' => '没有Code的页面数',
        'PAGE_COUNT_HADCODE_IN_30D' => '30天内曾有但现无Code的页面数',
        'PAGE_COUNT_HAS_SESSION' => '获得流量的页面数',
        'BOUNSE_RATE' => '平均BR',
        'SESSION' => '会话数',
        'BR_LT_20_PAGE_COUNT' => 'BR小于20%的页面数',
        'BR_MT_20_PAGE_COUNT' => 'BR大于20%的页面数',
        'BR_MT_30_PAGE_COUNT' => 'BR大于30%的页面数',
        'BR_MT_50_PAGE_COUNT' => 'BR大于50%的页面数',
        'BR_MT_70_PAGE_COUNT' => 'BR大于70%的页面数',
        'BR_LT_20_SESSEION_COUNT' => 'BR小于20%的会话数',
        'BR_MT_20_SESSEION_COUNT' => 'BR大于20%的会话数',
        'BR_MT_30_SESSEION_COUNT' => 'BR大于30%的会话数',
        'BR_MT_50_SESSEION_COUNT' => 'BR大于50%的会话数',
        'BR_MT_70_SESSEION_COUNT' => 'BR大于70%的会话数',
        'INDEX_PAGE_NUM' => '收录页面数',
        'AVG_POS' => '平均排名',
        'IMPRESSION_MT_5_PAGE_NUM' => '获得5次及以上展示的页面数',
        'CLICK_MT_5_PAGE_NUM' => '获得5次及以上点击的页面数',
        'POS_TOP_3_PAGE_NUM' => '平均排名前3的页面数',
        'POS_TOP_5_PAGE_NUM' => '平均排名前5的页面数',
        'POS_TOP_10_PAGE_NUM' => '平均排名前10的页面数',
        'INTER_NAME' => '页面价值区间',
        'Total_Page_Count' => '商家总数',
        'Has_Requirement_Page_Count' => '产生需求的页面数',
        'Done_Requirement_Page_Count' => '完成需求的页面数',
        'Reaching_Rate' => '需求达标率',
        'Update_Frequency' => '更新率',
        'PAGE_COUNT_UPD_IN_3D' => '0-3天内更新的页面数',
        'PAGE_COUNT_UPD_IN_3_7D' => '3-7天内更新的页面数',
        'PAGE_COUNT_UPD_IN_7_15D' => '7-15天内更新的页面数',
        'PAGE_COUNT_UPD_IN_15_30D' => '15-30天内更新的页面数',
        'PAGE_COUNT_UPD_MT_30D' => '30天以上更新的页面数',
        'PAGE_COUNT_COUPON_COUNT_IN_3' => '促销数量0-3条的页面数',
        'PAGE_COUNT_COUPON_COUNT_IN_3_5' => '促销数量3-5条的页面数',
        'PAGE_COUNT_COUPON_COUNT_IN_5_10' => '促销数量5-10条的页面数',
        'PAGE_COUNT_COUPON_COUNT_IN_10_20' => '促销数量10－20条的页面数',
        'PAGE_COUNT_COUPON_COUNT_MT_20' => '促销数量大于20条的页面数',
        'PAGE_COUNT_NOCODE' => '没有Code的页面数',
        'PAGE_COUNT_HADCODE_IN_30D' => '30天内曾有但现无Code的页面数',
        'Max_REVENUE' => '单个页面最大收入',
        'Max_REVENUE_LP' => '单个页面最大收入LP',
        'PAGE_COUNT_HAS_REVENUE' => '有收入的页面数',
        'TOTAL_REVENUE' => '整站总收入',
        'AFF_COUNT' => '有联盟的页面数',
        'MAIN_AFF_COUNT' => '有主联盟的页面数',
        'DATE' => '日期',
        'OBJNAME' => '页面',
        'PROMO_COUNT_ACTIVE' => '有效促销数',
        'LAST_PROMO_ADD_TIME' => '最后添加促销时间',
        'HAS_COUPON_DAYS_IN_LASTYEAR30DAY' => '30天内是否有促销',
        'OBJNAME' => '商家页面',
        'CONTENT_PROMO_COUNT' => '促销数量下限',
        'PROMO_COUNT_ACTIVE' => '实际促销数量',
        'CONTENT_COUPON_COUNT' => '是否应有Code',
        'CONTENT_PROMO_UPDATETIME_OFFSET' => '更新时间控制值',
        'COUPON_COUNT_ACTIVE' => '当前页面有无Code',
        'NO_Requirement_Page_Count' => '达标页面数',
        'DA_BIAO_LV' => '达标率'
    ],
);