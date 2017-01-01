<?php



class  LotteryAction extends CommonAction{


    public function index() {
//        $arr = D('Sms')->sendSms();
//        dump($this->_CONFIG['sms']);die;
        if (empty($this->uid)) {
            $this->error('登录状态失效!' , U('passport/login'));
        }
        $this->display(); // 输出模板
    }

    public function run() {
        $uid = $this->uid;
        if ($uid == null) {
            $result['error'] = '您还没有登录,请先登录再抽奖';
            echo json_encode($result);
            die;
        }
        $aid = I('id');
        $aInfo = D('weixin_lottery')->where("id = $aid")->find();
        $max_num = $aInfo['max_num']; //单人限制次数
        $daynum = $aInfo['daynums']; //每日限制人数
        $today = strtotime(TODAY);
        $todayNum = D('weixin_record')->where("ctime > $today")->count();
        $num = D('weixin_record')->where("uid = $uid and aid = $aid and type = 1")->count();
        if ($todayNum >= $daynum) {  //检测今日抽奖次数是否已达上限
            $result['error'] = '今日抽奖次数已达上限';
        } elseif ($num >= $max_num) {  //检测当前用户是否已达次数限制
            $result['error'] = '您的抽奖次数已用完';
        } else {
            $proArr = array();
            //v 是中奖概率   id为奖品编号  min max 分别为最大和最小角度
            $prize_arr = array(
                '0' => array('id' => 1 , 'min' => 1 , 'max' => 29 , 'prize' => $aInfo['fist'] , 'v' => $aInfo['fistlucknums']) ,
                '1' => array('id' => 2 , 'min' => 301 , 'max' => 329 , 'prize' => $aInfo['second'] , 'v' => $aInfo['secondlucknums']) ,
                '2' => array('id' => 3 , 'min' => 241 , 'max' => 269 , 'prize' => $aInfo['third'] , 'v' => $aInfo['thirdlucknums']) ,
                '3' => array('id' => 4 , 'min' => 181 , 'max' => 209 , 'prize' => $aInfo['four'] , 'v' => $aInfo['fourlucknums']) ,
                '4' => array('id' => 5 , 'min' => 121 , 'max' => 149 , 'prize' => $aInfo['five'] , 'v' => $aInfo['fivelucknums']) ,
                '5' => array('id' => 6 , 'min' => 61 , 'max' => 89 , 'prize' => $aInfo['six'] , 'v' => $aInfo['sixlucknums']) ,
                '6' => array('id' => 7 , 'min' => array(31 , 91 , 151 , 211 , 271 , 331) , 'max' => array(59 , 119 , 179 , 239 , 299 , 359) , 'prize' => $aInfo['aginfo'] , 'v' => $aInfo['unlucknums'])
            );
            //获取随机奖品
            foreach ($prize_arr as $v) {
                $proArr[$v['id']] = $v['v'];
            }
            $rid = $this->getRand($proArr); //根据概率获取奖项id
            $res = $prize_arr[$rid - 1]; //中奖项
            //	dd($res);die;
            $min = $res['min'];
            $max = $res['max'];

            //记录抽奖信息
            $data['uid'] = $uid;
            $data['aid'] = $aid;
            $data['type'] = 1;
            $data['ctime'] = time();
            D('weixin_record')->add($data);

            if ($res['id'] == 7) { //七等奖
                $i = mt_rand(0 , 5);
                $result['angle'] = mt_rand($min[$i] , $max[$i]);
            } else {
                //如果中奖了记录大乐透中奖信息表
                $nickname = D('users')->field('user_id,nickname')->where("user_id = $uid")->find();
                $datav['lottery_id'] = $aid;
                $datav['uid'] = $uid;
                $datav['shop_id'] = $aInfo['shop_id'];
                $datav['nickname'] = $nickname['nickname'];
                $datav['islottery'] = 1;
                $datav['prize'] = $res['id'];
                D('weixin_lotterysn')->add($datav);
                $result['angle'] = mt_rand($min , $max); //随机生成一个角度
            }
            $result['prize'] = $res['prize'];
            $result['error'] = '';
        }
        echo json_encode($result);
    }


    protected function getRand($proArr) {
        $result = '';

        //概率数组的总概率精度
        $proSum = array_sum($proArr);

        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1 , $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }


    public function zhong() {
        $this->display();
    }

    public function weizhong(){
        
        $this->display(); // 输出模板   
    }
	 
	 
   
}