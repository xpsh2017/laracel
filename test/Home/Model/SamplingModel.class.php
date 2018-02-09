<?php 
namespace Home\Model;
use Think\Model;
class SamplingModel extends Model {
        public function getData($map)
        {
            $field = $this->field;
            $data = $this->field($field)->where($map)->find();
            return isset($data)? $data: [];
        }
        public function getList($map, $fieldType='A')
        {
            $field = $this->field;
            $dateField = ['DATE'];
            $field = array_merge($dateField, $field);
            $list = $this->field($field)->where($map)->select();
            return $list;
        }
}