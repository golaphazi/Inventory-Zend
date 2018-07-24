<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use GlobalSetting\Model\AccountType;
	use GlobalSetting\Form\AccountTypeForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class AccountTypeController extends AbstractActionController {
		protected $accountTypeTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getAccountTypeTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'accountTypes' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new AccountTypeForm('accountType', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$accountType = new AccountType();
				$form->setInputFilter($accountType->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getAccountTypeTable()->transectionStart();
					$accountType->exchangeArray($request->getPost());
					if($this->getAccountTypeTable()->saveAccountType($accountType)) {
						$this->getAccountTypeTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Accounts type [ ".$accountType->ACCOUNT_TYPE." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('accounttype');	
					} else {
						$this->getAccountTypeTable()->transectionInterrupted();
						throw new \Exception("Account type couldn't save properly!");	
					}
				}
			}
			
			return array('form' => $form);
		}
		
		public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
		   	$id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('accounttype',array('action' => 'add'));
			}
			
			try {
				$accountType = $this->getAccountTypeTable()->getAccountType($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('accounttype', array('action' => 'index'));
			}
			
			$form = new AccountTypeForm('accountType', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($accountType);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getAccountTypeTable()->transectionStart();
					$accountType->exchangeArray($request->getPost());
					if($this->getAccountTypeTable()->saveAccountType($accountType)) {
						$this->getAccountTypeTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Accounts type [ ".$accountType->ACCOUNT_TYPE." ] edit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('accounttype');	
					} else {
						$this->getAccountTypeTable()->transectionInterrupted();
						throw new \Exception("Account type couldn't save properly!");	
					}
				}
			}
			
			return array(
				'id' => $id,
				'form' => $form,
			);
		}
		
		public function deleteAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$id = (int) $this->params()->fromRoute('id',0);
			
			if(!$id) {
				return $this->redirect()->toRoute('accounttype');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getAccountTypeTable()->deleteAccountType($id);
				}
				
				return $this->redirect()->toRoute('accounttype');
			}
			
			return array(
				'id'	=> $id,
				'accountType'	=> $this->getAccountTypeTable()->getAccountType($id),
			);
		}
		
		public function getAccountTypeTable() {
			if(!$this->accountTypeTable) {
				$sm = $this->getServiceLocator();
				$this->accountTypeTable = $sm->get('GlobalSetting\Model\AccountTypeTable');
			}
			return $this->accountTypeTable;
		}
	}
?>