<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
use Home\Model\SamplingContentModel;
use Home\Model\SamplingGscModel;
use Home\Model\SamplingGaModel;
use Home\Model\SamplingRevenueModel;
use Home\Model\SamplingAffModel;
use Think\Cache;
class SiteDailyController extends Controller {
    public function index()
    {
        $this->assign('panelList', C('PANEL_LIST'));
        $this->assign('metricList', C('SITEDAILY_METRIC_LIST'));
        $this->display();
    }

    public function getDefaultData()
    {
        $data['defaultSite'] = current(C('SITE_LIST'));
        $data['defaultDate'] = date('Y-m-d', strtotime(C('DEFAUTL_DATE')));
        $data['siteList'] = C('SITE_LIST');
        $data['metricList'] = C('SITEDAILY_METRIC_LIST');
        $this->ajaxReturn($data);
    }

    public function getBasicData($siteUUID, $siteDate, $refreshCache=0)
    {
        $panelName = 'Content Basic Status';
        $pageType = 'sample_content_p1';
        $map['COUPON_SITE_ID'] = $siteUUID;
        $map['DATE'] = $siteDate;
        $termFlag = isTerm($siteUUID);
        $contentModel = new SamplingContentModel;
        $cacheFlag = existKey($map['COUPON_SITE_ID'], $map['DATE'], $pageType);
        if($cacheFlag)
        {
            $contentData = $contentModel->getDataLocal($map, $termFlag);
        }else{
            $contentData = $contentModel->getData($map, $termFlag);
            if($refreshCache)
            {
                $map['TERM_FLAG'] = $termFlag ? 1: 0;
                insertDataLocal(array_merge($contentData, $map), 'mk_basic_data', $cacheKey);
            }
        }
        
        $data['list'] = $contentData;
        $data['panelName'] = getPanelName($panelName);
        $data['type'] = $pageType;
        $data['status'] = 1;
        $this->ajaxReturn($data);
    }
    public function getCouponContentData($siteUUID, $siteDate, $refreshCache=0)
    {
        $panelName = 'Coupon Content Status';
        $pageType = 'sample_content_p2';
        $map['COUPON_SITE_ID'] = $siteUUID;
        $map['DATE'] = $siteDate;
        $termFlag = isTerm($siteUUID);
        $contentModel = new SamplingContentModel;
        $cacheKey = getCacheKey($siteUUID, $siteDate, $pageType);
        $cacheFlag = S($cacheKey);
        if(!empty($cacheFlag))
        {
           $contentData = $contentModel->getCouponContentDataLocal($map, $termFlag); 
        }else{
            $contentData = $contentModel->getCouponContentData($map, $termFlag);
            if($refreshCache)
            {
                $map['TERM_FLAG'] = $termFlag ? 1: 0;
                insertDataLocal(array_merge($contentData, $map), 'mk_coupon_content_status', $cacheKey);
            }
        }   
        $data['list'] = $contentData;
        $data['panelName'] = getPanelName($panelName);
        $data['type'] = $pageType;
        $data['status'] = 1;
        $this->ajaxReturn($data);
    }
    public function getGGData($siteUUID, $siteDate, $refreshCache=0)
    {
        $map['COUPON_SITE_ID'] = $siteUUID;
        $map['DATE'] = $siteDate;

        # sample_gsc
        $pageType = 'sample_gsc';
        $cacheKey = getCacheKey($siteUUID, $siteDate, $pageType);
        $cacheFlag = S($cacheKey);
        $gscModel = new SamplingGscModel;
        if(!empty($cacheFlag))
        {
            $gscData = $gscModel->getDataLocal($map);
        }else{
            $gscData = $gscModel->getData($map);
            if($refreshCache)
                insertDataLocal(array_merge($gscData, $map), 'mk_ga_traffic_status', $cacheKey);
        }
        
        # sample_ga
        $pageType = 'sample_ga';
        $gaModel = new SamplingGaModel;
        $cacheKey = getCacheKey($siteUUID, $siteDate, $pageType);
        $cacheFlag = S($cacheKey);
        if($cacheFlag)
        {
            $gaData = $gaModel->getDataLocal($map);
        }else{
            $gaData = $gaModel->getData($map);
            if($refreshCache)
                insertDataLocal(array_merge($gaData, $map), 'mk_google_performance', $cacheKey);
        }

        # sample_ga_br
        $pageType = 'sample_ga_br';
        $cacheKey = getCacheKey($siteUUID, $siteDate, $pageType);
        $cacheFlag = S($cacheKey);
        if(!empty($cacheFlag))
        {
            $computedData = $gaModel->computeDataLocal($map);
        }else{
            $computedData = $gaModel->computeData($map, 'SEPARATE');
            if($refreshCache)
                insertDataLocal(array_merge($computedData[0], $computedData[1], $map), 'mk_ga_br_status', $cacheKey);
        }
        $panelList = ['Google表现', 'GA流量基本状态', 'BR的页面分布', 'BR的流量分布'];
        $typeList = ['sample_gsc', 'sample_ga', 'sample_ga_br_page', 'sample_ga_br_session'];
        $aaList = [$gscData, $gaData, $computedData[0], $computedData[1]];
        $data = [];
        foreach ($aaList as $k => $v) {
            $tmp= [];
            $tmp['list'] = $v;
            $tmp['panelName'] = $panelList[$k];
            $tmp['type'] = $typeList[$k];
            $tmp['status'] = 1;
            $data[] = $tmp;
        }
        $this->ajaxReturn($data);
    }

