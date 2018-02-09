<?php
/**
 * Created by PhpStorm.
 * User: pxu
 * Date: 2017/9/6
 * Time: 11:20
 */
namespace Home\Controller;
use Think\Controller;
use Think\Model;
use Think\Cache;
class AimListController extends Controller {
    public function index(){
        $model = new Model;
        ini_set('memory_limit', '256M');
        $sites = C('SITES');
        $site = I('post.site', 'PPUS');
        $site = isset($_COOKIE['SITE_NAME'])?$_COOKIE['SITE_NAME']:$site;

        $order = I('post.order','');
        $sort = I('post.sort','desc');
        $page = I('post.page', 1);
        $limit = I('post.limit', 100);
        $site_id = $sites[$site]['site_id'];
        $site_url = $sites[$site]['site_url'];
        $where = " where a.SITE_ID = '{$site_id}'";
        $pos = addslashes(I('post.aim_pos'));
        $ctr = addslashes(I('post.aim_ctr'));
        $br = addslashes(I('post.aim_br'));
        $lp = addslashes(I('post.lp'));
        //var_dump($_POST);die;
        if($lp){
            $where .= " and a.URL like '%{$lp}%'";
        }
        if($sites[$site]['type']=='merchant'){
            $aim_table = 'bo_entity_promo_mk_site_merchant_page_aim';
            $bench_table = 'bo_entity_promo_mk_site_merchant_page_benchmark';
            $current_table = 'bo_entity_promo_mk_site_merchant_page_current';
        }else{
            $aim_table = 'bo_entity_promo_mk_site_term_page_aim';
            $bench_table = 'bo_entity_promo_mk_site_term_page_benchmark';
            $current_table = 'bo_entity_promo_mk_site_term_page_current';
        }

        $sql = "SELECT a.UUID,a.URL,b.PAGE_VALUE FROM bo_entity_page as a LEFT JOIN {$bench_table} AS b ON(b.COUPON_PAGE_ID = a.UUID ) {$where} order by b.PAGE_VALUE desc";
        //var_dump($sql);die;
        $termList = $model->query($sql);
        /*$sql = "SELECT b.COUPON_PAGE_ID,b.SERP_POS,b.SERP_CTR,b.LP_BR FROM {$current_table} as b LEFT JOIN bo_entity_page AS a ON(b.COUPON_PAGE_ID = a.UUID ) where a.SITE_ID = '{$site_id}' ";
        $currentList = $model->query($sql);
        foreach($currentList as $k=>$v){
            unset($currentList[$k]);
            $currentList[$v['COUPON_PAGE_ID']] = $v;
        }*/

        $gaData = $this->get_ga_data($site_url);

        $gwtData =  $this->get_gwt_data($site_url);

        $sql = "SELECT b.COUPON_PAGE_ID,b.SERP_POS,b.SERP_CTR,b.LP_BR,b.CONTENT_COUPON_COUNT,b.CONTENT_PROMO_COUNT,b.CONTENT_PROMO_UPDATETIME_OFFSET FROM {$aim_table} as b LEFT JOIN bo_entity_page AS a ON(b.COUPON_PAGE_ID = a.UUID ) where a.SITE_ID = '{$site_id}' ";
        //var_dump($sql);die;
        $aimList = $model->query($sql);
        //var_dump($aimList);die;
        if($aimList){
            foreach($aimList as $k=>$v){
                unset($aimList[$k]);
                $aimList[$v['COUPON_PAGE_ID']] = $v;
            }
        }
        $siteData = $this->get_site_data($sites[$site]['site_id']);

        $position =[];
        foreach($termList as $k=>$v){
            $position[] = $v[''];
        }

        foreach($termList as $k=>$v){

            $termList[$k]['page_rank'] = $k+1;
            //$termList[$k]['SERP_POS'] = $currentList[$v['UUD']]['SERP_POS'];
            $termList[$k]['SERP_POS'] = number_format($gwtData[$v['UUID']]['POSITION'],1);
            //$termList[$k]['SERP_CTR'] = $currentList[$v['UUD']]['SERP_CTR'];
            $termList[$k]['SERP_CTR'] = number_format(100*$gwtData[$v['UUID']]['CTR'],2).'%';
            $termList[$k]['LP_BR'] = number_format(100*$gaData[$v['UUID']]['BR'],2).'%';
            $termList[$k]['search_value'] = '';
            $termList[$k]['aim_revenue'] = $termList[$k]['search_value']*abs($termList[$k]['aim_ctr']-$termList[$k]['SERP_CTR']);
            $termList[$k]['aim_pos'] = isset($aimList[$v['UUID']])?number_format($aimList[$v['UUID']]['SERP_POS'],1):'';
            $termList[$k]['aim_exist'] = isset($aimList[$v['UUID']])?'Y':'N';
            $termList[$k]['aim_ctr'] = isset($aimList[$v['UUID']])?number_format(100*$aimList[$v['UUID']]['SERP_CTR'],2).'%':'';
            $termList[$k]['aim_br'] = isset($aimList[$v['UUID']])?number_format($aimList[$v['UUID']]['LP_BR'],2).'%':'';
            $termList[$k]['aim_coupon'] = isset($aimList[$v['UUID']])?$aimList[$v['UUID']]['CONTENT_COUPON_COUNT']:'';
            $termList[$k]['aim_promo'] = isset($aimList[$v['UUID']])?$aimList[$v['UUID']]['CONTENT_PROMO_COUNT']:'';
            $termList[$k]['aim_offset'] = isset($aimList[$v['UUID']])?$aimList[$v['UUID']]['CONTENT_PROMO_UPDATETIME_OFFSET']:'';
            foreach($siteData as $kk=>$vv){
                if($termList[$k]['aim_pos']>$vv['FROM_POS'] && $termList[$k]['aim_pos']<=$vv['TO_POS']){
                    $termList[$k]['aim_pos_ctr'] = number_format(100*$vv['CTR'],2).'%';
                    $termList[$k]['aim_pos_br'] = number_format(100*$vv['BR'],2).'%';
                }
            }
            
            $orderList[$k] = $termList[$k][$order];

            $posFlag = false;
            $ctrFlag = false;
            $brFlag = false;
            if($pos){
                if($pos=='Y'&& empty($termList[$k]['aim_pos'])){
                    $posFlag = true;
                };
                if($pos=='N'&& !empty($termList[$k]['aim_pos'])){
                    $posFlag = true;
                };
            }
            if($ctr){
                if($ctr=='Y'&& empty($termList[$k]['aim_ctr'])){
                    $ctrFlag = true;
                };
                if($ctr=='N'&& !empty($termList[$k]['aim_ctr'])){
                    $ctrFlag = true;
                };
            }
            if($br){
                if($br=='Y'&& empty($termList[$k]['aim_br'])){
                    $brFlag = true;
                };
                if($br=='N'&& !empty($termList[$k]['aim_br'])){
                    $brFlag = true;
                };
            }
            if($posFlag||$ctrFlag||$brFlag){
                unset($termList[$k]);
            }else{
                if($order){
                    $orderList[$k] = $termList[$k][$order];
                }

            }


        }

        $count = count($termList);
        $limit = $limit<1?1:$limit;
        $pages = ceil($count/$limit);
        $page = $page<1?1:$page;
        $start = $limit*($page-1);
        $end = $start+$limit-1;

        $startPage = $page+9>$pages?$pages-9:$page;
        $endPage = $startPage+10;

        if($pages<10){
            $startPage = 1;
            $endPage = $pages+1;
        }
        //var_dump($orderList);die;
        if($order){
            $sort = $sort=='desc' ? SORT_DESC : SORT_ASC;
            array_multisort($orderList, $sort, $termList);
        }
        $aimList = [];
        foreach($termList as $k=>$v){
            if($k>=$start && $k<=$end){
                $aimList[$k] = $termList[$k];
            }
        }
        //var_dump($posList);die;
        $pageList = [
            'page'=>$page,
            'pages'=>$pages,
            'startPage'=>$startPage,
            'endPage'=>$endPage,
            'total'=>$count
        ];
        $search = $_POST;
        $this->assign('termList',$aimList);
        $this->assign('sites',$sites);
        $this->assign('site',$site);
        $this->assign('search',$search);
        $this->assign('pageList', $pageList);
        $this->assign('type', $sites[$site]['type']);
        $this->display();
    }

