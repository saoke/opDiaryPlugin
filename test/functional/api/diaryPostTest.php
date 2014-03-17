<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$t = new opTestFunctional(new sfBrowser());

include dirname(__FILE__).'/../../bootstrap/database.php';

$mailAddress = 'sns1@example.com';
$myFriendMailAddress = 'sns2@example.com';
$notMyFriendMailAddress = 'sns5@example';

$t->login($mailAddress, 'password');
$t->setCulture('en');

$t->info('should be able to post a new diary entry');
$title = 'テストタイトル';
$body = 'テスト本文';
$publicFlag = PluginDiaryTable::PUBLIC_FLAG_SNS;//全員に公開
$json = $t->post('/diary/post.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'title'       => $title,
      'body'        => $body,
      'public_flag' => $publicFlag,
    )
  )->getResponse()->getContent()
;
$data = json_decode($json, true);
$t->test()->is($data['status'], 'success', 'should return status code "success"');
$t->test()->ok($data['data']['id'], 'should have id');
$t->test()->ok($data['data']['member'], 'should have member info');
$t->test()->is($data['data']['title'], $title, 'should have the same title posted');
$t->test()->is($data['data']['body'], $body, 'should have the same body posted');
$t->test()->is($data['data']['public_flag'], $publicFlag, 'should have the same publid flag posted');
$t->test()->ok($data['data']['created_at'], 'should have the date posted');

$t->info('should return error when the title is empty');
$json = $t->post('/diary/post.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'title'       => '',
      'body'        => $body,
      'public_flag' => $publicFlag,
    )
  )
  ->with('response')->begin()
    ->isStatusCode('400')
  ->end()
;

$json = $t->post('/diary/post.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'body'        => $body,
      'public_flag' => $publicFlag,
    )
  )
  ->with('response')->begin()
    ->isStatusCode('400')
  ->end()
;

$t->info('should return error when the body is empty');
$json = $t->post('/diary/post.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'title'       => $title,
      'body'        => '',
      'public_flag' => $publicFlag,
    )
  )
  ->with('response')->begin()
    ->isStatusCode('400')
  ->end()
;

$json = $t->post('/diary/post.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'title'       => $title,
      'public_flag' => $publicFlag,
    )
  )
  ->with('response')->begin()
    ->isStatusCode('400')
  ->end()
;

$t->info('should return error when the public flag is empty');
$json = $t->post('/diary/post.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'title'       => $title,
      'body'        => $body,
      'public_flag' => '',
    )
  )
  ->with('response')->begin()
    ->isStatusCode('400')
  ->end()
;

$json = $t->post('/diary/post.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'title'       => $title,
      'body'        => $body,
    )
  )
  ->with('response')->begin()
    ->isStatusCode('400')
  ->end()
;

$t->info('should be able to edit a existing diary entry');
$id = 1;
$title = '編集後のテストタイトル'.time();
$body = '編集後のテスト本文'.time();
$publicFlag = PluginDiaryTable::PUBLIC_FLAG_PRIVATE;
$json = $t->post('/diary/post.json',
    array(
      'id'          => $id,
      'apiKey'      => 'dummyApiKey',
      'title'       => $title,
      'body'        => $body,
      'public_flag' => $publicFlag,
    )
  )->getResponse()->getContent()
;
$data = json_decode($json, true);
$t->test()->is($data['status'], 'success', 'should return status code "success"');
$t->test()->is($data['data']['id'], $id, 'should have the same id');
$t->test()->ok($data['data']['member'], 'should have member info');
$t->test()->is($data['data']['title'], $title, 'should have the same title posted');
$t->test()->is($data['data']['body'], $body, 'should have the same body posted');
$t->test()->is($data['data']['public_flag'], $publicFlag, 'should have the same publid flag posted');
$t->test()->ok($data['data']['created_at'], 'should have the date posted');

$t->info('only my friends should be able to view my diary entry for friends');
$title = '友人のみに公開するテストタイトル';
$body = '友人のみに公開するテスト本文';
$publicFlag = PluginDiaryTable::PUBLIC_FLAG_FRIEND;//友人のみに公開
$json = $t->post('/diary/post.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'title'       => $title,
      'body'        => $body,
      'public_flag' => $publicFlag,
    )
  )->getResponse()->getContent()
;
$postedData = json_decode($json, true);

$t->login($myFriendMailAddress, 'password');
$json = $t->get('/diary/search.json',
    array(
      'apiKey' => 'dummyApiKey',
      'id'     => $postedData['id']
    )
  )->getResponse()->getContent()
;
$data = json_decode($json, true);
$t->test()->is($postedData['id'], $data['data'], 'my friends can read my entries limited tothem.');

$t->login($notMyFriendMailAddress, 'password');
$json = $t->get('/diary/search.json',
    array(
      'apiKey' => 'dummyApiKey',
      'id'     => $postedData['id']
    )
  )->with('response')->begin()
    ->isStatusCode(400)
  ->end()
;

$t->info('only myself should be able to view my private diary entry');
$title = '秘密のテストタイトル';
$body = '秘密のテスト本文';
$publicFlag = PluginDiaryTable::PUBLIC_FLAG_PRIVATE;
$json = $t->post('/diary/post.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'title'       => $title,
      'body'        => $body,
      'public_flag' => $publicFlag,
    )
  )->getResponse()->getContent()
;

$t->login($myFriendMailAddress, 'password');
$json = $t->get('/diary/search.json',
    array(
      'apiKey' => 'dummyApiKey',
      'id'     => $postedData['id']
    )
  )->with('response')->begin()
    ->isStatusCode(400)
  ->end()
;

$t->login($notMyFriendMailAddress, 'password');
$json = $t->get('/diary/search.json',
    array(
      'apiKey' => 'dummyApiKey',
      'id'     => $postedData['id']
    )
  )->with('response')->begin()
    ->isStatusCode(400)
  ->end()
;

$t->info('存在しない日記の編集');
$json = $t->post('/diary/post.json',
array(
'id' => '0',
'apiKey' => 'dummyApiKey',
'title' => '日記タイトル',
'body' => '日記本文',
'public_flag' => 'PluginDiaryTable::PUBLIC_FLAG_FRIEND',
)
  )->with('response')->begin()
    ->isStatusCode(400)
  ->end()
;
$t->info('自分以外の日記の編集');
$diary = Doctrine::getTable('Diary')->findOneByMemberId(5);
$json = $t->post('/diary/post.json',
array(
'id' => $diary->getId(),
'apiKey' => 'dummyApiKey',
'title' => '日記タイトル',
'body' => '日記本文',
'public_flag' => 'PluginDiaryTable::PUBLIC_FLAG_FRIEND',
)
)
->with('response')->begin()
->isStatusCode('400')
->end()
;
