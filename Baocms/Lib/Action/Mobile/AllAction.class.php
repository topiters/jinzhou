<?php
class AllAction extends CommonAction {
	
	 public function index() {
		
	 $keyword = $this->_param('keyword', 'htmlspecialchars');
	 $type = $this->_param('type' , 'htmlspecialchars');
     $this->assign('keyword', $keyword);
     $this->assign('type' , $type);
     $this->assign('nextpage', LinkTo('all/load', array('t' => NOW_TIME,'keyword' => $keyword, 'p' => '0000', 'type' => $type)));
     $this->display(); // 输出模板
    }
	
	
    public function load(){
        import('ORG.Util.Page');
        $where_shop = array('closed'=>0,'audit' =>1,'city_id'=>$this->city_id);
		$where_tuan = array('closed'=>0,'audit' =>1,'end_date'=>array('EGT',TODAY));
		$where_goods = array('closed' => 0, 'audit' => 1, 'end_date' => array('EGT', TODAY));
		$where_coupon = array('audit' => 1,'closed' => 0, 'expire_date' => array('EGT', TODAY));


        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $where_shop['shop_name|tags'] = array('LIKE','%'.$keyword.'%');
			$where_tuan['title'] = array('LIKE', '%' . $keyword . '%');
			$where_goods['title'] = array('LIKE', '%' . $keyword . '%');
			$where_coupon['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

		$Shop=D('Shop');
		$Tuan=D('Tuan');
		$Goods=D('Goods');
		$Coupon = D('Coupon');
		
		$list_shop=$Shop->Field('"商家" as t_name,"shop/detail" as t_url,"shop_id" as t_param,shop_id as t_id,shop_name as t_title,photo as t_photo,tel as t_note')->order($orderby)->where($where_shop)->select();
//		echo $Shop->getLastSql();die;
		$list_tuan=$Tuan->Field('"团购" as t_name,"tuan/detail" as t_url,"tuan_id" as t_param,tuan_id as t_id,title as t_title,photo as t_photo,concat("￥",round(tuan_price/100,2)) as t_note')->order($orderby)->where($where_tuan)->select();
		$list_goods=$Goods->Field('"商品" as t_name,"mall/detail" as t_url,"goods_id" as t_param,goods_id as t_id,title as t_title,photo as t_photo,concat("￥",round(mall_price/100,2)) as t_note')->order($orderby)->where($where_goods)->select();
		$list_coupon=$Coupon->Field('"优惠" as t_name,"coupon/detail" as t_url,"coupon_id" as t_param,coupon_id as t_id,title as t_title,photo as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_coupon)->select();


		$list=array();
		if(!empty($list_tuan)){
			$list=array_merge($list,$list_tuan);
		}

		if(!empty($list_shop)){
			$list=array_merge($list,$list_shop);
		}
		if(!empty($list_goods)){
			$list=array_merge($list,$list_goods);
		}
		if(!empty($list_coupon)){
			$list=array_merge($list,$list_coupon);
		}

        $type = $this->_param('type' , 'htmlspecialchars');
		if ($type) {
		    if ($type == 'tab1') {
                $list = $list_tuan;
            }
            if ($type == 'tab2') {
                $list = $list_goods;
            }
            if ($type == 'tab3') {
                $list = $list_coupon;
            }
            if ($type == 'tab4') {
                $list = $list_shop;
            }
        }
		$Page=new Page(count($list),10);
	    $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		$list=array_slice($list,$Page->firstRow,$Page->listRows);
		$show=$Page->show();
		$this->assign('searchindex',0);
        $this->assign('total_num',count($list));
        $this->assign('list',$list);
        $this->assign('page', $show);

//        dump($list);die;

        $this->display();
    }
}