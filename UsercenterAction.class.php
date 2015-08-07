<?php
//引入用户类
$CmUser = LoadClass('CmUser',true);
//检测是否登录
$CmUser->NotLogInJump();

class UsercenterAction extends Action {

    function modifypasswd (){


        $this->display();
    
    }

    function modifypasswdsubmit (){

        $CmUser = LoadClass('CmUser',true);

        if ( $CmUser-> checkUserPasswd( GetSession('UserName') ,$_POST['old_pass']) ) {

            if ($_POST['new_pass'] !== $_POST['re_pass']){
                $this->error('两次密码输入不一致');
            }elseif(strlen($_POST['new_pass']) <6){
                $this->error('密码不能小于6位');
            }elseif($_POST['new_pass'] === $_POST['old_pass']){
                $this->error('新旧密码一样,无需修改');
            }else{
                if ( $CmUser->ModifyPasswd(GetSession('UserName'),$_POST['new_pass'],false,$_POST['old_pass']) ) {
                    $this->success('密码修改成功');
                }else{
                    $this->error('密码修改失败');
                }
            }
        
        }else{
            $this->error('原密码不正确');
        }

    }

}
