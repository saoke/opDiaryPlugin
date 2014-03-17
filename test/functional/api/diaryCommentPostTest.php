<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$t = new opTestFunctional(new sfBrowser());

include dirname(__FILE__).'/../../bootstrap/database.php';

$mailAddress = 'sns1@example.com';

$t->login($mailAddress, 'password');
$t->setCulture('en');

$t->info('should be able to post a new comment');
$diary = Doctrine::getTable('Diary')->findOneByMemberId(1);
$body = 'コメントテスト本文';
$json = $t->post('/diary_comment/post.json',
    array(
      'apiKey'   => 'dummyApiKey',
      'diary_id' => $diary->getId(),
      'body'     => $body,
    )
  )->getResponse()->getContent()
;
$data = json_decode($json, true);
$t->test()->is($data['status'], 'success', 'should return status code "success"');
$t->test()->ok($data['data']['id'], 'should have id');
$t->test()->ok($data['data']['member'], 'should have member info');
$t->test()->is($data['data']['diary_id'], $diary->getId(), 'should have the same diary_id posted');
$t->test()->is($data['data']['body'], $body, 'should have the same body posted');
$t->test()->ok($data['data']['created_at'], 'should have the date posted');
$t->test()->is($data['data']['images'], array(), 'should have the images field which is an empty array');

$t->info('should NOT be able to post a new comment without body');
$diary = Doctrine::getTable('Diary')->findOneByMemberId(1);
$body = '';
$json = $t->post('/diary_comment/post.json',
    array(
      'apiKey'   => 'dummyApiKey',
      'diary_id' => $diary->getId(),
      'body'     => $body,
    )
  )
  ->with('response')->begin()
    ->isStatusCode(400)
  ->end()
;

$json = $t->post('/diary_comment/post.json',
    array(
      'apiKey'   => 'dummyApiKey',
      'diary_id' => $diary->getId(),
    )
  )
  ->with('response')->begin()
    ->isStatusCode(400)
  ->end()
;

$t->info('存在しない日記にコメント');
$json = $t->post('/diary_comment/post.json',
array(
'apiKey' => 'dummyApiKey',
'diary_id' => 0,
'body' => 'コメント本文',
)
)
->with('response')->begin()
->isStatusCode('400')
->end()
;
