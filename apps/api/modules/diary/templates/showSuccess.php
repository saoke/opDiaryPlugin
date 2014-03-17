<?php
use_helper('opDiary');
$data = array();

if (isset($diary))
{
  $data = op_api_diary($diary);
  $images = $diary->getDiaryImages();
  foreach($images as $image){
    $data['images'][] = op_api_diary_image($image);
  }
  $nextDiary = $diary->getNext($diary->getMemberId());
  ($nextDiary != false) ? $data['next'] = $nextDiary->getId() : $data['next'] = null;
  $prevDiary = $diary->getPrevious($diary->getMemberId());
  ($prevDiary != false) ? $data['prev'] = $prevDiary->getId() : $data['prev'] = null;
  $data['editable'] = $diary->isAuthor($memberId);
}

return array(
  'status' => 'success',
  'data' => $data,
);
