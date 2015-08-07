<?php
//首页
class IndexAction extends Action {
    public function index(){
        //引入用户类
        $CmUser = LoadClass('CmUser',true);
        //检测是否登录
        $CmUser->NotLogInJump();

        $GroupSelect = M('subscribe_group')->field('group_id,group_name')->select();
        foreach ($GroupSelect as $val){
            $GroupData[$val['group_id']] = $val['group_name'];
        }
        $ItemSelect = M('subscribe_item')->field('group_id,sum(`sub_count`) as SubSum')->group('group_id')->select();
        foreach ($ItemSelect as $val){
            $ItemData[$val['group_id']] = $val['SubSum'];
        }

        $Chart1['title'] = '订阅量分布图';
        foreach ($GroupData as $key =>$val) {
            $ShowData .='["'.$val .'",'.$ItemData[$key].'],';
        }
        $Chart1['ShowData'] = substr($ShowData,0,-1);
        $this->assign('Chart1',$Chart1);


        $Chart2['title'] = '有效事件分布图';
        $ItemSelect = M('subscribe_item')->field('item_id,item_name')->select();
        foreach ($ItemSelect as $val){
            $ItemData[$val['item_id']] = $val['item_name'];
        }
        $Ex = implode(',',array_keys($ItemData));
        $today = date('Y-m-d');
        $EventSelect = M('subscribe_event')->field('item_id,count(*) as Count')->where("endtime >'$today'")->group('item_id')->select();
        foreach ($EventSelect as $val){
            $EventData[$val['item_id']]=$val['Count'];
        }
        foreach ( $ItemData as $key => $val){
            $n = $EventData[$key] ? $EventData[$key] : 0;
            $ShowData2.='["'.$val.'",'.$n.'],';
        }
        $Chart2['ShowData'] = substr($ShowData2,0,-1);

        $this->assign('Chart2',$Chart2);



        $PubTool = ClassPubTool();
        $CalDate = $PubTool->DateFormatToCalDate(date('Ymd'),true);
        $SelectData = M('subscribe_event')->field(" count(*) as Count,item_id")->where("event_date in ($CalDate)")->group('item_id')->select();
        foreach ($SelectData as $val){
            $ShowData3 .= '["'.$ItemData[$val['item_id']].'",'.$val['Count'].'],';
        }
        $Chart3['ShowData'] = substr($ShowData3,0,-1);
        $this->assign('Chart3',$Chart3);




        $this->display();
    }
}
