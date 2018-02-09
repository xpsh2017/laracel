<?php
/**
 * Created by PhpStorm.
 * User: pxu
 * Date: 2017/9/18
 * Time: 14:06
 */

namespace Home\Controller;
use Think\Controller;
use Think\Model;
use Think\Cache;
class OperationStateController extends Controller {
        public function index(){
            //$model = new Model;
            ini_set("memory_limit", "2048M");
            $sites = C('SITES');
            $site = I('post.site','PPUS');
            //$site = isset($_COOKIE['SITE_NAME'])?$_COOKIE['SITE_NAME']:$site;
            $order = addslashes(I('post.order','REVENUE'));
            $sort = addslashes(I('post.sort','desc'));
            $page = addslashes(I('post.page', 1));
            $limit = addslashes(I('post.limit', 100));
            $aff = addslashes(I('post.HAS_AFFILIATE'));
            $code = addslashes(I('post.CODES'));

            $site_id = $sites[$site]['site_id'];
            $site_type = $sites[$site]['type'];

            $limit = $limit<1?1:$limit;
            $page = $page<1?1:$page;
            $start = $limit*($page-1);

            if($site_type =='merchant'){
                $test_base = M('mk_site_merchant_page_report','','mysql://payne:sh@BZ1hnC@192.168.1.242:3306/test_oi_base');
                //$test_base = 'mk_site_merchant_page_report';

            }else{
                $test_base = M('mk_site_term_page_report','','mysql://payne:sh@BZ1hnC@192.168.1.242:3306/test_oi_base');
                //$test_base = 'mk_site_term_page_report';

            }
            $endDay = I('post.date', date("Y-m-d",strtotime("-1 days")));
            $startDay = I('post.start',date("Y-m-d",strtotime($endDay."-6 days")));

            $map['COUPON_SITE_ID'] = $site_id;
            $map['DATE'] = array('between',array($startDay,$endDay));
            if($code){
                if($code=='Y'){
                    $map['CODES'] = array('gt',0);
                }else{
                    $map['CODES'] = array('eq',0);
                }
            }


            /*$sql = "select COUPON_PAGE_ID,avg(POSITION) as POSITION,sum(IMPRESSION) as IMPRESSION,avg(CTR) as CTR,avg(BR) as BR,avg(DURATION) as DURATION,avg(PAGE_PER_SESSION)as PAGE_PER_SESSION,sum(SESSION) as SESSION,sum(OUTCLICK) as OUTCLICK,sum(REVENUE) as REVENUE,avg(RPI) as RPI,sum(CODES) as CODES,sum(PROMOS) as PROMOS,avg(INTERNAL_LINKS) as INTERNAL_LINKS,avg(SV) as SV
                    from mk_site_merchant_page_report where COUPON_SITE_ID='{$site_id}' and DATE BETWEEN '2017-09-11' and '2017-09-17'";
            var_dump($sql);die;*/

            $tmp =  $test_base->field("COUPON_PAGE_ID,URL,avg(POSITION) as POSITION,sum(IMPRESSION) as IMPRESSION,avg(CTR) as CTR,avg(BR) as BR,avg(DURATION) as DURATION,avg(PAGE_PER_SESSION)as PAGE_PER_SESSION,sum(SESSION) as SESSION,sum(OUTCLICK) as OUTCLICK,sum(REVENUE) as REVENUE,avg(RPI) as RPI,sum(CODES) as CODES,sum(PROMOS) as PROMOS,avg(INTERNAL_LINKS) as INTERNAL_LINKS,avg(SV) as SV,sum(if(`HAS_AFFILIATE`='590dd167d5414',1,0)) as HAS_AFFILIATE")
                ->where($map)->group('COUPON_PAGE_ID');

            if($aff){
                if($aff=='Y'){
                    $tmp = $tmp->having('HAS_AFFILIATE>0');
                }else{
                    $tmp = $tmp->having('HAS_AFFILIATE=0');
                }

            }
            if($order){
                $tmp = $tmp->order("$order $sort");
            }

            //$termList = $tmp->limit($start,$limit)->fetchSql(true)->select();
            //var_dump($termList);die;

            $termList = $tmp->limit($start,$limit)->select();
            //var_dump($termList);
            //$count = count($tmp->select());
            //var_dump($count);

            foreach($termList as $k=>$v){
                if($v['HAS_AFFILIATE']>0){
                    $termList[$k]['HAS_AFFILIATE'] = 'Y';
                }else{
                    $termList[$k]['HAS_AFFILIATE'] = 'N';
                }
                $termList[$k]['POSITION'] = number_format($v['POSITION'],1);
                $termList[$k]['CTR'] = number_format(100*$v['CTR'],2).'%';
                $termList[$k]['BR'] = number_format(100*$v['BR'],2).'%';
                $termList[$k]['DURATION'] = number_format($v['DURATION'],2);
                $termList[$k]['PAGE_PER_SESSION'] = number_format($v['PAGE_PER_SESSION'],2);
                $termList[$k]['REVENUE'] = number_format($v['REVENUE'],2);
                $termList[$k]['RPI'] = number_format($v['RPI'],3);
                $termList[$k]['INTERNAL_LINKS'] = number_format($v['INTERNAL_LINKS'],2);
                $termList[$k]['SV'] = number_format($v['SV'],2);
                $termList[$k]['IMPRESSION'] = number_format($v['IMPRESSION']);
                $termList[$k]['SESSION'] = number_format($v['SESSION']);
                $termList[$k]['OUTCLICK'] = number_format($v['OUTCLICK']);
                $termList[$k]['CODES'] = number_format($v['CODES']);
                $termList[$k]['PROMOS'] = number_format($v['PROMOS']);

            }
            $count = 10000;
            $pages = ceil($count/$limit);
            $startPage = $page+10>$pages?$pages-9:$page;
            $endPage = $startPage+10;

            if($pages<10){
                $startPage = 1;
                $endPage = $pages+1;
            }

            $pageList = [
                'page'=>$page,
                'pages'=>$pages,
                'startPage'=>$startPage,
                'endPage'=>$endPage,
                'total'=>$count
            ];

            $search = $_POST;
            $this->assign('termList',$termList);
            $this->assign('sites',$sites);
            $this->assign('site',$site);
            $this->assign('search',$search);
            $this->assign('pageList', $pageList);
            $this->assign('type', $sites[$site]['type']);
            $this->assign('date',$endDay);
            $this->assign('start',$startDay);
            $this->display();







        }



