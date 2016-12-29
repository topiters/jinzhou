<?php


class LotteryAction extends CommonAction {
    private $create_fields = array('id','predict_num','views','shop_id','keyword','photo','title','txt','use_tips','stime','ltime','info','aginfo','end_tips','end_photo','views','end_photo','fist','fistnums','fistlucknums','second','secondnums','secondlucknums','third','thirdnums','thirdlucknums','joinnum','max_num','parssword','four','fournums','fourlucknums','five','fivenums','fivelucknums','six','sixnums','sixlucknums','daynums','member_condtion','dateline','follower_condtion');
    private $edit_fields = array('id','predict_num','views','shop_id','keyword','photo','title','txt','use_tips','stime','ltime','info','aginfo','end_tips','end_photo','views','end_photo','fist','fistnums','fistlucknums','second','secondnums','secondlucknums','third','thirdnums','thirdlucknums','joinnum','max_num','parssword','four','fournums','fourlucknums','five','fivenums','fivelucknums','six','sixnums','sixlucknums','daynums','member_condtion','dateline','follower_condtion');
	private $goodscreate_fields = array('id','scratch_id','title','name','num','sort','photo','shop_id');
	private $goodsedit_fields = array('id','scratch_id','title','name','num','sort','photo','shop_id');
    public function _initialize() {
        parent::_initialize();
    }
    public function index() {
        $this->display(); // 输出模板
    }

