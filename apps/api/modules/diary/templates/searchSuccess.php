<?php
use_helper('opDiary', 'I18N');

$data = array();

if (count($diaries))
{
  foreach ($diaries as $diary)
  {
    $data[] = op_api_diary($diary);
  }
}

return array(
  'status' => 'success',
  'data' => $data,
  'next' => $next,
);