    public function getRevenueData($siteUUID, $siteDate)
    {
        $panelName = 'SEO_REVENUE';
        $pageType = 'sample_revenue';
        $map['COUPON_SITE_ID'] = $siteUUID;
        $map['DATE'] = $siteDate;
        $termFlag = isTerm($siteUUID);
        $model = new SamplingRevenueModel;
        $revenueData = $model->getRevenueData($map);
        $data['list'] = $revenueData;
        $data['panelName'] = getPanelName($panelName);
        $data['type'] = $pageType;
        $data['status'] = 1;
        $this->ajaxReturn($data);
    }

    public function getAFFData($siteUUID, $siteDate)
    {
        $panelName = 'Aff_Status';
        $pageType = 'sample_aff';
        $map['COUPON_SITE_ID'] = $siteUUID;
        $map['DATE'] = $siteDate;
        $termFlag = isTerm($siteUUID);
        $model = new SamplingAffModel;
        $affData = $model->geAFFData($map);
        $data['list'] = $affData;
        $data['panelName'] = getPanelName($panelName);
        $data['type'] = $pageType;
        $data['status'] = 1;
        $this->ajaxReturn($data);
    }
    public function getDataV2($siteUUID, $siteDate, $refreshCache=0){
        $termFlag = isTerm($siteUUID);
        $data = [];
        $pageType = 'sample_requirement';
        $map['COUPON_SITE_ID'] = $siteUUID;
        $map['DATE'] = $siteDate;
        $cacheKey = getCacheKey($siteUUID, $siteDate, $pageType);
        $cacheFlag = S($cacheKey);
        $model = new SamplingContentModel;
        if(!empty($cacheFlag))
        {
            $interList = $model->getDataV2Local($map, $termFlag);
        }else{
            $interList = $model->getDataV2($map, $termFlag);
            if($refreshCache)
            {
                $map['TERM_FLAG'] = $termFlag ? 1: 0;
                foreach ($interList as $key => $value) {
                    $order = ['SORT_ORDER' => ($key + 1)];
                    insertDataLocal(array_merge($value, $map, $order), 'mk_content_requirement_rate', $cacheKey);
                }
            }
        }
        $data['list'] = $interList;
        $data['panelName'] = '内容需求达标率';
        $data['status'] = 1;
        $data['type'] = $pageType;
        $data['columns'] = array_keys(current($interList));
        $this->ajaxReturn($data);
    }
    public function view($site, $source)
    {
        $this->assign('site', $site);
        $this->assign('source', $source);
        $this->assign('panelName', getSubPanelName($source));
        $this->display();
    }
    public function viewPageList($site, $metric, $date)
    {
        $this->assign('site', $site);
        $this->assign('panelName', getSubPanelName($metric));
        $this->assign('metric', $metric);
        $this->assign('date', $date);
        $this->display();
    }
    public function getPageList($siteUUID, $metric, $date, $start=0, $lmit=100)
    {
        $model = new SamplingContentModel;
        $termFlag = isTerm($siteUUID);
        $map['COUPON_SITE_ID'] = $siteUUID;
        $map['DATE'] = $date;
        $maxCount = 1;
        if(strripos(strtoupper($metric), 'REQUIREMENT') !== FALSE)
        {
            $rank = str_replace('REQUIREMENT_', '', $metric);
            $tmpData = $model->getDataV2($map, $termFlag, intval($rank));
            $finalData = array_slice($tmpData, $start, $lmit);
        }else{
            $rank = str_replace('PAGE_COUNT', 'PAGE', $metric, $maxCount);
            $data = $model->getPageList($map, $termFlag);
            $tmpData = $data[$rank];
            $finalData = array_slice($tmpData, $start, $lmit);
            /*foreach ($finalData as $k => $v) {
                if(preg_match('/http(.*?) /', $v['OBJNAME'], $match))
                {
                    $finalData[$k]['OBJNAME'] = isset($match[0])? $match[0]: '';
                }
            }*/
        }
        
        $returnData['list'] = $finalData;
        $returnData['count'] = count($tmpData);
        $returnData['columns'] = array_keys(current($returnData['list']));
        $this->ajaxReturn($returnData);
    }
    public function getList($siteUUID, $source, $startDate, $endDate)
    {
        $sourceList = [
            'sample_content_p1' => [
                'modelName' => 'Home\Model\SamplingContentModel',
                'filedType' => 'A'
            ],
            'sample_content_p2' => [
                'modelName' => 'Home\Model\SamplingContentModel',
            ],
            'sample_ga' => [
                'modelName' => 'Home\Model\SamplingGaModel',
                'filedType' => 'ALL'
            ],
            'sample_ga_br_page' => [
                'modelName' => 'Home\Model\SamplingGaModel',
                'filedType' => 'ComputedA'
            ],
            'sample_ga_br_session' => [
                'modelName' => 'Home\Model\SamplingGaModel',
                'filedType' => 'ComputedB'
            ],
            'sample_gsc' => [
                'modelName' => 'Home\Model\SamplingGscModel',
                'filedType' => 'ALL'
            ],
            'sample_revenue' => [
                'modelName' => 'Home\Model\SamplingRevenueModel',
                'filedType' => 'ALL'
            ],
            'sample_requirement' => [
                'modelName' => 'Home\Model\SamplingContentModel',
            ]
        ];
        if(strpos($source, 'sample_requirement') !== FALSE)
        {
            $rank = str_replace('sample_requirement_', '', $source);
            $source = 'sample_requirement';
        }
        if(!isset($sourceList[$source]))
            $this->error('invalid source!');
        $map['COUPON_SITE_ID'] = $siteUUID;
        $map['DATE'] = ['BETWEEN', $startDate.','.$endDate];

        $modelName = $sourceList[$source]['modelName'];
        $filedType = $sourceList[$source]['filedType'];
        $model = new $modelName;
        if($source == 'sample_content_p2')
        {
            $data = $model->getListByDay($map);
        }elseif(isset($rank)){
            $data = $model->getListByDayV2($map, $rank);
        }else{
            $data =$model->getList($map, $filedType);
        }
        $finalData['list'] = $data;
        $finalData['columns'] = array_keys(current($data));
        $this->ajaxReturn($finalData);
    }
}