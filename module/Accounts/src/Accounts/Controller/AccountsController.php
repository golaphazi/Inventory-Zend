<?php
	namespace Accounts\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;

	class AccountsController extends AbstractActionController {
		public function indexAction() {
			$userInfo 						= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID						= $userInfo->id;
			
			$this->layout()->leftMenu 		= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Accounts',$USER_ID);
			$this->layout()->controller 	= $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			return new ViewModel();
		}
	}
?>