        public function detail(){
            $model = new Model;
            $id = addslashes(I('get.uuid'));
            $URL = addslashes(I('get.URL'));
            $id = empty($id)?I('post.uuid'):$id;
            $type = I('get.type');
            $type = empty($type)?I('post.type'):$type;

            $endDate = I('get.date');
            $endDate = empty($endDate)?I('post.date'):$endDate;
            $startDate = I('post.start',date("Y-m-d",strtotime($endDate."-13 days")));
            if($type =='merchant'){
                $test_base = M('mk_site_merchant_page_report','','mysql://payne:sh@BZ1hnC@192.168.1.242:3306/test_oi_base');
                $mapping_table = 'bo_entity_02b5ec62903448542608695874952bdf';
                $field = 'PROMO_MK_SITE_MERCHANT_PAGE_UUID';
            }else{
                $test_base = M('mk_site_term_page_report','','mysql://payne:sh@BZ1hnC@192.168.1.242:3306/test_oi_base');
                $mapping_table = 'bo_entity_88ada9ee73d0fb29b987ad406cc2bdd1';
                $field = 'PROMO_MK_SITE_TERM_PAGE_UUID';
            }
            $map['DATE'] = array('between',array($startDate,$endDate));
            if($URL){
                $map['URL'] = $URL;
            }else{
                $map['COUPON_PAGE_ID'] = $id;
            }
            $termList = $test_base->field('COUPON_PAGE_ID,DATE,URL,POSITION,IMPRESSION,CTR,BR,DURATION,PAGE_PER_SESSION,SESSION,OUTCLICK,REVENUE,RPI,CODES,PROMOS,SV,HAS_AFFILIATE')
                ->where($map)->order("DATE DESC")->select();
            foreach($termList as $k=>$v){
                if($v['HAS_AFFILIATE']==C('YES')){
                    $termList[$k]['HAS_AFFILIATE'] = 'Y';
                }else{
                    $termList[$k]['HAS_AFFILIATE'] = 'N';
                }
            }
            $sql = "select c.UUID,c.OBJNAME from {$mapping_table} as a left join bo_entity_57ca1e5ea2455e9d71fb0a682000951f as b on a.PROMO_MERCHANT_UUID=b.PROMO_MERCHANT_UUID left join bo_entity_promo_competitor_merchant_page as c on c.UUID=b.PROMO_COMPETITOR_MERCHANT_PAGE_UUID  where a.{$field}='{$id}'";

            $competitor = $model->query($sql);

            $sql ="select PAGE_ID,CONTENT_TYPE,UPDATE_TIME,FROM_CONTENT,TO_CONTENT from bo_entity_page_seochangelog where PAGE_ID ='{$id}' and UPDATE_TIME BETWEEN  '{$startDate}' and '{$endDate}' order by UPDATE_TIME desc";
            $list = $model->query($sql);
            $seo = C('SEO');
            foreach($list as $k=>$v){
                $list[$k]['CONTENT_TYPE'] = $seo[$v['CONTENT_TYPE']];
            }

            $search = $_POST;
            $this->assign('search',$search);
            $this->assign('termList',$termList);
            $this->assign('list',$list);
            $this->assign('competitor',$competitor);
            $this->assign('uuid',$id);
            $this->assign('type',$type);
            $this->assign('date',$endDate);
            $this->assign('start',$startDate);
            $this->display();
        }


