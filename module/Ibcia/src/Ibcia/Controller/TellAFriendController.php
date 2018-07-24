<?php
	namespace Ibcia\Controller;
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use Ibcia\Form\TellAFriendForm;
	use Zend\Session\Container as SessionContainer; 
	class TellAFriendController extends AbstractActionController {
		public function indexAction() {
			$viewModel = new ViewModel();
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			return new ViewModel(array(
			));
		}
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			$this->session = new SessionContainer('post_supply');
			$startDate = $this->session->businessdate;
			$businessDate = $this->session->businessdate;
			$request = $this->getRequest();
			$form = new TellAFriendForm('tellafriend', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );			
			
			$form->get('submit')->setValue('Add');
			if($request->isPost()) {
				//$investormanagement = new InvestorManagement();
				//$form->setInputFilter($investormanagement->getInputFilter());
				$form->setData($request->getPost());
				$post 	= array_merge_recursive(
					$request->getPost()->toArray(),
					$request->getFiles()->toArray()
				);
				$form->setData($post);
				echo '<pre>';print_r($request->getPost());die();
			}
			
			//$form->get('submit')->setValue('Add');
			return array('form' => $form);
		}
	}
?>	