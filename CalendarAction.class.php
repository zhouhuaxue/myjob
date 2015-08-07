<?php
/*
 *日历页
 * */

//引入用户类
$CmUser = LoadClass('CmUser',true);
//检测是否登录
$CmUser->NotLogInJump();

class CalendarAction extends Action {

    //分组列表
    public function group(){

        $FileSys = ClassFileSys();
        $GroupList = M('subscribe_group')->order("`group_order` asc")->select() ;
        if ($GroupList){
            foreach ($GroupList as &$val) {
                $One = M('se_info')->field('se_name')->where("`se_id` = ".$val['parent_id'])->find();
                $val['se_name'] = $One['se_name'] ? $One['se_name'] : '公共';
                $val['group_icon_url'] = $FileSys->FileName2Url($val['group_icon']);
                $val['group_banner_url'] = $FileSys->FileName2Url($val['group_banner']);
                $val['group_description'] = mb_substr($val['group_description'],0,10,'utf8').'...';
            }
        }
        $this->assign("GroupList",$GroupList);

        $this->display();
    }

    //编辑页面
    public function groupedit(){

        $GroupId = (int)trim($_GET['group_id']);

        if ($GroupId) {
            $GroupInfo = M('subscribe_group')->where("`group_id` = $GroupId")->find();
            $GroupInfo['group_icon_url'] = ClassFileSys()->FileName2Url($GroupInfo['group_icon']);
            $GroupInfo['group_banner_url'] = ClassFileSys()->FileName2Url($GroupInfo['group_banner']);
            $this->assign('GroupInfo',$GroupInfo);
        }

        $AllSe = M('se_info')->field(array('se_id','se_name'))->select();

        $this->assign('AllSe',$AllSe);
        
        $this->display();
    
    }

    //提交页面
    public function groupsubmit(){
        $GroupId = (int)$_POST['group_id'];
        $GroupName = trim($_POST['group_name']);
        $GroupDescription = trim($_POST['group_description']);
        $ParentId = (int)$_POST['parent_id'];
        $GroupSubtitle = trim($_POST['group_subtitle']);

        if ($GroupId>0){
            $data['group_id']   = $GroupId;
        }
        $data['group_name'] = $GroupName;
        $data['group_description'] = $GroupDescription;
        $data['parent_id']  = $ParentId;
        $data['group_subtitle'] = $GroupSubtitle;

        if ($_FILES['group_icon']['tmp_name']){
            $FileSys = ClassFileSys();
            if( $UpFileName = $FileSys->UpLodeFile($_FILES['group_icon']) ){
                $data['group_icon'] = $UpFileName;
            }
        }
        
        if ($_FILES['group_banner']['tmp_name']){
            $FileSys = ClassFileSys();
            if( $UpFileName = $FileSys->UpLodeFile($_FILES['group_banner']) ){
                $data['group_banner'] = $UpFileName;
            }
        }

        if ($GroupId>0) {
            $Aff = M('subscribe_group')->where('group_id='.$GroupId)->limit(1)->save($data);
            if ($Aff > 0){
                LoadClass('SubData')->UpdateTime('group',$GroupId);
                $this->success('修改分组成功', CmMkurl('?m=calendar&a=group'));
            }else{
                $this->error('修改分组失败');
            }
        }else{
            $InsId = M('subscribe_group')->add($data);
            if($InsId){
                LoadClass('SubData')->UpdateTime('group',$InsId);
                $this->success('新增分组成功', CmMkurl('?m=calendar&a=group'));
            }else{
                $this->error('新增分组失败');
            }
        }
    }

    //ajax 提交分组排序
    function ajaxGroupOrder(){
        $i = 0;
        foreach ($_POST as $key => $val){
            if (is_numeric($key) && is_numeric($val)){
                $data = array('group_order' => $val);
                $Aff = M('subscribe_group')->where('group_id='.$key)->limit(1)->save($data);
                if ($Aff){
                    $i ++;
                }
            }
        }
        if ($i){
            $OutPut = array('success'=>1,'tips'=>'操作成功');
        }else{
            $OutPut = array('success'=>0,'tips'=>'没有修改');
        }
        echo json_encode($OutPut);
    }




