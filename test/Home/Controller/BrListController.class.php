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
class BrListController extends Controller {
    public function index(){
        $model = new Model;
        $sites = C('SITES');
        $site = I('post.site','PPUS');

        $site = isset($_COOKIE['SITE_NAME'])?$_COOKIE['SITE_NAME']:$site;

        $order = I('post.order','brChange');
        $sort = I('post.sort','desc');
        $page = I('post.page', 1);
        $limit = I('post.limit', 50);
        $site_id = $sites[$site]['site_id'];
        $site_url = $sites[$site]['site_url'];
        $where = " where a.SITE_ID = '{$site_id}'";
        $where .=" and b.SESSION>=10";

        $sql = "select DATE from bo_entity_promo_mk_site_page_sampling_ga where OBJNAME LIKE '%{$site_url}%' order by date desc limit 1";

        $gaUpdate = $model->query($sql);

        $sql  = "SELECT DATE FROM bo_entity_ext_gsc_data_search_analytics_page WHERE URL like '{$site_url}%' order by date desc limit 1";

        $gwtUpdate = $model->query($sql);

        $date = I('post.date', $gaUpdate[0]['DATE']);
        //var_dump($date);die;
        //$date = isset($_COOKIE['BR_SITE_DATE'])?$_COOKIE['SITE_DATE']:$date;

        $where .=" and b.date='{$date}'";
        /*$sql = "SELECT a.UUID,a.URL,b.POSITION FROM bo_entity_page as a LEFT JOIN bo_entity_ext_gsc_data_search_analytics_page AS b ON(b.COUPON_PAGE_ID = a.UUID and b.DATE='{$gwtUpdate[0]['DATE']}') {$where}";

        $termList = $model->query($sql);
        $sql = "SELECT COUPON_PAGE_ID,SESSION,BOUNSE_RATE as BR FROM bo_entity_promo_mk_site_page_sampling_ga where OBJNAME LIKE '%{$site_url}%' and DATE='{$date}'";
        $gaData = $model->query($sql);*/

        $sql = "SELECT a.UUID,a.URL,b.SESSION,b.BOUNSE_RATE as BR FROM bo_entity_page as a LEFT JOIN bo_entity_promo_mk_site_page_sampling_ga AS b ON(b.COUPON_PAGE_ID = a.UUID) {$where}";
        //var_dump($sql);die;
        $termList = $model->query($sql);
        //var_dump($termList);die;
        $sql = "SELECT COUPON_PAGE_ID,POSITION FROM bo_entity_ext_gsc_data_search_analytics_page where URL LIKE '%{$site_url}%' and DATE='{$gwtUpdate[0]['DATE']}'";
        $gwtData = $model->query($sql);
        //var_dump($gaData);die;
        foreach($gwtData as $k=>$v){
            unset($gwtData[$k]);
            $gwtData[$v['COUPON_PAGE_ID']] = $v;
        }
        $benchData = $this->get_bench_data($sites[$site]['site_id'],$sites[$site]['type']);
        //var_dump($benchData);die;

        foreach($termList as $k=>$v){

            $termList[$k]['todayPos'] = isset($gwtData[$v['UUID']])?number_format($gwtData[$v['UUID']]['POSITION'],1):'';
            $termList[$k]['todayBR'] = $v['BR'];
            $termList[$k]['serpBR'] = isset($benchData[$v['UUID']])?$benchData[$v['UUID']]['LP_BR']:'';
            $termList[$k]['brChange'] = abs($termList[$k]['todayBR']-$termList[$k]['serpBR'])/$termList[$k]['serpBR'];
            if($termList[$k]['brChange']<0.1){
                unset($termList[$k]);
            }else{
                if($order){
                    $orderList[$k] = $termList[$k][$order];
                    if(empty($termList[$k][$order])&&$sort == 'asc'){
                        $orderList[$k] = '999999999';
                    }
                    if(empty($termList[$k][$order])&&$sort == 'desc'){
                        $orderList[$k] ='0';
                    }
                }

                $termList[$k]['brChange'] = number_format(100*abs($termList[$k]['todayBR']-$termList[$k]['serpBR'])/$termList[$k]['serpBR'],2).'%';
                $termList[$k]['todayBR'] = !empty($v['BR'])?number_format(100*$v['BR'],2).'%':'';
                $termList[$k]['serpBR'] = isset($benchData[$v['UUID']])?number_format(100*$benchData[$v['UUID']]['LP_BR'],2).'%':'';

            }
        }
        //var_dump($orderList);die;

        $count = count($termList);
        $limit = $limit<1?1:$limit;
        $pages = ceil($count/$limit);
        $page = $page<1?1:$page;
        $page = $page > $pages? $pages : $page;
        $start = $limit*($page-1);
        $end = $start+$limit-1;

        $startPage = $page+10>$pages?$pages-9:$page;
        $endPage = $startPage+10;

        if($pages<10){
            $startPage = 1;
            $endPage = $pages+1;
        }

        $sort = $sort=='desc' ? SORT_DESC : SORT_ASC;
        array_multisort($orderList, $sort, $termList);
        $brList = [];
        $uuidList =[];
        foreach($termList as $k=>$v){
            if($k>=$start && $k<=$end){
                $brList[$k] = $termList[$k];
                $uuidList[] = $v['UUID'];
            }
        }

        $uuidList = implode(',',$uuidList);
        //var_dump($brList);die;

        $pageList = [
            'page'=>$page,
            'pages'=>$pages,
            'startPage'=>$startPage,
            'endPage'=>$endPage,
            'total'=>$count
        ];
        $search = $_POST;
        $this->assign('termList',$brList);
        $this->assign('uuidList',$uuidList);
        $this->assign('type',$sites[$site]['type']);
        $this->assign('site',$site);
        $this->assign('sites',$sites);
        $this->assign('search',$search);
        $this->assign('date',$date);
        $this->assign('pageList', $pageList);
        $this->display();
    }


