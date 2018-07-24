<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use GlobalSetting\Model\Relation;
	use GlobalSetting\Form\RelationForm;

	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class RelationController extends AbstractActionController {
		protected $relationTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getRelationTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'relations' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new RelationForm('relation', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$relation = new Relation();
				$form->setInputFilter($relation->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getRelationTable()->transectionStart();
					$relation->exchangeArray($request->getPost());
					if($this->getRelationTable()->saveRelation($relation)) {
						$this->getRelationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Relation [ ".$relation->RELATION." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('relation');	
					} else {
						$this->getRelationTable()->transectionInterrupted();
						throw new \Exception("Relation couldn't save properly!");
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
				return $this->redirect()->toRoute('relation',array('action' => 'add'));
			}
			
			try {
				$relation = $this->getRelationTable()->getRelation($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('relation', array('action' => 'index'));
			}
			
			$form = new RelationForm('relation', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($relation);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getRelationTable()->transectionStart();
					$relation->exchangeArray($request->getPost());
					if($this->getRelationTable()->saveRelation($relation)) {
						$this->getRelationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Relation [ ".$relation->RELATION." ] edit successfully!</h4></td>
																</tr>
															</table>");
						
						return $this->redirect()->toRoute('relation');	
					} else {
						$this->getRelationTable()->transectionInterrupted();
						throw new \Exception("Relation couldn't edit properly!");	
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
				return $this->redirect()->toRoute('relation');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getRelationTable()->deleteRelation($id);
				}
				
				return $this->redirect()->toRoute('relation');
			}
			
			return array(
				'id'	=> $id,
				'relation'	=> $this->getRelationTable()->getRelation($id),
			);
		}
		
		public function getRelationTable() {
			if(!$this->relationTable) {
				$sm = $this->getServiceLocator();
				$this->relationTable = $sm->get('GlobalSetting\Model\RelationTable');
			}
			return $this->relationTable;
		}
	}
?>