<?php
namespace Site\Controller;

class LoginController extends BaseController {

    public function login(){
        $email = I("post.email");
        $password = I("post.password");

        $email = remove_xss($email);
        $password = remove_xss($password);

        if(empty($email)){
            $this->jsonReturn(false, "请输入您注册的邮箱地址");
        }

        if(empty($password)){
            $this->jsonReturn(false, "请输入您的登录密码");
        }


        $customerEntity = M("customer")->where(["email" => $email])->find();
        if(empty($customerEntity)){
            $this->jsonReturn(false, "邮箱用户不存在");
        }
        
        if($customerEntity['password'] !== hashPassword($password)){
            if(empty($customerEntity)){
                $this->jsonReturn(false, "密码不正确");
            }
        }

        cookie(C("__YYG_SITE_AUTH_NAME__"), $customerEntity['id'], 3600);
        $this->jsonReturn(1);
    }
    
}