<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class CompetitorDailyController extends Controller {
    public function index(){
        $DOMAIN_ID = I('get.DOMAIN_ID');
        $DOMAIN_ID =empty($DOMAIN_ID)?I('post.DOMAIN_ID'):$DOMAIN_ID;

        $COUNTRY_ID = I('get.COUNTRY_ID');
        $COUNTRY_ID = empty($COUNTRY_ID)?I('post.COUNTRY_ID'):$COUNTRY_ID;

        $start_date = addslashes(I('post.start_date',date('Y-m-d', strtotime('-1 month'))));
        $end_date = addslashes(I('post.end_date',date('Y-m-d', strtotime('-1 days'))));
        $where = '';
        $where .= " AND `DATE` BETWEEN '{$start_date}' AND '{$end_date}'";

        $order = I('post.order','DATE');
        $sort = I('post.sort','desc');
        $page = I('post.page', 1);
        $limit = I('post.limit', 50);

        $model = new Model;
        /*$sql =" SELECT a.`RANK_IN_3_MONTH` as GLOBAL_RANK,b.`COUNTRY_RANK` ,c.`NAME_ENGLISH`,a.`DATE`
                FROM
                bo_entity_domain_sampling_alexa_usagestatistic AS a
                LEFT JOIN bo_entity_domain_sampling_alexa_rankbycountry AS b ON a.`DOMAIN_ID` = b.`DOMAIN_ID`
                LEFT JOIN bo_entity_country AS c ON c.`UUID` = b.`COUNTRY_ID`
                WHERE a.`DOMAIN_ID` = '{$DOMAIN_ID}' AND b.`COUNTRY_ID` = '{$COUNTRY_ID}' {$where} ";*/

        $sql =" SELECT `RANK_IN_3_MONTH` as GLOBAL_RANK,DOMAIN_ID,`DATE`
                FROM bo_entity_domain_sampling_alexa_usagestatistic
                WHERE `DOMAIN_ID` = '{$DOMAIN_ID}'{$where} ";
        $competitorDailyInfo = $model->query($sql);

        $sql =" SELECT b.`COUNTRY_RANK` ,c.`NAME_ENGLISH`,b.`DATE` as DATE
                FROM bo_entity_domain_sampling_alexa_rankbycountry AS b
                LEFT JOIN bo_entity_country AS c ON c.`UUID` = b.`COUNTRY_ID`
                WHERE b.`DOMAIN_ID` = '{$DOMAIN_ID}' AND b.`COUNTRY_ID` = '{$COUNTRY_ID}' {$where} ";

        $countryList = $model->query($sql);
        foreach($countryList as $k=>$v){
            unset($countryList[$k]);
            $countryList[$v['DATE']] = $v;
        }
        foreach($competitorDailyInfo as $k=>$v){
            $competitorDailyInfo[$k]['COUNTRY_RANK'] = isset($countryList[$v['DATE']])?$countryList[$v['DATE']]['COUNTRY_RANK']:'';
            $competitorDailyInfo[$k]['NAME_ENGLISH'] = isset($countryList[$v['DATE']])?$countryList[$v['DATE']]['NAME_ENGLISH']:'';
        }
        $count = count($competitorDailyInfo);

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

        foreach($competitorDailyInfo as $k=>$v){
            $orderList[$k] = $v[$order];
        }
        //排序
        $sort = $sort=='desc' ? SORT_DESC : SORT_ASC;
        array_multisort($orderList, $sort, $competitorDailyInfo);
        $dailyList = array();

        //分页
        foreach($competitorDailyInfo as $k=>$v){
            if($k>=$start && $k<=$end){
                $dailyList[$k] = $competitorDailyInfo[$k];
            }
        }
        $search = $_POST;

        $this->assign('competitorDailyInfo',$dailyList);
        $this->assign('search',$search);
        $this->assign('page', $page);
        $this->assign('pages', $pages);
        $this->assign('startPage', $startPage);
        $this->assign('endPage', $endPage);
        $this->assign('total', $count);
        $this->assign('DOMAIN_ID', $DOMAIN_ID);
        $this->assign('COUNTRY_ID', $COUNTRY_ID);
        $this->display();
    }
}
