<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use GlobalSetting\Model\MoneyMarketOrganization;
	use GlobalSetting\Form\MoneyMarketOrganizationForm;
	
	use GlobalSetting\Model\PortfolioCoa;
	use GlobalSetting\Model\Coa;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class MoneyMarketOrganizationController extends AbstractActionController {
		protected $moneyMarketOrganizationTable;
		protected $portfolioCoaTable;
		protected $generalCoaTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getMoneyMarketOrganizationTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'moneyMarketOrganizations' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$form		= new MoneyMarketOrganizationForm('moneyMarketOrganization', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');			
			$request	= $this->getRequest();			
			if($request->isPost()) {
				$moneyMarketOrganization	= new MoneyMarketOrganization();
				$form->setInputFilter($moneyMarketOrganization->getInputFilter());
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getMoneyMarketOrganizationTable()->transectionStart();
					$moneyMarketOrganization->exchangeArray($request->getPost());
					if($returnData	= $this->getMoneyMarketOrganizationTable()->saveMoneyMarketOrganization($moneyMarketOrganization)) {
						$success		= false;
						
						//General COA CODE Entry Start By Akhand
						$generalCoa	= new Coa();
						foreach($returnData['GCOA_DATA']['COA_CODE'] as $index=>$CoaCode) {
							$generalCoaData = array(
															'COMPANY_ID' 		=> $returnData['GCOA_DATA']['COMPANY_ID'][$index],
															'PARENT_COA'		=> $returnData['GCOA_DATA']['PARENT_COA'][$index],
															'COA_NAME' 			=> $returnData['GCOA_DATA']['COA_NAME'][$index],
															'COA_CODE' 			=> $CoaCode,
															'AUTO_COA' 			=> $returnData['GCOA_DATA']['AUTO_COA'][$index],
															'CASH_FLOW_HEAD' 	=> $returnData['GCOA_DATA']['CASH_FLOW_HEAD'][$index],
														);
							//echo "<pre>"; print_r($generalCoaData);die();
							$generalCoa->exchangeArray($generalCoaData);
							//echo "<pre>"; print_r($generalCoa);die();
							$status	= $this->getGeneralCoaTable()->saveCoa($generalCoa);
							if(!$status) {
								break;
							}
						}
						//General COA CODE Entry End By Akhand
						
					} else {
						$status	= 0;
					}
					//echo $status;die();
					if($status) {
						$this->getMoneyMarketOrganizationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Organization [ ".$moneyMarketOrganization->ORG_NAME." ] added successfully!</h4></td>
																</tr>
															</table>");
						
						return $this->redirect()->toRoute('moneymarketorganization');
					} else {
						$this->getMoneyMarketOrganizationTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Money market organization couldn't save properly!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('moneymarketorganization');
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
				return $this->redirect()->toRoute('moneymarketorganization',array('action' => 'add'));
			}
			try {
				$moneyMarketOrganization = $this->getMoneyMarketOrganizationTable()->getMoneyMarketOrganization($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('moneymarketorganization', array('action' => 'index'));
			}
			
			$form = new MoneyMarketOrganizationForm('moneyMarketOrganization', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($moneyMarketOrganization);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getMoneyMarketOrganizationTable()->transectionStart();
					$moneyMarketOrganization->exchangeArray($request->getPost());
					
					if($this->getMoneyMarketOrganizationTable()->saveMoneyMarketOrganization($moneyMarketOrganization)) {
						$this->getMoneyMarketOrganizationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Organization [ ".$moneyMarketOrganization->ORG_NAME." ] edit successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('moneymarketorganization');	
					} else {
						$this->getMoneyMarketOrganizationTable()->transectionInterrupted();
						throw new \getMoneyMarketOrganizationTable("Money market organization couldn't edit properly!");	
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
				return $this->redirect()->toRoute('moneymarketorganization');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getMoneyMarketOrganizationTable()->deleteMoneyMarketOrganization($id);
				}
				
				return $this->redirect()->toRoute('moneymarketorganization');
			}
			
			return array(
				'id'	=> $id,
				'moneyMarketOrganization'	=> $this->getMoneyMarketOrganizationTable()->getMoneyMarketOrganization($id),
			);
		}
		
		public function getMoneyMarketOrganizationTable() {
			if(!$this->moneyMarketOrganizationTable) {
				$sm = $this->getServiceLocator();
				$this->moneyMarketOrganizationTable = $sm->get('GlobalSetting\Model\MoneyMarketOrganizationTable');
			}
			return $this->moneyMarketOrganizationTable;
		}
		
		public function getPortfolioCoaTable() {
			if(!$this->portfolioCoaTable) {
				$sm = $this->getServiceLocator();
				$this->portfolioCoaTable = $sm->get('GlobalSetting\Model\PortfolioCoaTable');
			}
			return $this->portfolioCoaTable;
		}
		
		public function getGeneralCoaTable() {
			if(!$this->generalCoaTable) {
				$sm = $this->getServiceLocator();
				$this->generalCoaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->generalCoaTable;
		}
	}
?>