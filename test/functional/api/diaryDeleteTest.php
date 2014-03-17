<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$t = new opTestFunctional(new sfBrowser());

include dirname(__FILE__).'/../../bootstrap/database.php';

$mailAddress = 'sns1@example.com';

$t->login($mailAddress, 'password');
$t->setCulture('en');

$apiKey = '?apiKey=dummyApiKey';

$t->info('for the first thing, post a entry to delete afterwords');
$title = 'テストタイトル';
$body = 'テスト本文';
$publicFlag = 1;//全員に公開
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

$deleteId = $data['data']['id'];
$json = '';
$data = array();

$t->info('should be able to delete the entry');
$json = $t->post('/diary/delete.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'id' => $deleteId
    )
  )->getResponse()->getContent()
;
$data = json_decode($json, true);
var_dump($data, $json);
$t->test()->is($data['status'], 'success', 'should return status code "success"');
$t->test()->is($deleteId, $data['data']['id'], 'should have the same id posted');

$diary = Doctrine::getTable('Diary')->findOneByMemberId(5);
$t->info('should NOT be able to delete an entry of other people\'s');
$json = $t->post('/diary/delete.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'id' => $diary->getId()
    )
  )->with('response')->begin()
    ->isStatusCode(400)
  ->end()
;

