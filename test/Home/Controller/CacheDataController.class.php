<?php
namespace Home\Controller;
use Think\Controller;
class CacheDataController extends Controller {
    public $apiList = [
        'getBasicData' => 'SiteDaily/getBasicData',
        'getCouponContentData' => 'SiteDaily/getCouponContentData',
        'getGGData' => 'SiteDaily/getGGData',
        'getDataV2' => 'SiteDaily/getDataV2'
    ];
    public $apiRoot = 'http://markchu:chukui123456@pmi.meikaiinfotech.com/index.php/';
    public $user = 'markchu';
    public $pwd = 'chukui123456';
    public function checkRequest()
    {
        if(!IS_POST)
            $this->error('Method not Allowed!');
    }
    public function index($siteUUID, $siteDate, $source)
    {
        if(!IS_CLI)
            $this->checkRequest();
        $posts = [
            'siteUUID' => $siteUUID,
            'siteDate' => $siteDate,
            'refreshCache' => 1
        ];
        $url = $this->apiRoot. $this->apiList[$source];
        $data = $this->postByCurl($url, $posts,);
        if(IS_CLI)
            return $data;
        else
            $this->ajaxReturn($data);
    }

    public function historyData()
    {
        $startDate = '2017-01-01';
        $endDate = '2017-09-15';
        $siteList = C('SITE_LIST');
        foreach ($siteList as $k => $v) {
            foreach ($this->apiList as $kk => $vv) {
                for($i=$startDate; strtotime($i)<= strtotime($endDate); $i = date('Y-m-d', strtotime($i. ' +1 day')))
                {
                    $info = $this->index($v['SITE_UUID'], $i, $kk);
                    if($info['http_code'] == 200)
                    {
                        $cacheKey = 'LOCAL_'.$v['SITE_NAME']. '_'.$kk.'_'.$i;
                    }else{
                        $cacheKey = 'FAILED_'.$v['SITE_NAME']. '_'.$kk.'_'.$i;
                    }
                    S($cacheKey, 1);
                    echo $cacheKey. PHP_EOL;
                }
            }
        }
    }

    public function postByCurl($url, $posts, $user=null, $pwd=null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, $posts ? 0 :1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
        if($user)
        {
            curl_setopt($ch, CURLOPT_USERPWD, "{$user}:{$pwd}");  
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);     
        }
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $info;
        
    }
}