    //订阅源列表
    function item(){
        $GroupId = $_GET['group_id'];
        $FileSys = ClassFileSys();
        $ItemList = M('subscribe_item')->where("`group_id` = ".$GroupId)->order("`item_status` desc, `item_order` asc")->select() ;
        if ($ItemList){
            foreach ($ItemList as &$val) {
                $val['item_icon_url'] = $FileSys->FileName2Url($val['item_icon']);
                $val['item_banner_url'] = $FileSys->FileName2Url($val['item_banner']);
                $val['item_description'] = mb_substr($val['item_description'],0,10,'utf8').'...';
            }
        }
        $this->assign("ItemList",$ItemList);

        //Item所属厂商
        $GroupInfo = M('subscribe_group')->where("`group_id`=".$GroupId)->find();
        if ( $GroupInfo['parent_id'] == 0) {
            $SeName = '公共';
        }else{
            $SeInfo = M('se_info')->field('se_name')->where("`se_id` = ".$GroupInfo['parent_id'])->find();
            $SeName = $SeInfo['se_name'];
        }

        $this->assign('SeName',$SeName);

        $this->assign('GroupInfo',$GroupInfo);

        $this->display();
    
    }

    //修改订阅源
    function itemedit (){
        $ItemId = $_GET['item_id'];

        $GroupId = $_GET['group_id'];

        if ($ItemId) {

            $ItemInfo = M('subscribe_item')->where('`item_id`='.$ItemId)->limit(1)->find();

            $ItemInfo['item_icon_url'] = ClassFileSys()->FileName2Url($ItemInfo['item_icon']);
            
            $ItemInfo['item_banner_url'] = ClassFileSys()->FileName2Url($ItemInfo['item_banner']);

            $this->assign('ItemInfo',$ItemInfo);

            $GroupId = $ItemInfo['group_id'];
        }

        $GroupInfo = M('subscribe_group')->where("`group_id`=".$GroupId)->find();

        if ($GroupInfo['parent_id'] == 0 ) {
            $SeName = '公共';
        }else{
            $SeInfo = M('se_info')->field('se_name')->where("`se_id` = ".$GroupInfo['parent_id'])->find();
            $SeName = $SeInfo['se_name'];
        }

        $this->assign('SeName',$SeName);

        $this->assign('GroupInfo',$GroupInfo);

        $this->display();
    
    }

    //item修改(添加)
    function itemsubmit (){
        $FileSys = ClassFileSys();

        $ItemId = $_POST['item_id'];

        $data['item_name'] = trim($_POST['item_name']);
        $data['item_description'] = trim($_POST['item_description']);
        $data['is_remind'] = $_POST['is_remind'] ? 1 : 0;
        $data['is_permanent'] = $_POST['is_permanent'] ? 1 : 0;
        $data['item_subtitle'] = trim($_POST['item_subtitle']);
        if($_FILES['item_icon']['tmp_name']){
            if( $UpFileName = $FileSys->UpLodeFile($_FILES['item_icon']) ){
                $data['item_icon'] = $UpFileName;
            }
        }
        if($_FILES['item_banner']['tmp_name']){
            if ($UpFileName = $FileSys->UpLodeFile($_FILES['item_banner']) ){
                $data['item_banner'] = $UpFileName;
            }
        }
        
        if ($ItemId){ // add
            $Aff = M('subscribe_item')->where('item_id='.$ItemId)->limit(1)->save($data);
            if ($Aff > 0){
                LoadClass('SubData')->UpdateTime('item',$ItemId);
                $this->success('修改分组成功', CmMkurl('?m=calendar&a=item&group_id='.$_POST['group_id']));
            }else{
                $this->error('修改分组失败');
            }
        
        }else{
            $data['group_id'] = $_POST['group_id'];
            $InsId = M('subscribe_item')->add($data);
            if($InsId){
                LoadClass('SubData')->UpdateTime('item',$InsId);
                $this->success('新增订阅源成功', CmMkurl('?m=calendar&a=item&group_id='.$data['group_id']));
            }else{
                $this->error('新增订阅源失败');
            }

        }

    }

    // item 排序
    function ajaxItemOrder (){
        $GroupId = $_POST['group_id'];
        unset($_POST['group_id']);

        $i = 0;
        foreach ($_POST as $key => $val){
            if (is_numeric($key) && is_numeric($val)){
                $data = array('item_order' => $val);
                $Aff = M('subscribe_item')->where('group_id = '.$GroupId.' and item_id='.$key)->limit(1)->save($data);
                if ($Aff){
                    $i ++;
                }
            }
        }

        if ($i){
            $OutPut = array('success'=>1,'tips'=>'操作成功');
        }else{
            $OutPut = array('success'=>0,'tips'=>'没有修改');
        }
        
        echo json_encode($OutPut);
    }



