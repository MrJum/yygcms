<?php
/**
 * Created by PhpStorm.
 * User: daim01
 * Date: 2017/9/11
 * Time: 17:42
 */

namespace Admin\Controller;

use Admin\Model\OptionsModel;
use \Think\Controller;
use PHPHtmlParser\Dom;



class SpiderController extends Controller {
    //黑色=30，红色=31，绿色=32，黄色=33，蓝色=34，洋红=35，青色=36，白色=37
    const ECHO_RED = '31';
    const ECHO_BLACK = '30';
    const ECHO_GREEN = '32';
    const ECHO_YELLOW = '33';
    const ECHO_BLUE = '34';
    const ECHO_MAGENTA = '35';
    const ECHO_CYAN = '36';
    const ECHO_WHITE = '37';

    /**
     * SpiderController constructor.
     */
    public function __construct()
    {
        ini_set('memory_limit', '512M');
    }

    public function test(){
        $imgsrc = '<img class="aligncenter" title="2" src="http://news.ci123.com/uploads/2017/07/2017-07-27-10450065.jpeg" alt="" width="400" height="300">';
        $reTag = "/src=\"(http:\/\/.*)\.(jpg|png|gif|jpeg)\"/i";

        if (preg_match_all($reTag, $imgsrc, $matchResult)) {
            print_r($matchResult);
        }
    }

    public function testDom(){
        $dom = new Dom;
        $dom->load('http://www.oschina.net/news/88681/programmer-live-guide');
        $aa = $dom->find('img');
        foreach($aa as $aaa){
            echo $aaa->getAttribute('src')."\n";
        }
        $c = $dom->find(".news-content h1");
        echo $c->text;

        $cs = $dom->find(".news-content .from a");
        foreach($cs as $ccs){
            echo $ccs->text;
        }
        $cc = $dom->find(".editor-viewer");
        echo $cc->innerHtml;
    }

    public function handleCi123($url=''){
        if(empty($url)){
            $sourceURL = "http://www.ci123.com/";
        }else{
            $this->printColor("Begin Handle $url ======");
            $handled = M("url_handled")->where(['hash' => md5($url)])->find();
            if(empty($handled)){
                $sourceURL = $url;
                M("url_handled")->data(['hash' => md5($url), 'url' => $url])->add();
            }else{
                return;
            }
        }

        $matchArticleUrls = $this->getMatchArticleUrls($sourceURL);
        if(count($matchArticleUrls) > 0){
            foreach($matchArticleUrls as $matchArticleUrl){
                $this->getContent($matchArticleUrl);
                $this->handleCi123($matchArticleUrl);
            }
        }

    }

    private function getMatchArticleUrls($sourceURL){
        $dom = new Dom;
        $dom->load($sourceURL);
        $as = $dom->find("a");
        $re = '|^http://news\.ci123\.com/article/\d+\.html$|';

        //过滤获取指定的文件地址请求
        $matchArticleUrls = [];
        foreach ($as as $tmp) {
            $tempHref = $tmp->getAttribute('href');
            if (preg_match($re, $tempHref)) {
                $matchArticleUrls []= $tempHref;
            }
        }
        return $matchArticleUrls;
    }

    private function addCategory($categoryName){
        $categoryEntity = M("category")->where(['name' => $categoryName])->find();
        if(!empty($categoryEntity)){
            return $categoryEntity['id'];
        }
        $pageCode = D('Pinyin', 'Helper')->toPinyin($categoryName);
        if(empty($pageCode)){
            $pageCode = $categoryName;
        }
        $categoryModel = M("category"); // 实例化User对象
        return $categoryModel->data([
            'pid' => '0',
            'name' => $categoryName,
            'pagecode' => $pageCode,
            'status' => 1,
            'createtime' => date("Y-m-d H:i:s"),
            'modifytime' => date("Y-m-d H:i:s"),
        ])->add();
    }

