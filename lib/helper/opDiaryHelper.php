<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

function op_diary_link_to_show($diary, $withName = true, $withIcon = true)
{
  $html = '';

  $html .= link_to(op_diary_get_title_and_count($diary), op_diary_url_for_show($diary));

  if ($withName)
  {
    $html .= ' ('.$diary->getMember()->getName().')';
  }

  if ($withIcon)
  {
    $html .= op_diary_image_icon($diary);
  }

  return $html;

}

function op_diary_get_title_and_count($diary, $space = true, $width = 36)
{
  return sprintf('%s%s(%d)',
           op_truncate($diary->getTitle(), $width),
           $space ? ' ' : '',
           $diary->countDiaryComments());
}

function op_diary_image_icon($diary)
{
  $html = '';
  if ($diary->has_images)
  {
    $html = ' '.image_tag('icon_camera.gif', array('alt' => 'photo'));
  }

  return $html;
}

function op_diary_url_for_show($diary)
{
  $internalUri = '@diary_show?id='.$diary->getId();

  if ($count = $diary->countDiaryComments())
  {
    $internalUri .= '&comment_count='.$count;
  }

  return $internalUri;
}

function emojicode_to_image($matches)
{
  $emoji = new OpenPNE_KtaiEmoji_Img();
  return $emoji->get_emoji4emoji_code_id($matches[1]);
}

function op_api_diary_convert_emoji($str)
{
    $pattern = '/\[([ies]:[0-9]{1,3})\]/';
    return preg_replace_callback($pattern, 'emojicode_to_image', $str);
}

function op_api_diary($diary)
{
  if($diary)
  {
    $body = preg_replace(array('/&lt;op:.*?&gt;/', '/&lt;\/op:.*?&gt;/'), '', $diary->getBody());
    $body = preg_replace('/http.:\/\/maps\.google\.co[[:graph:]]*/', '', $body);
    $bodyShort = op_truncate($body, 60);
    $body = op_auto_link_text($body);
    return array(
      'id'          => $diary->getId(),
      'member'      => op_api_member($diary->getMember()),
      'title'       => $diary->getTitle(),
      'body'        => nl2br(op_api_diary_convert_emoji($body)),
      'body_short'  => nl2br(op_api_diary_convert_emoji($bodyShort)),
      'public_flag' => $diary->getPublicFlag(),
      'ago'         => op_format_activity_time(strtotime($diary->getCreatedAt())),
      'created_at'  => $diary->getCreatedAt(),
    );
  }
}

function op_api_diary_image($image)
{
  if($image)
  {
    return array(
      'filename' => sf_image_path($image->getFile()->getName()),
      'imagetag' => image_tag_sf_image($image->getFile()->getName(), array('size' => '120x120'))
    );
  }
}

function op_api_diary_comment($comment)
{
  if($comment)
  {
    $images = array();
    if ($comment->getHasImages())
    {
      foreach($comment->getDiaryCommentImages() as $image)
      {
        $images[] = op_api_diary_image($image);
      }
    }
    return array(
      'id'         => $comment->getId(),
      'diary_id'   => $comment->getDiaryId(),
      'number'     => $comment->getNumber(),
      'member'     => op_api_member($comment->getMember()),
      'body'       => nl2br($comment->getBody()),
      'ago'        => op_format_activity_time(strtotime($comment->getCreatedAt())),
      'created_at' => $comment->getCreatedAt(),
      'images'     => $images
    );
  }
}
