<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use GlobalSetting\Model\Nationality;
	use GlobalSetting\Form\NationalityForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class NationalityController extends AbstractActionController {
		protected $nationalityTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getNationalityTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'nationalitys' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new NationalityForm();
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$nationality = new Nationality();
				$form->setInputFilter($nationality->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getNationalityTable()->transectionStart();
					$nationality->exchangeArray($request->getPost());
					if($this->getNationalityTable()->saveNationality($nationality)) {
						$this->getNationalityTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Nationality [ ".$nationality->NATIONALITY." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('nationality');		
					} else {
						$this->getNationalityTable()->transectionInterrupted();
						throw new \Exception("Nationality couldn't save properly!");		
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
				return $this->redirect()->toRoute('nationality',array('action' => 'add'));
			}
			
			try {
				$nationality = $this->getNationalityTable()->getNationality($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('nationality', array('action' => 'index'));
			}
			
			$form = new NationalityForm();
			$form->bind($nationality);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getNationalityTable()->transectionStart();
					$nationality->exchangeArray($request->getPost());
					if($this->getNationalityTable()->saveNationality($nationality)) {
						$this->getNationalityTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Nationality [ ".$nationality->NATIONALITY." ] edit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('nationality');		
					} else {
						$this->getNationalityTable()->transectionInterrupted();
						throw new \Exception("Nationality couldn't edit properly!");		
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
				return $this->redirect()->toRoute('nationality');
			}			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getNationalityTable()->deleteNationality($id);
				}
				
				return $this->redirect()->toRoute('nationality');
			}
			
			return array(
				'id'	=> $id,
				'nationality'	=> $this->getNationalityTable()->getNationality($id),
			);
		}
		
		public function getNationalityTable() {
			if(!$this->nationalityTable) {
				$sm = $this->getServiceLocator();
				$this->nationalityTable = $sm->get('GlobalSetting\Model\NationalityTable');
			}
			return $this->nationalityTable;
		}
	}
?>