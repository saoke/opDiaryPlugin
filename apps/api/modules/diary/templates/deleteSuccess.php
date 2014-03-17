<?php
use_helper('opDiary');
$data = array();
if(isset($diary))
{
  $data = op_api_diary($diary);
}
return array(
  'status' => 'success',
  'data' => $data,
);
