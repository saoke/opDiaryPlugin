<?php

class opDiaryPluginUtil
{
  public static function sendNotification($fromMember, $toMember, $diaryId){
    $url = '/diary/'.$diaryId;
    
    sfApplicationConfiguration::getActive()->loadHelpers(array('I18N'));
    $message = format_number_choice('[1]1 diary has new comments|(1,Inf]%1% diaries have new comments', array('%1%'=>'1'), 1);
    
    opNotificationCenter::notify($fromMember, $toMember, $message, array('category'=>'other', 'url'=>$url, 'icon_url'=>null));
  }
}
