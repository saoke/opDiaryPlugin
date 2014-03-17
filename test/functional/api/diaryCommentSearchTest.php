<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$t = new opTestFunctional(new sfBrowser());

include dirname(__FILE__).'/../../bootstrap/database.php';

$mailAddress = 'sns1@example.com';

$t->login($mailAddress, 'password');
$t->setCulture('en');

$t->info('should fetch a list of comments ');
$diary = Doctrine::getTable('Diary')->findOneByMemberId(1);
$comments = $diary->getDiaryComments();
$json = $t->get('/diary_comment/search.json',
    array(
      'apiKey'      => 'dummyApiKey',
      'diary_id'       => $diary->getId(),
    )
  )
  ->getResponse()->getContent()
;
$data = json_decode($json, true);
$t->test()->is($data['status'], 'success', 'should return status code "success"');
$t->test()->is(count($data['data']['comments']), count($comments), 'should have the same number of comments');
$t->test()->ok(count($data['data']['comments'][0]['deletable']), 'should have deletable property');