    //事件列表
    function event(){
        $ItemId = $_GET['item_id'];
        $FileSys = ClassFileSys();
        $ClassPubTool = ClassPubTool();

        $SelectDate = $_GET['selectdate'] ? $_GET['selectdate'] : date('Ym');
        $TodayDate = date('Ym');

        $YearStart = substr($SelectDate,0,4);

        $UrlList['Select'] = '<b>'.$YearStart.'年</b>&nbsp;&nbsp;';

        for ($i = 101;$i <= 112;$i++){
            $t = $YearStart.substr($i,-2);
            if ($t == $SelectDate) {
                $UrlList['Select'] .= '<a href="#" class="btn btn-primary btn-xs disabled" role="button">'.substr($i,-2).'月</a>';
            }else{
                $Url = CmMkurl('?m=calendar&a=event&item_id='.$ItemId.'&selectdate='.$t);
                $UrlList['Select'] .= '<a href="'.$Url.'" class="btn btn-primary btn-xs" role="button">'.substr($i,-2).'月</a>';
            }
            $UrlList['Select'] .="&nbsp;";
        }





        $UrlList['preday']=CmMkurl('?m=calendar&a=event&item_id='.$ItemId.'&selectdate='.$ClassPubTool->PreMonth($SelectDate.'01','Ym'));
        $UrlList['today']=CmMkurl('?m=calendar&a=event&item_id='.$ItemId.'&selectdate='.$TodayDate);
        $UrlList['nexday']=CmMkurl('?m=calendar&a=event&item_id='.$ItemId.'&selectdate='.$ClassPubTool->NextMonth($SelectDate.'01','Ym'));
        
        $this->assign('SelectDate',$SelectDate);

        $this->assign('UrlList',$UrlList);

        $ItemInfo = M('subscribe_item')->where("`item_id` = $ItemId")->limit(1)->find();
        $this->assign('ItemInfo',$ItemInfo);

        $GroupInfo = M('subscribe_group')->where("`group_id`=".$ItemInfo['group_id'])->find();
        $this->assign('GroupInfo',$GroupInfo);
        
        $MaxDate = date('t', strtotime($SelectDate.'01'));
        $AllDateArray = array();
        for ($i = 101; $i <= $MaxDate+100;$i++ ){
            $tmp = $ClassPubTool->DateFormatToCalDate( $SelectDate.substr($i,-2) ) ;
            $AllDateArray = array_merge($AllDateArray,array_values($tmp));
        }
        $AllDateStr = '"'.implode ('","',array_unique ($AllDateArray)).'"';


        $starttime = date('Y-m-d',strtotime($SelectDate.'01'));
        $endtime = substr($starttime,0,-2).date('t',strtotime($starttime));

        $where = '`item_id`= '.$ItemId.' and `starttime` <= "'.$endtime.'" and `endtime` >= "'.$starttime.'" and `event_date` in (' .$AllDateStr. ')';
        $EventList = M('subscribe_event')->where($where)->order("`event_status` desc, `event_id` desc")->select();
        foreach ($EventList as &$val){
            $val['event_showtime'] = $ClassPubTool->DateFormatToCn($val['event_repeat'],$val['event_time']);
            $val['event_icon_url'] = $FileSys->FileName2Url($val['event_icon']);
            
            $val['event_description'] = str_replace("\n",'<br/>',$val['event_description']);

            $s1 = strpos($val['event_description'],'<br/>' );
            $shot = substr ( $val['event_description'], 0 , $s1);
            $s2 = strpos(substr($val['event_description'],$s1+5),'<br/>' );
            if($s2 === false) {
                $shot = $val['event_description'];
            }else{
                $shot .="<br/>".substr ( $val['event_description'], $s1+5 , $s2);
            }
            $val['event_description_shot'] = $shot;
        }
        $this->assign('EventList',$EventList);

        $this->display();
    
    }

