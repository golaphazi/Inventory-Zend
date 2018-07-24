<?php
	namespace Ibcia\Controller;
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use Zend\Session\Container as SessionContainer; 
	
	class IndexController extends AbstractActionController {
		public function indexAction() {
			$viewModel = new ViewModel();
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			//echo "Sumon";die();
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= date('d-m-Y',strtotime($this->session->businessdate));			
			$businessDateShowTemp	= explode("-",$businessDate);
			$businessDateShowTemp_format = date('l, F d, Y',mktime(0,0,0,$businessDateShowTemp[1],$businessDateShowTemp[0],$businessDateShowTemp[2]));
			$bMonth = date("m",strtotime($businessDate));
			$bYear = date("Y",strtotime($businessDate));
			$username 	= $this->session->username;
			$branchid	= $this->session->branchid;
			$rolename	= $this->session->rolename;
			
			return new ViewModel(array(
				'userName' 			=> ucwords($username),
				'businessDate' 		=> $businessDateShowTemp_format,
				'branchid' 			=> $branchid,
				'rolename' 			=> $rolename,
				'corporateAction' 	=> '',
			));
		}
	}
?>