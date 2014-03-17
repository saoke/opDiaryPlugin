<?php
class opSmtWidgetFormRichTextarea extends opWidgetFormRichTextarea
{
  static protected $isFirstRender  = true;

  static protected $buttons = array(
    'op_emoji_docomo' => array('caption' => 'Input Emoji(DoCoMo)')
  );

  static protected $buttonOnclickActions = array(
    'op_emoji_docomo' => '$("#%id%").opEmoji("togglePallet", "epDocomo");',
  );

  static protected function getButtons()
  {
    $buttons = array();
    foreach (self::$buttons as $key => $button)
    {
      if (is_numeric($key))
      {
        $buttonName = $button;
        $buttonConfig = array('imageURL' => image_path('deco_'.$buttonName.'.gif'));
      }
      else
      {
        $buttonName = $key;
        if (!isset($button['imageURL']))
        {
          $button['imageURL'] = image_path('deco_'.$buttonName.'.gif');
        }
        $buttonConfig = $button;
      }
      $buttons[$buttonName] = $buttonConfig;
    }

    return $buttons;
  }

  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->addOption('is_toggle', false);
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $js = '';

    if (self::$isFirstRender)
    {
      sfContext::getInstance()->getResponse()->addSmtJavascript('jquery.min.js');
      sfContext::getInstance()->getResponse()->addSmtJavascript('jquery-ui.min.js');
      sfContext::getInstance()->getResponse()->addSmtJavascript('tiny_mce/tiny_mce');
      sfContext::getInstance()->getResponse()->addSmtJavascript('op_emoji');
      sfContext::getInstance()->getResponse()->addSmtJavascript('Selection');
      sfContext::getInstance()->getResponse()->addSmtJavascript('decoration');

      $relativeUrlRoot = sfContext::getInstance()->getRequest()->getRelativeUrlRoot();
      $js .= sprintf("function op_mce_editor_get_config() { return %s; }\n", json_encode(self::getButtons()));
      $js .= sprintf('function op_get_relative_uri_root() { return "%s"; }', $relativeUrlRoot);

      self::$isFirstRender = false;
    }

    if ($js)
    {
      sfProjectConfiguration::getActive()->loadHelpers('Javascript');
      $js = javascript_tag($js);
    }

    $id = $this->getId($name, $attributes);
    $this->setOption('textarea_template', '<div id="'.$id.'_buttonmenu" class="'.$id.'">'
      .get_partial('global/richTextareaOpenPNEButton', array(
        'id' => $id,
        'configs' => self::getButtons(),
        'onclick_actions' => self::$buttonOnclickActions
      )).
      '</div>'.$this->getOption('textarea_template'));

    return $js.parent::render($name, $value, $attributes, $errors);
  }
}