        public function insertData(){
            set_time_limit(0);
            $model = new Model;
            $date = I('get.date');
            $sites = C('SITES');
            $site = I('get.site','PPUS');
            $site_id = $sites[$site]['site_id'];
            $site_url = $sites[$site]['site_url'];
            $site_type = $sites[$site]['type'];
            var_dump($site);var_dump($date);die;
            if($site_type =='merchant'){
                //$test_base = M('mk_site_merchant_page_report','','mysql://payne:sh@BZ1hnC@192.168.1.242:3306/test_oi_base');
                $test_base = M('mk_site_merchant_page_report','','mysql://root:root@127.0.0.1:3306/pmi_base');
                $aff_table = 'bo_entity_promo_mk_site_merchant_page_sampling_affiliate';
                $content_table = 'bo_entity_promo_mk_site_merchant_page_sampling_content';
                $bench_table = 'bo_entity_promo_mk_site_merchant_page_benchmark';
                $page_table = 'bo_entity_promo_mk_site_merchant_page';
            }else{
                //$test_base = M('mk_site_term_page_report','','mysql://payne:sh@BZ1hnC@192.168.1.242:3306/test_oi_base');
                $test_base = M('mk_site_term_page_report','','mysql://root:root@127.0.0.1:3306/pmi_base');
                $aff_table = 'bo_entity_promo_mk_site_term_page_sampling_affiliate';
                $content_table = 'bo_entity_promo_mk_site_term_page_sampling_content';
                $bench_table = 'bo_entity_promo_mk_site_term_page_benchmark';
                $page_table = 'bo_entity_promo_mk_site_term_page';
            }

            echo microtime(true)."\n";

            //站点商家页面
            $sql = "SELECT a.UUID as COUPON_PAGE_ID,a.URL,a.SITE_ID as COUPON_SITE_ID,a.ALL_KEYWORD_SEARCH_VOLUME as SV FROM bo_entity_page as a left join {$page_table} as b on(b.UUID=a.UUID) WHERE a.SITE_ID = '{$site_id}'";
            $termList = $model->query($sql);

            //警戒值相关数据
            $sql = "SELECT a.COUPON_PAGE_ID,a.SERP_POS,a.SERP_CTR,a.LP_BR,a.PAGE_VALUE FROM {$bench_table}  as a LEFT JOIN bo_entity_page AS b ON(a.COUPON_PAGE_ID = b.UUID) where b.SITE_ID = '{$site_id}'";
            $benchData = $model->query($sql);
            foreach($benchData as $k=>$v){
                unset($benchData[$k]);
                $benchData[$v['COUPON_PAGE_ID']] = $v;
            }

            //gwt相关数据
            $sql = "SELECT COUPON_PAGE_ID,POSITION,IMPRESSION,CTR FROM bo_entity_ext_gsc_data_search_analytics_page WHERE URL like '{$site_url}%' AND DATE ='{$date}'";
            $gwtData = $model->query($sql);
            foreach($gwtData as $k=>$v){
                unset($gwtData[$k]);
                $gwtData[$v['COUPON_PAGE_ID']] = $v;
            }

            echo microtime(true)."\n";
            //ga相关数据
            $sql = "SELECT a.COUPON_PAGE_ID,a.SESSION,a.BOUNSE_RATE,a.AVG_SESSION_DURATION,a.ACCESS_PAGE_NUM_PER_SESSION FROM bo_entity_promo_mk_site_page_sampling_ga  as a LEFT JOIN bo_entity_page AS b ON(a.COUPON_PAGE_ID = b.UUID) where b.SITE_ID = '{$site_id}' and a.date='{$date}'";
            $gaData = $model->query($sql);
            foreach($gaData as $k=>$v){
                unset($gaData[$k]);
                $gaData[$v['COUPON_PAGE_ID']] = $v;
            }

            echo microtime(true)."\n";
            //revenue
            $sql = "select a.SEO_REVENUE,a.COUPON_PAGE_ID from bo_entity_promo_mk_site_page_sampling_revenue_medium as a left join bo_entity_page as b on(a.COUPON_PAGE_ID=b.UUID) where b.SITE_ID='{$site_id}' and a.date='{$date}'";
            $revData =  $model->query($sql);
            foreach($revData as $k=>$v){
                unset($revData[$k]);
                $revData[$v['COUPON_PAGE_ID']] = $v;
            }

            echo microtime(true)."\n";
            //有无联盟
            $sql = "select a.HAS_AFFILIATE,a.COUPON_PAGE_ID from {$aff_table} as a left join bo_entity_page as b on(a.COUPON_PAGE_ID=b.UUID) where b.SITE_ID='{$site_id}' and a.date='{$date}'";
            $affData = $model->query($sql);
            foreach($affData as $k=>$v){
                unset($affData[$k]);
                //$affData[$v['COUPON_PAGE_ID']] = $v;
                if($v['HAS_AFFILIATE'] ==C("YES")){
                    $affData[$v['COUPON_PAGE_ID']]['HAS_AFFILIATE'] = 1;
                }else{
                    $affData[$v['COUPON_PAGE_ID']]['HAS_AFFILIATE'] = 0;
                }
            }

            echo microtime(true)."\n";
            //coupon统计信息
            $sql = "select a.COUPON_PAGE_ID,a.PROMO_COUNT_ACTIVE,a.COUPON_COUNT_ACTIVE,a.PROMO_COUNT_EXPIRE,a.COUPON_COUNT_EXPIRE from {$content_table} as a left join bo_entity_page as b on(a.COUPON_PAGE_ID=b.UUID) where b.SITE_ID='{$site_id}' and a.date='{$date}'";
            $contentData = $model->query($sql);
            foreach($contentData as $k=>$v){
                unset($contentData[$k]);
                $contentData[$v['COUPON_PAGE_ID']] = $v;
            }

            echo microtime(true)."\n";

            //outClicks统计信息
            $sql = "select a.COUPON_PAGE_ID,a.CLICK from bo_entity_promo_mk_site_page_sampling_internal as a left join bo_entity_page as b on(a.COUPON_PAGE_ID=b.UUID) where b.SITE_ID='{$site_id}' and a.date='{$date}'";
            $clickData = $model->query($sql);
            foreach($clickData as $k=>$v){
                unset($clickData[$k]);
                $clickData[$v['COUPON_PAGE_ID']] = $v;
            }

            echo microtime(true)."\n";

            foreach($termList as $k=>$v){
                $termList[$k]['ADDTIME'] = date('Y-m-d H:i:s');
                $termList[$k]['DATE'] = $date;
                $termList[$k]['IMPRESSIONS'] = isset($gwtData[$v['COUPON_PAGE_ID']])?$gwtData[$v['COUPON_PAGE_ID']]['IMPRESSION']:'';
                $termList[$k]['POSITION'] = isset($gwtData[$v['COUPON_PAGE_ID']])?$gwtData[$v['COUPON_PAGE_ID']]['POSITION']:'';
                $termList[$k]['CTR'] = isset($gwtData[$v['COUPON_PAGE_ID']])?$gwtData[$v['COUPON_PAGE_ID']]['CTR']:'';
                $termList[$k]['BR'] = isset($gaData[$v['COUPON_PAGE_ID']])?$gaData[$v['COUPON_PAGE_ID']]['BOUNSE_RATE']:'';
                $termList[$k]['DURATION'] = isset($gaData[$v['COUPON_PAGE_ID']])?$gaData[$v['COUPON_PAGE_ID']]['AVG_SESSION_DURATION']:'';
                $termList[$k]['PAGE_PER_SESSION'] = isset($gaData[$v['COUPON_PAGE_ID']])?$gaData[$v['COUPON_PAGE_ID']]['ACCESS_PAGE_NUM_PER_SESSION']:'';
                $termList[$k]['SESSION'] = isset($gaData[$v['COUPON_PAGE_ID']])?$gaData[$v['COUPON_PAGE_ID']]['SESSION']:'';
                $termList[$k]['OUTCLICK'] = isset($clickData[$v['COUPON_PAGE_ID']])?$clickData[$v['COUPON_PAGE_ID']]['CLICK']:'';
                $termList[$k]['REVENUE'] = isset($revData[$v['COUPON_PAGE_ID']])?$revData[$v['COUPON_PAGE_ID']]['SEO_REVENUE']:'';
                $termList[$k]['RPI'] = !empty($termList[$k]['SESSION'])?round($termList[$k]['REVENUE']/$termList[$k]['SESSION'],3):'';
                $termList[$k]['HAS_AFFILIATE'] = isset($affData[$v['COUPON_PAGE_ID']])?$affData[$v['COUPON_PAGE_ID']]['HAS_AFFILIATE']:'';
                $termList[$k]['CODES'] = isset($contentData[$v['COUPON_PAGE_ID']])?$contentData[$v['COUPON_PAGE_ID']]['COUPON_COUNT_ACTIVE']+$contentData[$v['COUPON_PAGE_ID']]['COUPON_COUNT_EXPIRE']:'';
                $termList[$k]['PROMOS'] = isset($contentData[$v['COUPON_PAGE_ID']])?$contentData[$v['COUPON_PAGE_ID']]['PROMO_COUNT_ACTIVE']+$contentData[$v['COUPON_PAGE_ID']]['PROMO_COUNT_EXPIRE']:'';
                $termList[$k]['SERP_POS'] = isset($benchData[$v['COUPON_PAGE_ID']])?$benchData[$v['COUPON_PAGE_ID']]['SERP_POS']:'';
                $termList[$k]['SERP_CTR'] = isset($benchData[$v['COUPON_PAGE_ID']])?$benchData[$v['COUPON_PAGE_ID']]['SERP_CTR']:'';
                $termList[$k]['LP_BR'] = isset($benchData[$v['COUPON_PAGE_ID']])?$benchData[$v['COUPON_PAGE_ID']]['LP_BR']:'';
                $termList[$k]['PAGE_VALUE'] = isset($benchData[$v['COUPON_PAGE_ID']])?$benchData[$v['COUPON_PAGE_ID']]['PAGE_VALUE']:'';
                $termList[$k]['POS_CHANGE'] = !empty($termList[$k]['SERP_POS'])?($termList[$k]['POSITION']-$termList[$k]['SERP_POS'])/$termList[$k]['SERP_POS']:'';
                $termList[$k]['CTR_CHANGE'] = !empty($termList[$k]['SERP_CTR'])?($termList[$k]['CTR']-$termList[$k]['SERP_CTR'])/$termList[$k]['SERP_CTR']:'';
                $termList[$k]['BR_CHANGE'] = !empty($termList[$k]['LP_BR'])?($termList[$k]['BR']-$termList[$k]['LP_BR'])/$termList[$k]['LP_BR']:'';
                $termList[$k]['INTERNAL_LINKS'] = '';
                //var_dump($termList[$k]);die;

            }
            //var_dump($termList);die;
            echo microtime(true)."\n";

            foreach($termList as $k=>$v){
                $res = $test_base->add($v, [], TRUE);
                echo $res."\n";
            }

            echo microtime(true)."\n";
        }







}