    public function run() {

        if (1) {
            $result['error'] = '没次数了';
        } else {
            $proArr = array();
            //v 是中奖概率   id为奖品编号  min max 分别为最大和最小角度
            $prize_arr = array(
                '0' => array('id' => 1 , 'min' => 1 , 'max' => 29 , 'prize' => '一等奖' , 'v' => 1) ,
                '1' => array('id' => 2 , 'min' => 302 , 'max' => 328 , 'prize' => '二等奖' , 'v' => 2) ,
                '2' => array('id' => 3 , 'min' => 242 , 'max' => 268 , 'prize' => '三等奖' , 'v' => 3) ,
                '3' => array('id' => 4 , 'min' => 182 , 'max' => 208 , 'prize' => '四等奖' , 'v' => 4) ,
                '4' => array('id' => 5 , 'min' => 122 , 'max' => 148 , 'prize' => '五等奖' , 'v' => 5) ,
                '5' => array('id' => 6 , 'min' => 62 , 'max' => 88 , 'prize' => '六等奖' , 'v' => 10) ,
                '6' => array('id' => 7 , 'min' => array(32 , 92 , 152 , 212 , 272 , 332) , 'max' => array(58 , 118 , 178 , 238 , 298 , 358) , 'prize' => '谢谢参与' , 'v' => 75)
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

            if ($res['id'] == 7) { //七等奖
                $i = mt_rand(0 , 5);
                $result['angle'] = mt_rand($min[$i] , $max[$i]);
            } else {
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
	
	public function create()
	{
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
		if ($this->isPost()) {
			$data = $this->createCheck();
			$obj = D('Weixin_lottery');
			$objk = D('Shop_weixin_keyword');
			
			$map = array();
			$map['shop_id'] = $shop_id;
			$map['keyword'] = $data['keyword'];
			if($k = $objk->where($map)->select()){
				$this->baoError('该关键字已经被使用，请修改关键字');
			}else{
				$keyword = array();
				$keyword['shop_id'] = $data['shop_id'];
				$keyword['keyword'] = $data['keyword'];
				$keyword['type'] = news;
				$keyword['keyword'] = $data['keyword'];
				$keyword['content'] = $data['use_tips'];
				$keyword['url'] = $data[''];
				$keyword['photo'] = $data['end_photo'];
				if(!$keyword_id = $objk->add($keyword)){
					$this->baoError('添加关键字失败！');
				}	
			}				
			if ($id = $obj->add($data)) {
				 $this->baoSuccess('添加成功', U('lottery/index'));
            }else{
				$this->baoError('添加失败！');
			}	
		}else{
			$this->display();
		}
	}
	
	
	 private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        /*if (empty($data['title'])) {
            $this->baoError('活动名称不能为空');
        }
		if (empty($data['stime'])) {
            $this->baoError('活动开始时间不能为空');
        }
		if (empty($data['ltime'])) {
            $this->baoError('活动结束时间不能为空');
        }
		if (empty($data['fist'])) {
            $this->baoError('活动奖项不能为空');
        }*/
        $data['shop_id'] = $this->shop_id;
        $data['type'] = news;
		$data['dateline'] = NOW_TIME;
        if (empty($data['type'])) {
            $data['type'] = news;
        }
        //$data['create_time'] = NOW_TIME;
        //$data['create_ip'] = get_client_ip();
        return $data;
    }
	
	
	
	 public function edit($id = 0) {
        if ($id = (int) $id) {
            $obj = D('Weixin_lottery');
            if (!$detail = $obj->find($id)) {
                $this->baoError('请选择要编辑的活动');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->error('不可操作其他商家的活动！');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
				$obj = D('Weixin_lottery');
				$data['id'] = $id;			
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('修改成功', U('lottery/index'));
                }
                $this->baoError('修改失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑活动');
        }
    }
	
	private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
		/*if (empty($data['title'])) {
            $this->baoError('活动名称不能为空');
        }
		if (empty($data['stime'])) {
            $this->baoError('活动开始时间不能为空');
        }
		if (empty($data['ltime'])) {
            $this->baoError('活动结束时间不能为空');
        }
		if (empty($data['fist'])) {
            $this->baoError('活动奖项不能为空');
        }*/
        return $data;
    }
	
	public function delete($id=null)
    {
		$obj = D('Weixin_lottery');
        if($id = (int)$id){
			if(!$detail = $obj->find($id)){
				$this->baoError('你要删除的内容不存在');
			}elseif($obj->delete($id)){
				$this->baoSuccess('删除成功！',U('lottery/index'));
			}
        }else{
			$this->baoError('非法操作！');
		}
    }

	public function sn($id = 0) {
        if ($id = (int) $id) {
            $obj = D('Weixin_lotterysn');
			$obje = D('Weixin_lottery');
			if(!$detail = $obje->find($id)){
				$this->baoError('该活动不存在');
			}else{
				$this->assign('detail', $detail);
			}
			$map = array();
			$map['lottery_id'] =$id;
			import('ORG.Util.Page'); // 导入分页类
			$count = $obj->where($map)->count();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$list = $obj->where($map)->order(array('sn_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
			if($list){
				foreach($list as $k => $v){
					if($v['prize']==1){
						$list[$k]['prizes'] = "一等奖";
					}else if($v['prize']==2){
						$list[$k]['prizes'] = "二等奖";
					}else if($v['prize']==3){
						$list[$k]['prizes'] = "三等奖";
					}else if($v['prize']==4){
						$list[$k]['prizes'] = "四等奖";
					}else if($v['prize']==5){
						$list[$k]['prizes'] = "五等奖";
					}else if($v['prize']==6){
						$list[$k]['prizes'] = "六等奖";
					}else if($v['prize'] > 6){
						$list[$k]['prizes'] = "未中奖";
					}
					
				}	
				$this->assign('list', $list);
				$this->assign('page', $show); // 赋值分页输出
			}
			 
		}else{
			$this->baoError('该活动不存在');
		}
		$this->display();
    }

	public function snedit($sn_id=null)
    {
		$obj = D('Weixin_lotterysn');
        if($sn_id = (int)$sn_id){
			if(!$detail = $obj->find($sn_id)){
				$this->baoError('你要修改的内容不存在或已经删除');
			}else{
				if($detail['is_use'] == '1'){
					$data['is_use'] = 0;
					$data['use_time'] = '';
				}else{
					$data['is_use'] = 1;
					$data['use_time'] = __TIME;
				}
				$data['sn_id'] = $sn_id;
                if($obj->save($data)){
					$this->baoSuccess('修改成功！',U('lottery/sn',array('id'=>$detail['lottery_id'])));
                }
            }
        }
    }  
	
	public function sndelete($sn_id=null)
    {
		$obj = D('Weixin_lotterysn');
        if($sn_id = (int)$sn_id){
			if(!$detail = $obj->find($sn_id)){
				$this->baoError('你要修改的内容不存在或已经删除');
			}elseif($obj->delete($sn_id)){
				$this->baoSuccess('删除成功！',U('lottery/sn',array('id'=>$detail['lottery_id'])));
			}
        }
    }
}