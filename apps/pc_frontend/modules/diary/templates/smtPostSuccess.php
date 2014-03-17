<?php
if ($diary)
{
  $title = __('Edit the diary');
  $diaryId    = $diary->getId();
  $diaryTitle = $diary->getTitle();
  $diaryBody  = $diary->getBody();
  $publicFlag = $diary->getPublicFlag();
}
else
{
  $title = __('Post a diary');
  $diaryId    = '';
  $diaryTitle = '';
  $diaryBody  = '';
  $publicFlag = 1;
}
use_helper('opAsset');
op_smt_use_stylesheet('/opDiaryPlugin/css/smt-diary.css', 'last');
op_smt_use_javascript('jquery-ui.min.js', 'last');
op_smt_use_javascript('op_emoji.js', 'last');
op_smt_use_javascript('Selection.js', 'last');
op_smt_use_javascript('decoration.js', 'last');
?>

<script id="successMessageTemplate" type="text/x-jquery-tmpl">
    投稿しました<br/>
    <a href="<?php echo public_path('diary') ?>/${id}">日記を見る</a>
</script>

<script type="text/javascript">
function op_get_relative_uri_root()
{
  return "<?php echo $relativeUrlRoot;?>";
}

function getParams()
{
  var query = $('form').serializeArray(),
  json = {apiKey: openpne.apiKey};

  for (i in query)
  {
    json[query[i].name] = query[i].value
  }

  return json;
}

function toggleSubmitState()
{
  $('#loading').toggle();
  $('input[name=submit]').toggle();
}

$(function(){
  $("#diary_body").opEmoji();

  $("#post_diary").click(function()
  {
    $('#successMessage').html('');
    toggleSubmitState();
    var params = getParams();

    $.post(openpne.apiBase + "diary/post.json",
      params,
      'json'
    )
    .success(
      function(res)
      {
        if (params['id'] == '')
        {
          $('#id').val('');
          $('#title').val('');
          $('#diary_body').val('');
          $('#diary_public_flag_1').attr('checked', true);
        }
        var _mes = $('#successMessageTemplate').tmpl(res['data']);
        $('#successMessage').html(_mes);
      }
    )
    .error(
      function(res)
      {
        console.log(res);
      }
    )
    .complete(
      function(res)
      {
        toggleSubmitState();
      }
    );
  });
})
</script>

<div class="row">
  <div class="gadget_header span12"><?php echo __($title) ?></div>
</div>

<div class="row">
  <div class="span12">
    <form>
    <input type="hidden" name="id" id="id" value="<?php echo $diaryId ?>"/>
    <label class="control-label span12"><?php echo __('Title') ?></label>
    <input type="text" name="title" id="title" class="span12" value="<?php echo $diaryTitle ?>">
    <label class="control-label span12"><?php echo __('Body') ?></label>
<a id="diary_body_button_op_emoji_docomo" href="#" onclick="$('#diary_body').opEmoji('togglePallet', 'epDocomo'); return false;">
<img alt="" src="/images/deco_op_emoji_docomo.gif" /></a>
    <textarea name="body" id="diary_body" class="span12" rows="10"><?php echo $diaryBody ?></textarea>
    <label class="control-label span12"><?php echo __('Public flag') ?></label>
    <ul class="radio_list">
    <?php foreach($publicFlags as $key=>$value):?>
      <li><input name="public_flag" value="<?php echo $key;?>" id="diary_public_flag_<?php echo $key;?>" class="input_radio" type="radio" <?php if($publicFlag == $key) echo 'checked'?>>&nbsp;<label for="diary_public_flag_<?php echo $key;?>"><?php echo $value;?></label></li>
    <?php endforeach; ?>
    </ul>
    </form>
    <div class="center">
      <input type="submit" name="submit" value="<?php echo __('Post') ?>" id="post_diary" class="btn btn-primary span12" />
    </div>
  </div>
  <hr class="toumei">
  <div id="loading" class="center hide">
    <?php echo op_image_tag('ajax-loader.gif');?>
  </div>
  <div id="successMessage" class="center">
  </div>
</div>
