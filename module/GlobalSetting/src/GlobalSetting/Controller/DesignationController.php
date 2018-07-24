<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use GlobalSetting\Model\Designation;
	use GlobalSetting\Form\DesignationForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class DesignationController extends AbstractActionController {
		protected $designationTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getDesignationTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'designations' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new DesignationForm('designation', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$designation = new Designation();
				$form->setInputFilter($designation->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getDesignationTable()->transectionStart();
					$designation->exchangeArray($request->getPost());
					if($this->getDesignationTable()->saveDesignation($designation)) {
						$this->getDesignationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Designation [ ".$designation->DESIGNATION." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('designation');	
					} else {
						$this->getDesignationTable()->transectionInterrupted();
						throw new \Exception("Designation couldn't save properly!");	
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
				return $this->redirect()->toRoute('designation',array('action' => 'add'));
			}
			
			try {
				$designation = $this->getDesignationTable()->getDesignation($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('designation', array('action' => 'index'));
			}
			
			$form = new DesignationForm('designation', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($designation);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getDesignationTable()->transectionStart();
					$designation->exchangeArray($request->getPost());
					if($this->getDesignationTable()->saveDesignation($designation)) {
						$this->getDesignationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Designation [ ".$designation->DESIGNATION." ] edit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('designation');	
					} else {
						$this->getDesignationTable()->transectionInterrupted();
						throw new \Exception("Designation couldn't edit properly!");	
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
				return $this->redirect()->toRoute('designation');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getDesignationTable()->deleteDesignation($id);
				}
				
				return $this->redirect()->toRoute('designation');
			}
			
			return array(
				'id'	=> $id,
				'designation'	=> $this->getDesignationTable()->getDesignation($id),
			);
		}
		
		public function getDesignationTable() {
			if(!$this->designationTable) {
				$sm = $this->getServiceLocator();
				$this->designationTable = $sm->get('GlobalSetting\Model\DesignationTable');
			}
			return $this->designationTable;
		}
	}
?>