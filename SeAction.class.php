<?php

//引入用户类
$CmUser = LoadClass('CmUser',true);
//检测是否登录
$CmUser->NotLogInJump();

class SeAction extends Action {


    //首页 厂商列表
    function index(){

        $SeList = M('se_info')->order('se_id desc')->select();

        $this->assign('SeList',$SeList);

        $this->display();
    }


    //添加厂商
    function seadd(){

        $this->display();
    
    }

    function seaddsubmit(){

        $data['se_name'] = $_POST['se_name'];
        $data['se_secret'] = $_POST['se_secret'];
        $data['createtime'] = date('Y-m-d H:i:s');
        if ( M('se_info')->add($data) ) {
            $this->success('操作成功',CmMkurl('?m=se&a=index'));
        }else{
            $this->error();
        
        }
    }

    function randsecret(){
        $array = [
            "A","B","C","D","E","*","F","G","(","H",
            "I","J","K","L","M","N","!","O","P","Q",
            "R","S","T","@","U","V","W","X","Y","Z",
            "#","a","b","=","c","d","e","$","f","g",
            "%","h","i","j","k","l","m","n","^","o",
            "p","q","r","s","t","&","u","v","w","-",
            "x","y","z","1","2","3","*","4","5","(",
            "6","7",")","8","9","0"
        ];
        $Str = '';
        for($i=0;$i<32;$i++){
            shuffle ($array);
            $Str.=current ( $array );
        }
        echo md5($Str);
    }





    //厂商用户
    function user(){

        $Se = M('se_info')->field('se_id,se_name')->order('se_id desc')->select();
        $SeList = array ();
        foreach ($Se as $val){
            $SeList[$val['se_id']] = $val['se_name'];
        }
        $this->assign('SeList',$SeList);


        $WhereData = array();
        if ($_GET['se_id']>0){
            $WhereData['se_id'] = $_GET['se_id'];
        }


        $UserList = M('se_user')->field('user_id,se_id,user_name')->where($WhereData)->order('user_id desc')->select();
        $this->assign('UserList',$UserList);

        $this->display();
    }

    //添加用户
    function useradd(){
        $Se = M('se_info')->field('se_id,se_name')->order('se_id desc')->select();
        $SeList = array ();
        foreach ($Se as $val){
            $SeList[$val['se_id']] = $val['se_name'];
        }
        $this->assign('SeList',$SeList);
        $this->display();
    
    }

    //添加用户提交
    function useraddsubmit(){
        $SeUser = LoadClass('SeUser',true);
        $data['se_id'] = $_POST['se_id'];
        $data['user_name'] = $_POST['user_name'];
        $data['user_passwd'] = $SeUser->MkPasswd($_POST['user_passwd']);
        if ($data == array_filter($data)){
            if(M('se_user')->add($data)){
                $this->success('添加成功',CmMkurl('?m=se&a=user'));
            }else{
                $this->error('添加用户失败');
            }
        }else{
            $this->error('参数错误');
        }
    }

    function randpasswd(){
        $array = [
            "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q",
            "R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i",
            "j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
            "1","2","3","4","5","6","7","8","9","0"
        ];
        $Str = '';
        for($i=0;$i<6;$i++){
            shuffle ($array);
            $Str.=current ( $array );
        }
        echo $Str;
    
    }


}
