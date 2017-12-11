<style>
    .comment { width: 680px; margin: 20px auto; position: relative; background: #fff; padding: 20px 50px 50px; border: 1px solid #DDD; border-radius: 5px; }
    .comment h3 { height: 28px; line-height: 28px }
    .com_form { width: 100%; position: relative }
    .input { width: 99%; height: 60px; border: 1px solid #ccc }
    .com_form p { height: 28px; line-height: 28px; position: relative; margin-top: 10px; }
    span.emotion { width: 42px; height: 20px; padding-left: 20px; cursor: pointer }
    span.emotion:hover { background-position: 2px -28px }
    .qqFace { margin-top: 4px; background: #fff; padding: 2px; border: 1px #dfe6f6 solid; }
    .qqFace table td { padding: 0px; }
    .qqFace table td img { cursor: pointer; border: 1px #fff solid; }
    .qqFace table td img:hover { border: 1px #0066cc solid; }
    #show { width: 770px; margin: 20px auto; background: #fff; padding: 5px; border: 1px solid #DDD; vertical-align: top; }
    .sub_btn { position: absolute; right: 0px; top: 0; display: inline-block; zoom: 1; /* zoom and *display = ie7 hack for display:inline-block */  *display: inline;
        vertical-align: baseline; margin: 0 2px; outline: none; cursor: pointer; text-align: center; font: 14px/100% Arial, Helvetica, sans-serif; padding: .5em 2em .55em; text-shadow: 0 1px 1px rgba(0,0,0,.6); -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px; -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.2); -moz-box-shadow: 0 1px 2px rgba(0,0,0,.2); box-shadow: 0 1px 2px rgba(0,0,0,.2); color: #e8f0de; border: solid 1px #538312; background: #64991e; background: -webkit-gradient(linear, left top, left bottom, from(#7db72f), to(#4e7d0e)); background: -moz-linear-gradient(top, #7db72f, #4e7d0e);  filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#7db72f', endColorstr='#4e7d0e');
    }
    .sub_btn:hover { background: #538018; background: -webkit-gradient(linear, left top, left bottom, from(#6b9d28), to(#436b0c)); background: -moz-linear-gradient(top, #6b9d28, #436b0c);  filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#6b9d28', endColorstr='#436b0c');
    }

</style>
<hr/>
<?php if(!empty($loginInfo)){ ?>
<form class="col-sm-12 comment-box" method="post" action="<?php echo site_url('article/addComment') ?>" onsubmit="return checkAndSubmit(this)">
    <div style="border:1px solid #e3e4e5">
        <textarea id="comment-text" name="comment-text" class="form-control comment-textarea nohighlight" rows="3" placeholder="输入您的评论..."></textarea>
        <div class="comment-submit-bar">
            <img id="face_btn" src="__PUBLIC__/site/{$Think.THEME_NAME}/images/happy_hover.png">
        </div>
    </div>
    <div id="comment-error"><span class="glyphicon glyphicon-info-sign"></span></div><button type="submit" class="btn btn-primary btn-sm pull-right" style="margin-top: 3px;width: 60px;">发 布</button>
</form>

<script type="text/javascript" src="__PUBLIC__/site/{$Think.THEME_NAME}/fangface/jquery.qqFace.js"></script>
<script type="text/javascript">

    $(function(){
        $('#face_btn').qqFace({
            id : 'facebox',
            assign:'comment-text',
            path:'__PUBLIC__/site/{$Think.THEME_NAME}/fangface/imgs/',	//表情存放的路径
            imglist : <?php echo json_encode($imglist, JSON_UNESCAPED_UNICODE) ?>,
        });

        $(".sub_btn").click(function(){
            var str = $("#saytext").val();
            $("#show").html(replace_em(str));
        });
    });

    //查看结果
    function replace_em(str){
        str = str.replace(/\</g,'&lt;');
        str = str.replace(/\>/g,'&gt;');
        str = str.replace(/\n/g,'<br/>');
        str = str.replace(/\[em_([0-9]*)\]/g,'<img src="arclist/$1.gif" border="0" />');
        return str;
    }
    
    function checkAndSubmit(formboj) {
        if($("#comment-text").val() == ''){
            showCommentError('请输入评论内容');
            return false;
        }

        if($("#comment-text").val().length < 4){
            showCommentError('请输入至少输入4个字符');
            return false;
        }

        $.post(formboj.action, { cid: "<?php echo $article['id'] ?>", content: $("#comment-text").val() },
            function(data){
                if(typeof data != 'object'){
                    data = JSON.parse(data);
                }
                if(data['errCode'] !== 0){
                    showCommentError(data['errMsg']);
                }else{
                    window.location.reload();
                }
            });

        return false;
    }

    function showCommentError(msg){
        $("#comment-error span").html(msg);
        $("#comment-error").show();
    }

</script>
<?php } ?>
<div class="col-sm-12 comment-list">
    <?php foreach($commentlist as $commentItem){ ?>
        <div class="comment-item">
            <div class="comment-user-panel">
                <?php
                    $avatarImgUrl = $commentItem['avatar'];
                    if(empty($avatarImgUrl)){
                        $avatarImgUrl = '/Public/site/'.$Think.THEME_NAME.'/images/uface.jpg';
                    }
                    ?>
                    <img src="<?php echo $avatarImgUrl ?>">
            </div>
            <div class="comment-body">
                <div class="comment-nav">
                    <div class="u-nickname"><?php echo $commentItem['nickname'] ?></div>
                    <div class="bb-age">宝宝：<?php echo getbbage($commentItem['bbbirthday']) ?></div>
                    <div class="comment-time"><?php echo $commentItem['modifytime'] ?></div>
                </div>
                <div class="comment-cont"><?php echo addFaceToContent($commentItem['content'])   ?></div>
            </div>
        </div>
    <?php }?>
</div>
