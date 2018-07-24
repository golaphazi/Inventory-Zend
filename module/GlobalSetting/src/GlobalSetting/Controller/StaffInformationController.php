<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use GlobalSetting\Model\StaffInformation;
	use GlobalSetting\Form\StaffInformationForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;

	class StaffInformationController extends AbstractActionController {
		protected $staffinformationTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator 					= $this->getStaffInformationTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'staffinfors' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		

		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new StaffInformationForm();
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				
				$staffinformation = new StaffInformation();
				$form->setInputFilter($staffinformation->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getStaffInformationTable()->transectionStart();
					$staffinformation->exchangeArray($request->getPost());
					if($this->getStaffInformationTable()->saveStaffInformation($staffinformation)) {
						$this->getStaffInformationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>staff information [ ".$staffinformation->name." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('staffinformation');	
					} else {
						$this->getStaffInformationTable()->transectionInterrupted();
						throw new \Exception("staffinformation couldn't save properly!");
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
				return $this->redirect()->toRoute('staffinformation',array('action' => 'add'));
			}
			//echo $id;exit();
			try {
				$staffinformation = $this->getStaffInformationTable()->getStaffInformation($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('staffinformation', array('action' => 'index'));
			}
			//print_r( $staffinformation); exit();
			$form = new StaffInformationForm();
			//$form->setBindOnValidate(false);
			$form->bind($staffinformation);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getStaffInformationTable()->transectionStart();
					$staffinformation->exchangeArray($request->getPost());
					
					if($this->getStaffInformationTable()->saveStaffInformation($staffinformation)) {
						$this->getStaffInformationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Staff Information [ ".$staffinformation->name." ] edit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('staffinformation');	
					} else {
						$this->getStaffInformationTable()->transectionInterrupted();
						throw new \Exception("Staff Information couldn't edit properly!");
					}
				}
			}
			//print_r( $staffinformation); exit();
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
				return $this->redirect()->toRoute('staffinformation');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getStaffInformationTable()->deleteStaffInformation($id);
				}
				
				return $this->redirect()->toRoute('staffinformation');
			}
			
			return array(
				'id'	=> $id,
				'staffinformation'	=> $this->getStaffInformationTable()->getStaffInformation($id),
			);
		}
		
		public function getStaffInformationTable() {
			if(!$this->staffinformationTable) {
				$sm = $this->getServiceLocator();
				$this->staffinformationTable = $sm->get('GlobalSetting\Model\StaffInformationTable');
			}
			return $this->staffinformationTable;
		}
		
	}
?>