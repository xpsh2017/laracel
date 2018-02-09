<?php
namespace Home\Model;
use Think\Model;
class SamplingContentModel extends SamplingModel {
    protected $tableName = 'bo_entity_promo_mk_site_sampling_content';
    public $field = ['PAGE_COUNT', 'PAGE_COUNT_NEW', 'PAGE_COUNT_DELETE',];
    public $contentBField = ['PAGE_COUNT_NOUPD_IN_30D', 'PAGE_COUNT_NOUPD_IN_15D', 'PAGE_COUNT_NOUPD_IN_7D'];
    public $contentCField = ['PAGE_COUNT_PROMOCNT_LE_3', 'PAGE_COUNT_PROMOCNT_LE_5', 'PAGE_COUNT_PROMOCNT_LE_10', 'PAGE_COUNT_NOCODE', 'PAGE_COUNT_HADCODE_IN_30D'];


    public function getData($map, $termFlag)
    {
        $data = parent::getData($map);
        $table = $termFlag? 'bo_entity_promo_mk_site_term_page_sampling_content': 'bo_entity_promo_mk_site_merchant_page_sampling_content';
        $sql = sprintf("SELECT count(*) as `PAGE_COUNT`  FROM `%s` AS sc LEFT JOIN `bo_entity_page` AS g ON sc.`COUPON_PAGE_ID` = g.`UUID` WHERE g.`SITE_ID` = '%s' AND sc.`DATE` = '%s'", $table, $map['COUPON_SITE_ID'], $map['DATE']);
        $count = $this->query($sql);
        $data['PAGE_COUNT'] = $count[0]['PAGE_COUNT'];
        return $data;
    }
    public function getDataLocal($map, $termFlag)
    {
        $model = $this->db(1, C('LOCAL_DB_STR'));
        $map['TERM_FLAG'] = $termFlag ? 1 : 0;
        $data = $model->table('mk_basic_data')->where($map)->find();
        $this->db(0);
        unset($data['COUPON_SITE_ID']);
        unset($data['DATE']);
        unset($data['TERM_FLAG']);
        return $data;
    }
    public function getList($map)
    {
        $model = $this->db(1, C('LOCAL_DB_STR'));
        $map['TERM_FLAG'] = isTerm($map['COUPON_SITE_ID']) ? 1 : 0;
        $data = $model->table('mk_basic_data')->where($map)->select();
        $this->db(0);
        foreach ($data as $key => $value) {
            unset($data[$key]['COUPON_SITE_ID']);
            unset($data[$key]['TERM_FLAG']);
        }
        return $data;
    }