    public  function get_site_data($site){
        $model = new Model;
        $sql  = "SELECT COUPON_SITE_ID,FROM_POS,TO_POS,BR,CTR FROM `bo_entity_promo_mk_site_benchmark` where COUPON_SITE_ID='{$site}'";
        $siteData = $model->query($sql);
        return $siteData;
    }
    public function get_gwt_data($site_url){
        $model = new Model;
        $sql  = "SELECT DATE FROM bo_entity_ext_gsc_data_search_analytics_page WHERE URL like '{$site_url}%' order by date desc limit 1";
        $gwtUpdate = $model->query($sql);
        $date = $gwtUpdate[0]['DATE'];

        $sql  = "select COUPON_PAGE_ID,POSITION,CTR from bo_entity_ext_gsc_data_search_analytics_page  where URL like '{$site_url}%' and DATE='{$date}'";
        //var_dump($sql);die;
        $gwtData = $model->query($sql);
        foreach($gwtData as $k=>$v){
            unset($gwtData[$k]);
            $gwtData[$v['COUPON_PAGE_ID']] =$v;
        }
        return $gwtData;
    }

    public function get_ga_data($site_url){
        $model = new Model;
        $sql = "select DATE from bo_entity_promo_mk_site_page_sampling_ga where OBJNAME LIKE '%{$site_url}%' order by date desc limit 1";
        $gaUpdate = $model->query($sql);
        $date = $gaUpdate[0]['DATE'];
        $sql = "SELECT COUPON_PAGE_ID,BOUNSE_RATE as BR FROM bo_entity_promo_mk_site_page_sampling_ga where OBJNAME LIKE '%{$site_url}%' and DATE='{$date}'";
        $gaData = $model->query($sql);
        //var_dump($gaData);die;
        foreach($gaData as $k=>$v){
            unset($gaData[$k]);
            $gaData[$v['COUPON_PAGE_ID']] =$v;
        }
        return $gaData;
    }

}