    public function get_detail(){

        $model = new Model;
        $id = I('get.uuid');
        $id = empty($id)?I('post.uuid'):$id;
        $type = I('get.type');
        $type = empty($type)?I('post.type'):$type;

        $endDate = I('get.date');
        $endDate = empty($endDate)?I('post.date'):$endDate;
        $startDate = I('post.start',date("Y-m-d",strtotime($endDate."-13 days")));

        if($type=='term'){
            $benchTable = 'bo_entity_promo_mk_site_term_page_benchmark';
            $contentTable = 'bo_entity_promo_mk_site_term_page_sampling_content';
        }else{
            $benchTable = 'bo_entity_promo_mk_site_merchant_page_benchmark';
            $contentTable = 'bo_entity_promo_mk_site_merchant_page_sampling_content';
        }


        $brList =[];
        //var_dump($id);var_dump($type);die;
        $sql = "select LP_BR from {$benchTable} where COUPON_PAGE_ID='{$id}'";
        $serpList = $model->query($sql);

        $n = (strtotime($endDate)-strtotime($startDate))/(3600*24);
        for($i=0;$i<=$n;$i++){
            $d1 = date("Y-m-d",strtotime($endDate."-{$i} days"));

            $last_D30 = date("Y-m-d",strtotime($d1."-29 days"));
            $sql = "SELECT COUPON_PAGE_ID,POSITION FROM bo_entity_ext_gsc_data_search_analytics_page where COUPON_PAGE_ID='{$id}' and DATE='{$d1}'";
            $posList = $model->query($sql);
            $sql = "SELECT COUPON_PAGE_ID,SESSION,BOUNSE_RATE as BR FROM bo_entity_promo_mk_site_page_sampling_ga where COUPON_PAGE_ID='{$id}' and DATE='{$d1}'";
            $gaList = $model->query($sql);
            $brList[$d1]['todayBR'] = isset($gaList[0]['BR'])?number_format(100*$gaList[0]['BR'],2).'%':'';
            $brList[$d1]['todayPos'] = isset($posList[0]['POSITION'])?number_format($posList[0]['POSITION'],1):'';
            $brList[$d1]['serpBR'] = number_format(100*$serpList[0]['LP_BR'],2).'%';
            $brList[$d1]['brChange'] = number_format(100*abs($brList[$d1]['todayBR']-$brList[$d1]['serpBR'])/$brList[$d1]['serpBR'],2).'%';
            $sql = "SELECT COUPON_PAGE_ID,COUPON_COUNT_ACTIVE,PROMO_COUNT_ACTIVE,PROMO_COUNT_ADD,COUPON_COUNT_ADD,PROMO_COUNT_EXPIRE,COUPON_COUNT_EXPIRE FROM {$contentTable} where COUPON_PAGE_ID='{$id}' and DATE='{$d1}'";
            $todayCodeData =  $model->query($sql);
            /*$sql = "SELECT COUPON_PAGE_ID,COUPON_COUNT_ACTIVE,PROMO_COUNT_ACTIVE FROM {$contentTable} where COUPON_PAGE_ID='{$id}' and DATE='{$yesterday}'";
            $lastCodeData =  $model->query($sql);*/
            $brList[$d1]['COUPON_COUNT_ACTIVE'] = $todayCodeData[0]['COUPON_COUNT_ACTIVE'];
            $brList[$d1]['COUPON_COUNT_ADD'] = $todayCodeData[0]['COUPON_COUNT_ADD'];
            $brList[$d1]['COUPON_COUNT_EXPIRE'] = $todayCodeData[0]['COUPON_COUNT_EXPIRE'];
            $brList[$d1]['PROMO_COUNT_ACTIVE'] = $todayCodeData[0]['PROMO_COUNT_ACTIVE'];
            $brList[$d1]['PROMO_COUNT_ADD'] = $todayCodeData[0]['PROMO_COUNT_ADD'];
            $brList[$d1]['PROMO_COUNT_EXPIRE'] = $todayCodeData[0]['PROMO_COUNT_EXPIRE'];
            $sql = "SELECT COUPON_PAGE_ID,DATE,LAST_PROMO_ADD_TIME FROM {$contentTable} WHERE COUPON_PAGE_ID='{$id}' AND DATE <= '{$date}' AND DATE >= '{$last_D30}'";
            $offsetData = $model->query($sql);
            $updateOffset = 0;
            foreach($offsetData as $k=>$v){
                $offset = floor((strtotime($v['DATE'])-strtotime($v['LAST_PROMO_ADD_TIME']))/3600);
                $offset = $offset>0?$offset:0;
                $updateOffset += $offset;
            }
            $brList[$d1]['offset'] = number_format($updateOffset/30,2);

        }
        //var_dump($brList);die;
        $search = $_POST;
        $this->assign('search',$search);
        $this->assign('termList',$brList);
        $this->assign('uuid',$id);
        $this->assign('type',$type);
        $this->assign('date',$endDate);
        $this->assign('start',$startDate);
        $this->display();
    }


