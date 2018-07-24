<?php
	namespace LocalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use LocalSetting\Model\ZoneInformation;
	use LocalSetting\Form\ZoneInformationForm;
	
	use GlobalSetting\Model\Coa;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class ZoneInformationController extends AbstractActionController {
		protected $zoneInformationTable;
		protected $CoaTable;
		
		public function indexAction() {
			
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getZoneInformationTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'zoneInformations' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new ZoneInformationForm('zoneInformation', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$zoneInformation = new ZoneInformation();
				$form->setInputFilter($zoneInformation->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$zoneInformation->exchangeArray($request->getPost());
					$this->getZoneInformationTable()->transectionStart();
					
					if($returnData = $this->getZoneInformationTable()->saveZoneInformation($zoneInformation)) {
						$status = 1;
						// General Account Details Chart of Account Entry Start
						/*$Coa = new Coa();
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
								if($substrCode = '201'){
									$this->getZoneInformationTable()->updateZoneInformation('p',$CoaCode, $returnData['SUPPLIER_INFO_ID']);
								}elseif($substrCode = '301'){
									$this->getZoneInformationTable()->updateZoneInformation('r',$CoaCode, $returnData['SUPPLIER_INFO_ID']);
								}
								
								 
								
							}
						} */
						// General Account Details Chart of Account Entry End						
						
					} else {
						$status = 0;
					}					
					if($status) {
						$this->getZoneInformationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Zone Information [ ".$zoneInformation->NAME." ] added successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('zoneinformation');
					} else {
						$this->getZoneInformationTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Zone Information [ ".$zoneInformation->NAME." ] couldn't save properly!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('zoneinformation');
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
				return $this->redirect()->toRoute('zoneinformation',array('action' => 'add'));
			}
			
			try {
				$zoneInformation = $this->getZoneInformationTable()->getZoneInformation($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('zoneinformation', array('action' => 'index'));
			}
			
			$form = new ZoneInformationForm('zoneInformation', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($zoneInformation);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getZoneInformationTable()->transectionStart();
					$zoneInformation->exchangeArray($request->getPost());
					if($this->getZoneInformationTable()->saveZoneInformation($zoneInformation)) {
						$this->getZoneInformationTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Zone Information[ ".$zoneInformation->NAME." ] edit successfully!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('zoneinformation');
					} else {
						$this->getZoneInformationTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Zone Information[ ".$zoneInformation->NAME." ] couldn't edit properly!!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('zoneinformation');	
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
				return $this->redirect()->toRoute('zoneinformation');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getZoneInformationTable()->deleteZoneInformation($id);
				}
				
				return $this->redirect()->toRoute('zoneinformation');
			}
			
			return array(
				'id'	=> $id,
				'zoneInformation'	=> $this->getZoneInformationTable()->getZoneInformation($id),
			);
		}
		
		public function getZoneInformationTable() {
			
			if(!$this->zoneInformationTable) {
				$sm = $this->getServiceLocator();
				$this->zoneInformationTable = $sm->get('LocalSetting\Model\ZoneInformationTable');
			}
			return $this->zoneInformationTable;
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