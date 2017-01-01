<?php



class  ScratchAction extends CommonAction{


    public function index() {
        if (empty($this->uid)) {
            $this->error('登录状态失效!' , U('passport/login'));
            exit;
        }
        $uid = $this->uid;
        $aid = I('id');
        $aInfo = D('weixin_scratch')->where("scratch_id = $aid")->find();
        $prize = D('weixin_prize')->where("scratch_id = $aid")->order('luck asc')->select();
        $time = D('weixin_record')->where("uid = $uid and type = 2 and aid = $aid")->count();
        $nickname = D('users')->field('user_id,nickname')->where("user_id = $uid")->find();
//        dump($aInfo);dump($prize);die;
        $this->assign('nickname',$nickname['nickname']);
        $this->assign('time',$time);
        $this->assign('aInfo',$aInfo);
        $this->assign('prize',$prize);
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
        $aInfo = D('weixin_scratch')->where("scratch_id = $aid")->find();
//        dump($aInfo);die;
        $max_num = $aInfo['max_num']; //单人限制次数
        $prize = D('weixin_prize')->where("scratch_id = $aid")->select();
//        dump($prize);die;
        $num = D('weixin_record')->where("uid = $uid and aid = $aid and type = 2")->count();
        if ($num >= $max_num) {  //检测当前用户是否已达次数限制
            $result['error'] = '您的抽奖次数已用完';
        } else {
            //获取随机奖品
            $proArr[0] = $aInfo['unluck'];
            foreach ($prize as $k=>$v) {
                $prize[$v['id']] = $v;
                $proArr[$v['id']] = $v['luck'];
            }
            $rid = $this->getRand($proArr); //根据概率获取奖项id
            if ($rid == 0) {
                $res = '谢谢参与';
            } else {
                $res = $prize[$rid - 1]; //中奖项
            }
//            dump($res);die;
            //记录抽奖信息
            $data['uid'] = $uid;
            $data['aid'] = $aid;
            $data['type'] = 2;
            $data['ctime'] = time();
            D('weixin_record')->add($data);

            if ($res == '谢谢参与') { //没有中奖
                $result['prize'] = '谢谢参与';
            } else {
                //如果中奖了记录刮刮卡中奖信息表
                $nickname = D('users')->field('user_id,nickname')->where("user_id = $uid")->find();
                $datav['scratch_id'] = $aid;
                $datav['uid'] = $uid;
                $datav['shop_id'] = $aInfo['shop_id'];
                $datav['nickname'] = $nickname['nickname'];
                $datav['prize_id'] = $res['id'];
                $datav['prize_title'] = $res['title'];
                D('weixin_scratchsn')->add($datav);
                $result['prize'] = $res['name'];
            }
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