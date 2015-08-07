<?php
//引入用户类
$CmUser = LoadClass('CmUser',true);
//检测是否登录
$CmUser->NotLogInJump();

class RecommendAction extends Action {

    function index(){

        $RecList = [
            'l'=>[
                'img'=>'loding.jpg',
                'title'=>'Loading页'
            ],
            'r'=>[
                'img'=>'recomindex.jpg',
                'title'=>'推荐首页'
            ],
            'c'=>[
                'img'=>'recomcalendar.jpg',
                'title'=>'日历首页',
            ],
            'g'=>[
                'img' => 'group.jpg',
                'title'=>'分组页面',
            ],
            'i'=>[
                'img'=>'item.jpg',
                'title'=>'订阅源页',
            ],
        ];

        $this->assign('RecList',$RecList);

        $this->display();
    
    }

    function reclist(){
        $Show = $_GET['show'];
        $RecPos = $_GET['pos'];
        $ItemId = $_GET['item_id'];
        $FileSys = ClassFileSys();

        //推荐位置
        $RecList = ['l'=>'loading页','r'=>'推荐首页','c'=>'日历首页','g'=>'分组页面','i'=>'订阅源页'];
        $this->assign('RecList',$RecList);

        //厂商
        $Se=M('se_info')->field('se_id,se_name')->select();
        foreach ($Se as $val){
            $SeList[$val['se_id']] = $val['se_name'];
        }
        $SeList[0] = '全部';
        $this->assign('SeList',$SeList);

        //切换图标
        $S = [''=>'推荐中','old'=>'已过期','new'=>'未发生'];
        $this->assign('SwitchArr',$S);
        foreach ($S as $k=>$v){
            $url = CmMkurl('?m=recommend&a=reclist&pos='.$RecPos.'&show='.$k);
            if ($Show == $k){
                $ButtonSwitch .='<a href="'.$url.'" class="btn btn-primary btn-lg active btn-sm" role="button" disabled="disabled">'.$v.'</a>';
            }else{
                $ButtonSwitch .='<a href="'.$url.'" class="btn btn-primary btn-lg active btn-sm" role="button">'.$v.'</a>';
            }
            $ButtonSwitch .="&nbsp;&nbsp;&nbsp;";
        }
        $this->assign('ButtonSwitch',$ButtonSwitch);

        //推荐类型
        $RecType = ['g'=>'分组', 'i'=>'订阅源', 'e'=>'事件' ,'0'=>'无'];
        $this->assign('RecType',$RecType);

        //Item列表推荐为多个推荐
        $ItemShow = '';
        if ($RecPos == 'i') {
            if ($ItemId === '0'){
                $ItemShow .= '全源推荐<br/>';
                $this->assign('NowItem','全源推荐');
            }else{
                $ItemShow .= '<a href="'.CmMkurl('?m=recommend&a=reclist&pos='.$RecPos.'&show='.$Show.'&item_id=0').'">全源推荐</a><br/>';
            }
            $GroupList = M('subscribe_group')->field('group_id,group_name')->select();
            foreach ($GroupList as $GroupInfo){
                $ItemShow .= '<b>'.$GroupInfo['group_name'].':</b>';
                $ItemList = M('subscribe_item')->field("item_id,item_name")->where("`group_id`=".$GroupInfo['group_id'])->select();
                foreach ($ItemList as $ItemInfo){
                    if ($ItemId == $ItemInfo['item_id']){
                        $ItemShow.="[".$ItemInfo['item_name'].']&nbsp;';
                        $this->assign('NowItem',$ItemInfo['item_name']);
                    }else{
                        $ItemShow.="[<a href='".CmMkurl('?m=recommend&a=reclist&pos='.$RecPos.'&show='.$Show.'&item_id='.$ItemInfo['item_id'])."'>".$ItemInfo['item_name'].'</a>]&nbsp;';
                    }
                }
                $ItemShow.='<br/>';
            }
        }
        $this->assign('ItemShow',$ItemShow);


        $date = date('Y-m-d H:i:s');

        $Where = '';
        if($RecPos == 'i' && $ItemId  !== Null){
            $Where .=' and item_id ='. $ItemId;
        }
        if ($Show == 'old') {
            $ListData = M('subscribe_recommend')->where("`rec_pos` = '$RecPos' $Where and `rec_end` < '$date'")->select();
        } elseif ($Show == 'new'){
            $ListData = M('subscribe_recommend')->where("`rec_pos` = '$RecPos' $Where and `rec_start` > '$date'")->select();
        } else {
            $ListData = M('subscribe_recommend')->where("`rec_pos` = '$RecPos' $Where and `rec_start` < '$date' and `rec_end` > '$date'")->select();
        }

        foreach ($ListData as &$val){
            $val['rec_image_url'] = $FileSys->FileName2Url($val['rec_image']);
        }
        $this->assign('ListData',$ListData);

        $this->assign('RecPos',$RecPos);
        $this->assign('ItemId',$ItemId);

        $this->display();
    
    }

