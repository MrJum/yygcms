<?php
namespace Site\Controller;

use Site\Service\CustomerService;

class CustomerController extends BaseController {

    public function setting(){
        $this->checkAuthStatus();
        $this->display();
    }

    public function changePassword(){
        $this->checkAuthStatus();
        $passwordCurrent = trim(I("post.password_current"));
        $passwordNew = trim(I("post.password_new"));
        $passwordAgain = trim(I("post.password_again"));

        if(empty($passwordCurrent)){
            $this->jsonReturn (false, '当前密码不能为空');
        }

        if(empty($passwordNew)){
            $this->jsonReturn (false, '新密码不能为空');
        }

        if(empty($passwordAgain)){
            $this->jsonReturn (false, '再次输入新密码不能为空');
        }

        if(strlen($passwordNew) < 6){
            $this->jsonReturn (false, '新密码不能小于6个字符');
        }

        if($passwordNew != $passwordAgain){
            $this->jsonReturn (false, '再次输入新密码不正确');
        }

        $loginCst = $this->getLoginInfo();
        if($loginCst['password'] != hashPassword($passwordCurrent, true)){
            $this->jsonReturn (false, '当前密码不正确');
        }

        M('Customer')->where(['id' => $loginCst['id']])->data(['password' => hashPassword($passwordNew, true)])->save();

        $this->jsonReturn (true);
    }

    public function uploadAvatar(){
        $this->checkAuthStatus();
        $allowExts = ['png', 'jpg', 'gif'];
        $maxsize = 500 * 1024;
        if(empty($_FILES["file"]['size'])){
            $this->jsonReturn (false, '请选择要上传的文件');
        }

        $filename = $_FILES["file"]['name'];
        $fileext = strtolower(substr($filename, strrpos($filename, '.') + 1));
        if(!in_array($fileext, $allowExts)){
            $this->jsonReturn (false, '仅支持 PNG / JPG / GIF 文件');
        }

        if($_FILES["file"]['size'] > $maxsize){
            $this->jsonReturn (false, '仅支持 500k大小的文件');
        }

        $loginCst = $this->getLoginInfo();
        $relPath = C('__YYG_UPLOAD_DIR__')."/avatars/".$loginCst['id'].'/';
        $uidDir = THINK_PATH."/../".$relPath;
        if(!file_exists($uidDir)){
            @mkdir($uidDir);
        }
        $justfilename = 'headpic.'.$fileext;
        $newfilename = $uidDir . $justfilename;
        if(move_uploaded_file($_FILES["file"]["tmp_name"], $newfilename)){
            genAvatarThumbs($newfilename, ['width' => ['big' => '80', 'middle' => '48', 'small' => '24'], 'height' => ['big' => '80', 'middle' => '48', 'small' => '24']],
                $uidDir, $justfilename);
            M('Customer')->where(['id' => $loginCst['id']])->data(['avatar' => $relPath.$justfilename])->save();

            $this->jsonReturn ([
                'small' => $relPath.'small_'.$justfilename."?t=".time(),
                'middle' => $relPath.'middle_'.$justfilename."?t=".time(),
                'big' => $relPath.'big_'.$justfilename."?t=".time(),
            ]);
        }
        $this->jsonReturn (false, '上传失败');

    }

    public function checkNickName(){
        $nickname = I('nickname');
        $uid = I('uid');
        $existCustomer = M('Customer')->where("`id`<>'$uid' and nickname='$nickname'")->find();
        if($existCustomer){
            $this->jsonReturn (false, '昵称已存在，请用其他昵称');
        }
        $this->jsonReturn (true);
    }

    public function checkEmail(){
        $email = I('email');
        $uid = I('uid');
        $existCustomer = M('Customer')->where("`id`<>'$uid' and email='$email'")->find();
        if($existCustomer){
            $this->jsonReturn (false, '邮箱已存在，请用其他邮箱');
        }
        $this->jsonReturn (true);
    }

