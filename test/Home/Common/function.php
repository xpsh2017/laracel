<?php 
function siteList()
{
    return C('SITE_LIST');
}
function defaultSite()
{
    $data['SITE_UUID'] = cookie('SITE_UUID');
    $data['SITE_NAME'] = cookie('SITE_NAME');
    $data['SITE_DATE'] = cookie('SITE_DATE');
    if(empty($data['SITE_UUID']))
    {
        $data = current(C('SITE_LIST'));
    }
    if(empty($data['SITE_DATE']))
    {
        $diffDay = C('DEFAUTL_DATE');
        $data['SITE_DATE'] = date('Y-m-d', strtotime($diffDay));
    }
    return $data;
}

function getVersion()
{
    return APP_DEBUG ? rand(0, 50000): C('STATIC_VERSION');
}

function isTerm($siteUUID)
{
    $siteList = C('SITE_LIST');
    $termList = C('TERM_LIST');
    $termFlag = FALSE;
    foreach ($siteList as $site) {
        if($site['SITE_UUID'] == $siteUUID)
        {
            if(in_array($site['SITE_NAME'], $termList))
            {
                $termFlag = TRUE;   
                break;
            }
        }
    }
    return $termFlag;
}

function getPanelName($name)
{
    $panelList = C('PANEL_LIST');
    return isset($panelList[$name]) ? $panelList[$name] : '';
}
function getSubPanelName($name)
{
    $subPanelList = C('SUB_PANEL_LIST');
    $metricList = C('SITEDAILY_METRIC_LIST');
    if(isset($metricList[$name]))
    {
        return $metricList[$name]. '- 商家列表';
    }else{
        return isset($subPanelList[$name]) ? $subPanelList[$name] : '';    
    }
    
}
function transferKeyToZN($data, $panelName)
{
    $list = C('SITEDAILY_METRIC_LIST_V2');
    $newData = [];
    foreach ($data as $k => $v) {
        if(isset($list[$panelName][$k]))
            $newData[$list[$panelName][$k]] = $v;
    }
    return $newData;
}

function getCacheKey($a, $b, $c)
{
    return 'pmi_'.$a.'_'.$b. '_'.$c;
}

function insertDataLocal($data, $table, $cacheKey)
{
    $model = new Think\Model('', '', C('LOCAL_DB_STR'));
    $flag = $model->table($table)->add($data, [], TRUE);
    //echo $model->getDBError();die;
    //echo $model->_sql();die;
    if($flag)
        S($cacheKey, 1);
}
function existKey($siteUUID, $siteDate, $pageType)
{
    $cacheKey = getCacheKey($siteUUID, $siteDate, $pageType);
    $cacheFlag = S($cacheKey);
    return !empty($cacheFlag);
}