<?php
namespace Home\Model;
use Think\Model;
class SamplingAffModel extends Model {
    protected $tableName = 'bo_entity_promo_mk_site_term_page_sampling_affiliate';
    public function geAFFData($map, $termFlag = FALSE)
    {
        $table = $termFlag ? 'bo_entity_promo_mk_site_term_page_sampling_affiliate' : 'bo_entity_promo_mk_site_merchant_page_sampling_affiliate';
        $yesFlag = '590dd167d5414';
        $sql = sprintf("SELECT count(1) as AFF_COUNT FROM  `%s` AS sa LEFT JOIN `bo_entity_page` AS p ON sa.`COUPON_PAGE_ID` = p.`UUID` WHERE p.`SITE_ID` = '%s' AND sa.`DATE` = '%s' AND sa.`HAS_AFFILIATE`='%s' ", $table, $map['COUPON_SITE_ID'], $map['DATE'], $yesFlag);
        $data = $this->query($sql);
        return $data[0];
    }
}