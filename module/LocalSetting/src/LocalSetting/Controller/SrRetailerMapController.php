<?php
	namespace LocalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use LocalSetting\Model\SrRetailerMap;
	use LocalSetting\Form\SrRetailerMapForm;
	
	use GlobalSetting\Model\Coa;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class SrRetailerMapController extends AbstractActionController {
		protected $srRetailerMapTable;
		protected $CoaTable;
		protected $retailerInformationTable;
		
		public function indexAction() {
			
			
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getSrRetailerMapTable()->fetchAll(true);			
			//$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));			
			//$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'srRetailerMaps' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new SrRetailerMapForm('srRetailerMap', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$srRetailerMap = new SrRetailerMap();
				$form->setInputFilter($srRetailerMap->getInputFilter());
				$form->setData($request->getPost());
				$postedData = $request->getPost();
				//if($form->isValid()) {
					$srRetailerMap->exchangeArray($request->getPost());
					$this->getSrRetailerMapTable()->transectionStart();
					//echo "<pre>"; print_r($srRetailerMap); die();
					for($i=0;$i<sizeof($postedData["RETAILER_ID"]);$i++) {						
						$retailerID								= $postedData["RETAILER_ID"][$i];
						$data['RETAILER_ID'] 					= $postedData["RETAILER_ID"][$i];
						$data['DESIGNATION_ID'] 				= $postedData['DESIGNATION_ID'];
						$data['EMPLOYEE_ID'] 					= $postedData['EMPLOYEE_ID'];
						$srRetailerMap->exchangeArray($data);
						//echo "<pre>"; print_r($srRetailerMap); die();
						if($msg = $this->getSrRetailerMapTable()->saveSrRetailerMap($srRetailerMap)) {
							//echo "<pre>"; print_r($msg); die();
							//echo $msg;die();
							if($msg['SR_RETAILER_MAP_ID'] == 0){
								$status = 1;
							} else {
								$status = 0;
							}
						} else {
							$status = 0;
						}
					}
					
					//if($returnData = $this->getSrRetailerMapTable()->saveSrRetailerMap($srRetailerMap)) {
						//$status = 1;
					//} else {
						//$status = 0;
					//}					
					if($status) {
						$this->getSrRetailerMapTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR and Retailer Map [ ".$srRetailerMap->EMPLOYEE_ID." ] added successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('srretailermap');
					} else {
						$this->getSrRetailerMapTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR and Retailer Map [ ".$srRetailerMap->EMPLOYEE_ID." ] couldn't save properly!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('srretailermap');
					}
					
					
				//}
			}
			
			return array('form' => $form);
		}
		
		/*public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
		    $id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('srretailermap',array('action' => 'add'));
			}
			
			try {
				$srRetailerMap = $this->getSrRetailerMapTable()->getSrRetailerMap($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('srretailermap', array('action' => 'index'));
			}
			
			$form = new SrRetailerMapForm('srRetailerMap', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($srRetailerMap);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getSrRetailerMapTable()->transectionStart();
					$srRetailerMap->exchangeArray($request->getPost());
					if($this->getSrRetailerMapTable()->saveSrRetailerMap($srRetailerMap)) {
						$this->getSrRetailerMapTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR and Zone Map [ ".$srRetailerMap->EMPLOYEE_ID." ] edit successfully!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('srretailermap');
					} else {
						$this->getSrRetailerMapTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR and Zone Map [ ".$srRetailerMap->EMPLOYEE_ID." ] couldn't edit properly!!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('srretailermap');	
					}
				}
			}
			
			return array(
				'id' => $id,
				'form' => $form,
			);
		}
		*/
		public function editAction(){
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new SrRetailerMapForm('srRetailerMap', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$srRetailerMap = new SrRetailerMap();
				$form->setInputFilter($srRetailerMap->getInputFilter());
				$form->setData($request->getPost());
				$postedData = $request->getPost();
				//if($form->isValid()) {
					$srRetailerMap->exchangeArray($request->getPost());
					$this->getSrRetailerMapTable()->transectionStart();
					//echo "<pre>"; print_r($srRetailerMap); die();
					for($i=0;$i<sizeof($postedData["RETAILER_ID"]);$i++) {						
						$retailerID								= $postedData["RETAILER_ID"][$i];
						$data['RETAILER_ID'] 					= $postedData["RETAILER_ID"][$i];
						$data['DESIGNATION_ID'] 				= $postedData['DESIGNATION_ID'];
						$data['EMPLOYEE_ID'] 					= $postedData['EMPLOYEE_ID'];
						$srRetailerMap->exchangeArray($data);
						//echo "<pre>"; print_r($srRetailerMap); die();
						if($msg = $this->getSrRetailerMapTable()->saveSrRetailerMap($srRetailerMap)) {
							//echo "<pre>"; print_r($msg); die();
							//echo $msg;die();
							if($msg['SR_RETAILER_MAP_ID'] == 0){
								$status = 1;
							} else {
								$status = 0;
							}
						} else {
							$status = 0;
						}
					}
					
					//if($returnData = $this->getSrRetailerMapTable()->saveSrRetailerMap($srRetailerMap)) {
						//$status = 1;
					//} else {
						//$status = 0;
					//}					
					if($status) {
						$this->getSrRetailerMapTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR and Retailer Map [ ".$srRetailerMap->EMPLOYEE_ID." ] added successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('srretailermap');
					} else {
						$this->getSrRetailerMapTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR and Retailer Map [ ".$srRetailerMap->EMPLOYEE_ID." ] couldn't save properly!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('srretailermap');
					}
					
					
				//}
			}
			
			return array('form' => $form);

		}
		public function deleteAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$id = (int) $this->params()->fromRoute('id',0);
			
			if(!$id) {
				return $this->redirect()->toRoute('srretailermap');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getSrRetailerMapTable()->deleteSrRetailerMap($id);
				}
				
				return $this->redirect()->toRoute('srretailermap');
			}
			
			return array(
				'id'	=> $id,
				'srRetailerMap'	=> $this->getSrRetailerMapTable()->getSrRetailerMap($id),
			);
		}
		
		public function getRetailerListAction() {
			$id =  $_GET['id'];			
			if($id == 0) {
				throw new \Exception("Invalid id");
			} else {
			
				$templateChargeList = $this->getRetailerInformationTable()->getRetailerListForMap($id);
				echo $templateChargeList;exit();
			}
		}
		
		/*
		public function getRetailerListAction() {
			$id =  $_GET['id'];			
			if($id == 0) {
				throw new \Exception("Invalid id");
			} else {
			
				$templateChargeList = $this->getRetailerInformationTable()->getRetailerListForMap($id);
				$data = array();
				if($templateChargeList) {
					foreach($templateChargeList as $row) {
						$data[] = array(
										'RETAILER_ID' 	=> $row['RETAILER_ID'],
										'NAME' 			=> $row['NAME'],
										'SHOP_NAME' 	=> $row['SHOP_NAME'],
										'ADDRESS' 		=> $row['ADDRESS'],
										
										//'ZONENAME'		=> $row['ZONENAME'],
									);
					}
				}
				if(empty($data)) {
					throw new \Exception("Invalid id");
				} else {
					echo json_encode($data);exit;
				}
			}
		}
		*/
		public function getRetailerListDataAction(){
			$id = $_GET['id'];
			
			$templateChargeList = $this->getRetailerInformationTable()->getRetailerListForMapCheck($id);
			$data = array();
				if($templateChargeList) {
					foreach($templateChargeList as $row) {
						$data[] = array(
										'RETAILER_ID' 	=> $row['RETAILER_ID'],
										'NAME' 			=> $row['NAME'],
										'SHOP_NAME' 	=> $row['SHOP_NAME'],
										'ADDRESS' 		=> $row['ADDRESS'],
										'SR_RETAILER_MAP_ID' => $row['SR_RETAILER_MAP_ID'],
										'END_DATE' 			=> $row['END_DATE'],
										//'ZONENAME'		=> $row['ZONENAME'],
									);
					}
				}
				if(empty($data)) {
					throw new \Exception("Invalid id");
				} else {
					echo json_encode($data);exit;
				}
		}
		public function getRetailerListDataEditAction(){
			$id = $_GET['id'];
			
			$templateChargeList = $this->getSrRetailerMapTable()->getRetailerListForMapUpdate($id);
			echo json_encode($templateChargeList);exit;
		}
		
		public function getRetailerListDataAddAction(){
			$id = $_GET['id'];
			$reId = $_GET['reId'];
			$srID = $_GET['srID'];
			
			$templateChargeList = $this->getSrRetailerMapTable()->getRetailerListForMapUpdateInsert($id,$reId,$srID);
			echo json_encode($templateChargeList);exit;
		}
		public function getSrRetailerMapTable() {
			
			if(!$this->srRetailerMapTable) {
				$sm = $this->getServiceLocator();
				$this->srRetailerMapTable = $sm->get('LocalSetting\Model\SrRetailerMapTable');
			}
			return $this->srRetailerMapTable;
		}
		public function getCoaTable() {
			if(!$this->CoaTable) {
				$sm = $this->getServiceLocator();
				$this->CoaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->CoaTable;
		}
		public function getRetailerInformationTable() {
			
			if(!$this->retailerInformationTable) {
				$sm = $this->getServiceLocator();
				$this->retailerInformationTable = $sm->get('LocalSetting\Model\RetailerInformationTable');
			}
			return $this->retailerInformationTable;
		}
	}
?>