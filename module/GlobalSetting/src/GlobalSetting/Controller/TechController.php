<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use GlobalSetting\Model\Tech;
	use GlobalSetting\Form\TechForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class TechController extends AbstractActionController {
		protected $TechTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getTechTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'Techs' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new TechForm();
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				
				$Tech = new Tech();
				$form->setInputFilter($Tech->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getTechTable()->transectionStart();
					$Tech->exchangeArray($request->getPost());
					if($this->getTechTable()->saveTech($Tech)) {
						$this->getTechTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Tech [ ".$Tech->Tech." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('Tech');		
					} else {
						$this->getTechTable()->transectionInterrupted();
						throw new \Exception("Tech couldn't save properly!");		
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
				return $this->redirect()->toRoute('Tech',array('action' => 'add'));
			}
			
			try {
				$Tech = $this->getTechTable()->getTech($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('Tech', array('action' => 'index'));
			}
			
			$form = new TechForm();
			$form->bind($Tech);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getTechTable()->transectionStart();
					$Tech->exchangeArray($request->getPost());
					if($this->getTechTable()->saveTech($Tech)) {
						$this->getTechTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Tech [ ".$Tech->Tech." ] edit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('Tech');		
					} else {
						$this->getTechTable()->transectionInterrupted();
						throw new \Exception("Tech couldn't edit properly!");		
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
				return $this->redirect()->toRoute('Tech');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getTechTable()->deleteTech($id);
				}
				
				return $this->redirect()->toRoute('Tech');
			}
			
			return array(
				'id'	=> $id,
				'Tech'	=> $this->getTechTable()->getTech($id),
			);
		}
		
		public function getTechTable() {
			if(!$this->TechTable) {
				$sm = $this->getServiceLocator();
				$this->TechTable = $sm->get('GlobalSetting\Model\TechTable');
			}
			return $this->TechTable;
		}
	}
?>