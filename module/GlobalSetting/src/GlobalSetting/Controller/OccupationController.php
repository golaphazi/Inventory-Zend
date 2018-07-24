<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use GlobalSetting\Model\Occupation;
	use GlobalSetting\Form\OccupationForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;

	class OccupationController extends AbstractActionController {
		protected $occupationTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getOccupationTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'occupations' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new OccupationForm();
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				
				$occupation = new Occupation();
				$form->setInputFilter($occupation->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getOccupationTable()->transectionStart();
					$occupation->exchangeArray($request->getPost());
					if($this->getOccupationTable()->saveOccupation($occupation)) {
						$this->getOccupationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Occupation [ ".$occupation->OCCUPATION." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('occupation');	
					} else {
						$this->getOccupationTable()->transectionInterrupted();
						throw new \Exception("Occupation couldn't save properly!");
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
				return $this->redirect()->toRoute('occupation',array('action' => 'add'));
			}
			
			try {
				$occupation = $this->getOccupationTable()->getOccupation($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('occupation', array('action' => 'index'));
			}
			
			$form = new OccupationForm();
			//$form->setBindOnValidate(false);
			$form->bind($occupation);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getOccupationTable()->transectionStart();
					$occupation->exchangeArray($request->getPost());
					if($this->getOccupationTable()->saveOccupation($occupation)) {
						$this->getOccupationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Occupation [ ".$occupation->OCCUPATION." ] edit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('occupation');	
					} else {
						$this->getOccupationTable()->transectionInterrupted();
						throw new \Exception("Occupation couldn't edit properly!");
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
				return $this->redirect()->toRoute('occupation');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getOccupationTable()->deleteOccupation($id);
				}
				
				return $this->redirect()->toRoute('occupation');
			}
			
			return array(
				'id'	=> $id,
				'occupation'	=> $this->getOccupationTable()->getOccupation($id),
			);
		}
		
		public function getOccupationTable() {
			if(!$this->occupationTable) {
				$sm = $this->getServiceLocator();
				$this->occupationTable = $sm->get('GlobalSetting\Model\OccupationTable');
			}
			return $this->occupationTable;
		}
	}
?>