    public function get_bench_data($site,$type){
        if($type=='merchant'){
            $bench_db = M('bo_entity_promo_mk_site_merchant_page_benchmark');
        }else{
            $bench_db = M('bo_entity_promo_mk_site_term_page_benchmark');
        }
        $map['b.SITE_ID']  = $site;
        //$sql  = "select a.COUPON_PAGE_ID,a.POSITION,a.date from bo_entity_ext_gsc_data_search_analytics_page as a left join bo_entity_page as b on(a.COUPON_PAGE_ID=b.UUID) where b.SITE_ID='{$site}' and a.DATE BETWEEN '{$begin}' and '{$end}'";
        $benchData = $bench_db->alias('a')->field('a.COUPON_PAGE_ID,a.LP_BR')->join('left join bo_entity_page as b on a.COUPON_PAGE_ID=b.UUID')->where($map)->select();
        //var_dump($bench_db->getLastSql());die;
        foreach($benchData as $k=>$v){
            $benchData[$v['COUPON_PAGE_ID']] = $v;

            unset($benchData[$k]);
        }
        return $benchData;

    }


    public function get_offset_data(){

        $model = new Model;
        $type = I('post.type');
        if($type=='merchant'){
            $content_table = 'bo_entity_promo_mk_site_merchant_page_sampling_content';
        }else{
            $content_table = 'bo_entity_promo_mk_site_term_page_sampling_content';
        }
        $uuidList = I('post.uuidList');

        $uuidList = "'".str_replace(',',"','",$uuidList)."'";

        $date = I('post.date', date("Y-m-d",strtotime("-1 days")));
        $last = date("Y-m-d",strtotime($date."-29 days"));
        $sql = "SELECT COUPON_PAGE_ID,DATE,LAST_PROMO_ADD_TIME FROM {$content_table} WHERE COUPON_PAGE_ID in({$uuidList}) AND DATE <= '{$date}' AND DATE >= '{$last}'";

        $couponData = $model->query($sql);
        foreach($couponData as $k=>$v){
            unset($couponData[$k]);
            $offset = floor((strtotime($v['DATE'])-strtotime($v['LAST_PROMO_ADD_TIME']))/3600);
            $offset = $offset>0?$offset:0;
            $couponData[$v['COUPON_PAGE_ID']]['offset'] = isset($couponData[$v['COUPON_PAGE_ID']]['offset'])?$couponData[$v['COUPON_PAGE_ID']]['offset']+$offset:$offset;
        }
        $str = '';
        foreach($couponData as $k=>$v){
            $avg_offset = number_format($v['offset']/30,2);
            $str .= $k.'_'.$avg_offset.',';
        }
        //echo $str;
        $this->ajaxReturn($str);
    }

