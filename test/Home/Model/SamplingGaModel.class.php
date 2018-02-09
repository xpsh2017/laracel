<?php
namespace Home\Model;
use Think\Model;
class SamplingGaModel extends SamplingModel {
    protected $tableName = 'bo_entity_promo_mk_site_sampling_ga';
    public $field = ['PAGE_COUNT_HAS_SESSION', 'BOUNSE_RATE', 'SESSION'];
    public function getData($map)
    {
        $data = parent::getData($map);
        $uuid = $map['COUPON_SITE_ID'];
        $date = $map['DATE'];
        $sql = "SELECT count(*) as `PAGE_COUNT_HAS_SESSION`, SUM(`SESSION`) AS SESSION FROM `bo_entity_promo_mk_site_page_sampling_ga` AS sg LEFT JOIN `bo_entity_page` AS p ON sg.`COUPON_PAGE_ID` = p.`UUID` WHERE p.`SITE_ID` = '{$uuid}' AND sg.`DATE` = '{$date}'";
        $count = $this->query($sql);
        $data['PAGE_COUNT_HAS_SESSION'] = $count[0]['PAGE_COUNT_HAS_SESSION'];
        $data['SESSION'] = $count[0]['SESSION'];
        return $data;
    }
    public function getDataLocal($map)
    {
        $model = $this->db(1, C('LOCAL_DB_STR'));
        $data = $model->table('mk_google_performance')->where($map)->find();
        $this->db(0);
        unset($data['COUPON_SITE_ID']);
        unset($data['DATE']);
        return $data;
    }
    public function computeData($map, $returnType = 'ALL')
    {
        $uuid = $map['COUPON_SITE_ID'];
        $date = $map['DATE'];
        $sql = "SELECT BOUNSE_RATE,SESSION FROM `bo_entity_promo_mk_site_page_sampling_ga` AS sg LEFT JOIN `bo_entity_page` AS p ON sg.`COUPON_PAGE_ID` = p.`UUID` WHERE p.`SITE_ID` = '{$uuid}' AND sg.`DATE` = '{$date}'";
        $data = $this->query($sql);
        return $this->compute($data, $returnType);
    }
    public function computeDataLocal($map)
    {
        $model = $this->db(1, C('LOCAL_DB_STR'));
        $data = $model->table('mk_ga_br_status')->where($map)->find();
        $this->db(0);
        unset($data['COUPON_SITE_ID']);
        unset($data['DATE']);
        $dataA = [];
        $dataB = [];
        foreach ($data as $k => $v) {
            if(strpos($k, 'PAGE') !== FALSE)
                $dataA[$k] = $v;
            else
                $dataB[$k] = $v;
        }
        // var_dump([$dataA, $dataB]);die;
        return [$dataA, $dataB];
    }
    public function compute($data, $returnType = 'ALL')
    {
        $computedAData['BR_LT_20_PAGE_COUNT'] = 0;
        $computedAData['BR_MT_20_PAGE_COUNT'] = 0;
        $computedAData['BR_MT_30_PAGE_COUNT'] = 0;
        $computedAData['BR_MT_50_PAGE_COUNT'] = 0;
        $computedAData['BR_MT_70_PAGE_COUNT'] = 0;
        $computedBData['BR_LT_20_SESSEION_COUNT'] = 0;
        $computedBData['BR_MT_20_SESSEION_COUNT'] = 0;
        $computedBData['BR_MT_30_SESSEION_COUNT'] = 0;
        $computedBData['BR_MT_50_SESSEION_COUNT'] = 0;
        $computedBData['BR_MT_70_SESSEION_COUNT'] = 0;
        foreach ($data as $k => $v) {
            $tmpBr = $v['BOUNSE_RATE'] * 100;
            if($tmpBr < 20)
            {
                $computedAData['BR_LT_20_PAGE_COUNT']++;
                $computedBData['BR_LT_20_SESSEION_COUNT'] += $v['SESSION'];
            }
            if($tmpBr >= 20)
            {
                $computedAData['BR_MT_20_PAGE_COUNT'] ++;
                $computedBData['BR_MT_20_SESSEION_COUNT'] += $v['SESSION'];
            }
            if($tmpBr > 30)
            {
                $computedAData['BR_MT_30_PAGE_COUNT'] ++;
                $computedBData['BR_MT_30_SESSEION_COUNT'] += $v['SESSION'];   
            }
            if($tmpBr > 50)
            {
                $computedAData['BR_MT_50_PAGE_COUNT'] ++;
                $computedBData['BR_MT_50_SESSEION_COUNT'] += $v['SESSION'];   
            }
            if($tmpBr > 70)
            {
                $computedAData['BR_MT_70_PAGE_COUNT'] ++;
                $computedBData['BR_MT_70_SESSEION_COUNT'] += $v['SESSION'];   
            }
        }
        $computedData = array_merge($computedAData, $computedBData);
        if($returnType == 'ALL')
        {
            return $computedData;    
        }elseif ($returnType == 'A') {
            return $computedAData;
        }elseif($returnType == 'B'){
            return $computedBData;
        }else{
            //var_dump([$computedAData, $computedBData]);die;
            return [$computedAData, $computedBData];
        }
        
    }
    public function getList($map, $fieldType='ALL')
    {
        if($fieldType == 'ALL')
        {
            // $data = parent::getList($map, $fieldType);
            $model = $this->db(1, C('LOCAL_DB_STR'));
            $data = $this->table('mk_google_performance_v2')->field($this->field)->where($map)->select();
            return $data;
        }else{
            $uuid = $map['COUPON_SITE_ID'];
            $dateRange = explode(",", $map['DATE'][1]);
            $data = [];
            $pageType = 'sample_ga_br';
            $returnType = str_replace('Computed', '', $fieldType);
            for ($i=$dateRange[0];strtotime($i) <= strtotime($dateRange[1]);$i = date('Y-m-d', strtotime($i. ' +1 day'))) { 
                $cacheFlag = existKey($uuid, $i, $pageType);
                $tmpMap['COUPON_SITE_ID'] = $uuid;
                $tmpMap['DATE'] = $i;
                if($cacheFlag)
                {
                    $list = $this->computeDataLocal($tmpMap);
                    if($returnType == 'A')
                        $list = $list[0];
                    else
                        $list = $list[1];
                }else{
                    $list = $this->computeData($tmpMap, $returnType);
                }
                $list = array_merge(['DATE' => $i], $list);
                $data[] = $list;
            }
            return $data;
        }
    }
}