<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$t = new opTestFunctional(new sfBrowser());

include dirname(__FILE__).'/../../bootstrap/database.php';

$mailAddress = 'sns1@example.com';

$t->login($mailAddress, 'password');
$t->setCulture('en');

$t->info('should be able to delete a comment');
$comment = Doctrine::getTable('DiaryComment')->findOneByMemberId(1);
$body = 'コメントテスト本文';
$json = $t->post('/diary_comment/delete.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'id'       => $comment->getId(),
    )
  )->getResponse()->getContent()
;
$data = json_decode($json, true);
$t->test()->is($data['status'], 'success', 'should return status code "success"');
$t->test()->ok($data['data']['id'], 'should have id');

$t->info('should NOT be able to delete a comment of other\'s on other\'s diary');
$diary = Doctrine::getTable('Diary')->findOneByMemberId(5);
$comment = new DiaryComment();
$comment->setMemberId(5);
$comment->setDiaryId($diary->getId());
$comment->setBody('not to be deleted');
$comment->save();
$json = $t->post('/diary_comment/delete.json',
    array(
      'apiKey' => 'dummyApiKey',
      'id'     => $comment->getId(),
    )
  )
  ->with('response')->begin()
    ->isStatusCode(400)
  ->end()
;

$t->info('存在しないコメントの削除');
$json = $t->post('/diary_comment/delete.json',
array(
'apiKey' => 'dummyApiKey',
'id' => '0',
)
)
->with('response')->begin()
->isStatusCode('400')
->end()
;
