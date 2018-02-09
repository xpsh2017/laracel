<?php
namespace Home\Model;
use Think\Model;
class SamplingGscModel extends SamplingModel {
    protected $tableName = 'bo_entity_promo_mk_site_sampling_gsc';
    public $field = ['INDEX_PAGE_NUM', 'AVG_POS', 'IMPRESSION_MT_5_PAGE_NUM', 'CLICK_MT_5_PAGE_NUM', 'POS_TOP_3_PAGE_NUM', 'POS_TOP_5_PAGE_NUM', 'POS_TOP_10_PAGE_NUM'];

    public function getDataLocal($map)
    {
        $model = $this->db(1, C('LOCAL_DB_STR'));
        $data = $model->table('mk_ga_traffic_status')->where($map)->find();
        unset($data['COUPON_SITE_ID']);
        unset($data['DATE']);
        $this->db(0);
        return isset($data) ? $data : [];
    }
}