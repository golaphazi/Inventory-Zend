<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use GlobalSetting\Model\OrganizationType;
	use GlobalSetting\Form\OrganizationTypeForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class OrganizationTypeController extends AbstractActionController {
		protected $organizationTypeTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getOrganizationTypeTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'organizationTypes' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new OrganizationTypeForm('organizationType', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$organizationType = new OrganizationType();
				$form->setInputFilter($organizationType->getInputFilter());
				$form->setData($request->getPost());
				//echo 'hi there';die();
				if($form->isValid()) {
					$organizationType->exchangeArray($request->getPost());
					$this->getOrganizationTypeTable()->transectionStart();
					
					if($this->getOrganizationTypeTable()->saveOrganizationType($organizationType)) {
						$this->getOrganizationTypeTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Organization type [ ".$organizationType->ORG_TYPE." ] added successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('organizationtype');	
					} else {
						$this->getOrganizationTypeTable()->transectionInterrupted();
						throw new \getOrganizationTypeTable("Organization type couldn't save properly!");
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
				return $this->redirect()->toRoute('organizationtype',array('action' => 'add'));
			}
			
			try {
				$organizationType = $this->getOrganizationTypeTable()->getOrganizationType($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('organizationtype', array('action' => 'index'));
			}
			
			$form = new OrganizationTypeForm('organizationType', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($organizationType);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getOrganizationTypeTable()->transectionStart();
					$organizationType->exchangeArray($request->getPost());
					if($this->getOrganizationTypeTable()->saveOrganizationType($organizationType)) {
						$this->getOrganizationTypeTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Organization type [ ".$organizationType->ORG_TYPE." ] edit successfully!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('organizationtype');
					} else {
						$this->getOrganizationTypeTable()->transectionInterrupted();
						throw new \getOrganizationTypeTable("Money market organization couldn't edit properly!");		
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
				return $this->redirect()->toRoute('organizationtype');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getOrganizationTypeTable()->deleteOrganizationType($id);
				}
				
				return $this->redirect()->toRoute('organizationtype');
			}
			
			return array(
				'id'	=> $id,
				'organizationType'	=> $this->getOrganizationTypeTable()->getOrganizationType($id),
			);
		}
		
		public function getOrganizationTypeTable() {
			if(!$this->organizationTypeTable) {
				$sm = $this->getServiceLocator();
				$this->organizationTypeTable = $sm->get('GlobalSetting\Model\OrganizationTypeTable');
			}
			return $this->organizationTypeTable;
		}
	}
?>