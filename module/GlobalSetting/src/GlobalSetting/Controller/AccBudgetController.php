<?php
	// Portfolio Chart of Account Start By Akhand
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use GlobalSetting\Model\AccBudget;
	use GlobalSetting\Form\AccBudgetForm;
	use Zend\Session\Container as SessionContainer;

	class AccBudgetController extends AbstractActionController {
		protected $coaTable;
		protected $accBudgetTable;
		protected $branchTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$request	= $this->getRequest();
			$form 	= new AccBudgetForm('accbudget', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array());
			return new ViewModel(array(
				'coaTreeView' => $this->getAccBudgetTable()->budgetTreeView(),
				'flashMessages' => $this->flashMessenger()->getMessages(),
				'form' => $form,
			));
		}
		
		public function addAction() {
			
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$request	= $this->getRequest();
			$form 		= new AccBudgetForm('accbudget', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array());
			$form->get('submit')->setValue('Add');
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("d-m-Y", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;			
			if($request->isPost()) {				
				$accbudget 	= new AccBudget();
				$form->setInputFilter($accbudget->getInputFilter());
				$form->setData($request->getPost());
				$postedData = $request->getPost();
				//echo '<pre>';print_r($request->getPost());die();
				//if($form->isValid()) {
					$this->getAccBudgetTable()->transectionStart();
					for($i=0;$i<sizeof($postedData["BRANCH_ID"]);$i++) {						
						$branchID								= $postedData["BRANCH_ID"][$i];
						$data['BRANCH_ID'] 						= $postedData["BRANCH_ID"][$i];
						$data['BUDGET_AMOUNT'] 					= $postedData["BRANCHAMOUNT{$branchID}"];
						$data['BUDGET_ACC_NAME'] 				= $postedData['BUDGET_ACC_NAME'];
						$data['BUDGET_ACC_CODE'] 				= $postedData['BUDGET_ACC_CODE'];
						$data['FISCAL_YEAR'] 					= $postedData['FISCAL_YEAR'];
						$data['PARENT_COA'] 					= $postedData['PARENT_COA'];
						$accbudget->exchangeArray($data);
						if($msg = $this->getAccBudgetTable()->saveBudget($accbudget)) {
							if($msg == 'Success'){
								$status = 1;
							} else if($msg == 'Failed'){
								$status = 0;
							} else {
								$status = 0;
							}
						} else {
							$status = 0;
						}
					}
					if($status) {
						$this->getAccBudgetTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Budget added successfully!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('accbudget');
					} else {
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>{$msg}</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('accbudget');
					}
				//}
			}
			
			return array('form' => $form);
		}
		
		public function deleteAction() {
			$id = $_REQUEST['id'];
			if(0<$id){
				$success = 0;
				$this->getCoaTable()->transectionStart();
				if($this->getCoaTable()->deleteCOA($id)){
					$success = 1;
				}
			}
			//echo $success;die();
			if($success) {
				$this->getCoaTable()->transectionEnd();
				return $this->redirect()->toRoute('coa');
			} else {
				$this->getCoaTable()->transectionInterrupted();
				throw new \Exception("There have a child!!!");
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
										'COA_ID' 	=> $row->COA_ID,
										'COA_NAME' 	=> $row->COA_NAME
									);
					}
				}
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
				$maxOrderNumberData = $this->getCoaTable()->getCOACodeForBudget($companyId,$COAId);
				//echo print_r($data); die();
				if(empty($maxOrderNumberData)){
					echo json_encode($maxOrderNumberData);
					exit;
				}else{
					echo json_encode($maxOrderNumberData);
					exit;	
				}
			}
		}
		public function getSuggestRefCOANameAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower( $_REQUEST['no'] );
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getAccBudgetTable()->getBudgetAccName($input);			
			foreach ($IPAData as $selectOption) {
				$coaCode = $selectOption['BUDGET_ACC_CODE'];
				$coaHead = $selectOption['BUDGET_ACC_NAME'];
				$coaCodeHead = $coaCode.",".$coaHead;
				$str .= "<div align='left' onClick=\"fill_id('".$coaCodeHead."','".$no."');\"><b>".$coaCode."-".$coaHead."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		
		public function getSuggestRefCOACodeAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower( $_REQUEST['no'] );			
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getAccBudgetTable()->getBudgetAccCode($input);			
			foreach ($IPAData as $selectOption) {
				$coaCode = $selectOption['BUDGET_ACC_CODE'];
				$coaHead = $selectOption['BUDGET_ACC_NAME'];
				$coaCodeHead = $coaCode.",".$coaHead;
				$str .= "<div align='left' onClick=\"fill_id_code('".$coaCodeHead."','".$no."');\"><b>".$coaCode."-".$coaHead."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		public function getASAccBudgetAction() {
			$this->session = new SessionContainer('post_supply');
			$businessDate = $this->session->businessdate;	
			$fsYearID 		= $_REQUEST['FISCAL_YEAR_ID'];
			$branchID 		= $_REQUEST['BRANCH_ID'];
			$fsYear 		= $_REQUEST['FISCAL_YEAR'];
			$branchName		= $_REQUEST['BRANCH_NAME'];
			$accCode 		= $_REQUEST['accCode'];
			$accHead		= $_REQUEST['accHead'];
			
			$cond = '';
			if ((!empty($accCode))) {
				$cond .= " AND accbudget.BUDGET_ACC_CODE = '".$accCode."'";
			} else {
				$cond .= '';
			}
			if (!empty($branchID)) {
				$cond .= " AND accbudget.BRANCH_ID = '".$branchID."'";
				$branchCond = $branchID;
			} else {
				$cond .= '';
				$productCond = 'All Branch';
			}
			$accHeadPlace = '';
			if ((!empty($accHead))) {
				$accHeadPlace = $accHead;
			} else {
				$accHeadPlace = "All";
			}
			$branchNamePlace = '';
			if ($branchName == '--- please choose ---') {
				$branchNamePlace = 'All Branches';
			} else {
				$branchNamePlace = $branchName;
			}
			
			$reconTable = '';
			$reconTable .= "<table border='0' cellpadding='5' cellspacing='5' width='100%'>";
			$reconTable .= "<tr class=''>
							<td valign='top' colspan='1'> 
									<table border='0' cellpadding='5' cellspacing='5' width='100%' style='font-size:85%;border:1px dotted #888;font-family:Tahoma, Geneva, sans-serif;'>
										<tr style='border:1px dotted #888;font-size:120%;'>
											<td valign='top' colspan='3' align='left'>
												&nbsp;
											</td>
										</tr>
										<tr style='border:1px dotted #888;font-size:120%;'>
											<td valign='top' colspan='3' align='left'>
												Search Parameter: Fiscal Year - {$fsYear}, Branch - {$branchNamePlace}, Budget Head - {$accHeadPlace} , 
											</td>
										</tr>
									</table>
							 </td>
						</tr>
						</table>";
			
			$instrumentData = array();
			$instrumentData  = $this->getAccBudgetTable()->fetchAdvanceSearch($cond);
			$reconTable 		.= "<table class='motherTbl' cellpadding='3' cellspacing='1' border='0' width='90%' style='margin:0px auto 0px auto; font-size:85%;font-family:Tahoma, Geneva, sans-serif;'>";
			$reconTable 		.= "<tr style='border:1px dotted #888;line-height:2em;font-weight:bold;'>
										<td width='30%'>Branch</td>
										<td width='20%'>Account Head</td>
										<td width='25%'>Fiscal Year</td>
										<td width='25%' align='right'>Amount</td>
									</tr>";
			
			$counter          				= 1;
			$uniqueBranchName  				= '';
			$uniqueAccountHead  			= '';
			$foundFlag						= 0;
			foreach($instrumentData as $instrumentDatas) {				
				$budgerAmount					= $instrumentDatas["BUDGET_AMOUNT"];
				$fiscalYearID					= $instrumentDatas["FISCAL_YEAR_ID"];
				$branchID						= $instrumentDatas["BRANCH_ID"];
				$branchName						= $instrumentDatas["BRANCH_NAME"];			
				$fiscalStartDate				= $instrumentDatas["FISCAL_START"];
				$fiscalEndDate					= $instrumentDatas["FISCAL_END"];
				$fiscalPeriod					= $instrumentDatas["FISCAL_PERIOD"];
				$fiscalMonth					= $instrumentDatas["FISCAL_MONTH"];
				$accountHead					= $instrumentDatas["BUDGET_ACC_NAME"];
				$foundFlag						= 1;
				if($uniqueBranchName != $branchName) {
					$reconTable .= "
								<tr onmouseover='this.style.background=\"#ccffcc\"' onmouseout='this.style.background=\"#F7F4F4\"'>
									<td>".$branchName."</td>
									<td colspan='3'>&nbsp;</td>
								</tr>";
					$uniqueBranchName 	= $branchName;
					
				}
				if($uniqueAccountHead != $accountHead) {
					$reconTable .= "
					<tr onmouseover='this.style.background=\"#ccffcc\"' onmouseout='this.style.background=\"#F7F4F4\"'>
						<td>&nbsp;</td>
						<td>
							".$accountHead."
						</td>
						<td colspan='2'>
							&nbsp;
						</td>
					</tr>
					";
					$uniqueAccountHead	= $accountHead;
					$uniquefiscalPeriod 	= '';
				}
				if(($uniqueBranchName == $branchName) && ($uniquefiscalPeriod != $fiscalPeriod)) {
					$reconTable .= "	
								<tr onmouseover='this.style.background=\"#ccffcc\"' onmouseout='this.style.background=\"#F7F4F4\"'>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td >".$fiscalPeriod."</td>
									<td>&nbsp;</td>
							   </tr>
							   ";
					$uniquefiscalPeriod = $fiscalPeriod;
				}
				
				$reconTable .= "	
								<tr onmouseover='this.style.background=\"#ccffcc\"' onmouseout='this.style.background=\"#F7F4F4\"'>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td align='right'>".number_format($budgerAmount,2)."</td>
								</tr>
								";
			}
			if($foundFlag == 0){
				$reconTable 		.= "<tr style='border:1px dotted #888;line-height:2em;font-weight:bold;'>
											<td width='30%'>Branch</td>
											<td width='20%'>Account Head</td>
											<td width='25%'>Fiscal Year</td>
											<td width='25%' align='right'>Amount</td>
										</tr>";
				$reconTable .= "	
								<tr>
									<td colspan='4' align='center' >No Information found</td>
								</tr>
								";
			}
			$reconTable 	.= "</table>";
			//echo $reconTable;
			echo json_encode($reconTable);exit;
		}
		public function getBranchListAction() {
			$id =  $this->params()->fromQuery('id', 0);			
			if($id == 0) {
				throw new \Exception("Invalid id");
			} else {
				$templateChargeList = $this->getBranchTable()->getBranchList($id);
				$data = array();
				if($templateChargeList) {
					foreach($templateChargeList as $row) {
						$data[] = array(
										'BRANCH_ID' 	=> $row['BRANCH_ID'],
										'BRANCH_NAME' 	=> $row['BRANCH_NAME'],
										'BRANCH_CODE' 	=> $row['BRANCH_CODE'],
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
		public function getBranchTable() {
			if(!$this->branchTable) {
				$sm = $this->getServiceLocator();
				$this->branchTable = $sm->get('CompanyInformation\Model\BranchTable');
			}
			return $this->branchTable;
		}
		public function getCoaTable() {
			if(!$this->coaTable) {
				$sm = $this->getServiceLocator();
				$this->coaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->coaTable;
		}
		public function getAccBudgetTable() {
			if(!$this->accBudgetTable) {
				$sm = $this->getServiceLocator();
				$this->accBudgetTable = $sm->get('GlobalSetting\Model\AccBudgetTable');
			}
			return $this->accBudgetTable;
		}
	}
	// Portfolio Chart of Account End By Akhand
?>