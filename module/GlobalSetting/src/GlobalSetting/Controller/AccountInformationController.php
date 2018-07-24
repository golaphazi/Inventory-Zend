<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use GlobalSetting\Model\AccountDetails;
	use GlobalSetting\Form\AccountDetailsForm;
	
	use GlobalSetting\Model\OrganizationBranch;
	
	use GlobalSetting\Model\Coa;
	use GlobalSetting\Model\PortfolioCoa;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class AccountInformationController extends AbstractActionController {
		protected $accountDetailsTable;
		protected $CoaTable;
		protected $portfolioCoaTable;
		protected $orgBranchTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getAccountDetailsTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'accountDetailss' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$request	= $this->getRequest();
			$form 		= new AccountDetailsForm('accountDetails', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array());
			$form->get('submit')->setValue('Add');
			
			if($request->isPost()) {
				$accountDetails = new AccountDetails();
				$form->setInputFilter($accountDetails->getInputFilter());
				$form->setData($request->getPost());
				
				$this->getAccountDetailsTable()->transectionStart();
				$accountDetails->exchangeArray($request->getPost());
				//echo '<pre>';print_r($accountDetails);die();
				if($returnData = $this->getAccountDetailsTable()->saveAccountDetails($accountDetails)) {
					// General Account Details Chart of Account Entry Start
					$Coa = new Coa();
					$success = false;
					foreach($returnData['COA_DATA']['COA_CODE'] as $index=>$CoaCode) {
						$accountDetailsCoaData = array(
														'COMPANY_ID' 	=> $returnData['COA_DATA']['COMPANY_ID'][$index],
														'PARENT_COA'	=> $returnData['COA_DATA']['PARENT_COA'][$index],
														'COA_NAME' 		=> $returnData['COA_DATA']['COA_NAME'][$index],
														'COA_CODE' 		=> $CoaCode,
														'AUTO_COA' 		=> $returnData['COA_DATA']['AUTO_COA'][$index],
														'CASH_FLOW_HEAD' => $returnData['COA_DATA']['CASH_FLOW_HEAD'][$index],
													);
						
						$Coa->exchangeArray($accountDetailsCoaData);
						$status = $this->getCoaTable()->saveCoa($Coa);
						if(!$status) {
							break;
						}
					}
					// General Account Details Chart of Account Entry End
				} else {
					$status = 0;
				}
				
				if($status) {
					$this->getAccountDetailsTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Accounts information [ ".$accountDetails->ACCOUNT_NAME." ] added successfully!</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('accountinformation');
				} else {
					$this->getAccountDetailsTable()->transectionInterrupted();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Account information couldn't save properly!</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('accountinformation');
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
				return $this->redirect()->toRoute('accountinformation',array('action' => 'add'));
			}
			try {
				$accountInformation = $this->getAccountDetailsTable()->getAccountDetails($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('accountinformation', array('action' => 'index'));
			}
			
			$request	= $this->getRequest();
			$form 		= new AccountDetailsForm('accountDetails', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array());
			$form->bind($accountInformation);
			$form->get('submit')->setAttribute('value','Edit');
			
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getAccountDetailsTable()->transectionStart();
					$accountInformation->exchangeArray($request->getPost());
					if($this->getAccountDetailsTable()->saveAccountDetails($accountInformation)) {
						$this->getAccountDetailsTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Accounts information [ ".$accountInformation->ACCOUNT_NAME." ] edit successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('accountinformation');	
					} else {
						$this->getAccountDetailsTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Account information couldn't edit properly!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('accountinformation');
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
				return $this->redirect()->toRoute('accountinformation');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getAccountDetailsTable()->deleteAccountDetails($id);
				}
				
				return $this->redirect()->toRoute('accountinformation');
			}
			
			return array(
				'id'	=> $id,
				'accountDetails'	=> $this->getAccountDetailsTable()->getAccountDetails($id),
			);
		}
		
		public function getAccountDetailsTable() {
			if(!$this->accountDetailsTable) {
				$sm = $this->getServiceLocator();
				$this->accountDetailsTable = $sm->get('GlobalSetting\Model\AccountDetailsTable');
			}
			return $this->accountDetailsTable;
		}
		
		public function getMoneyMarketOrganizationListAction() {
			$id =  $this->params()->fromQuery('id', 0);
			
			if($id == 0) {
				throw new \Exception("Invalid id");
			} else {
				$organizationList = $this->getOrganizationBranchTable()->getOrgWiseBranchList($id);
				$data = array();
				if($organizationList) {
					foreach($organizationList as $row) {
						$data[] = array(
										'ORG_BRANCH_ID' => $row->ORG_BRANCH_ID,
										'BRANCH_NAME' => $row->BRANCH_NAME
									);
					}
				}
				echo json_encode($data);
				exit;
			}
		}
		
		public function getOrganizationBranchTable() {
			if(!$this->orgBranchTable) {
				$sm = $this->getServiceLocator();
				$this->orgBranchTable = $sm->get('GlobalSetting\Model\OrganizationBranchTable');
			}
			return $this->orgBranchTable;
		}
		
		public function getCoaTable() {
			if(!$this->CoaTable) {
				$sm = $this->getServiceLocator();
				$this->CoaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->CoaTable;
		}
	}
?>