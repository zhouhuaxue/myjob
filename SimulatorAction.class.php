<?php

//引入用户类
$CmUser = LoadClass('CmUser',true);
//检测是否登录
$CmUser->NotLogInJump();

class SimulatorAction extends Action {

    private $SimArr = [
        'c=calendar&a=groupList'=>[ //分组页面
            'data' => [
                'guid'=>'1111123456511544886',
                ],
        ],
        'c=calendar&a=itemList'=>[ //订阅源页面
            'data' => [
                'guid'=>'1111123456511544886',
                'group_id'=>1,
                ],
        ],
    
    ];
    


    //首页
    function calendar(){

        $this->display();
    }



    //API接口
    function api(){

        $this->display();
    
    }

}