    function recadd(){
        $FileSys = ClassFileSys();

        $RecommendId = $_GET['recommend_id'];
        $ItemId = $_GET['item_id'];

        if ($RecommendId){
            $RecommInfo = M('subscribe_recommend')->where('id='.$RecommendId)->find();
            $RecommInfo['rec_image_url'] = $FileSys->FileName2Url($RecommInfo['rec_image']);
        }

        $this->assign('RecommInfo',$RecommInfo);

        if($_GET['pos']){
            $RecPos = $_GET['pos'];
        }else{
            $RecPos = $RecommInfo['rec_pos'];
        }

        //推荐位置
        $RecList = ['l'=>'loading页','r'=>'推荐首页','c'=>'日历首页','g'=>'分组页面','i'=>'订阅源页'];
        $this->assign('RecList',$RecList);
        
        //厂商
        $Se=M('se_info')->field('se_id,se_name')->select();
        $SeList[0] = '全部';
        foreach ($Se as $val){
            $SeList[$val['se_id']] = $val['se_name'];
        }
        $this->assign('SeList',$SeList);
        
        //推荐类型
        $RecType = ['0' =>'无' , 'g' => '分组', 'i' => '订阅源', 'e' => '事件'];
        $this->assign('RecType',$RecType);

        //推荐至Item
        $GroupList = M('subscribe_group')->field('group_id,group_name')->select();
        foreach ($GroupList as $GroupInfo){
            $ItemShow .= '<b>'.$GroupInfo['group_name'].':</b><br/>&nbsp;&nbsp;&nbsp;';
            $ItemList = M('subscribe_item')->field("item_id,item_name")->where("`group_id`=".$GroupInfo['group_id'])->select();
            foreach ($ItemList as $ItemOne){
                $ItemShow .='<label><input type="radio" name="item_id"  value="'.$ItemOne['item_id'].'"/>'.$ItemOne['item_name'].'</label>&nbsp;&nbsp;&nbsp;';
            }
            $ItemShow.='<br/>';
        }
        $this->assign('ItemShow',$ItemShow);

        $this->assign('ItemId',$ItemId);
        if ($ItemId) {
            $ItemInfo = M('subscribe_item')->where("`item_id` = $ItemId")->find();
            $this->assign('ItemInfo',$ItemInfo);
        }

        $this->assign('RecPos',$RecPos);
    
        $this->display();
    }

