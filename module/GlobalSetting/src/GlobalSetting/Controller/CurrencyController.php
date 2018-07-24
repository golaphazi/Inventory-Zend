<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use GlobalSetting\Model\Currency;
	use GlobalSetting\Form\CurrencyForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class CurrencyController extends AbstractActionController {
		protected $currencyTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getCurrencyTable()->fetchAll(true);
			//echo "<pre>"; print_r($paginator); die();
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'currencys' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new CurrencyForm();
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				
				$currency = new Currency();
				$form->setInputFilter($currency->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$this->getCurrencyTable()->transectionStart();
					$currency->exchangeArray($request->getPost());
					if($this->getCurrencyTable()->saveCurrency($currency)) {
						$this->getCurrencyTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Currency [ ".$currency->CURRENCY_NAME." ] added successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('currency');		
					} else {
						$this->getCurrencyTable()->transectionInterrupted();
						throw new \Exception("Currency couldn't save properly!");		
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
				return $this->redirect()->toRoute('currency',array('action' => 'add'));
			}
			
			try {
				$currency = $this->getCurrencyTable()->getCurrency($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('currency', array('action' => 'index'));
			}
			
			$form = new CurrencyForm();
			$form->bind($currency);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getCurrencyTable()->transectionStart();
					$currency->exchangeArray($request->getPost());
					if($this->getCurrencyTable()->saveCurrency($currency)) {
						$this->getCurrencyTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Currency [ ".$currency->CURRENCY_NAME." ] edit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('currency');		
					} else {
						$this->getCurrencyTable()->transectionInterrupted();
						throw new \Exception("Currency couldn't edit properly!");		
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
				return $this->redirect()->toRoute('currency');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getCurrencyTable()->deleteCurrency($id);
				}
				
				return $this->redirect()->toRoute('currency');
			}
			
			return array(
				'id'	=> $id,
				'currency'	=> $this->getCurrencyTable()->getCurrency($id),
			);
		}
		
		public function getCurrencyTable() {
			if(!$this->currencyTable) {
				$sm = $this->getServiceLocator();
				$this->currencyTable = $sm->get('GlobalSetting\Model\CurrencyTable');
			}
			return $this->currencyTable;
		}
	}
?>