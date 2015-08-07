<?php
//用户页面
class UserAction extends Action {

    //登入页
    public function login(){
        //载入用户类
        $CmUser = LoadClass('CmUser',true);
        //如果登录状态就跳首页
        if ( $CmUser -> isLogin() ) {
            header('Location:'.CmMkurl('?m=index&a=index'));
            exit;
        }
        
        $AjaxLoginUrl = CmMkurl('?m=ajax&a=login');
        $this->assign('AjaxLoginUrl',$AjaxLoginUrl);

        $this->display();

    }

    //登出页
    public function logout(){
        $CmUser = LoadClass('CmUser',true);
        $CmUser->logOut();
        header('Location:'.CmMkurl('?m=user&a=login'));
    }
}
