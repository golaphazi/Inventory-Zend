<?php
	namespace LocalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use LocalSetting\Model\SrZoneMap;
	use LocalSetting\Form\SrZoneMapForm;
	
	use GlobalSetting\Model\Coa;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class SrZoneMapController extends AbstractActionController {
		protected $srZoneMapTable;
		protected $CoaTable;
		protected $designationTable;
		protected $zoneInformationTable;
		
		public function indexAction() {
			
			
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getSrZoneMapTable()->fetchAll(true);			
			//$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));			
			//$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'srZoneMaps' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new SrZoneMapForm('srZoneMap', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$srZoneMap = new SrZoneMap();
				$form->setInputFilter($srZoneMap->getInputFilter());
				$form->setData($request->getPost());
				$postedData = $request->getPost();
				//if($form->isValid()) {
					$srZoneMap->exchangeArray($request->getPost());
					$this->getSrZoneMapTable()->transectionStart();
					for($i=0;$i<sizeof($postedData["ZONE_ID"]);$i++) {						
						$zoneID									= $postedData["ZONE_ID"][$i];
						$data['ZONE_ID'] 						= $postedData["ZONE_ID"][$i];
						$data['DESIGNATION_ID'] 				= $postedData['DESIGNATION_ID'];
						$data['EMPLOYEE_ID'] 					= $postedData['EMPLOYEE_ID'];
						$srZoneMap->exchangeArray($data);
						//echo "<pre>"; print_r($srZoneMap); die();
						if($msg = $this->getSrZoneMapTable()->saveSrZoneMap($srZoneMap)) {
							//echo "<pre>"; print_r($msg); die();
							//echo $msg;die();
							if($msg['SR_ZONE_MAP_ID'] == 0){
								$status = 1;
							} else {
								$status = 0;
							}
						} else {
							$status = 0;
						}
					}	
					if($status) {
						$this->getSrZoneMapTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR and Zone Map [ ".$srZoneMap->EMPLOYEE_ID." ] added successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('srzonemap');
					} else {
						$this->getSrZoneMapTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR and Zone Map [ ".$srZoneMap->EMPLOYEE_ID." ] couldn't save properly!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('srzonemap');
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
				return $this->redirect()->toRoute('srzonemap',array('action' => 'add'));
			}
			
			try {
				$srZoneMap = $this->getSrZoneMapTable()->getSrZoneMap($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('srzonemap', array('action' => 'index'));
			}
			
			$form = new SrZoneMapForm('srZoneMap', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($srZoneMap);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getSrZoneMapTable()->transectionStart();
					$srZoneMap->exchangeArray($request->getPost());
					if($this->getSrZoneMapTable()->saveSrZoneMap($srZoneMap)) {
						$this->getSrZoneMapTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR and Zone Map [ ".$srZoneMap->EMPLOYEE_ID." ] edit successfully!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('srzonemap');
					} else {
						$this->getSrZoneMapTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR and Zone Map [ ".$srZoneMap->EMPLOYEE_ID." ] couldn't edit properly!!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('srzonemap');	
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
				return $this->redirect()->toRoute('srzonemap');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getSrZoneMapTable()->deleteSrZoneMap($id);
				}
				
				return $this->redirect()->toRoute('srzonemap');
			}
			
			return array(
				'id'	=> $id,
				'srZoneMap'	=> $this->getSrZoneMapTable()->getSrZoneMap($id),
			);
		}
		
		public function getEmpNameAction() {
			
			$designationId = $_REQUEST['designationId'];
			$cbCOAList_array = array();
			
			
			$IPAData = $this->getDesignationTable()->getEmpNameInfo($designationId);
			foreach ($IPAData as $selectOption) {
				$suppId = $selectOption['EMPLOYEE_ID'];
				$suppName = $selectOption['EMPLOYEE_NAME'];
				$cbCOAList_array[] = array('optionValue'=>$suppId,'optionDisplay'=>$suppName);
			}
			echo json_encode($cbCOAList_array);
			//echo '<pre>';print_r($cbCOAList_array);die();
			exit;
		}
		public function getZoneListAction() {
			$id =  $this->params()->fromQuery('id', 0);			
			if($id == 0) {
				throw new \Exception("Invalid id");
			} else {
				$templateChargeList = $this->getZoneInformationTable()->getZoneListForSRMap();
				$data = array();
				if($templateChargeList) {
					foreach($templateChargeList as $row) {
						$data[] = array(
										'ZONE_ID' 	=> $row['ZONE_ID'],
										'NAME' 			=> $row['NAME'],
										'SHORT_NAME' 	=> $row['SHORT_NAME'],
										'ADDRESS'		=> $row['ADDRESS'],
										'BRANCHNAME'	=> $row['BRANCHNAME'],
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
		
		public function getSrZoneMapTable() {
			
			if(!$this->srZoneMapTable) {
				$sm = $this->getServiceLocator();
				$this->srZoneMapTable = $sm->get('LocalSetting\Model\SrZoneMapTable');
			}
			return $this->srZoneMapTable;
		}
		public function getDesignationTable() {
			
			if(!$this->designationTable) {
				$sm = $this->getServiceLocator();
				$this->designationTable = $sm->get('GlobalSetting\Model\DesignationTable');
			}
			return $this->designationTable;
		}
		
		public function getCoaTable() {
			if(!$this->CoaTable) {
				$sm = $this->getServiceLocator();
				$this->CoaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->CoaTable;
		}
		public function getZoneInformationTable() {
			
			if(!$this->zoneInformationTable) {
				$sm = $this->getServiceLocator();
				$this->zoneInformationTable = $sm->get('LocalSetting\Model\ZoneInformationTable');
			}
			return $this->zoneInformationTable;
		}
	}
?>