    //修改(新建)事件
    function eventedit (){
        $FileSys = ClassFileSys();
        $PubTool = ClassPubTool();
        $ItemId = $_GET['item_id'];
        $EventId = $_GET['event_id'];

        $EventRepeat = array('N'=>'单次','Y'=>'年重复', 'M'=>'月重复', 'D'=>'日重复', 'W'=>'周重复');
        $this->assign('EventRepeat',$EventRepeat);

        $ItemInfo = M('subscribe_item')->where("`item_id` = $ItemId")->limit(1)->find();
        $this->assign('ItemInfo',$ItemInfo);

        $GroupInfo = M('subscribe_group')->where("`group_id`=".$ItemInfo['group_id'])->find();
        $this->assign('GroupInfo',$GroupInfo);

        $EventTypeList = M('subscribe_event_type')->select();
        $this->assign('EventTypeList',$EventTypeList);

        if ($EventId){
            $EventInfo = M('subscribe_event')->where("`event_id` = ".$EventId)->find();
            $EventInfo['event_icon_url'] = $FileSys->FileName2Url($EventInfo['event_icon']);
            $END = $PubTool->CalDateDeFormat($EventInfo['event_time']);
            $EventInfo['event_date'] = $END['date'];
            $EventInfo['event_time'] = $END['time'];

            if ( $EventInfo['event_type']> 0 ) {
                //$Detail = M('subscribe_event_detail_'.$EventInfo['event_type'])->where("`event_id` = ".$EventId)->find();
                //$EventInfo['detail'] = $Detail;
            }

            $this->assign('EventInfo',$EventInfo);
        }

        $this->display();
    
    }

