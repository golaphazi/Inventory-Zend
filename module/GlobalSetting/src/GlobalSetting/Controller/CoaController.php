<?php
	// Portfolio Chart of Account Start By Akhand
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use GlobalSetting\Model\Coa;
	use GlobalSetting\Form\CoaForm;
	

	class CoaController extends AbstractActionController {
		protected $coaTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			return new ViewModel(array(
				'coaTreeView' 	=> $this->getCoaTable()->coaTreeView(),
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$request	= $this->getRequest();
			$form 		= new CoaForm('coa', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array());
			$form->get('submit')->setValue('Add');
			if($request->isPost()) {
				$this->getCoaTable()->transectionStart();
				$coa 	= new Coa();
				$form->setInputFilter($coa->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$coa->exchangeArray($request->getPost());
					if($this->getCoaTable()->saveCoa($coa)) {
						//echo 'aaa';die();
						$this->getCoaTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Coa [ ".$coa->COA_CODE." ] Submit successfully!</h4></td>
																</tr>
															</table>");
															
						return $this->redirect()->toRoute('coa');	
					} else {
						$this->getCoaTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Sorry! There is system error.</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('coa');
					}
				}
			}
			
			return array('form' => $form,'flashMessages' 	=> $this->flashMessenger()->getMessages());
		}
		
		public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
		    $id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('coa',array('action' => 'add'));
			}
			
			try {
				$coa 			= $this->getCoaTable()->getCoa($id);
				$COA_DATA_ARRAY	= $this->getCoaTable()->getSpecificCOACode($id);
				$COA_SELECT		= $this->getCoaTable()->getCOAForSelect();
				
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('coa', array('action' => 'index'));
			}
			
			$request	= $this->getRequest();
			$form 		= new CoaForm('coa', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array());
			$form->bind($coa);
			$form->get('submit')->setValue('Edit','Edit');
			
			if($request->isPost()) {
				$form->setData($request->getPost());
				
				$postedData	= $request->getPost();
				
				$coa 	= new Coa();
				$coa->exchangeArray($request->getPost());
				//echo "<pre>"; print_r($coa); die();
				
				$this->getCoaTable()->transectionStart();
				if($this->getCoaTable()->saveCoa($coa)) {
					//echo 'aaa';die();
					$this->getCoaTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Coa [ ".$coa->COA_CODE." ] edit successfully!</h4></td>
															</tr>
														</table>");
														
					return $this->redirect()->toRoute('coa');	
				} else {
					$this->getCoaTable()->transectionInterrupted();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Sorry! There is system error.</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('coa');
				}
			
			}
			
			return array(
				'id' 				=> $id,
				'form' 				=> $form,
				'COA_SELECT' 		=> $COA_SELECT,
				'COA_DATA_ARRAY' 	=> $COA_DATA_ARRAY,
				'flashMessages' 	=> $this->flashMessenger()->getMessages(),
			);
		}
		
		public function deleteAction() {
			$id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('coa',array('action' => 'add'));
			}
			
			try {
				$LFT	= '';
				$RGT	= '';
				$coa 			= $this->getCoaTable()->getCoaLftRgt($id);
				foreach($coa as $coaData) {
					$LFT	= $coaData->LFT;
					$RGT	= $coaData->RGT;	
				}
				$this->getCoaTable()->transectionStart();
				if($this->getCoaTable()->deleteCOA($LFT,$RGT)) {
					$this->getCoaTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Coa deleted successfully!</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('coa');	
				} else {
					$this->getCoaTable()->transectionInterrupted();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='error_msg'>
																<td colspan='3' style='text-align:center;'><h4>Sorry! There is system error.</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('coa');
				}
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('coa', array('action' => 'index'));
			}
		}
		
		public function getCOAListAction() {
			$companyId 	= $_REQUEST['companyId'];
			if($companyId == 0) {
				throw new \Exception("Invalid id");
			} else {
				$coaList 	= $this->getCoaTable()->getCompanyWiseCOA($companyId);
				$data 		= array();
				if($coaList) {
					foreach($coaList as $row) {
						$data[] = array(
										'COA_ID' 		=> $row->COA_ID,
										'COA_NAME_DOT' 	=> $row->COA_NAME_DOT,
										'COA_NAME' 		=> $row->COA_NAME_DOT.'('.$row->COA_CODE.')'.'-'.$row->COA_NAME
									);
					}
				}
				//echo "<pre>"; print_r($data);die();
				echo json_encode($data);
				exit;
			}
		}
		
		public function getCOACodeAction() {
			//$id =  $this->params()->fromQuery('id', 0);
			$companyId 	= $_REQUEST['companyId'];
			$COAId 		= $_REQUEST['COAId'];
			
			if(($companyId == 0)) {
				throw new \Exception("Invalid id");
			} else {
				$maxOrderNumberData = $this->getCoaTable()->getCOACode($companyId,$COAId);
				//echo "<pre>"; print_r($maxOrderNumberData);die();
				echo json_encode($maxOrderNumberData);
				exit;
			}
		}
		
		public function getCoaTable() {
			if(!$this->coaTable) {
				$sm = $this->getServiceLocator();
				$this->coaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->coaTable;
		}
	}
	// Portfolio Chart of Account End By Akhand
?>