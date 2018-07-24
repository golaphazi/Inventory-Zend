<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use GlobalSetting\Model\Country;
	use GlobalSetting\Form\CountryForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class CountryController extends AbstractActionController {
		protected $countryTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getCountryTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'countrys' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new CountryForm();
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				
				$country = new Country();
				$form->setInputFilter($country->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getCountryTable()->transectionStart();
					$country->exchangeArray($request->getPost());
					if($this->getCountryTable()->saveCountry($country)) {
						$this->getCountryTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Country [ ".$country->COUNTRY." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('country');		
					} else {
						$this->getCountryTable()->transectionInterrupted();
						throw new \Exception("Country couldn't save properly!");		
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
				return $this->redirect()->toRoute('country',array('action' => 'add'));
			}
			
			try {
				$country = $this->getCountryTable()->getCountry($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('country', array('action' => 'index'));
			}
			
			$form = new CountryForm();
			$form->bind($country);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getCountryTable()->transectionStart();
					$country->exchangeArray($request->getPost());
					if($this->getCountryTable()->saveCountry($country)) {
						$this->getCountryTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Country [ ".$country->COUNTRY." ] edit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('country');		
					} else {
						$this->getCountryTable()->transectionInterrupted();
						throw new \Exception("Country couldn't edit properly!");		
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
				return $this->redirect()->toRoute('country');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getCountryTable()->deleteCountry($id);
				}
				
				return $this->redirect()->toRoute('country');
			}
			
			return array(
				'id'	=> $id,
				'country'	=> $this->getCountryTable()->getCountry($id),
			);
		}
		
		public function getCountryTable() {
			if(!$this->countryTable) {
				$sm = $this->getServiceLocator();
				$this->countryTable = $sm->get('GlobalSetting\Model\CountryTable');
			}
			return $this->countryTable;
		}
	}
?>