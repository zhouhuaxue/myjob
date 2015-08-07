<?php

//引入用户类
$CmUser = LoadClass('CmUser',true);
//检测是否登录
$CmUser->NotLogInJump();

class ConstituteAction extends Action{
    function index(){
        $FileSys = ClassFileSys();
        $id = $_GET['id'] > 0 ? $_GET['id'] : 0;
        $where = " parentid = " . $id;
        $show_info = M('event_constitute')->where($where)->order('id desc')->select();

        foreach ($show_info as $key => $value) {
            $show_info[$key]['logourl'] = $FileSys->FileName2Url($value['logo']);
            $show_info[$key]['parentname'] = M('event_constitute')->where('id=' . $value['parentid'])->getField('name', 1);
        }
        $this->assign('ConstList', $show_info);
        $this->display();
    }

    function insert(){
        if(empty($_GET['id'])){
            $ConstList = M('event_constitute')->where("parentid=0")->field('id,name')->select();
            $this->assign('ConstList', $ConstList);
        }else{
            $conId = $_GET['id'];
            $conPar=M('event_constitute')->where('id='.$conId)->field('parentid,name')->find();
            $this->assign('conPar',$conPar);
            $ConstList=M('event_constitute')->where('parentid='.$conPar['parentid'])->field('name,id')->select();
            $this->assign('ConstList',$ConstList);

            $FileSys = ClassFileSys();
            $con_info = M('event_constitute')->where("id=$conId")->limit(1)->find();
            $con_info['logourl'] = $FileSys->FileName2Url($con_info['logo']);
            $con_info['parentname'] = M('event_constitute')->where('id=' . $con_info['parentid'])->getField('name', 1);
            $this->assign('con_info', $con_info);
        }
        $this->display();
    }

    function Submit(){
        $parentid = $_POST['parentid'];
        $name = $_POST['name'];
        $item_id = isset($_POST['itemid']) ? $_POST['itemid'] : 0;
        $FileSys = ClassFileSys();
        if ($UpFileName = $FileSys->UpLodeFile($_FILES['logo'])) {
            $UpFileName;
        } else {
            $UpFileName = '';
        }
        if (empty($name)) {
            $this->error('名字不能为空', CmMkurl('?m=Constitute&a=insert'));
        }
        if (empty($parentid)) {
            $parentid = 0;
        }
        $insdata = array(
            'name' => $name,
            'itemid' => $item_id,
            'parentid' => $parentid,
            'logo' => $UpFileName,
        );
        if( $_POST['id'] > 0 ){
            if ($UpFileName == '') {
                unset($insdata['logo']);
            }
            $update = M('event_constitute')->where('id ='.$_POST['id'])->save($insdata);
            if ($update) {
                $this->success('修改成功', CmMkurl('?m=Constitute&a=index'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $add = M('event_constitute')->data($insdata)->add();
            if ($add) {
                $this->success('添加成功', CmMkurl('?m=Constitute&a=index'));
            } else {
                $this->error('添加失败');
            }
        }

    }
/*删除操作*/
    function ConstituteDel(){
        $arrjson = array(
            'status'=> 0,
            'msg' =>'',
        );
        $son = M('event_constitute')->where("parentid = " . $_GET['id'])->select();
        if($son){
            $arrjson['status'] = 1;
            $arrjson['msg'] = '存在子类不能删除';
        } else {
            $delete = M('event_constitute')->where("id = " . $_GET['id'])->delete();
            if($delete){
                $arrjson['msg'] = '删除成功！';
            } else {
                $arrjson['status'] = 1;
                $arrjson['msg'] = '删除失败！';
            }
        }
        echo json_encode($arrjson);
    }
/*输入itemid时对itemname的显示ajax操作*/
    function conIdToNameAjax(){
        if (is_numeric($_POST['conItemId'])) {
            $conItemName = M('subscribe_item')->where('item_id=' . $_POST['conItemId'])->field('item_name')->find();
            if ($conItemName['item_name']) {
                echo $conItemName['item_name'];
            } else {
                echo 0;
            }
        } else {
            echo 0;
        }
    }
/*在搜索框输入itemname进行查找时相应的itemid的显示ajax操作*/
    function conSearchNameAjax(){
        $search = M('subscribe_item')->where(" item_name like '%{$_POST['search']}%'")->field('item_name,item_id,group_id')->limit(10)->select();

        foreach($search as $value){
            $searchgroupname=M('subscribe_group')->where("group_id={$value['group_id']}")->field('group_name')->find();
            echo "<p Sid='{$value['item_id']}'>{$value['item_name']}({$searchgroupname['group_name']})</p>";
        }

    }
/*插入操作时父类名称的相应显示ajax操作*/
    function conParNameAjax(){
        $conParName = M('event_constitute')->where("parentid={$_POST['pId']}")->field('name,id')->select();
        if ($conParName) {
            echo '<div class="col-sm-1">';
            echo "<select class='form-control SelectConst'>";
            echo '<option selected="selected">--</option>';
            foreach ($conParName as $value) {
                echo '<option value="'.$value['id'].'">' . $value['name'] . '</option>';
            }
            echo "</select></div>";
        }
    }
/*修改操作时父类名称的相应显示ajax操作*/
    function conUpdParNameAjax(){
        $conParInfo=M('event_constitute')->where("id={$_POST['Pid']}")->field('name,parentid')->find();
        $conParName = M('event_constitute')->where("parentid={$conParInfo['parentid']}")->field('name,id')->select();

        $ShowHtml = '';
        if ($conParName) {
            $ShowHtml.= '<div class="col-sm-1">';
            $ShowHtml.= "<select class='form-control SelectConst'>";
            $ShowHtml.= '<option selected="selected">--</option>';
            foreach ($conParName as $value) {
                $ShowHtml.= '<option value="'.$value['id'].'">' . $value['name'] . '</option>';
            }
            $ShowHtml.= "</select></div>";
        }
        echo json_encode ( array ( 'html'=> $ShowHtml , 'pid'=> $conParInfo['parentid']?$conParInfo['parentid']:0 ) );
    }
}
