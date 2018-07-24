<?php
	namespace LocalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use LocalSetting\Model\RetailerInformation;
	use LocalSetting\Form\RetailerInformationForm;
	
	use GlobalSetting\Model\Coa;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class RetailerInformationController extends AbstractActionController {
		protected $retailerInformationTable;
		protected $CoaTable;
		protected $employeePersonalInfoTable;
		
		public function indexAction() {
			
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getRetailerInformationTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'retailerInformations' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new RetailerInformationForm('retailerInformation', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$retailerInformation = new RetailerInformation();
				$form->setInputFilter($retailerInformation->getInputFilter());
				$form->setData($request->getPost());
				
				//if($form->isValid()) {
					$retailerInformation->exchangeArray($request->getPost());
					$this->getRetailerInformationTable()->transectionStart();
					
					if($returnData = $this->getRetailerInformationTable()->saveRetailerInformation($retailerInformation)) {
						$status = 1;
						// General Account Details Chart of Account Entry Start
						$Coa = new Coa();
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
							//echo "<pre>"; print_r($Coa); die();
							$status = $this->getCoaTable()->saveCoa($Coa);
							if($status) {
								$substrCode = substr($CoaCode,0,3);
								if($substrCode == '201'){
									$this->getRetailerInformationTable()->updateRetailerInformation('p',$CoaCode, $returnData['RETAILER_ID']);
								}elseif($substrCode == '302'){
									$this->getRetailerInformationTable()->updateRetailerInformation('r',$CoaCode, $returnData['RETAILER_ID']);
								}
								
								 
								
							}
						} 
						// General Account Details Chart of Account Entry End						
						
					} else {
						$status = 0;
					}					
					if($status) {
						$this->getRetailerInformationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Retailer Information [ ".$retailerInformation->NAME." ] added successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('retailerinformation');
					} else {
						$this->getRetailerInformationTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Retailer Information [ ".$retailerInformation->NAME." ] couldn't save properly!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('retailerinformation');
					}
					
					
				//}
			}
			
			return array('form' => $form);
		}
		
		public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
		    $id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('retailerinformation',array('action' => 'add'));
			}
			try {
				$retailerInformation = $this->getRetailerInformationTable()->getRetailerInformation($id);
				$employeeInformation = $this->getEmployeePersonalInfoTable()->fetchSRInfoRetailerEdit($id);
				//echo "<pre>"; print_r($employeeInformation); die();
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('retailerinformation', array('action' => 'index'));
			}
			
			$form = new RetailerInformationForm('retailerInformation', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($retailerInformation);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				//if($form->isValid()) {
					$this->getRetailerInformationTable()->transectionStart();
					$retailerInformation->exchangeArray($request->getPost());
					//echo "<pre>"; print_r($retailerInformation); die();
					if($this->getRetailerInformationTable()->saveRetailerInformation($retailerInformation)) {
						$this->getRetailerInformationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Retailer Information[ ".$retailerInformation->NAME." ] edit successfully!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('retailerinformation');
					} else {
						$this->getRetailerInformationTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Retailer Information[ ".$retailerInformation->NAME." ] couldn't edit properly!!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('retailerinformation');	
					}
				//}
			}
			
			return array(
				'id' => $id,
				'form' => $form,
				'srInfo' => $employeeInformation,
			);
		}
		
		public function deleteAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$id = (int) $this->params()->fromRoute('id',0);
			
			if(!$id) {
				return $this->redirect()->toRoute('retailerinformation');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getRetailerInformationTable()->deleteRetailerInformation($id);
				}
				
				return $this->redirect()->toRoute('retailerinformation');
			}
			
			return array(
				'id'	=> $id,
				'retailerInformation'	=> $this->getRetailerInformationTable()->getRetailerInformation($id),
			);
		}
		
		public function getRetailerInformationTable() {
			
			if(!$this->retailerInformationTable) {
				$sm = $this->getServiceLocator();
				$this->retailerInformationTable = $sm->get('LocalSetting\Model\RetailerInformationTable');
			}
			return $this->retailerInformationTable;
		}
		public function getEmployeePersonalInfoTable() {			
			if(!$this->employeePersonalInfoTable) {
				$sm = $this->getServiceLocator();
				$this->employeePersonalInfoTable = $sm->get('HumanResource\Model\EmployeePersonalInfoTable');
			}
			return $this->employeePersonalInfoTable;
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