    public function saveProfile(){
        $this->checkAuthStatus();
        foreach($_POST as $k=>$v){
            $_POST[$k] = remove_xss($v);
        }
        $uid = I('post.uid');
        $nickname = I('post.nickname');
        $phone = I('post.phone');
        $email = I('post.email');
        $age = intval(I('post.age'));
        $sex = boolval(I('post.sex'));
        $bbbirthday = date('Y-m-d H:i:s', strtotime(I('post.bbbirthday')));
        $stage = intval(I('post.stage'));
        $address = I('post.address');
        $intro = I('post.intro');

        if(empty($nickname)){
            $this->jsonReturn (false, '昵称不能为空');
        }

        if(empty($email)){
            $this->jsonReturn (false, '邮箱地址不能为空');
        }

        $existCustomer = M('Customer')->where("`id`<>'$uid' and nickname='$nickname'")->find();
        if($existCustomer){
            $this->jsonReturn (false, '昵称已存在，请用其他昵称');
        }

        $existCustomer = M('Customer')->where("`id`<>'$uid' and email='$email'")->find();
        if($existCustomer){
            $this->jsonReturn (false, '邮箱已存在，请用其他邮箱');
        }

        if($age < 0 || $age > 150){
            $this->jsonReturn (false, '年龄不正确');
        }

        $data = [
            'nickname' => $nickname,
            'phone' => $phone,
            'email' => $email,
            'age' => $age,
            'sex' => $sex,
            'bbbirthday' => $bbbirthday,
            'stage' => $stage,
            'address' => $address,
            'intro' => $intro,
        ];
        M('Customer')->where(['id' => $uid])->data($data)->save();

        $this->jsonReturn (true);

    }

    public function register(){
        $this->display();
    }

    public function doRegister(){
        foreach($_POST as $k=>$v){
            $_POST[$k] = remove_xss($v);
        }
        $nickname = I('post.nickname');
        $email = I('post.email');
        $passwd = I('post.password');
        $verifyCode = I('post.verifyCode');

        if(empty($nickname)){
            $this->jsonReturn (false, '昵称不能为空');
        }

        if(empty($email)){
            $this->jsonReturn (false, '邮箱地址不能为空');
        }

        if(strlen($passwd) < 6){
            $this->jsonReturn (false, '密码不能小于6个字符');
        }

        if(!$this->check_verify($verifyCode)){
            $this->jsonReturn (false, '验证码错误');
        }

        $existCustomer = M('Customer')->where("nickname='$nickname'")->find();
        if($existCustomer){
            $this->jsonReturn (false, '昵称已存在，请用其他昵称');
        }

        $existCustomer = M('Customer')->where("email='$email'")->find();
        if($existCustomer){
            $this->jsonReturn (false, '邮箱已存在，请用其他邮箱');
        }

        $data = [
            'nickname' => $nickname,
            'email' => $email,
            'password' => hashPassword($passwd, true),
        ];
        $ret = M('Customer')->data($data)->add();
        if(!$ret){
            $this->jsonReturn (false, '注册失败');
        }

        /** @var  $customerService CustomerService */
        $customerService = D("Customer", "Service");
        $customerService->loginCustomer($ret);

        $this->jsonReturn (true);


        $this->display();
    }

    public function verifyCode(){
        $Verify = new \Think\Verify([
            'length' => 6,
            'useNoise' => false,
            'fontSize' => 13,
        ]);
        $Verify->useZh = true;
        $Verify->zhSet = '当宰首父谷粱属微裴陆荣翁信登项祝董粱录或闻关人东和穆萧尹方者公甄魏家词封众号客冉乌焦巴弓宰郦雍服消皮卡齐康息的验证赵钱孙李网站官景詹束龙公孙仲孙方提供';
        $Verify->entry();
    }

    function check_verify($code){
        $verify = new \Think\Verify();
        return $verify->check($code, '');
    }

    public function forgotPasswd(){
        $this->display();
    }

    public function submitForgotPasswd(){
        foreach($_POST as $k=>$v){
            $_POST[$k] = remove_xss($v);
        }
        $nickname = I('post.nickname');
        $email = I('post.email');
        $verifyCode = I('post.verifyCode');

        if(empty($nickname)){
            $this->jsonReturn (false, '昵称不能为空');
        }

        if(empty($email)){
            $this->jsonReturn (false, '邮箱地址不能为空');
        }


        if(!$this->check_verify($verifyCode)){
            $this->jsonReturn (false, '验证码错误');
        }

        $data = [
            'nickname' => $nickname,
            'email' => $email,
        ];
        $ret = M('forgotpasswd')->data($data)->add();
        if(!$ret){
            $this->jsonReturn (false, '提交失败');
        }

        $this->jsonReturn (true);
    }
    
}