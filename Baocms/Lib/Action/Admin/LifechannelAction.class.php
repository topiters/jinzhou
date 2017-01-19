<?php



class LifechannelAction extends CommonAction {

    private $create_fields = array('channel_id', 'channel_name', 'channel_img' , 'order');
    private $edit_fields = array('channel_id' , 'channel_name' , 'channel_img' , 'order');

    public function index() {
        $Lifecate = D('life_channel');
        import('ORG.Util.Page'); // 导入分页类
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $map = array();
        if ($keyword) {
            $map['channel_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);

        $count = $Lifecate->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = (int)$_GET[$var];
        $this->assign('p',$p);
        $list = $Lifecate->where($map)->order(array('order' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        foreach (D('Lifecate')->getChannelMeans() as $k=>$v) {
            $arr[$v['channel_id']]=$v['channel_name'];
        }
        $this->assign('channelmeans', $arr);
        $this->display(); // 输出模板
    }
    
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('life_channel');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('life_channel/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['channel_name'] = htmlspecialchars($data['channel_name']);
        if (empty($data['channel_name'])) {
            $this->baoError('分类名称不能为空');
        }

        $data['order'] = (int)$data['order'];
        return $data;
    }

    public function edit($channel_id = 0) {
        if ($channel_id = (int) $channel_id) {
            $obj = D('life_channel');
            if (!$detail = $obj->find($channel_id)) {
                $this->baoError('请选择要编辑的分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['channel_id'] = $channel_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('lifechannel/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的分类');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['channel_name'] = htmlspecialchars($data['channel_name']);
        if (empty($data['channel_name'])) {
            $this->baoError('分类名称不能为空');
        }
        $data['order'] = (int)$data['order'];
        return $data;
    }

    public function delete($channel_id = 0) {
        if (is_numeric($channel_id) && ($channel_id = (int) $channel_id)) {
            $obj = D('Lifechannel');
            $obj->delete($channel_id);
            $this->baoSuccess('删除成功！', U('lifechannel/index'));
        } else {
            $channel_id = $this->_post('channel_id', false);
            if (is_array($channel_id)) {
                $obj = D('Lifechannel');
                foreach ($channel_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('lifechannel/index'));
            }
            $this->baoError('请选择要删除的分类管理');
        }
    }
    
    public function delattr($attr_id){
        if(empty($attr_id)){
            $this->baoError('操作失败！');
        }
        if(!$detail = D('Lifecateattr')->find($attr_id)){
            $this->baoError('操作失败');
        }
        D('Lifecateattr')->delete($attr_id);
        $this->baoSuccess('删除成功！',U('lifecate/setting',array('cate_id'=>$detail['cate_id'])));
    }
    
    
    public function ajax($channel_id,$life_id=0){
        if(!$channel_id = (int)$channel_id){
            $this->error('请选择正确的分类');
        }
        if(!$detail = D('Lifecate')->find($channel_id)){
            $this->error('请选择正确的分类');
        }
        $this->assign('cate',$detail);
        $this->assign('attrs',D('Lifecateattr')->order(array('orderby'=>'asc'))->where(array('cate_id'=>$channel_id))->select());
        if($life_id){
            $this->assign('detail',D('Life')->find($life_id));
            $this->assign('maps',D('LifeCateattr')->getAttrs($life_id));
        }
        $this->display();
    }

    
    public function setting($channel_id){
        if(!$channel_id = (int)$channel_id){
            $this->error('请选择正确的分类');
        }
        if(!$detail = D('Lifecate')->find($channel_id)){
             $this->error('请选择正确的分类');
        }
        if($this->isPost()){
            $obj = D('Lifecateattr');
            $data = $this->_post('data',false);
            foreach($data as $key => $val){
                foreach($val as $k=>$v){
                    if(!empty($v['attr_name'])){
                       $obj->add(array(
                           'cate_id'    => $channel_id,
                           'type'       => htmlspecialchars($key),
                           'attr_name'  => htmlspecialchars($v['attr_name']),
                           'orderby'    => (int)$v['orderby']
                       ));                        
                    }
                }
            }  
            
            $old = $this->_post('old',false);
            
            foreach($old  as $key => $val){
                $obj->save(array(
                    'attr_id'   => (int)$key,
                    'attr_name' => htmlspecialchars($val['attr_name']),
                     'orderby' => (int)$val['orderby'],
                ));
            }            
            $this->baoSuccess('操作成功！',U('lifecate/setting',array('cate_id'=>$channel_id)));
        }else{
            $this->assign('detail',$detail);
            $this->assign('attrs',D('Lifecateattr')->order(array('orderby'=>'asc'))->where(array('cate_id'=>$channel_id))->select());
            $this->display();
        }
    }

}