    function getContent($siteUrl) {
        $this->printColor("请求的网页地址：".$siteUrl.' ===================================================== ', self::ECHO_BLUE);
        $dom = new Dom;
        $dom->load($siteUrl);

        $titleNode = $dom->find('.postTitle');
        $title =  $titleNode->text;
        $contentEntity = D("content")->where(['title' => $title])->find();
        if(!empty($contentEntity)){
            $this->printColor("文章【".$title.'】已存在 ', self::ECHO_YELLOW);
            return;
        }

        $breadCrumbsNodes =  $dom->find('.breadCrumbs')->find("a");
        $category = $breadCrumbsNodes[1]->text;

        $categoryId = $this->addCategory($category);

        $infoNodes = $dom->find(".postInfo span");
        $tagNodes = $infoNodes[1]->find('a');
        $tags = [];
        foreach($tagNodes as $tagNode){
            $tags []= $tagNode->text;
        }

        $contentNode = $dom->find('.entry');
        $content = $contentNode->innerHtml;

        $imgs = $contentNode->find("img");

        $attacIds = [];
        //匹配图片的正则表达式
        $reTag = "/(http:\/\/.*)\.(jpg|png|gif|jpeg)/i";
        foreach($imgs as $img){
            //http://file2.ci123.com/logo2.png
            $imgSrc = $img->getAttribute('src');
            if(preg_match($reTag, $imgSrc, $matchResult)){
                $imgRet = $this->saveImgURL($matchResult[1], $matchResult[2]);
                if(!$imgRet){
                    continue;
                }
                $attacIds []= $imgRet['id'];
                $content = str_replace($imgRet['ori'], $imgRet['new'], $content);
            }
        }

        /** @var  $contentD /Admin/Model/ContentModel */
        $contentD = D("Content");
        $contentD->startTrans();
        if (!$contentD->create([
            'title' => $title,
            'category_id' => $categoryId,
            'content' => $content,
            'intro' => mb_substr(strip_tags($content),0,70,'utf-8'),
            'embed_code' => '',
            'relurl' => '',
        ])){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            $this->printColor($contentD->getError(), self::ECHO_RED);
            $contentD->rollback();
            return;
        }else{
            try{
                $cid = $contentD->add();
                $contentD->addTags($cid, $tags);
                $contentD->commit();
            }catch (\Exception $e){
                $contentD->rollback();
                $this->printColor($e->getMessage(), self::ECHO_RED);
            }
        }


    }

    function saveImgURL($name, $suffix) {
        $UPLOAD_DIR = '/Uploads/';
        $this->printColor(sprintf("使用%2.f M\n", round(memory_get_usage() / 1024 / 1024, 6)), self::ECHO_MAGENTA);

        $url = $name.".".$suffix;

        $this->printColor("请求的图片地址：".$url);

        $imgSavePath = THINK_PATH."/../".$UPLOAD_DIR;

        $relSavepath = '';
        if ($suffix == "gif") {
            $relSavepath .= "emotion";
        } else {
            $relSavepath = "topic";
        }

        $imgSavePath .= $relSavepath;

        if (!file_exists($imgSavePath)){
            mkdir($imgSavePath, 0777, true);
        }

        $imageName = uuidBase62().".".$suffix;
        $imgSavePath .= ("/".$imageName);

        $imgFile = file_get_contents($url);
        $imgSaved = M("url_handled")->where(['hash' => md5($imgFile)])->find();
        if(!empty($imgSaved)){
            $this->printColor("文件".$imgSavePath."已存在，将不会重复保存", self::ECHO_RED);
            return;
        }

        $flag = file_put_contents($imgSavePath, $imgFile);

        if ($flag) {
            M("url_handled")->data(['hash' => md5($imgFile), 'url' => $imgSavePath])->add();
            $this->printColor("文件".$imgSavePath."保存成功", self::ECHO_GREEN);
        }

        $data['title'] = $imageName;
        $data['path'] = substr($imgSavePath, strpos($imgSavePath, $UPLOAD_DIR));
        $data['createtime'] = date("Y-m-d H:i:s");
        $id = M("attac")->add($data);
        genThumbs($imgSavePath, OptionsModel::getOptions(), substr($imgSavePath, 0, strpos($imgSavePath, $UPLOAD_DIR) + strlen($UPLOAD_DIR)), $relSavepath, $imageName);

        return ['ori' => $url, 'new' => $imgSavePath, 'id' => $id];
    }

    function printColor($str, $c=self::ECHO_WHITE){
        echo sprintf("\e[{$c}m%s\e[0m\n", $str);
    }
}