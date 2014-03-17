<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * diary actions.
 *
 * @package    OpenPNE
 * @subpackage diary
 * @author     Rimpei Ogawa <ogawa@tejimaya.com>
 */
class opDiaryPluginDiaryActions extends opDiaryPluginActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('diary', 'list');
  }

  public function executeList(opWebRequest $request)
  {
    $this->forwardIf($request->isSmartphone(), 'diary', 'smtList');

    $publicFlag = $this->getUser()->isSNSMember() ? DiaryTable::PUBLIC_FLAG_SNS : DiaryTable::PUBLIC_FLAG_OPEN;

    $this->isSearchEnable =  Doctrine::getTable('SnsConfig')->get('op_diary_plugin_search_enable', '1');

    $this->pager = Doctrine::getTable('Diary')->getDiaryPager($request['page'], 20, $publicFlag);
  }

  public function executeSmtList(opWebRequest $request)
  {
    return sfView::SUCCESS;
  }

  public function executeSearch(sfWebRequest $request)
  {
    $this->isSearchEnable =  Doctrine::getTable('SnsConfig')->get('op_diary_plugin_search_enable', '1');
    $this->forward404Unless($this->isSearchEnable);

    $this->keyword = $request['keyword'];

    $keywords = opDiaryPluginToolkit::parseKeyword($this->keyword);
    $this->forwardUnless($keywords, 'diary', 'list');

    $publicFlag = $this->getUser()->isSNSMember() ? DiaryTable::PUBLIC_FLAG_SNS : DiaryTable::PUBLIC_FLAG_OPEN;

    $this->pager = Doctrine::getTable('Diary')->getDiarySearchPager($keywords, $request['page'], 20, $publicFlag);
    $this->setTemplate('list');
  }

  public function executeListMember(sfWebRequest $request)
  {
    if (!$this->getUser()->isSNSMember())
    {
      $this->forwardUnless($this->member && $this->member->id && Doctrine::getTable('Diary')->hasOpenDiary($this->member->id),
          sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));
    }

    $this->forwardIf($request->isSmartphone(), 'diary', 'smtListMember');

    $this->year  = (int)$request['year'];
    $this->month = (int)$request['month'];
    $this->day   = (int)$request['day'];

    if ($this->year && $this->month)
    {
      $this->forward404Unless(checkdate($this->month, ($this->day) ? $this->day : 1, $this->year), 'Invalid date format');
    }

    $this->pager = Doctrine::getTable('Diary')->getMemberDiaryPager($this->member->id, $request['page'], 20, $this->myMemberId, $this->year, $this->month, $this->day);
  }

  public function executeSmtListMember(sfWebRequest $request)
  {
    $this->id = isset($request['id']) ? $request['id'] : $this->member->getId();
    $this->setTemplate('smtList');
  }

  public function executeListFriend(sfWebRequest $request)
  {
    $this->pager = Doctrine::getTable('Diary')->getFriendDiaryPager($this->getUser()->getMemberId(), $request['page'], 20);
  }

  public function executeShow(sfWebRequest $request)
  {
    if (!$this->diary->is_open && !$this->getUser()->isSNSMember())
    {
      $this->forward(sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));
    }

    $this->forward404Unless($this->isDiaryViewable());

    if ($this->isDiaryAuthor())
    {
      Doctrine::getTable('DiaryCommentUnread')->unregister($this->diary);
    }

    $this->forwardIf($request->isSmartphone(), 'diary', 'smtShow');

    $this->form = new DiaryCommentForm();
  }

  public function executeSmtShow(sfWebRequest $request)
  {
    if ($this->diary->getMemberId() !== $this->getUser()->getMemberId())
    {
      $this->member = $this->diary->getMember();
    }
    else
    {
      $this->member = $this->getUser()->getMember();
    }
    opSmartphoneLayoutUtil::setLayoutParameters(array('member' => $this->member)); 

    $this->id = $request['id'];

    return sfView::SUCCESS;
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->forwardIf($request->isSmartphone(), 'diary', 'smtNew');
    $this->form = new DiaryForm();
  }

  public function executeSmtNew(sfWebRequest $request)
  {
    $this->diary = null;
    $this->smtPost($request);
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->form = new DiaryForm();
    $this->form->getObject()->member_id = $this->getUser()->getMemberId();
    $this->processForm($request, $this->form);
    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($this->isDiaryAuthor());
    $this->forwardIf($request->isSmartphone(), 'diary', 'smtEdit');

    $this->form = new DiaryForm($this->diary);
  }

  public function executeSmtEdit(sfWebRequest $request)
  {
    $this->diary = Doctrine::getTable('Diary')->findOneById($request['id']);
    $body = $this->diary->getBody();
    $body = preg_replace(array('/<op:.*?>/', '/<\/op:.*?>/'), '', $body);
    $body = preg_replace('/http.:\/\/maps\.google\.co[[:graph:]]*/', '', $body);
    $this->diary->setBody($body);
    $this->smtPost($request);
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($this->isDiaryAuthor());

    $this->form = new DiaryForm($this->diary);
    $this->processForm($request, $this->form);
    $this->setTemplate('edit');
  }

  public function executeDeleteConfirm(sfWebRequest $request)
  {
    $this->forward404Unless($this->isDiaryAuthor());

    $this->form = new BaseForm();
  }

  public function executeDelete(sfWebRequest $request)
  {
    $this->forward404Unless($this->isDiaryAuthor());
    $request->checkCSRFProtection();

    $this->diary->delete();

    $this->getUser()->setFlash('notice', 'The diary was deleted successfully.');

    $this->redirect('@diary_list_member?id='.$this->getUser()->getMemberId());
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind(
      $request->getParameter($form->getName()),
      $request->getFiles($form->getName())
    );

    if ($form->isValid())
    {
      $diary = $form->save();

      $this->redirect('@diary_show?id='.$diary->id);
    }
  }

  protected function smtPost(sfWebRequest $request)
  {
    $this->publicFlags = Doctrine::getTable('Diary')->getPublicFlags();
    unset($this->publicFlags[4]);
    $this->relativeUrlRoot = $request->getRelativeUrlRoot();
    $this->setLayout('smtLayoutSns');
    $this->setTemplate('smtPost');
  }
}
