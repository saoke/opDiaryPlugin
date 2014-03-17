<?php
use_helper('opDiary');
$data = op_api_diary_comment($comment);
$data['deletable'] = $comment->isDeletable($memberId);
unset($data['images']);

return array(
  'status' => 'success',
  'data' => $data,
);
