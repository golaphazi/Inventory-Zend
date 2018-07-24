<?php
	namespace LocalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use LocalSetting\Model\SrTarget;
	use LocalSetting\Form\SrTargetForm;
	
	use LocalSetting\Model\SrTargetBkdn;
	use LocalSetting\Form\SrTargetBkdnForm;
	
	use GlobalSetting\Model\Coa;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class SrTargetController extends AbstractActionController {
		protected $srTargetTable;
		protected $srTargetBkdnTable;
		protected $CoaTable;
		
		public function indexAction() {
			
			
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getSrTargetTable()->fetchAll(true);			
			//$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));			
			//$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'srTargets' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new SrTargetForm('srTarget', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$srTarget = new SrTarget();
				$form->setInputFilter($srTarget->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					//$srTarget->exchangeArray($request->getPost());
					$this->getSrTargetTable()->transectionStart();
					
					$postedData		= $request->getPost();
					//echo "<pre>"; print_r($postedData); die();
					
					if(!empty($postedData['EMPLOYEE_ID'])) {
						
						$srTargetData		= array();					
						$srTargetData 		= array(
							'EMPLOYEE_ID' 		=> $postedData["EMPLOYEE_ID"],
							'START_DATE' 		=> $postedData["START_DATE"],
							'END_DATE' 			=> $postedData["END_DATE"],
							'REMARKS' 			=> $postedData["REMARKS"],
						);
						$srTarget->exchangeArray($srTargetData);
						//echo "<pre>"; print_r($srTarget); die();
						if($returnData = $this->getSrTargetTable()->saveSrTarget($srTarget)) {
							//echo "<pre>"; print_r($returnData); die();
							if($returnData){
									$i = 0;
									foreach($postedData["TARGET_FROM"] as $srTargetBkdnVal){
										//echo "<pre>"; print_r($srTargetBkdnVal[0]); die();
											$srTargetBkdn = new SrTargetBkdn();
											$srTargetBkdnData		= array();					
											$srTargetBkdnData 		= array(
												'SR_TARGET_ID' 		=> $returnData['SR_TARGET_ID'],
												'TARGET_FROM' 		=> $postedData["TARGET_FROM"][$i],
												'TARGET_TO' 		=> $postedData["TARGET_TO"][$i],
												'TARGET_VALUE'		=> $postedData["TARGET_VALUE"][$i],
											);
											
											$srTargetBkdn->exchangeArray($srTargetBkdnData);
											//echo "<pre>"; print_r($srTargetBkdn); die();
											if($abc = $this->getSrTargetBkdnTable()->saveSrTargetBkdn($srTargetBkdn)) {
												$status = 1;
											}else{
												$status = 0;
												break;	
											}
										$i++;
									}
									
								
								
							}else{
								$status = 0;	
							}
							
							
						} else {
							$status = 0;
						}
						
						
					}else{
						$status = 0;
					}
					
				
					
										
					if($status == 1) {
						$this->getSrTargetTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR Target [ ".$postedData["EMPLOYEE_ID"]." ] added successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('srtarget');
					} else {
						$this->getSrTargetTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR Target[ ".$postedData["EMPLOYEE_ID"]." ] couldn't save properly!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('srtarget');
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
				return $this->redirect()->toRoute('srtarget',array('action' => 'add'));
			}
			
			try {
				$srTarget = $this->getSrTargetTable()->getSrTarget($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('srtarget', array('action' => 'index'));
			}
			
			$form = new SrTargetForm('srTarget', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($srTarget);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getSrTargetTable()->transectionStart();
					$srTarget->exchangeArray($request->getPost());
					if($this->getSrTargetTable()->saveSrTarget($srTarget)) {
						$this->getSrTargetTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR Target [ ".$srTarget->EMPLOYEE_ID." ] edit successfully!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('srtarget');
					} else {
						$this->getSrTargetTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>SR Target [ ".$srTarget->EMPLOYEE_ID." ] couldn't edit properly!!</h4></td>
																</tr>
															</table>");	
						return $this->redirect()->toRoute('srtarget');	
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
				return $this->redirect()->toRoute('srtarget');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getSrTargetBkdnTable()->deleteSrTargetBkdn($id);
					$this->getSrTargetTable()->deleteSrTarget($id);
				}
				
				return $this->redirect()->toRoute('srtarget');
			}
			
			return array(
				'id'	=> $id,
				'srTarget'	=> $this->getSrTargetTable()->getSrTarget($id),
			);
		}
		
		public function getSrTargetTable() {
			
			if(!$this->srTargetTable) {
				$sm = $this->getServiceLocator();
				$this->srTargetTable = $sm->get('LocalSetting\Model\SrTargetTable');
			}
			return $this->srTargetTable;
		}
		public function getSrTargetBkdnTable() {
			
			if(!$this->srTargetBkdnTable) {
				$sm = $this->getServiceLocator();
				$this->srTargetBkdnTable = $sm->get('LocalSetting\Model\SrTargetBkdnTable');
			}
			return $this->srTargetBkdnTable;
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