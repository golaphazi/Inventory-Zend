<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
		
	use GlobalSetting\Model\City;
	use GlobalSetting\Form\CityForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;

	class CityController extends AbstractActionController {
		protected $cityTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getCityTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'cityes' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new CityForm('city', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			$request = $this->getRequest();
			if($request->isPost()) {
				$city = new City();
				$form->setInputFilter($city->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getCityTable()->transectionStart();
					$city->exchangeArray($request->getPost());
					if($this->getCityTable()->saveCity($city)) {
						$this->getCityTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>City [ ".$city->CITY." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('city');		
					} else {
						$this->getCityTable()->transectionInterrupted();
						throw new \Exception("City couldn't save properly!");		
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
				return $this->redirect()->toRoute('city',array('action' => 'add'));
			}
			
			try {
				$city = $this->getCityTable()->getCity($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('city', array('action' => 'index'));
			}
			
			$form = new CityForm('city', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($city);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getCityTable()->transectionStart();
					$city->exchangeArray($request->getPost());
					if($this->getCityTable()->saveCity($city)) {
						$this->getCityTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>City [ ".$city->CITY." ] edit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('city');		
					} else {
						$this->getCityTable()->transectionInterrupted();
						throw new \Exception("City couldn't edit properly!");		
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
				return $this->redirect()->toRoute('city');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getCityTable()->deleteCity($id);
				}
				
				return $this->redirect()->toRoute('city');
			}
			
			return array(
				'id'	=> $id,
				'city'	=> $this->getCityTable()->getCity($id),
			);
		}
	
		public function getCityTable() {
			if(!$this->cityTable) {
				$sm = $this->getServiceLocator();
				$this->cityTable = $sm->get('GlobalSetting\Model\CityTable');
			}
			return $this->cityTable;
		}
	}
?>	