    public function get_code_change(){
        $model = new Model;
        $type = I('post.type');

        if($type=='merchant'){
            $content_table = 'bo_entity_promo_mk_site_merchant_page_sampling_content';
        }else{
            $content_table = 'bo_entity_promo_mk_site_term_page_sampling_content';
        }
        $uuidList = I('post.uuidList');
        //$uuidList = '5971cc62854ee,5971dd38678ca,5971d59e3599c,5971a9d59cfc6,59703e3c4ca20,596f2f7c6bbdb,5971a8c67ddea,59706d79cd8ce,59717d5fbb768,5971dd49094d4,596f63527d6e7,59706576dd0ef,59706fd817487,597172c9d6ce0,59703aa51015e,5970358d295ff,5971d4b31e379,5971a1b02b0ab,597092083f675,5970355f3dac5,596f65660f10c,597065044085f,596f473d3b236,59703e020731b,59719e3c3b6da,59719e61493d7,5971acc31bcf0,59703697d53f5,5971a93a150c2,597043b74020b,596f2d0ad70fb,596f2d0f54931,596f2d1f326d1,596f2d23dea11,596f2d2716988,596f2d2bdecc2,596f2d32648fe,596f2d43694e8,596f2d493a9d2,596f2d4dae19e,596f2d50e4f7a,596f2d53143a4,596f2d575db72,596f2d5b78ef9,596f2d5e1baf0,596f2d638fee4,596f2d6709f51,596f2d6aa9d52,596f2d6e3c351,596f2d794d32c,596f2d7c2db8b,596f2d7e51ea0,596f2d880797d,596f2d8ae3150,596f2d8fcf8be,596f2da3c41ec,596f2dae975ab,596f2db155a18,596f2db7e886a,596f2dbd59a57,596f2dc4a45da,596f2dcae2d58,596f2dce007f8,596f2dd070c12,596f2dd8e02a6,596f2de328571,596f2de558d8b,596f2de9b1653,596f2ded3cd6c,596f2df2b24f2,596f2df85db36,596f2dfacb8fc,596f2dfe60094,596f2e0106dbb,596f2e0769384,596f2e0a28957,596f2e0d07fe9,596f2e102c432,596f2e19be104,596f2e1f1bb25,596f2e21f0672,596f2e24667ed,596f2e27898de,596f2e2cc5766,596f2e320d3ab,596f2e3493c82,596f2e3727d33,596f2e3e892fa,596f2e4478583,596f2e4792bbe,596f2e4a1350d,596f2e4cf3724,596f2e5546603,596f2e57b8371,596f2e5eb01ef,596f2e6528399,596f2e67bf2b8,596f2e6b01d7b,596f2e6dd1fb9,596f2e707d1b2';
        $uuidList = "'".str_replace(',',"','",$uuidList)."'";
        $date = I('post.date', date("Y-m-d",strtotime("-1 days")));

        $sql = "SELECT COUPON_PAGE_ID,COUPON_COUNT_ACTIVE,PROMO_COUNT_ACTIVE,PROMO_COUNT_ADD,COUPON_COUNT_ADD,PROMO_COUNT_EXPIRE,COUPON_COUNT_EXPIRE FROM {$content_table} WHERE COUPON_PAGE_ID in({$uuidList}) AND DATE = '{$date}'";

        $todayCodeData = $model->query($sql);

        $data = [
            'code'=>'',
            'promo'=>'',
        ];
        foreach($todayCodeData as $k=>$v){
            $data['promo'] .= $v['COUPON_PAGE_ID'].'_'.$v['PROMO_COUNT_ACTIVE'].',';
            $data['code'] .= $v['COUPON_PAGE_ID'].'_'.$v['COUPON_COUNT_ACTIVE'].',';
            $data['promoAdd'] .= $v['COUPON_PAGE_ID'].'_'.$v['PROMO_COUNT_ADD'].',';
            $data['codeAdd'] .= $v['COUPON_PAGE_ID'].'_'.$v['COUPON_COUNT_ADD'].',';
            $data['promoExpire'] .= $v['COUPON_PAGE_ID'].'_'.$v['PROMO_COUNT_EXPIRE'].',';
            $data['codeExpire'] .= $v['COUPON_PAGE_ID'].'_'.$v['COUPON_COUNT_EXPIRE'].',';
        }
        //echo $str;
        $this->ajaxReturn($data);
    }
}