    //事件提交
    function eventsubmit(){
        $EventId = $_POST['event_id'];

        $FileSys = ClassFileSys();

        $EventName=$_POST['event_name'];
        $EventAddress = $_POST['event_address'];
        $EventDescription = $_POST['event_description'];
        $EventRepeat = $_POST['event_repeat'];

        $Starttime = $_POST['starttime'];
        $Endtime = $_POST['endtime'];

        $DataOri = $_POST['event_data'][$EventRepeat] or '';

        $PubTool = ClassPubTool();
        $DateBase10 = $PubTool->RepeatDate2Base10($EventRepeat,$DataOri);
        if($DateBase10 === false){
            $this->error('事件日期不正确');
        }
        $EventData = $PubTool->Base10to62($DateBase10);

        $EventTime =$PubTool->CalDateFormat( $DataOri,$_POST['event_time']);
        $ItemId = $_POST['item_id'];
        $EventType = $_POST['event_type'];

        $EventUrl = trim($_POST['event_url']);

        if( $UpFileName = $FileSys->UpLodeFile($_FILES['event_icon']) ){
            $EventIcon = $UpFileName;
        }

        $InsData = array(
            'item_id'=>$ItemId,
            'event_name'=>$EventName,
            'event_address'=>$EventAddress,
            'event_description'=>$EventDescription,
            'event_icon'=>$EventIcon,
            'starttime'=>$Starttime,
            'endtime'=>$Endtime,
            'event_date'=>$EventData,
            'event_repeat'=>$EventRepeat,
            'event_time'=>$EventTime,
            'event_type'=>$EventType,
            'event_url'=>$EventUrl,
        );
        //Z此处添加了以下两个变量,接收了eventedit传来的post值
        $insConItemId1=$_POST['conItemId1'];
        $insConItemId2=$_POST['conItemId2'];

        if ($EventId > 0){
            $InsData = array_filter($InsData);
            $InsData['event_date'] = $EventData;
            M('subscribe_event')->where("`event_id` = ".$EventId )->limit(1)->save($InsData) ;
            $InsId = $EventId;

            LoadClass('SubData')->UpdateTime('event',$EventId);
        }else{
            $InsId = M('subscribe_event')->add(array_filter($InsData));
            LoadClass('SubData')->UpdateTime('event',$InsId);

            //Z此处添加了在表中插入三条除itemID不同外的相同数据
            if ($insConItemId1 || $insConItemId2) {
                $AddRes = M('subscribe_event')->where("`event_id` = ".$InsId )->limit(1)->find();
                unset($AddRes['event_id']);
            }

            if ($insConItemId1) {
                $AddRes['item_id'] = $insConItemId1;
                $InsId1=M('subscribe_event')->add($AddRes);
            }

            if ($insConItemId2) {
                $AddRes['item_id'] = $insConItemId2;
                $InsId2=M('subscribe_event')->add($AddRes) ;
            }
        }
        if($InsId) {
            if ($EventType == 1){ //体育类
                $detail['event_id'] = $InsId;
                $detail['team1'] = $_POST['team1'];
                $detail['logo1'] = $_POST['logo1'];
                if( $UpFileName = $FileSys->UpLodeFile($_FILES['logo1']) ){
                    $detail['logo1'] = $UpFileName;
                }
                $detail['score1'] = $_POST['score1'];
                $detail['team2'] = $_POST['team2'];
                $detail['logo2'] = $_POST['logo2'];
                if( $UpFileName = $FileSys->UpLodeFile($_FILES['logo2']) ){
                    $detail['logo2'] = $UpFileName;
                }
                $detail['score2'] = $_POST['score2'];
                $detail['status'] = $_POST['status'];
                if ($EventId>0){
                    $detail['event_id'] = $EventId;
                    $detail=array_filter($detail);
                    $detail['score1'] = $detail['score1'] ? (int) $detail['score1'] : 0;
                    $detail['score2'] = $detail['score2'] ? (int) $detail['score2'] : 0;

                    $FindOne = M('subscribe_event_detail_1')->where('event_id = '.$detail['event_id'])->find();

                    if ($FindOne) {
                        if (M('subscribe_event_detail_1')->where('event_id = '.$detail['event_id'])->limit(1)->save($detail)){   //修改
                            $this->success('修改成功',CmMkurl('?m=calendar&a=event&item_id='.$ItemId));
                        }else{
                            $this->success('修改事件成功,修改详情失败',CmMkurl('?m=calendar&a=event&item_id='.$ItemId));
                        }
                    }else{
                        if ( M('subscribe_event_detail_1')->limit(1)->add($detail,array(),true) ){  //后来添加
                            $this->success('修改成功',CmMkurl('?m=calendar&a=event&item_id='.$ItemId));
                        }else{
                            $this->success('修改事件成功,修改详情失败',CmMkurl('?m=calendar&a=event&item_id='.$ItemId));
                        }
                    }
                    //Z此处添加了两个修改操作
//                    $detail=M('subscribe_event_detail_1')->where('event_id = '.$detail['event_id'])->find();
//                    unset($detail['event_id']);
//                    $detail['event_id']=$InsId1;
//                    $detail=array_filter($detail);
//                    $detail['score1'] = $detail['score1'] ? (int) $detail['score1'] : 0;
//                    $detail['score2'] = $detail['score2'] ? (int) $detail['score2'] : 0;
//                    $FindTwo = M('subscribe_event_detail_1')->where('event_id = '.$detail['event_id'])->find();
//
//                    if ($FindTwo) {
//                        if (M('subscribe_event_detail_1')->where('event_id = '.$detail['event_id'])->limit(1)->save($detail)){   //修改
//                            $this->success('修改成功');
//                        }else{
//                            $this->success('修改事件成功,修改详情失败');
//                        }
//                    }else{
//                        if ( M('subscribe_event_detail_1')->limit(1)->add($detail,array(),true) ){  //后来添加
//                            $this->success('修改成功');
//                        }else{
//                            $this->success('修改事件成功,修改详情失败');
//                        }
//                    }
//
//                    $addDetail2=M('subscribe_event_detail_1')->where('event_id = '.$InsId)->find();
//                    $addDetail2['event_id']=$InsId2;
//                    $FindThree = M('subscribe_event_detail_1')->where('event_id = '.$addDetail2['event_id'])->find();
//                    if ($FindThree) {
//                        if (M('subscribe_event_detail_1')->where('event_id = '.$addDetail2['event_id'])->limit(1)->save($addDetail2)){   //修改
//                            $this->success('修改成功');
//                        }else{
//                            $this->success('修改事件成功,修改详情失败');
//                        }
//                    }else{
//                        if ( M('subscribe_event_detail_1')->limit(1)->add($addDetail2,array(),true) ){  //后来添加
//                            $this->success('修改成功');
//                        }else{
//                            $this->success('修改事件成功,修改详情失败');
//                        }
//                    }


                }else {
                    $Res = M('subscribe_event_detail_1')->add($detail);

                    if ($Res > 0) {
                        if ($InsId1 || $InsId2) {
                            $AddDetail = M('subscribe_event_detail_1')->where("`event_id` = ".$InsId )->limit(1)->find();
                        }
                        if ($InsId1) {
                            $AddDetail['event_id'] = $InsId1;
                            M('subscribe_event_detail_1')->add($AddDetail);
                        }
                        if ($InsId2) {
                            $AddDetail['event_id'] = $InsId2;
                            M('subscribe_event_detail_1')->add($AddDetail);
                        }

                        $this->success('添加成功', CmMkurl('?m=calendar&a=event&item_id=' . $ItemId));
                    } else {
                        $this->success('事件添加成功,详情添加失败', CmMkurl('?m=calendar&a=event&item_id=' . $ItemId));
                    }


                }
            }else{
                if ($EventId > 0){
                    $this->success('修改成功',CmMkurl('?m=calendar&a=event&item_id='.$ItemId));
                }else{
                    $this->success('添加成功',CmMkurl('?m=calendar&a=event&item_id='.$ItemId));
                }
            }
        
        }else{
            if ( $EventId>0 ){
                $this->error('事件修改失败');
            }else{
                $this->error('事件添加失败');
            }
        }
    }