    //推荐提交
    function recsubmit (){
        $RecommendId = $_POST['recommend_id'];
        $FileSys = ClassFileSys();
        $RecImage = $FileSys->UpLodeFile($_FILES['rec_image']);
        if ($RecImage){
            $data['rec_image'] = $RecImage;
        }

        if (strtotime($_POST['rec_start']) > strtotime($_POST['rec_end'])){
            $this->error('开始时间不能大于结束时间');
        }
        if (strtotime($_POST['rec_end']) == 0){
            $this->error('结束时间不能为空');
        }

        $data["rec_se"]   =$_POST["rec_se"];
        $data["rec_pos"]  =$_POST["rec_pos"];
        $data["item_id"]  =$_POST["item_id"];
        $data["rec_type"] =$_POST["rec_type"];
        $data["rec_id"]   =$_POST["rec_id"];
        $data["rec_title"]=$_POST["rec_title"];
        $data["rec_start"]=$_POST["rec_start"];
        $data["rec_end"]  =$_POST["rec_end"];
        $data["rec_url"]  = trim($_POST["rec_url"]);

        if ($RecommendId) {
            if ( M('subscribe_recommend')->where("id = ".$RecommendId)->limit(1)->save($data) ){
                $this->success('修改成功', CmMkurl('?m=recommend&a=reclist&pos='.$data['rec_pos']));
            }else{
                $this->error('修改推荐失败');
            }

        }else{
            $InsId = M('subscribe_recommend')->Add($data);
            if($InsId){
                $this->success('添加成功', CmMkurl('?m=recommend&a=reclist&pos='.$data['rec_pos']));
            }else{
                $this->error('添加推荐失败');
            }
        }
        
    
    }
    function recomindex(){
    
        $SeList = M('se_info')->field('se_id,se_name')->select();

        $this->assign('SeList',$SeList);

        $this->display();
    }

    function reccustom (){

        $SeId = $_GET['se_id'];

        $this->assign('SeId',$SeId);

        $SeInfo = M('se_info')->where("se_id=".$SeId)->find();
        $this->assign('SeInfo',$SeInfo);


        $RecList = M('se_recommend')->where('se_id='.$SeId)->find();
        $RecList = explode(',',$RecList['se_rec']);
        $this->assign('RecList',$RecList);


        if ($SeId) {
            $GroupList = M('subscribe_group')->where("`parent_id` in (0,$SeId) ")->select();
            foreach ($GroupList as &$val){
                $ItemList = M('subscribe_item')->where ("`group_id` = {$val['group_id']}")->select();
                $val['ItemList'] = $ItemList;
            }
        }

        $this->assign('GroupList',$GroupList);
        
        $this->assign('ReloadCache',GlobalsCleanCacheUrl('class',array('class'=>'SubData','method'=>'SetSeRecom','SeId'=>$SeId)));


        $this->display();
    
    
    }

    function reccustomsubmit (){

        $SeId = $_POST['SeId'];

        $SelectList = $_POST['SelectList'];

        if ($SelectList){
            $SeRec=implode(',',$SelectList);
            $M = M('se_recommend')->execute("replace into __TABLE__ (`se_id`,`se_rec`) values('$SeId','$SeRec')");

            if ($M){
                $this->success('操作成功', CmMkurl('?m=recommend&a=reccustom&se_id='.$SeId));
            }else{
                $this->error('操作出错');
            }

        }else{
            $this->error('您没有选择任何推荐');
        }

    }

    function ajaxSearch (){
        $SearchName = $_POST['SearchName'] ;
        $Type = $_POST['type'] ;
        if (!in_array($Type,array('g','i','e'))){
            echo json_encode(array('success'=>0,'tips'=>'错误'));
        }

        $tab = array(
            'g'=>array('id'=>'group_id','name'=>'group_name','tab'=>'subscribe_group'),
            'i'=>array('id'=>'item_id' ,'name'=>'item_name' ,'tab'=>'subscribe_item'),
            'e'=>array('id'=>'event_id','name'=>'event_name','tab'=>'subscribe_event')
        );

        $f = $tab[$Type]['id'];
        $t = $tab[$Type]['tab'];
        $n = $tab[$Type]['name'];

        $Data = M($t)->field($f.' as id,'.$n.' as name')->where("$n like '%$SearchName%'")->limit(10)->select();

        if ($Data) {
            echo json_encode(array('success'=>1,'tips'=>'success','data'=>$Data));
        }else{
            echo json_encode(array('success'=>0,'tips'=>'没有'));
        }
    }

}
