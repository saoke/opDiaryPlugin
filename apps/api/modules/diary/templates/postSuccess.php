<?php
use_helper('opDiary');
$data = op_api_diary($diary);

return array(
  'status' => 'success',
  'data' => $data,
);
