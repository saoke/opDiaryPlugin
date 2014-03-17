<?php
use_helper('opDiary');

$data = array('comments'=>array());

if (count($comments))
{
  foreach ($comments as $comment)
  {
    $_comment =  op_api_diary_comment($comment);
    $_comment['deletable'] = $comment->isDeletable($memberId);
    $data['comments'][] = $_comment;
  }
}

return array(
  'status' => 'success',
  'data' => $data,
);
