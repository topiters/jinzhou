<?php



class IndexAction extends CommonAction {

   public function index(){
//       dump($this->uid);die;
		if(empty($this->uid)){
			redirect('mobile/passport/login');
		}else{
			redirect('/mcenter/member');
		}
   		
   }

}
