<?php


class IndexAction extends CommonAction {

    public function index() {

        $this->assign('lifecate' , D('Lifecate')->fetchAll());
        $this->assign('channel' , D('Lifecate')->getChannelMeans());

        //获取用户自定坐标
        $lat = cookie('lat_ok');
        $lng = cookie('lng_ok');


        if (empty($lat) || empty($lng)) {
            $lat = cookie('lat');
            $lng = cookie('lng');
        }


        if (empty($lat) || empty($lng)) {
            $lat = $this->_CONFIG['site']['lat'];
            $lng = $this->_CONFIG['site']['lng'];
        }

        // $this->assign('zuobiao1',$lat);
        // $this->assign('zuobiao2',$lng);

        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
        $shoplist = D('Shop')->where(array('city_id' => $this->city_id , 'closed' => 0 , 'audit' => 1))->order($orderby)->limit(0 , 6)->select();
        foreach ($shoplist as $k => $val) {
            $shoplist[$k]['d'] = getDistance($lat , $lng , $val['lat'] , $val['lng']);
        }


        $news = D('Article')->where(array('city_id' => $this->city_id , 'closed' => 0 , 'audit' => 1))->order(array('orderby' => 'desc'))->limit(0 , 5)->select();
        $community = D('Community')->where(array('city_id' => $this->city_id , 'closed' => 0 , 'audit' => 1 ,))->order($orderby)->limit(0 , 5)->select();
        foreach ($community as $k => $val) {
            $community[$k]['d'] = getDistance($lat , $lng , $val['lat'] , $val['lng']);
        }

        foreach ($shoplist as $k => $val) {

            //微店
            $Weidian = D('Weidian_details');
            $weidianid = $Weidian->where('shop_id=' . $shoplist[$k]['shop_id'] . ' ')->find();
            //$this->assign('weidian_id', $weidianid['id']);
            $shoplist[$k]['weidianid'] = $weidianid['id'];

        }


        $this->assign('shoplist' , $shoplist);
        $this->assign('news' , $news);
        $this->assign('community' , $community);

        $maps = array('status' => 2 , 'closed' => 0);
        $this->assign('nav' , $nav = D('Navigation')->where($maps)->order(array('orderby' => 'asc'))->select());

        $condition = array('parent_id' => 0);
        $this->assign('daohang' , $daohang = D('Goodscate')->where($condition)->order(array('orderby' => 'asc'))->select());

        $bg_time = strtotime(TODAY);
        $this->assign('sign_day' , $sign_day = (int)D('Usersign')->where(array('user_id' => $this->uid , 'create_time' => array(array('ELT' , NOW_TIME) , array('EGT' , $bg_time))))->count());

        //首页推荐美食
        //$c['city_id'] = $this->city_id;
        $c['closed'] = 0;
        $c['audit'] = 1;
        $c['end_date'] = array("EGT" , TODAY);
        $meishi = D('Goods')->order(" orderby desc ")->where($c)->select();
        $this->assign("meishi" , $meishi);

        $this->display();
    }


    public function search() {
        $keys = D('Keyword')->fetchAll();
        $keytype = D('Keyword')->getKeyType();
        $this->assign('keys' , $keys);
        $this->display();
    }

    public function dingwei() {
        $lat = $this->_get('lat' , 'htmlspecialchars');
        $lng = $this->_get('lng' , 'htmlspecialchars');
        cookie('lat' , $lat);
        cookie('lng' , $lng);
        echo NOW_TIME;
    }

    public function more() {
        $maps = array('status' => 2 , 'closed' => 0);
        $this->assign('nav' , $nav = D('Navigation')->where($maps)->order(array('orderby' => 'asc'))->select());
        $this->display();
    }


}
