<?php


class LotteryAction extends CommonAction {
    private $create_fields = array('id','predict_num','views','shop_id','keyword','photo','title','txt','use_tips','stime','ltime','info','aginfo','end_tips','end_photo','views','end_photo','fist','fistnums','fistlucknums','second','secondnums','secondlucknums','third','thirdnums','thirdlucknums','joinnum','max_num','parssword','four','fournums','fourlucknums','five','fivenums','fivelucknums','six','sixnums','sixlucknums','daynums','member_condtion','dateline','follower_condtion' , 'unlucknums');
    private $edit_fields = array('id','predict_num','views','shop_id','keyword','photo','title','txt','use_tips','stime','ltime','info','aginfo','end_tips','end_photo','views','end_photo','fist','fistnums','fistlucknums','second','secondnums','secondlucknums','third','thirdnums','thirdlucknums','joinnum','max_num','parssword','four','fournums','fourlucknums','five','fivenums','fivelucknums','six','sixnums','sixlucknums','daynums','member_condtion','dateline','follower_condtion' , 'unlucknums');
	private $goodscreate_fields = array('id','scratch_id','title','name','num','sort','photo','shop_id');
	private $goodsedit_fields = array('id','scratch_id','title','name','num','sort','photo','shop_id');
    public function _initialize() {
        parent::_initialize();
    }
	
	public function create()
	{
		if(!$shop_id = $this->shop_id){
			 $this->error('商家不能为空');
		}
		if ($this->isPost()) {
			$data = $this->createCheck();
            if (!empty($_FILES['photo_file']['name'])) {
                $upload = new UploadAction();
                $arr = $upload->uploadify();
                $data['photo'] = $arr[0];
                $data['end_photo'] = $arr[1];
            }
			$obj = D('Weixin_lottery');
			$objk = D('Shop_weixin_keyword');
			
			$map = array();
			$map['shop_id'] = $shop_id;
			$map['keyword'] = $data['keyword'];
			if($k = $objk->where($map)->select()){
				$this->error('该关键字已经被使用，请修改关键字');
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
					$this->error('添加关键字失败！');
				}	
			}				
			if ($id = $obj->add($data)) {
				 $this->success('添加成功', U('store/coupon/prize'));
            }else{
				$this->error('添加失败！');
			}	
		}else{
			$this->display();
		}
	}
	
	
	 private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->error('活动名称不能为空');
        }
		if (empty($data['stime'])) {
            $this->error('活动开始时间不能为空');
        }
		if (empty($data['ltime'])) {
            $this->error('活动结束时间不能为空');
        }
		if (empty($data['fist'])) {
            $this->error('活动奖项不能为空');
        }
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
                $this->error('请选择要编辑的活动');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->error('不可操作其他商家的活动！');
            }
            if ($this->isPost()) {
//                dump($_FILES);die;
                $data = $this->editCheck();
                if (!empty($_FILES['photo_file']['name'])) {
                    $upload = new UploadAction();
                    $arr = $upload->uploadify();
                    $data['photo'] = $arr[0];
                    $data['end_photo'] = $arr[1];
                }

				$obj = D('Weixin_lottery');
				$data['id'] = $id;			
                if (false !== $obj->save($data)) {
                    $this->success('修改成功', U('store/coupon/prize'));
                }
                $this->error('修改失败');
            } else {
                $this->assign('nowtime',time());
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑活动');
        }
    }
	
	private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
		if (empty($data['title'])) {
            $this->error('活动名称不能为空');
        }
		if (empty($data['stime'])) {
            $this->error('活动开始时间不能为空');
        }
		if (empty($data['ltime'])) {
            $this->error('活动结束时间不能为空');
        }
		if (empty($data['fist'])) {
            $this->error('活动奖项不能为空');
        }
        return $data;
    }
	
	public function delete($id=null)
    {
		$obj = D('Weixin_lottery');
        if($id = (int)$id){
			if(!$detail = $obj->find($id)){
				$this->error('你要删除的内容不存在');
			}elseif($obj->delete($id)){
				$this->success('删除成功！',U('store/coupon/prize'));
			}
        }else{
			$this->error('非法操作！');
		}
    }

	public function sn($id = 0) {
        if ($id = (int) $id) {
            $obj = D('Weixin_lotterysn');
			$obje = D('Weixin_lottery');
			if(!$detail = $obje->find($id)){
				$this->error('该活动不存在');
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
			$this->error('该活动不存在');
		}
		$this->display();
    }

	public function snedit($sn_id=null)
    {
		$obj = D('Weixin_lotterysn');
        if($sn_id = (int)$sn_id){
			if(!$detail = $obj->find($sn_id)){
				$this->error('你要修改的内容不存在或已经删除');
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
					$this->success('修改成功！',U('lottery/sn',array('id'=>$detail['lottery_id'])));
                }
            }
        }
    }  
	
	public function sndelete($sn_id=null)
    {
		$obj = D('Weixin_lotterysn');
        if($sn_id = (int)$sn_id){
			if(!$detail = $obj->find($sn_id)){
				$this->error('你要修改的内容不存在或已经删除');
			}elseif($obj->delete($sn_id)){
				$this->success('删除成功！',U('lottery/sn',array('id'=>$detail['lottery_id'])));
			}
        }
    }
}