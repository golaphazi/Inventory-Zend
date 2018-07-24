<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use GlobalSetting\Model\OrganizationBranch;
	use GlobalSetting\Form\OrganizationBranchForm;

	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class OrganizationBranchController extends AbstractActionController {
		protected $organizationBranchTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getOrganizationBranchTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'organizationBranchs' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new OrganizationBranchForm('organizationBranch', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$organizationBranch = new OrganizationBranch();
				$form->setInputFilter($organizationBranch->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getOrganizationBranchTable()->transectionStart();
					$organizationBranch->exchangeArray($request->getPost());
					if($this->getOrganizationBranchTable()->saveOrganizationBranch($organizationBranch)) {
						$this->getOrganizationBranchTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Organization Branch [ ".$organizationBranch->BRANCH_NAME." ] added successfully!</h4></td>
																</tr>
															</table>");
						
						return $this->redirect()->toRoute('organizationbranch');	
					} else {
						$this->getOrganizationBranchTable()->transectionInterrupted();
						throw new \getOrganizationBranchTable("Money market organization branch couldn't save properly!");
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
				return $this->redirect()->toRoute('organizationbranch',array('action' => 'add'));
			}
			
			try {
				$organizationBranch = $this->getOrganizationBranchTable()->getOrganizationBranch($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('organizationbranch', array('action' => 'index'));
			}
			
			$form = new OrganizationBranchForm('organizationBranch', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			//$form->setBindOnValidate(false);
			$form->bind($organizationBranch);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getOrganizationBranchTable()->transectionStart();
					$organizationBranch->exchangeArray($request->getPost());
					if($this->getOrganizationBranchTable()->saveOrganizationBranch($organizationBranch)) {
						$this->getOrganizationBranchTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Organization Branch [ ".$organizationBranch->BRANCH_NAME." ] edit successfully!</h4></td>
																</tr>
															</table>");
						
						return $this->redirect()->toRoute('organizationbranch');	
					} else {
						$this->getOrganizationBranchTable()->transectionInterrupted();
						throw new \getOrganizationBranchTable("Money market organization branch couldn't edit properly!");	
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
				return $this->redirect()->toRoute('organizationbranch');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getOrganizationBranchTable()->deleteOrganizationBranch($id);
				}
				
				return $this->redirect()->toRoute('organizationbranch');
			}
			
			return array(
				'id'	=> $id,
				'organizationBranch'	=> $this->getOrganizationBranchTable()->getOrganizationBranch($id),
			);
		}
		
		public function getOrganizationBranchTable() {
			if(!$this->organizationBranchTable) {
				$sm = $this->getServiceLocator();
				$this->organizationBranchTable = $sm->get('GlobalSetting\Model\OrganizationBranchTable');
			}
			return $this->organizationBranchTable;
		}
	}
?>