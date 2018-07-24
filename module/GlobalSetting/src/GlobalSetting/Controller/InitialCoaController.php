<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	
	use GlobalSetting\Form\InitialCoaForm;	
	use GlobalSetting\Model\InitialCoa;
		
	use GlobalSetting\Model\Coa;
	
	use Accounts\Model\Voucher;
	
	use Zend\Session\Container as SessionContainer;
	
	class InitialCoaController extends AbstractActionController {
		protected $generalCoaTable;
		protected $initialCoaTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('GlobalSetting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			return new ViewModel(array(
				//'tradeDatas' 	=> $this->getTradeDataTable()->dailyOrderDetails(),
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function initialCoaAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->session 				= new SessionContainer('post_supply');
			$BUSINESS_DATE 				= $this->session->businessdate;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('GlobalSetting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$request	= $this->getRequest();
			$form		= new InitialCoaForm('initial-coa-form', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Process Start');
			
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($request->isPost()) {
					$postedData			= $request->getPost();
					//echo "<pre>"; print_r($postedData);die();
					
					//if($returnData = $this->getInitialCoaTable()->getInitialCoa()) {
					if($returnData	= $this->getInitialCoaTable()->getInitialCoa()) {	
						//General COA CODE Entry start
						$this->getInitialCoaTable()->transectionStart();
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
							/*if(!$status) {
								break;
							}*/
						}
						//General COA CODE Entry start
						
					}else {
						$status	= 0;
					}
					if($status) {
						$this->getInitialCoaTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																	<tr class='valid_msg'>
																		<td colspan='3' style='text-align:center;'><h4>Initial COA Saved Successfully!</h4></td>
																	</tr>
																</table>");
						return $this->redirect()->toRoute('initialcoa');
					} else {
						$this->getInitialCoaTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																	<tr class='valid_msg'>
																		<td colspan='3' style='text-align:center;'><h4>Sorry, There is a System Error.!</h4></td>
																	</tr>
																</table>");
						return $this->redirect()->toRoute('initialcoa');	
					}
					
					
				}
			}
			
			return array('form' => $form);
		}
		
		
		
		public function getInitialCoaTable() {
			if(!$this->initialCoaTable) {
				$sm = $this->getServiceLocator();
				$this->initialCoaTable = $sm->get('GlobalSetting\Model\InitialCoaTable');
			}
			return $this->initialCoaTable;
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