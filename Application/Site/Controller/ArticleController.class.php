<?php
namespace Site\Controller;
use Hashids\Hashids;

class ArticleController extends BaseController {

    static $imglist = ['hold住', '不要', '偷笑', '冰冻', '加油', '发火', '发狂', '可怜', '叹气', '吃东西', '吃惊', '吐', '呆萌', '哭', '囧', '奸诈', '好吃', '害羞', '感冒', '戴眼镜',
        '挖鼻孔', '晕', '流口水', '流汗', '流眼泪', '点赞', '烧香', '狂笑', '病了', '研究', '示爱', '笑', '调皮', '酷', '闭嘴', '阴险'];

    public function view($id){
        $hashids = new Hashids();

        $artRet = $hashids->decode($id);
        if(empty($artRet)){
            $this->error("找不到页面");
        }
        $artId = $artRet[0];
        $articleContent = M("content")->where(['id' => $artId])->find();
        $articleContent['content'] = htmlspecialchars_decode($articleContent['content']);


        M("content")->where(['id' => $artId])->save(['viewnum' => $articleContent['viewnum'] + 1, 'lastviewtime' => date("Y-m-d H:i:s")]);
        $this->assign('article', $articleContent);
        $this->assign('commentlist', $this->getCommentList($artId));
        $this->assign('imglist', self::$imglist);
    	$this->display();
    }

    private function getCommentList($cid){
        $comments = M("comment")->field("yyg_comment.*, yyg_customer.email,yyg_customer.nickname,yyg_customer.bbbirthday,yyg_customer.age,yyg_customer.sex,
                yyg_customer.createtime as ucreatetime,yyg_customer.modifytime as umodifytime,yyg_customer.lastlogintime,yyg_customer.avatar,yyg_customer.address")->
                join('LEFT JOIN yyg_customer ON yyg_customer.id = yyg_comment.uid')->
                where(['cid' => $cid, 'yyg_comment.status' => 1])->order("yyg_comment.modifytime desc")->select();

        $newComments = [];
        foreach ($comments as $comment){
            $comment['avatar'] = $this->getAvatarurl($comment['avatar']);
            $newComments []= $comment;
        }
        return $newComments;
    }

    public function addComment(){
        $loginInfo = $this->getLoginInfo();
        if(empty($loginInfo)){
            $this->jsonReturn(false, "请登录后再评论！");
        }

        $content = I("post.content");
        $cid = I("post.cid");

        if(empty($cid)){
            $this->jsonReturn(false, "文章id不能为空");
        }

        if(empty($content)){
            $this->jsonReturn(false, "请输入评论内容");
        }

        if(mb_strlen($content) < 4){
            $this->jsonReturn(false, "请输入至少输入4个字符");
        }

        if(mb_strlen($content) > 2000){
            $this->jsonReturn(false, "评论内容不能超过2000个字符");
        }

        $articleContent = M("content")->where(['id' => $cid])->find();
        if(empty($articleContent)){
            $this->jsonReturn(false, "找不到要评论的文章");
        }

        $content = remove_xss($content);
        $sensitives = M("sensitive")->select();
        foreach($sensitives as $sensitive){
            $word = $sensitive['word'];
            if(mb_strpos($content, $word) !== false){
                $this->jsonReturn(false, "评论内容包含敏感词汇，不合法！");
                break;
            }
        }


        $data = [
            'content' => $content,
            'cid' => $cid,
            'uid' => $loginInfo['id'],
            'pid' => 0,
            'createtime' => date('Y-m-d H:i:s'),
            'modifytime' => date('Y-m-d H:i:s'),
        ];
        $commentM = M('comment');
        $commentM->startTrans();
        try{
            $ret = $commentM->data($data)->add();
            M("content")->where(['id' => $cid])->save(['commentnum' => $articleContent['commentnum'] + 1, 'lastcommenttime' => date("Y-m-d H:i:s")]);
            $commentM->commit();
            if($ret){
                return $this->jsonReturn(1);
            }
        }catch (\Exception $e){
            $commentM->rollback();
        }

        $this->jsonReturn(false, '添加评论失败');

    }
    
}