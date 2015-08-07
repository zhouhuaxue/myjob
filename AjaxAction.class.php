<?php
//ajax页面
class AjaxAction extends Action {

    public function login(){
    
        $UserName = trim($_REQUEST["username"]);
        $PassWord = trim($_REQUEST["password"]);
        $IsRemember = $_REQUEST["is_remember"] ? true : false;

        $CmUser = LoadClass('CmUser' , true);
        $UserId = $CmUser -> checkUserPasswd($UserName,$PassWord);

        if ($UserId > 0 ) {
            $success = '1';
            $href = CmMkurl('?m=index&a=index');
            $CmUser -> setLogIn($UserId,$IsRemember);
            SetSession('UserName',$UserName);
        }else{
            $success = '0';
            $href = '';
        }
        
        $OutPut = [
            'success' => $success,
            'href' => $href,
        ];

        $this->show(json_encode($OutPut));
    }
    
    public function CleanCache(){
        //引入用户类
        $CmUser = LoadClass('CmUser',true);
        //检测是否登录
        $CmUser->NotLogInJump();

        $URL = C('API_URL').'?c=cache';
        if($_GET['act'] == 'class'){
            $URL .= '&a=CleanByClass';
        }elseif($_GET['act'] == 'func'){
            $URL .= '&a=CleanByFunc';
        }elseif($_GET['act'] == 'key'){
            $URL .= '&a=CleanByKey';
        }else{
            exit;
        }
        $Res = file_get_contents( $URL.'&'.http_build_query($_POST) );
        echo $Res;
    }
}