    public function getCouponContentData($map, $termFlag = FALSE)
    {
        $table = $termFlag? 'bo_entity_promo_mk_site_term_page_sampling_content': 'bo_entity_promo_mk_site_merchant_page_sampling_content';
        $sql = sprintf("SELECT sc.`PROMO_COUNT_ACTIVE`, sc.`COUPON_COUNT_ACTIVE`, sc.`LAST_PROMO_ADD_TIME`,sc.`HAS_COUPON_DAYS_IN_LASTYEAR30DAY` FROM `%s` AS sc LEFT JOIN `bo_entity_page` AS g ON sc.`COUPON_PAGE_ID` = g.`UUID` WHERE g.`SITE_ID` = '%s' AND sc.`DATE` = '%s'", $table, $map['COUPON_SITE_ID'], $map['DATE']);
        //echo $sql;die; 
        $data = $this->query($sql);
        $computedData['PAGE_COUNT_UPD_IN_3D'] = 0;
        $computedData['PAGE_COUNT_UPD_IN_3_7D'] = 0;
        $computedData['PAGE_COUNT_UPD_IN_7_15D'] = 0;
        $computedData['PAGE_COUNT_UPD_IN_15_30D'] = 0;
        $computedData['PAGE_COUNT_UPD_MT_30D'] = 0;
        $computedData['PAGE_COUNT_COUPON_COUNT_IN_3'] = 0;
        $computedData['PAGE_COUNT_COUPON_COUNT_IN_3_5'] = 0;
        $computedData['PAGE_COUNT_COUPON_COUNT_IN_5_10'] = 0;
        $computedData['PAGE_COUNT_COUPON_COUNT_IN_10_20'] = 0;
        $computedData['PAGE_COUNT_COUPON_COUNT_MT_20'] = 0;
        $computedData['PAGE_COUNT_NOCODE'] = 0;
        $computedData['PAGE_COUNT_HADCODE_IN_30D'] = 0;
        foreach ($data as $lp) {
            # 促销数量区间判断
            $promoCount = $lp['PROMO_COUNT_ACTIVE'];
            $couponCount = $lp['COUPON_COUNT_ACTIVE'];
            if($couponCount == 0)
                    $computedData['PAGE_COUNT_NOCODE'] ++;
            if($promoCount <= 3)
            {
                $computedData['PAGE_COUNT_COUPON_COUNT_IN_3'] ++;
            }elseif ($promoCount > 3 && $promoCount <= 5) {
                $computedData['PAGE_COUNT_COUPON_COUNT_IN_3_5'] ++;
            }elseif ($promoCount > 5 && $promoCount <= 10) {
                $computedData['PAGE_COUNT_COUPON_COUNT_IN_5_10'] ++;
            }elseif ($promoCount > 10 && $promoCount <= 20) {
                $computedData['PAGE_COUNT_COUPON_COUNT_IN_10_20'] ++;
            }elseif ($promoCount > 20) {
                $computedData['PAGE_COUNT_COUPON_COUNT_MT_20'] ++ ;
            }

            #更新时间判断
            $lastAddTime = $lp['LAST_PROMO_ADD_TIME'];
            $diffDays = floor((strtotime($map['DATE']) - strtotime($lastAddTime) ) / 86400);
            if($diffDays <= 3)
            {
                $computedData['PAGE_COUNT_UPD_IN_3D'] ++;
            }elseif ($diffDays > 3 && $diffDays <= 7) {
                $computedData['PAGE_COUNT_UPD_IN_3_7D'] ++;
            }elseif ($diffDays > 7 && $diffDays <= 15) {
                $computedData['PAGE_COUNT_UPD_IN_7_15D'] ++;
            }elseif ($diffDays > 15 && $diffDays <= 30) {
                $computedData['PAGE_COUNT_UPD_IN_15_30D'] ++;
            }elseif($diffDays > 30){
                $computedData['PAGE_COUNT_UPD_MT_30D'] ++;
            }
            # 30天内是否有code
            $hasCodeDays = $lp['HAS_COUPON_DAYS_IN_LASTYEAR30DAY'];
            if($hasCodeDays)
                $computedData['PAGE_COUNT_HADCODE_IN_30D'] ++;
        }
        return $computedData;
    }
    public function getCouponContentDataLocal($map, $termFlag = FALSE)
    {
        $model = $this->db(1, C('LOCAL_DB_STR'));
        $map['TERM_FLAG'] = $termFlag ? 1 : 0;
        $data = $model->table('mk_coupon_content_status')->where($map)->find();
        $this->db(0);
        unset($data['COUPON_SITE_ID']);
        unset($data['DATE']);
        unset($data['TERM_FLAG']);
        return $data;
    }
    public function getPageList($map, $termFlag = FALSE)
    {
        $table = $termFlag? 'bo_entity_promo_mk_site_term_page_sampling_content': 'bo_entity_promo_mk_site_merchant_page_sampling_content';
        $sql = sprintf("SELECT g.`OBJNAME`,sc.`PROMO_COUNT_ACTIVE`,sc.`LAST_PROMO_ADD_TIME`,sc.`HAS_COUPON_DAYS_IN_LASTYEAR30DAY` FROM `bo_entity_promo_mk_site_merchant_page_sampling_content` AS sc LEFT JOIN `bo_entity_page` AS g ON sc.`COUPON_PAGE_ID` = g.`UUID` WHERE g.`SITE_ID` = '%s' AND sc.`DATE` = '%s'", $map['COUPON_SITE_ID'], $map['DATE']);
        $data = $this->query($sql);
        $computedData['PAGE_UPD_IN_3D'] = [];
        $computedData['PAGE_UPD_IN_3_7D'] = [];
        $computedData['PAGE_UPD_IN_7_15D'] = [];
        $computedData['PAGE_UPD_IN_15_30D'] = [];
        $computedData['PAGE_UPD_MT_30D'] = [];
        $computedData['PAGE_COUPON_COUNT_IN_3'] = [];
        $computedData['PAGE_COUPON_COUNT_IN_3_5'] = [];
        $computedData['PAGE_COUPON_COUNT_IN_5_10'] = [];
        $computedData['PAGE_COUPON_COUNT_IN_10_20'] = [];
        $computedData['PAGE_COUPON_COUNT_MT_20'] = [];
        $computedData['PAGE_NOCODE'] = [];
        $computedData['PAGE_HADCODE_IN_30D'] = [];
        foreach ($data as $lp) {
            # 促销数量区间判断
            $promoCount = $lp['PROMO_COUNT_ACTIVE'];
            if($promoCount <= 3)
            {
                if($promoCount == 0)
                {
                    $computedData['PAGE_NOCODE'] [] = $lp;
                }
                $computedData['PAGE_UPD_IN_3D'][] = $lp;
            }elseif ($promoCount > 3 && $promoCount <= 5) {
                $computedData['PAGE_COUPON_COUNT_IN_3_5'][] = $lp;
            }elseif ($promoCount > 5 && $promoCount <= 10) {
                $computedData['PAGE_COUPON_COUNT_IN_5_10'][] =$lp;
            }elseif ($promoCount > 10 && $promoCount <= 20) {
                $computedData['PAGE_COUPON_COUNT_IN_10_20'][] = $lp;
            }elseif ($promoCount > 20) {
                $computedData['PAGE_COUPON_COUNT_MT_20'][] = $lp;
            }

            #更新时间判断
            $lastAddTime = $lp['LAST_PROMO_ADD_TIME'];
            $diffDays = floor((strtotime($map['DATE']) - strtotime($lastAddTime) ) / 86400);
            if($diffDays <= 3)
            {
                $computedData['PAGE_UPD_IN_3D'][] = $lp;
            }elseif ($diffDays > 3 && $diffDays <= 7) {
                $computedData['PAGE_UPD_IN_3_7D'][] = $lp;
            }elseif ($diffDays > 7 && $diffDays <= 15) {
                $computedData['PAGE_UPD_IN_7_15D'][] = $lp;
            }elseif ($diffDays > 15 && $diffDays <= 30) {
                $computedData['PAGE_UPD_IN_15_30D'][] = $lp;
            }elseif($diffDays > 30){
                $computedData['PAGE_UPD_MT_30D'][] = $lp;
            }
            # 30天内是否有code
            $hasCodeDays = $lp['HAS_COUPON_DAYS_IN_LASTYEAR30DAY'];
            if($hasCodeDays)
            {
                $computedData['PAGE_HADCODE_IN_30D'][] = $lp;
            }
        }
        return $computedData;
    }
    public function getListByDay($map)
    {
        $data = [];
        $termFlag = isTerm($map['COUPON_SITE_ID']);
        $dateRange = explode(",", $map['DATE'][1]);
        $data = [];
        $pageType = 'sample_content_p2';
        for ($i=$dateRange[0];strtotime($i) <= strtotime($dateRange[1]);$i = date('Y-m-d', strtotime($i. ' +1 day'))) { 
            $tmpMap['COUPON_SITE_ID'] = $map['COUPON_SITE_ID'];
            $tmpMap['DATE'] = $i;
            $cacheFlag = existKey($tmpMap['COUPON_SITE_ID'], $i, $pageType);
            if($cacheFlag)
                $list = $this->getCouponContentDataLocal($tmpMap, $termFlag);
            else
                $list = $this->getCouponContentData($tmpMap, $termFlag);
            $list = array_merge(['DATE' => $i], $list);
            $data[] = $list;
        }
        return $data;
    }
    public function getListByDayV2($map, $rank)
    {
        $data = [];
        $termFlag = isTerm($map['COUPON_SITE_ID']);
        $dateRange = explode(",", $map['DATE'][1]);
        $data = [];
        $pageType = 'sample_requirement';
        for ($i=$dateRange[0];strtotime($i) <= strtotime($dateRange[1]);$i = date('Y-m-d', strtotime($i. ' +1 day'))) { 
            $tmpMap['COUPON_SITE_ID'] = $map['COUPON_SITE_ID'];
            $tmpMap['DATE'] = $i;
            $cacheFlag = existKey($tmpMap['COUPON_SITE_ID'], $i, $pageType);
            if($cacheFlag)
                $list = $this->getDataV2Local($tmpMap, $termFlag);
            else
                $list = $this->getDataV2($tmpMap, $termFlag);
            $list = $list[$rank -1];
            $list = array_merge(['DATE' => $i], $list);
            $data[] = $list;
        }
        return $data;
    }
    public function getDataV2($map, $termFlag = FALSE, $flag = 0)
    {
        $table = $termFlag ? 'term' : 'merchant';
        $sql = sprintf("SELECT 
      /**sb.`COUPON_PAGE_ID`,**/ p.`OBJNAME`, sb.`CONTENT_COUPON_COUNT`, sb.`CONTENT_PROMO_COUNT`, sb.`CONTENT_PROMO_UPDATETIME_OFFSET`,
      sc.`COUPON_COUNT_ACTIVE`, sc.`PROMO_COUNT_ACTIVE`, sc.`LAST_PROMO_ADD_TIME`
    FROM
      `bo_entity_page` AS p  
      LEFT JOIN 
      `bo_entity_promo_mk_site_%s_page_sampling_benchmark` AS sb 
        ON (sb.`COUPON_PAGE_ID` = p.`UUID`)
      LEFT JOIN 
      `bo_entity_promo_mk_site_%s_page_sampling_content` AS sc
        ON (p.`UUID` = sc.`COUPON_PAGE_ID`)
    WHERE p.`SITE_ID` = '%s' AND sc.`DATE` = '%s' AND sb.`DATE` = '%s'  ORDER BY sb.page_value DESC ", $table, $table, $map['COUPON_SITE_ID'], $map['DATE'], $map['DATE']);
        $list = $this->query($sql);
        foreach ($list as $k => $v) {
            $list[$k]['task'] = 0;
            if($v['CONTENT_COUPON_COUNT'] && !$v['COUPON_COUNT_ACTIVE'])
            {
                $list[$k]['task'] = 1;
            }
            if($v['PROMO_COUNT_ACTIVE'] < $v['CONTENT_PROMO_COUNT'])
            {
                $list[$k]['task'] = 2;
            }
            $offSetHours = $v['CONTENT_PROMO_UPDATETIME_OFFSET'];
            $trueoffHours = floor((strtotime($siteDate. '23:59:59') - strtotime($v['LAST_PROMO_ADD_TIME'])) / 3600);
            if($trueoffHours > $offSetHours)
            {
                $list[$k]['task'] = 3;
            }
        }
        $count = count($list);
        $interList = [
            [
                'INTER_NAME' => 'Top 10%',
                'interval' => 0.1,
            ],
            [
                'INTER_NAME' => '10% - 30%',
                'interval' => 0.3,
            ],
            [
                'INTER_NAME' => '30% - 60%',
                'interval' => 0.6,
            ],
            [
                'INTER_NAME' => '60% - 100%',
                'interval' => 1,
            ]
        ];
        $merchantList = [];
        $test = [];
        foreach ($interList as $k => $v) {
            $tmpCount = floor($count * $v['interval']);
            if($k == 0)
                $start = 0;
            else
                $start = $tmpCount;

            $test[] = $tmpCount;
            $limit = $tmpCount - $start;
            $tmpList = array_slice($list, $start, $limit);
            $interList[$k]['Total_Page_Count'] = count($tmpList);
            $interList[$k]['Has_Requirement_Page_Count'] = 0;
            $interList[$k]['NO_Requirement_Page_Count'] = 0;
            foreach ($tmpList as $kk => $vv) {
                    if($vv['task'])
                    {
                        if($flag && ($flag == ($k +1)))
                        {
                            unset($vv['task']);
                            $merchantList[] = $vv;
                        }
                        $interList[$k]['Has_Requirement_Page_Count']++;
                    }
                    else
                    {
                        $interList[$k]['NO_Requirement_Page_Count']++;
                    }
            }
            unset($interList[$k]['interval']);
            unset($interList[$k]['task']);
        }
        // echo json_encode($test);die;
        return ($flag) ? $merchantList: $interList;
    }
    public function getDataV2Local($map, $termFlag = FALSE)
    {
        $model = $this->db(1, C('LOCAL_DB_STR'));
        $map['TERM_FLAG'] = $termFlag ? 1 : 0;
        $data = $model->table('mk_content_requirement_rate')->where($map)->order('SORT_ORDER')->select();
        foreach ($data as $k => $v) {
            unset($data[$k]['COUPON_SITE_ID']);
            unset($data[$k]['DATE']);
            unset($data[$k]['TERM_FLAG']);
        }
        return $data;
    }
}