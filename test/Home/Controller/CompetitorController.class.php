<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class CompetitorController extends Controller {
    public function index(){
        $model = new Model;
        $where = ' WHERE true';
        $sites = C('SITES');
        $site = I('post.site', 'PPUS');
        $compete = I('post.compete');
        $where .= !empty($compete) ? " AND b.`NAME` = '{$compete} '" : "";

        $site = isset($_COOKIE['SITE_NAME'])?$_COOKIE['SITE_NAME']:$site;

        $page = I('post.page', 1);
        $limit = I('post.limit', 50);
        $sort = I('post.sort','asc');
        $sort = $sort == 'desc' ? "DESC" : "ASC";

        $order = I('post.order','alexa');
        $orderStr = $order == 'addtime' ? "ORDER BY a.`ADDTIME` ".$sort : "";
        $sql = "SELECT  b.`NAME`,b.URL, c.`DOMAIN`,d.`NAME_ENGLISH`, e.`ABBREVIATION`, a.`ADDTIME`,
                IF(c.`ROOT_DOMAIN_ID`='',c.`UUID`,c.`ROOT_DOMAIN_ID`) AS `DOMAIN_ID`, d.`UUID` AS `COUNTRY_ID`
                FROM
                bo_entity_promo_mk_site AS a LEFT JOIN bo_entity_website AS b ON a.`UUID` = b.`UUID`
                LEFT JOIN bo_entity_domain AS c ON b.`DOMAIN_ID` = c.`UUID`
                LEFT JOIN bo_entity_country AS d ON b.`MASTER_COUNTRY` = d.`UUID`
                LEFT JOIN bo_entity_language AS e ON b.`MASTER_LANGUAGE` = e.`UUID`
                where a.UUID='{$sites[$site]['site_id']}'";
        $mkSiteInfo = $model->query($sql);
        //var_dump($mkSiteInfo);die;
        $where .= " and  d.`NAME_ENGLISH` = '{$mkSiteInfo[0]['NAME_ENGLISH']}' and e.`ABBREVIATION` = '{$mkSiteInfo[0]['ABBREVIATION']} '";

        $sql = "SELECT  b.`NAME`,b.URL, c.`DOMAIN`,d.`NAME_ENGLISH`, e.`ABBREVIATION`, a.`ADDTIME`,
                IF(c.`ROOT_DOMAIN_ID`='',c.`UUID`,c.`ROOT_DOMAIN_ID`) AS `DOMAIN_ID`, d.`UUID` AS `COUNTRY_ID`
                FROM 
                bo_entity_promo_competitor_site AS a LEFT JOIN bo_entity_website AS b ON a.`UUID` = b.`UUID` 
                LEFT JOIN bo_entity_domain AS c ON b.`DOMAIN_ID` = c.`UUID`
                LEFT JOIN bo_entity_country AS d ON b.`MASTER_COUNTRY` = d.`UUID`
                LEFT JOIN bo_entity_language AS e ON b.`MASTER_LANGUAGE` = e.`UUID`
                {$where} {$orderStr} ";

        //var_dump($sql);die;
        $competitorInfo = $model->query($sql);
        $domain = '';
        $mkSiteInfo[0]['self'] = 'yes';
        array_unshift($competitorInfo,$mkSiteInfo[0]);
        foreach($competitorInfo as $k=>$v){
            $domain .="'".$v['DOMAIN_ID']."',";
            $competitorInfo[$k]['self'] = isset($competitorInfo[$k]['self'])?'yes':'no';
        }
        //var_dump($competitorInfo);die;
        $domain = trim($domain,',');

        $sql = " SELECT RANK_IN_3_MONTH,DATE,DOMAIN_ID FROM `bo_entity_domain_sampling_alexa_usagestatistic` WHERE DOMAIN_ID in({$domain})";
        $tmpInfo = $model->query($sql);
        //var_dump($tmpInfo);die;
        foreach($tmpInfo as $k=>$v){
            if(isset($tmpInfo[$v['DOMAIN_ID']])){
                $tmpInfo[$v['DOMAIN_ID']] = $tmpInfo[$v['DOMAIN_ID']]['DATE']>$v['DATE']?$tmpInfo[$v['DOMAIN_ID']]:$v;
            }else{
                $tmpInfo[$v['DOMAIN_ID']] = $v;
            }
            unset($tmpInfo[$k]);
        }
        foreach ($competitorInfo as $k => $v) {
            $competitorInfo[$k]['RANK'] = $tmpInfo[$v['DOMAIN_ID']]['RANK_IN_3_MONTH'];
        }

        $count = count($competitorInfo);

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


        $language = array(0 => 'all');
        $country = array(0 => 'all');
        foreach ($competitorInfo as $v) {
            $language[] = $v['ABBREVIATION'];
            $country[] = $v['NAME_ENGLISH'];
        }
        $language = array_keys(array_flip($language));
        $country = array_keys(array_flip($country));
        $this->assign('language',$language);
        $this->assign('country',$country);
        //var_dump($competitorInfo);die;
        if($order == 'alexa'){
            foreach ($competitorInfo as $k => $v) {

                $edition[$k] = $v['RANK'];
                if(empty($v['RANK'])&&$sort == 'ASC'){
                    $edition[$k] = '999999999';
                }
                if(empty($v['RANK'])&&$sort == 'DESC'){
                    $edition[$k] ='0';
                }
            }
            $sort = I('post.sort');
            $sort = $sort=='desc' ? SORT_DESC : SORT_ASC;
            array_multisort($edition, $sort, $competitorInfo);
        }

        //分页
        $competitorList = [];
        foreach($competitorInfo as $k=>$v){
            if($k>=$start && $k<=$end){
                $competitorList[$k] = $competitorInfo[$k];
            }
        }
        $pageList = [
            'page'=>$page,
            'pages'=>$pages,
            'startPage'=>$startPage,
            'endPage'=>$endPage,
            'total'=>$count
        ];
        $search = $_POST;
        $this->assign('search',$search);
        $this->assign('site',$site);
        $this->assign('competitorInfo',$competitorList);
        $this->assign('pageList', $pageList);
        $this->display();
    }
}
