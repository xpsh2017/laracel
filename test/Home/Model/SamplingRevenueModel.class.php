<?php
namespace Home\Model;
use Think\Model;
class SamplingRevenueModel extends Model {
    protected $tableName = 'bo_entity_promo_mk_site_page_sampling_revenue_medium';

    public function getRevenueData($map)
    {
        $sql = sprintf("SELECT rm.OBJNAME,rm.SEO_REVENUE FROM `bo_entity_promo_mk_site_page_sampling_revenue_medium` AS rm LEFT JOIN `bo_entity_page` AS p ON rm.`COUPON_PAGE_ID` = p.`UUID` WHERE p.`SITE_ID` = '%s' AND rm.`DATE` = '%s' AND rm.`SEO_REVENUE` > 0 ORDER BY rm.`SEO_REVENUE` DESC", $map['COUPON_SITE_ID'], $map['DATE']);
        $data = $this->query($sql);
        $maxLp = current($data);
        $revenueData['Max_REVENUE'] = $maxLp['SEO_REVENUE'];
        if(preg_match('/http(.*?) /', $maxLp['OBJNAME'], $match))
        {
            $revenueData['Max_REVENUE_LP'] = isset($match[0])? $match[0]: '';
        }
        $revenueData['TOTAL_REVENUE'] = 0.00;
        $revenueData['PAGE_COUNT_HAS_REVENUE'] = count($data);
        foreach ($data as $k => $v) {
            $revenueData['TOTAL_REVENUE'] += $v['SEO_REVENUE'];
        }
        return $revenueData;
    }

    public function getList()
    {
        
    }
}