    function eventDel (){
        /*Z此处添加了对event_type的判断*/
        $type=M('subscribe_event')->where("event_id = ".$_GET['event_id'])->find();
        if(!$type){
            echo 0;
            exit;
        }
        M('subscribe_event')->where("event_id = ".$_GET['event_id'])->limit(1)->delete() ;
        if($type['event_type']>0){
            M("subscribe_event_detail_{$type['event_type']}")->where("event_id = ".$_GET['event_id'])->limit(1)->delete() ;
            echo 1;
        }else {
            echo 1;
        }

    }

    //EventAjax获取
    function ajaxGetEventDetail(){
        $FileSys = ClassFileSys();
        $EventId = $_GET['event_id'];
        $EventType = $_POST['event_type'];
        if ($EventType == 1){
            $Detail = M('subscribe_event_detail_1')->where("`event_id` = ".$EventId)->find();
            $Detail['logo1_url'] = $FileSys->FileName2Url($Detail['logo1']);
            $Detail['logo2_url'] = $FileSys->FileName2Url($Detail['logo2']);
//            $Detail['conItemId1']=M('event_constitute')->where("name=".$Detail['team1'])->field('itemid')->find();
//            $Detail['conItemId2']=M('event_constitute')->where("name=".$Detail['team2'])->field('itemid')->find();
//            var_dump($Detail);
            $this->assign('Detail',$Detail);
            
            $Array = array(
                0=>'未开始',
                1=>'进行中',
                2=>'结束',
                3=>'取消'
            );
            foreach ($Array as $key=>$val){
                if ($key == $Detail['status']) {
                    $SelectShow .= "<option value='$key' selected>$val</option>";
                }else{
                    $SelectShow .= "<option value='$key'>$val</option>";
                }
            }
            $this->assign('SelectShow',$SelectShow);

            $this->display("Calendar/ajaxGetEventDetail1");
        }elseif($EventType == 2){
            $Detail = M('subscribe_event_detail_1')->where("`event_id` = ".$EventId)->find();

            var_dump ($Detail);
        
            $this->display("Calendar/ajaxGetEventDetail2");
        
        }
    
    }


    function eventstop () {
        if ($_GET['go'] == 'stop') {
            $event_status = '0';
        }else{
            $event_status = '1';
        }
        if ( M('subscribe_event')->where("event_id = ".$_GET['event_id'])->limit(1)->save(array('event_status'=>$event_status)) ) {
            echo 1;   
        }else{
            echo 0;
        }
    }


    function ajaxStopItem (){
        if($_POST['go'] == 'stop') {
            $item_status = 0;
        }else{
            $item_status = 1;
        }
        if ( M('subscribe_item')->where("item_id = ".$_POST['item_id'] )->limit(1)->save(array('item_status'=>$item_status)) ) {
            echo 1;
        }else{
            echo 0;
        }
    }

/*Z实现team搜索ajax代码*/
    function calSearchNameAjax(){
        $search = M('subscribe_item')->where(" item_name like '%{$_POST['search']}%'")->field('item_name,item_id')->limit(4)->select();
        foreach($search as $value){
            $searchlogo=M('event_constitute')->where("itemid={$value['item_id']}")->field('logo')->find();
            $search['logourl'] = ClassFileSys()->FileName2Url($searchlogo['logo']);
            echo "<p Sid='{$value['item_id']}' Slogo='{$search['logourl']}' Slogoname='{$searchlogo['logo']}'>{$value['item_name']}</p>";
        }

    }


}
