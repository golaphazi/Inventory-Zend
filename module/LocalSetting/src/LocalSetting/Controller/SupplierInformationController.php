<?php
	namespace LocalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use LocalSetting\Model\SupplierInformation;
	use LocalSetting\Form\SupplierInformationForm;
	
	use GlobalSetting\Model\Coa;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class SupplierInformationController extends AbstractActionController {
		protected $supplierInformationTable;
		protected $CoaTable;
		
		public function indexAction() {
			
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getSupplierInformationTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'supplierInformations' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new SupplierInformationForm('supplierInformation', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$supplierInformation = new SupplierInformation();
				$form->setInputFilter($supplierInformation->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$supplierInformation->exchangeArray($request->getPost());
					$this->getSupplierInformationTable()->transectionStart();
					
					if($returnData = $this->getSupplierInformationTable()->saveSupplierInformation($supplierInformation)) {
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
									$this->getSupplierInformationTable()->updateSupplierInformation('p',$CoaCode, $returnData['SUPPLIER_INFO_ID']);
								}elseif($substrCode == '302'){
									$this->getSupplierInformationTable()->updateSupplierInformation('r',$CoaCode, $returnData['SUPPLIER_INFO_ID']);
								}
								
								 
								
							}
						}
						// General Account Details Chart of Account Entry End						
						
					} else {
						$status = 0;
					}					
					if($status) {
						$this->getSupplierInformationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Supplier Information [ ".$supplierInformation->NAME." ] added successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('supplierinformation');
					} else {
						$this->getSupplierInformationTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Supplier Information [ ".$supplierInformation->NAME." ] couldn't save properly!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('supplierinformation');
					}
					
					
				}
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
				return $this->redirect()->toRoute('supplierinformation',array('action' => 'add'));
			}
			
			try {
				$supplierInformation = $this->getSupplierInformationTable()->getSupplierInformation($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('supplierinformation', array('action' => 'index'));
			}
			
			$form = new SupplierInformationForm('supplierInformation', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($supplierInformation);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getSupplierInformationTable()->transectionStart();
					$supplierInformation->exchangeArray($request->getPost());
					if($this->getSupplierInformationTable()->saveSupplierInformation($supplierInformation)) {
						$this->getSupplierInformationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Supplier Information[ ".$supplierInformation->NAME." ] edit successfully!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('supplierinformation');
					} else {
						$this->getSupplierInformationTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Supplier Information[ ".$supplierInformation->NAME." ] couldn't edit properly!!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('supplierinformation');	
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
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$id = (int) $this->params()->fromRoute('id',0);
			
			if(!$id) {
				return $this->redirect()->toRoute('supplierinformation');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getSupplierInformationTable()->deleteSupplierInformation($id);
				}
				
				return $this->redirect()->toRoute('supplierinformation');
			}
			
			return array(
				'id'	=> $id,
				'supplierInformation'	=> $this->getSupplierInformationTable()->getSupplierInformation($id),
			);
		}
		
		public function getSupplierInformationTable() {
			
			if(!$this->supplierInformationTable) {
				$sm = $this->getServiceLocator();
				$this->supplierInformationTable = $sm->get('LocalSetting\Model\SupplierInformationTable');
			}
			return $this->supplierInformationTable;
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