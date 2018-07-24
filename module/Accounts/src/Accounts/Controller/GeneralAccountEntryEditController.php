<?php
	namespace Accounts\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Accounts\Form\GeneralAccountEntryEditForm;
	
	use Accounts\Model\Voucher;
	
	use Zend\Session\Container as SessionContainer;
	
	class GeneralAccountEntryEditController extends AbstractActionController {
		protected $dbAdapter;
		protected $coaTable;
		protected $investorManagementTable;
		protected $voucherTable;
		protected $investorFundTable;
		protected $companyTable;
		protected $branchTable;
		protected $trialBalanceTable;
		
		public function indexAction() {
			$userInfo 						= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID						= $userInfo->id;
			
			$this->layout()->leftMenu 		= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Accounts',$USER_ID);
			$this->layout()->controller 	= $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 	= $this->getRequest();
			$form 		= new GeneralAccountEntryEditForm('generalaccountentryedit', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			
			$form->get('submit')->setValue('Add');
			
			if($request->isPost()) {
				$form->setData($request->getPost());
				$postedData 			= $request->getPost();
				//echo "<pre>"; print_r($postedData); die();
				$msg = '';
				
				$voucherType  			= $postedData["frm"];
				if(strtolower($voucherType)=='c') {
					//echo 'hi therer';die();
					$transStatus   		= 0;
					$msg				= '';
					$flag				= 0;
					$tmTransectionDate 		= $postedData['transection_date'];
					$existingVoucherNumber  = $postedData['existingVoucherNumber'];
					
					$tmNtr              = '';
					
					$drawnOn 			= '';
					$tmCbjt 		    = '';
					$tmCbCOACode        = '';
					$tmEffectedAtBank	= '';
					$tmChequeNo 	    = '';
					$tmChqDate 	        = "''";
					
					$tcAccCodes       	= array();
					$tcNtrs           	= array();
					$tcNarrations     	= array();
					$tcAmounts        	= array();
					$paymentCodes		= array();
					$tcAccCode		  	= '';
					$tcAmount		 	= '';
					$tcNarration	  	= '';
					$tcPortfolioCode	= $postedData['fundCode'];
					$tcNarration 		= $postedData["particulars"];
					$NumberOfRows 		= $postedData["NumberOfRows"];
					
					for($i = 1; $i < $NumberOfRows; $i++) {
						if(isset($postedData["coa_code{$i}"]) && isset($postedData["amount{$i}"])){
							$tcAccCode	= $postedData["coa_code{$i}"];
							$tcAmount   = str_replace(",", "", $postedData["amount{$i}"]);
							$tcNtr		= (isset($postedData["accountType{$i}"])) ? $postedData["accountType{$i}"]:'';
							
							if(!empty($tmTransectionDate) 
							   && !empty($tcAccCode) 
							   && !empty($tcPortfolioCode) 
							   && !empty($tcAmount)
							   && !empty($tcNtr)) {
								if((substr($tcAccCode,0,3) == '303') && (strtoupper($tcNtr) == 'C')){
									$tmNtr				= 'D';
									$tmCbjt  			= 'C';
									$tmCbCOACode        = $tcAccCode;
									$drawnOn			= (isset($postedData["drawnOn{$i}"])) ? $postedData["drawnOn{$i}"]:'';
								} else if((substr($tcAccCode,0,3) == '304') && (strtoupper($tcNtr) == 'C')) {
									$tmNtr				= 'D';
									$tmCbjt       		= 'B';
									$tmCbCOACode        = $tcAccCode;
									$tmEffectedAtBank   = $postedData['effectedAtBank'];
									$tmChequeNo   		= (isset($postedData["chequeNo{$i}"])) ? $postedData["chequeNo{$i}"]:'';
									$tmChqDate    		= (isset($postedData["chq_date{$i}"])) ? $postedData["chq_date{$i}"]:"''";
									$drawnOn			= (isset($postedData["drawnOn{$i}"])) ? $postedData["drawnOn{$i}"]:'';
								} else {
									$paymentCodes[] = array('paymentCOACode'=>$tcAccCode,
															'paymentAmount'=>$tcAmount,
															'portfolioCode'=>$tcPortfolioCode,
															'voucherType'=>$tcNtr,
															'particulars'=>$tcNarration
															); 
								}
								$flag 				= 1;
							}
						}
					}
					/*echo "<pre>";
					echo $tmNtr;
					echo $tmCbjt;
					echo $tmCbCOACode;
					print_r($paymentCodes);
					echo $flag;
					die();*/
					
					if($flag) {
						$updateDetails[] = array('tmNtr'=>$tmNtr,
												'tmCbjt' => $tmCbjt,
												'tmCbCOACode' => $tmCbCOACode,
												'tmChequeNo' => $tmChequeNo,
												'tmChqDate' => $tmChqDate,
												'drawnOn' => $drawnOn,
												'existingVoucherNumber' => $existingVoucherNumber,
												);
						//echo '<pre>';print_r($updateDetails);die();
						if($status = $this->getVoucherTable()->updateContraInfoTM($updateDetails)) {
							if($status) {
								for($k=0;$k<sizeof($paymentCodes);$k++){
									$transStatus 		= 0;			
									$tcAccCode 			= $paymentCodes[$k]['paymentCOACode'];
									$tcAmount 			= $paymentCodes[$k]['paymentAmount'];
									$tcNarration		= $paymentCodes[$k]['particulars'];
									$tcvoucherType		= $paymentCodes[$k]['voucherType'];
									$tcPortfolioCode 	= $paymentCodes[$k]['portfolioCode'];
									if(!empty($tcAccCode) 
									   && !empty($tcAmount) 
									   && !empty($tcPortfolioCode)) {
										$insertDetails = array('existingVoucherNumber'		=> $existingVoucherNumber,
																'tcPortfolioCode' 			=> $tcPortfolioCode,
																'tcAccCode' 				=> $tcAccCode,
																'tcvoucherType' 			=> $tcvoucherType,
																'tmCbjt' 					=> $tmCbjt,
																'tmCbCOACode' 				=> $tmCbCOACode,
																'tcNarration' 				=> $tcNarration,
																'tcAmount' 					=> $tcAmount,
																'tmNtr'						=> $tmNtr,
																);
										//echo '<pre>';print_r($insertDetails);die();
										if($this->getVoucherTable()->saveContraInfoTC($insertDetails)) {
											$transStatus = 1;
											if($transStatus) {
												$updateDetails = array('tmNtr'	=> $tmNtr,
																'tcAccCode' 			=> $tcAccCode,
																'tmChequeNo' 			=> $tmChequeNo,
																'tmChqDate' 			=> $tmChqDate,
																'drawnOn' 				=> $drawnOn,
																'existingVoucherNumber' => $existingVoucherNumber,
																);
												//echo '<pre>';print_r($updateDetails);die();
												if($this->getVoucherTable()->updateContraInfoTM2ndTime($updateDetails)) {
													$transStatus = 1;
													if($transStatus) {
														if($this->getVoucherTable()->saveContraInfoTC2ndTime($insertDetails)) {
															$transStatus = 1;
														} else {
															$transStatus = 0;
															break;
														}
													}
												} else {
													$transStatus = 0;
													break;
												}
											}
										} else {
											$transStatus = 0;
											break;
										}
									}
								}
								if(!$transStatus) {
									$this->getInvestorFundTable()->transectionInterrupted();
									$msg	= "<span class='errorMsg'>Sorry! There is a system error.</span>";
									$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																			<tr class='error_msg'>
																				<td width='100%' style='text-align:center;'>{$msg}</td>
																			</tr>
																		</table>");
									return $this->redirect()->toRoute('generalaccountentryedit');
								} else {
									$transStatusGTrialBalance = 1;
									//if($transStatusGTrialBalance = $this->getVoucherTable()->updateGeneralTrialBalance()) {
										if($transStatusGTrialBalance) {
											$msg	= "<span class='validMsg'>Voucher updated successfully.</span>";
											$this->getInvestorFundTable()->transectionEnd();
											$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																					<tr class='valid_msg'>
																						<td width='100%' style='text-align:center;'>{$msg}</td>
																					</tr>
																				</table>");
											return $this->redirect()->toRoute('generalaccountentryedit');
										} else {
											$msg	= "<span class='errorMsg'>Sorry! There is a system error.</span>";
											$this->getInvestorFundTable()->transectionInterrupted();
											//throw new \Exception("Voucher update couldn't save properly!");
											$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																					<tr class='error_msg'>
																						<td width='100%' style='text-align:center;'>{$msg}</td>
																					</tr>
																				</table>");
											return $this->redirect()->toRoute('generalaccountentryedit');
										}
									//}
								}
								
							}
						}
					
					} else {
						$msg	= "<span class='errorMsg'>Please, fill up the entry correctly!</span>";
					}
				
				} 
				else {
					//echo 'hi hterer';die();
					$transStatus   			= 0;
					$msg					= '';
					$flag					= 0;
					$tmTransectionDate 		= $postedData['transection_date'];
					$existingVoucherNumber  = $postedData['existingVoucherNumber'];
					
					$drawnOn				= '';
					$tmCbjt 		    	= '';
					$tmCbCOACode        	= '';
					$tmEffectedAtBank		= '';
					$tmChequeNo 	    	= '';
					$tmChqDate 	        	= "''";
					
					$tcAccCodes       		= array();
					$tcNtrs           		= array();
					$tcNarrations     		= array();
					$tcAmounts        		= array();
					$paymentCodes			= array();
					$tcAccCode		  		= '';
					$tcAmount		 		= '';
					$tcNarration	  		= '';
					$tcPortfolioCode		= $postedData['fundCode'];
					$tcNarration 			= $postedData["particulars"];
					$NumberOfRows 			= $postedData["NumberOfRows"];
					
					//echo $voucherType;die();
					
					if(strtolower($voucherType)=='p') {
						$tmNtr              = 'D';
					} else if(strtolower($voucherType)=='r') {
						$tmNtr              = 'C';
					} else {
						$tmNtr              = '';
						$tmCbjt  			= 'J';
					}
					//echo $NumberOfRows;die();
					for($i = 1; $i < $NumberOfRows; $i++) {
						if(isset($postedData["coa_code{$i}"]) && isset($postedData["amount{$i}"])){
							$tcAccCode	= $postedData["coa_code{$i}"];
							$tcAmount   = str_replace(",", "", $postedData["amount{$i}"]);
							$tcNtr		= (isset($postedData["accountType{$i}"])) ? $postedData["accountType{$i}"]:'';
							if(!empty($tmTransectionDate) 
							   && !empty($tcAccCode) 
							   && !empty($tcPortfolioCode) 
							   && !empty($tcAmount)
							   && !empty($tcNtr)) {
								if(substr($tcAccCode,0,3) == '303'){
									$tmCbjt  			= 'C';
									$tmCbCOACode        = $tcAccCode;
									$drawnOn			= (isset($postedData["drawnOn{$i}"])) ? $postedData["drawnOn{$i}"]:'';
								} else if(substr($tcAccCode,0,3) == '304') {
									$tmCbjt       		= 'B';
									$tmCbCOACode        = $tcAccCode;
									$tmEffectedAtBank   = $postedData['effectedAtBank'];
									$tmChequeNo   		= (isset($postedData["chequeNo{$i}"])) ? $postedData["chequeNo{$i}"]:'';
									$tmChqDate    		= (isset($postedData["chq_date{$i}"])) ? $postedData["chq_date{$i}"]:"''";
									$drawnOn			= (isset($postedData["drawnOn{$i}"])) ? $postedData["drawnOn{$i}"]:'';
								} else {
									$paymentCodes[] = array('paymentCOACode'=>$tcAccCode,
															'paymentAmount'=>$tcAmount,
															'portfolioCode'=>$tcPortfolioCode,
															'voucherType'=>$tcNtr,
															'particulars'=>$tcNarration
															);
								}
								$flag 				= 1;
							}
						}
					}
					//echo "<pre>";print_r($paymentCodes);die();
					$transStatus = 0;
					if($flag) {
						$updateDetails[] = array('tmNtr'=>$tmNtr,
												'tmCbjt' => $tmCbjt,
												'tmCbCOACode' => $tmCbCOACode,
												'tmChequeNo' => $tmChequeNo,
												'tmChqDate' => $tmChqDate,
												'drawnOn' => $drawnOn,
												'existingVoucherNumber' => $existingVoucherNumber,
												);
						if($status = $this->getVoucherTable()->updateTransactionMaster($updateDetails)) {
							if($status) {
								for($k=0;$k<sizeof($paymentCodes);$k++){
									$transStatus 		= 0;			
									$tcAccCode 			= $paymentCodes[$k]['paymentCOACode'];
									$tcAmount 			= $paymentCodes[$k]['paymentAmount'];
									$tcNarration		= $paymentCodes[$k]['particulars'];
									$tcvoucherType		= $paymentCodes[$k]['voucherType'];
									$tcPortfolioCode 	= $paymentCodes[$k]['portfolioCode'];
									if(!empty($tcAccCode) 
									   && !empty($tcAmount) 
									   && !empty($tcPortfolioCode)) {
										$insertDetails = array('existingVoucherNumber'	=> $existingVoucherNumber,
																'tcPortfolioCode' 			=> $tcPortfolioCode,
																'tcAccCode' 				=> $tcAccCode,
																'tcvoucherType' 			=> $tcvoucherType,
																'tmCbjt' 					=> $tmCbjt,
																'tmCbCOACode' 				=> $tmCbCOACode,
																'tcNarration' 				=> $tcNarration,
																'tcAmount' 					=> $tcAmount,
																);
										if($this->getVoucherTable()->saveTransactionChild($insertDetails)) {
											$transStatus = 1;
										} else {
											break;
										}
									}
								}
								if(!$transStatus) {
									$msg	= "<span class='errorMsg'>Sorry! There is a system error.</span>";
									$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																			<tr class='error_msg'>
																				<td width='100%' style='text-align:center;'>{$msg}</td>
																			</tr>
																		</table>");
									return $this->redirect()->toRoute('generalaccountentryedit');
								} else {
									if($this->getTrialBalanceTable()->updateTrialBalance($tmTransectionDate)) {
										$msg	= "<span class='validMsg'>Voucher updated successfully.</span>";
										$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																				<tr class='valid_msg'>
																					<td width='100%' style='text-align:center;'>{$msg}</td>
																				</tr>
																			</table>");
									} else {
										$msg	= "<span class='errorMsg'>Sorry! There is a system error.</span>";
										$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																				<tr class='error_msg'>
																					<td width='100%' style='text-align:center;'>{$msg}</td>
																				</tr>
																			</table>");	
									}
									return $this->redirect()->toRoute('generalaccountentryedit');
								}
								
							}
						}
					} else {
						//echo 'else e dhukce';die();
						$msg	= "<span class='errorMsg'>Sorry! There is a system error.</span>";
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
															<tr class='errorMsg'>
																<td width='100%' style='text-align:center;'>{$msg}</td>
															</tr>
														</table>");
						return $this->redirect()->toRoute('generalaccountentryedit');
					}	
				
				}
			}
			return array('form' => $form,'flashMessages' => $this->flashMessenger()->getMessages());
		}
		
		public function getNDAInvestorListAction() {
			if(!$this->getOperatorTable()->getBusinessDateInfo()){return $this->redirect()->toRoute('login');}			
			$portfolioTypeID = $_REQUEST['portfolioTypeID'];
			if($portfolioTypeID == 0) {
				throw new \Exception("Invalid id");
			} else {
				$ndaInvestorList = $this->getInvestorManagementTable()->getNDAInvestor($portfolioTypeID);				
				$data = array();
				if($ndaInvestorList) {
					foreach($ndaInvestorList as $row) {
						$data[] = array(
										'PORTFOLIO_CODE' => $row->PORTFOLIO_CODE,
										'INVESTOR_NAME' => $row->INVESTOR_NAME
									);
					}
				}
				echo json_encode($data);
				exit;
			}
		}
		public function getSuggestIPANoAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$cond = '';
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getInvestorManagementTable()->fetchIPANo($input,$cond);			
			foreach ($IPAData as $selectOption) {
				$ipaNo = $selectOption['PORTFOLIO_CODE'];
				$mailingAddress = '';
				$investorName = $selectOption['INVESTOR_NAME'];
				$str .= "<div align='left' onClick=\"fill_id_code('".$ipaNo."','".$mailingAddress."','".$investorName."');\"><b>".$ipaNo.'('.$investorName.')'."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		public function getSuggestBankNameAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$id	= $_REQUEST['id'];
			$cond = '';
			$str='';
			$investorInfoArray 	= array();
			$BANKData = $this->getMoneyMarketOrganizationTable()->fetchBankNameForSuggestion($input,$cond);			
			foreach ($BANKData as $selectOption) {
				$orgName = $selectOption['ORG_NAME'];
				$str .= "<div align='left' onClick=\"fill_bank_name('".$orgName."','".$id."');\"><b>".$orgName."</b></div>";
			}
			//echo json_encode($str);exit;
			echo $str;exit;
		}
		public function getSuggestBankNameForCIHAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$id	= $_REQUEST['id'];
			$cond = '';
			$str='';
			$investorInfoArray 	= array();
			$BANKData = $this->getMoneyMarketOrganizationTable()->fetchBankNameForSuggestion($input,$cond);			
			foreach ($BANKData as $selectOption) {
				$orgName = $selectOption['ORG_NAME'];
				$str .= "<div align='left' onClick=\"fill_bank_nameCIH('".$orgName."','".$id."');\"><b>".$orgName."</b></div>";
			}
			//echo json_encode($str);exit;
			echo $str;exit;
		}
		public function getSuggestCOAPaymentRceiptAction() {
			$frm = strtolower( $_REQUEST['frm'] );
			$fundCode = strtolower($_REQUEST['fundCode']);
			$coa_head = array();
			if($frm=='p') {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (305, 501) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else if($frm=='r') {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (201, 202, 302, 601) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else if($frm=='c') {
				$switch = "SUBSTR(COA_CODE, 0, 3) IN (303,304) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (303,304) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			}
			/*if($fundCode == 0) {
				throw new \Exception("Invalid id");
			} else {*/
				$COAList = $this->getCoaTable()->getCOAListForPaymentEntry($switch);				
				$data = array();
				if($COAList) {
					foreach($COAList as $row) {
						$data[] = array('id' => $row->COA_CODE,'text' => $row->COA_NAME);
					}
				}
				//echo '<pre>';print_r($data);die();
				echo json_encode($data);
				//echo $data;
				exit;
			//}
		}
		
		public function getTrialBalPayRecAmountAction() {
			$amount		= 0;
			$coaCode	= $_REQUEST['coaCode'];
			$frm		= $_REQUEST['frm'];
			$fundCode	= '';
			if($fundCode == 0) {
				throw new \Exception("Invalid id");
			} else {
				$portfolioCOAList 	= $this->getCoaTable()->getTrialBalPayReceiptAmount($coaCode,$frm,$fundCode);				
				$data 				= array();
				if($portfolioCOAList) {
					foreach($portfolioCOAList as $row) {
						$data[] = array(
										'AMOUNT' => $row->AMOUNT,
									);
					}
				}
				echo number_format($amount,2);
				//echo json_encode($data);
				exit;
			}			
		}
		public function getCompanyListAction() {
			echo $companyData 	= $this->getCompanyTable()->fetchAll();
			$selectOption 	= array();
			$selectData 	= array();
			foreach ($companyData as $selectOption) {
				$selectData[] = array(
								'COMPANY_ID' => $selectOption->COMPANY_ID,
								'COMPANY_NAME' => $selectOption->COMPANY_NAME,
							);
			}
			//echo "<pre>";print_r($selectData);die();
			echo json_encode($selectData);
			//echo $selectData;
		}
		public function getRemoveVoucherNumberAction() {
			$this->session 		= new SessionContainer('post_supply');
			date_default_timezone_set("Asia/Dhaka");
			$businessDate  		= date("d-m-Y", strtotime($this->session->businessdate));
			
			$VoucherNo 			= $_REQUEST['VoucherNo'];
			$printVoucher		= $VoucherNo;
			$transactionNumber 	= $_REQUEST['transactionNumber'];
			$VoucherNo 			= explode('-',$VoucherNo);
			$voucherPart1 		= $VoucherNo[0];
			$voucherPart2 		= $VoucherNo[1];
			$voucherPart3 		= str_replace(',','',number_format($VoucherNo[2]));
			
			$voucherType 		= substr($voucherPart1,0,2);
			$cond				= '';
			if($voucherType == 'CV') {
				$cond 			= " AND	CONTRA_VOUCHER = $voucherPart3";
			} else if($voucherType == 'BP') {
				$cond 			= " AND	DEBIT_VOUCHER = $voucherPart3";
			} else if($voucherType == 'CP') {
				$cond 			= " AND	DEBIT_VOUCHER = $voucherPart3";
			} else if($voucherType == 'BR') {
				$cond 			= " AND	CREDIT_VOUCHER = $voucherPart3";
			} else if($voucherType == 'CR') {
				$cond 			= " AND	CREDIT_VOUCHER = $voucherPart3";
			} else if($voucherType == 'JV') {
				$cond 			= " AND	JOURNAL_VOUCHER = $voucherPart3";
			} else {
				$cond			= '';
			}
			
			$fundCode 			= substr($voucherPart1,2,5);//die();
			$msg				= '';
			if(!empty($VoucherNo)) {
				if($removeVoucherNoStatus = $this->getVoucherTable()->removeAccountEntry($fundCode,$voucherPart2,$cond,$transactionNumber)) {
					if($removeVoucherNoStatus) {
						if($this->getTrialBalanceTable()->updateTrialBalance($businessDate)) {
							$msg	= "<div class='valid_msg'>Voucher {$printVoucher} has been remove successfully.</div>";
						} else {
							$msg	= "<div class='error_msg'>Process failed.</div>";	
						}
					} else {
						$msg	= "<div class='error_msg'>Process failed.</div>";
					}
				}
			}
			echo json_encode($msg);exit;
		}
		public function getVoucherNumberForAccountsEditAction() {
			$this->session 		= new SessionContainer('post_supply');
			date_default_timezone_set("Asia/Dhaka");
			$businessDate  		= date("d-m-Y", strtotime($this->session->businessdate));
			$branchID 			= $_REQUEST['BRANCH_ID'];
			$branchData  = $this->getBranchTable()->getBranchInfoFinancialStatement($branchID);
			foreach ($branchData as $selectOption) {
				$companyName 	= $selectOption['COMPANY_NAME'];
				$branchName 	= $selectOption['BRANCH_NAME'];
				$branchCode 	= $selectOption['BRANCH_CODE'];
				$branchAddress 	= $selectOption['ADDRESS'];
				$webAddress 	= $selectOption['WEB'];
			}
			
			$vType 				= $_REQUEST['vType'];
			
			$transDateFrom 	= $_REQUEST['tranDateFrom'];
			$transDateTo 	= $_REQUEST['tranDateTo'];
			
			$cond = '';
			if(($transDateFrom != '') && ($transDateTo != '')) {
				$cond = "AND 	DATE_FTDT BETWEEN '".date("Y-m-d", strtotime($transDateFrom))."' AND '".date("Y-m-d", strtotime($transDateTo))."' ";
			}  else {
				$cond = "AND 	DATE_FTDT BETWEEN '".date("Y-m-d", strtotime($businessDate))."' AND '".date("Y-m-d", strtotime($businessDate))."' ";
			}
			
			$VCondition = '';
			if(strtolower($vType) == 'payment') {
				$cashBankCond = '';
				$cashBankFlag = $_REQUEST['voucherTypeCBCond'];
				if ($cashBankFlag == 'cash') {
					$cashBankCond = "AND VOUCHAR_TYPE_T = 'C'";
				} elseif ($cashBankFlag == 'bank') {
					$cashBankCond = "AND VOUCHAR_TYPE_T = 'B'";
				} else {
					$cashBankCond = '';
				}
				$VCondition = "AND UPPER(SUBSTR(VOUCHER_NO_,1,2)) IN ('BP','CP') {$cashBankCond}";
			} else if(strtolower($vType) == 'receipt') {
				$cashBankCond = '';
				$cashBankFlag = $_REQUEST['voucherTypeCBCond'];
				if ($cashBankFlag == 'cash') {
					$cashBankCond = "AND VOUCHAR_TYPE_T = 'C'";
				} elseif ($cashBankFlag == 'bank') {
					$cashBankCond = "AND VOUCHAR_TYPE_T = 'B'";
				} else {
					$cashBankCond = '';
				}
				$VCondition = "AND UPPER(SUBSTR(VOUCHER_NO_,1,2)) IN ('BR','CR') {$cashBankCond}";
			} else if(strtolower($vType) == 'contra') {
				$cashBankCond = '';
				$cashBankFlag = $_REQUEST['voucherTypeCBCond'];
				if ($cashBankFlag == 'cash') {
					$cashBankCond = "AND VOUCHAR_TYPE_T = 'C'";
				} elseif ($cashBankFlag == 'bank') {
					$cashBankCond = "AND VOUCHAR_TYPE_T = 'B'";
				} else {
					$cashBankCond = '';
				}
				$VCondition = "AND UPPER(SUBSTR(VOUCHER_NO_,1,2)) = 'CV' {$cashBankCond}";
			} else if(strtolower($vType) == 'journal') {
				$VCondition = "AND UPPER(SUBSTR(VOUCHER_NO_,1,2)) = 'JV'";
			}
			
			$voucherNumberList = array();
			$voucherData 	= $this->getTrialBalanceTable()->fetchDistinctVoucherNoList($branchName,$VCondition,$cond);
			foreach ($voucherData as $selectOption) {
				$voucherNumberList[] = array('optionValue'=>$selectOption->VOUCHER_NO_,'optionDisplay'=>$selectOption->VOUCHER_NO_);
			}
			//echo "<pre>";print_r($voucherData);die();
			echo json_encode($voucherNumberList);exit;
			//echo $voucherNumberList;
			
		}
		public function getAccountsEditForEodAction() {
			$this->session 		= new SessionContainer('post_supply');
			date_default_timezone_set("Asia/Dhaka");
			$businessDate  		= date("d-m-Y", strtotime($this->session->businessdate));
			$cond 					= '';
			$totalDebitBalance   	= 0;
			$totalCreditBalance   	= 0;
			$voucherData 			= '';
			if(isset($_REQUEST['voucherData'])) {
				$voucherData 		= $_REQUEST['voucherData'];
			}
			$branchID 		= $_REQUEST['BRANCH_ID'];
			//echo $branchID;die();
			$branchData  	= $this->getBranchTable()->getBranchInfoFinancialStatement($branchID);
			foreach ($branchData as $selectOption) {
					$companyName 	= $selectOption['COMPANY_NAME'];
					$branchName 	= $selectOption['BRANCH_NAME'];
					$branchCode 	= $selectOption['BRANCH_CODE'];
					$branchAddress 	= $selectOption['ADDRESS'];
					$webAddress 	= $selectOption['WEB'];
			}		
			$transDateFrom 	= $_REQUEST['tranDateFrom'];
			$transDateTo 	= $_REQUEST['tranDateTo'];
			
			$frm			= '';
			$vType 			= '';
			$vType = substr($voucherData,0,2);
			if($vType == 'CV') {
				$frm		= 'c';
				$vType 		= 'Contra Voucher';	
			} /*else if($vType == 'PV') {
				$frm		= 'p';
				$vType 		= 'Payment Voucher';
			} else if($vType == 'RV') {
				$frm		= 'r';
				$vType 		= 'Receipt Voucher';
			} */
			else if($vType == 'CP') {
				$frm		= 'p';
				$vType 		= 'Cash Payment Voucher';
			} else if($vType == 'BP') {
				$frm		= 'p';
				$vType 		= 'Bank Payment Voucher';
			} else if($vType == 'CR') {
				$frm		= 'r';
				$vType 		= 'Cash Receive Voucher';
			} else if($vType == 'BR') {
				$frm		= 'r';
				$vType 		= 'Bank Receive Voucher';
			} else if($vType == 'JV') {
				$frm		= 'j';
				$vType 		= 'Journal Voucher';
			}
			$qData 					= '';
			$qDataPrint 			= '';
			if(!empty($voucherData)){
				$qData 				= " AND UPPER(VOUCHER_NO_) in ('".$voucherData."')";
				$qDataPrint 		.= "&voucherData=".$voucherData;
				$VoucherNo 			= $voucherData;
			}
			
			if(($transDateFrom != '') && ($transDateTo != '')) {
				$cond = "AND 	DATE_FTDT BETWEEN '".date("Y-m-d", strtotime($transDateFrom))."' AND '".date("Y-m-d", strtotime($transDateTo))."' {$qData}";
			}  else {
				$cond = "AND 	DATE_FTDT BETWEEN '".date("Y-m-d", strtotime($businessDate))."' AND '".date("Y-m-d", strtotime($businessDate))."' {$qData}";
				$transDateFrom      = $businessDate;
				$transDateTo        = $businessDate;
			}
				
			$generateTable 	   = "";
			$transactionNumber = '';
			$tranNoData 	= $this->getTrialBalanceTable()->fetchTranNofromTransaction($branchName,$cond);
			foreach ($tranNoData as $selectOption) {
				$transactionNumber = $selectOption->TRAN_NO;
			}
			$generateTable .= "
								<table align='center' border='0' cellpadding='3' cellspacing='1' width='100%' class='tablesorter' style='font-size:75%'>
									<tr id='loading' style='height:100px; padding:0px; text-align:center; display:none;'>
										<!--<td id='preloader' style='height:100px; padding:0px; text-align:center; display:none;'>
											<h4 id='msg' style='font-weight:normal;margin-top:0px;'>Please, wait while statement in process . . .</h4>
											<span>
												<img src='../img/preloader2.gif' style='width:40px;' />
											</span>
										</td>-->
									</tr>
									<tr valign='top'>
										<td align='right' colspan='4'>
											<a href='javascript:void(0)' onclick=\"editVoucherNumber('{$VoucherNo}','{$transactionNumber}')\">
											<img src='../img/cancel.png' title='Cancel voucher number of {$VoucherNo}' border='0'/></a>
										</td>
									</tr>
									<tr valign='top'>
										<td align='right'>Branch</td>
										<td align='left'>:</td>
										<td align='left'>
											<select  name='portfolioCode' id='portfolioCode' style='width:160px;'>
												<option value=''>please chose</option>
												<option value='".$branchID."' selected='selected'>".$branchName."</option>
											</select>
										</td>
										<td align='left'>&nbsp;</td>
									</tr>
									<tr valign='top'>
										<td width='45%' align='right'>Transaction Date :</td>
										<td align='left'>:</td>
										<td width='40%' align='left'><input name='transection_date'  class='FormDateTypeInput' id='transection_date2' type='text' readonly='readonly' value='{$transDateFrom}'/></td>
										<td width='35%' align='left'>&nbsp;</td>
									</tr>
									<tr valign='top'>
										<td align='right'>Voucher Type</td>
										<td align='left'>:</td>
										<td align='left'>
											".$vType."
										</td>
										<td align='left'>&nbsp;</td>
									</tr>
									<tr valign='top'>
										<td align='left'>&nbsp;</td>
										<td align='left'>&nbsp;</td>
										<td align='left'>&nbsp;</td>
										<td align='left'>&nbsp;</td>
									</tr>
									</table>
									<table align='center' border='0' cellpadding='3' cellspacing='1' width='100%' class='tablesorter' style='font-size:75%' id='ClosingBalTab'>
									<tr valign='top' style='font-weight:bold; text-align:center; background:#E8E1E1;'>
										<td width='30%' align='left' class=''>ACCOUNT HEAD</td>
										<td width='20%' align='center' class=''>ACCOUNT CODE</td>
										<td width='30%' align='center' class=''>DR/CR</td>
										<td width='20%' align='right' class=''>AMOUNT</td>
									</tr>
						 ";
				 
			$tranDetailsForEditData = array();
			$tranDetailsForEditData 	= $this->getTrialBalanceTable()->fetchTranDetailsForAccEdit($branchName,$cond);
			$class 			= '';
			$i  			= 1;
			$checked		= '';
			$display 		= '';
			foreach ($tranDetailsForEditData as $selectOption) {
					$transactionDate 	= $selectOption->DATE_FTDT;
					$voucherNumber	  	= $selectOption->VOUCHER_NO_;
					$particulars 		= $selectOption->PARTICULARS_;
					$narration		  	= $selectOption->NARRATION_;
					$debit	  			= $selectOption->DEBIT_;
					$credit 			= $selectOption->CREDIT_;
					$coaCode 			= $selectOption->COA_CODE_;
					$drawnOn 			= $selectOption->DRAWN_ON_;
					$chequeNo 			= $selectOption->CHQ_NO;
					$chequeDate			= $selectOption->CHQ_DATE;
					$fundCode			= $selectOption->FUND_ID_;
					
					$debitChecked 	= '';
					$creditChecked 	= '';
					if(!empty($debit)) {
						$amount 			= number_format($debit,2);
						$alignment 			= "left";
						$debitChecked 		= 'checked';
						$totalDebitBalance	+= $debit;
					} else {
						$amount 			= number_format($credit,2);
						$alignment 			= "right";
						$creditChecked 		= 'checked';
						$totalCreditBalance	+= $credit;
					}
					
					if(substr($coaCode,0,3) == '303'){
						$chequeNo   		= '';
						$chequeDate    		= '';
						$display 			= '';
						$display = "
									<tr valign='top' id='transDetails{$i}'>
										<td colspan='4'>
											Drawn on : <input name='drawnOn{$i}' class='FormTextTypeInput' id='drawnOn{$i}' style='width:150px;' type='text' value = '$drawnOn' onkeyup='bankNameSuggestForCashInHand(this.value,this.id);' />
										</td>
									</tr>";
					} else if(substr($coaCode,0,3) == '304') {
						$chequeNo   		= $chequeNo;
						$chequeDate    		= $chequeDate;
						$display 			= '';
						$display = "
									<tr valign='top' id='transDetails{$i}'>
										<td colspan='4'>
											<div style='position:relative; display:block;' id='showCustBankName'>
											Drawn on : <input name='drawnOn{$i}' class='FormTextTypeInput' id='drawnOn{$i}' style='width:150px;' type='text' value = '$drawnOn' onkeyup='bankNameSuggestForCashAtBank(this.value,this.id);' /><div id='BankNameSuggestions' style='display:none; width:205px; height:205px;' class='ClassempIdSuggestions'><div id='BankNameSuggestionsList' class='ClasssuggestingEmpIdList'></div></div>&nbsp;&nbsp;
											Instrument No. : <input maxlength='20' class='FormNumericTypeInput' type='text'  id='chequeNo{$i}' name='chequeNo{$i}' style='text-align:left;' value='$chequeNo'/>&nbsp;&nbsp;&nbsp;
											Date : <input value='$chequeDate' name='chq_date{$i}' class='FormDateTypeInput' id='chq_date{$i}' type='text'  onclick='$(\"#chq_date{$i}\").datepicker({dateFormat : \"dd-mm-yy\"});' value='{$chequeDate}'/>&nbsp;&nbsp;&nbsp;</div>
										</td>
									</tr>";
					}
									
					if($i%2==0) {
						$class	= "evenRow";
					} else {
						$class	= "oddRow";
					}
					$generateTable .="<tr valign='top' >
										<td align='left' class=''>
												<input name='coa_head{$i}' type='text'  id='coa_head{$i}' style='width:320px;' value='{$particulars}' autocomplete='off' onclick='if($(\"#portfolioCode\").val()==\"\"){ alert(\"Please select branch!\");  $(\"#portfolioCode\").focus();}'/>
										</td>
										<td align='center' class=''><div id='coa_codeshow{$i}'>{$coaCode}</div><input type='hidden' readonly='readonly' style='background-color:#999' name='coa_code{$i}' id='coa_code{$i}' size='20' value='{$coaCode}'/></td>
										<td align='center'>
											<input type='radio' onclick='DrCrAmountAlign($i); totalDRCRShow();' name='accountType{$i}' id='accountTypeDebit{$i}' value='D' $debitChecked/>Dr
											<input type='radio' onclick='DrCrAmountAlign($i); totalDRCRShow();' name='accountType{$i}' id='accountTypeCredit{$i}' value='C' $creditChecked/>Cr
										</td>
										<td><input type='text' name='amount{$i}' id='amount{$i}' onkeyup='removeChar(this);' size='15' style='text-align:{$alignment}' onfocus='if(this.value==\"0.00\") this.value=\"\";' onblur='numberFormat(\"amount{$i}\",this.value,2,\",\",\".\"); if((this.value==\"\") || (this.value==0)) this.value=\"0.00\"; AddTabRow(); totalDRCRShow();' value=\"{$amount}\" /></td>
										{$display}
									</tr>";	
					$i++;
					$display = '';
				
			}
			$generateTable .="<tr valign='top'>
						<td align='left' class=''>
								<input name='coa_head{$i}' type='text'  id='coa_head{$i}' style='width:320px;' value='' autocomplete='off' onclick='if($(\"#BRANCH_ID\").val()==\"\"){ alert(\"Please select fund!\");  $(\"#BRANCH_ID\").focus();}' />
						</td>
						<td align='center' class=''><div id='coa_codeshow{$i}'></div><input type='hidden' readonly='readonly' style='background-color:#999' name='coa_code{$i}' id='coa_code{$i}' size='20'/></td>
						<td  align='center' class=''>
							<input type='radio' onclick='DrCrAmountAlign($i); totalDRCRShow();' name='accountType{$i}' id='accountTypeDebit{$i}' value='D'/>Dr
							<input type='radio' onclick='DrCrAmountAlign($i); totalDRCRShow();' name='accountType{$i}' id='accountTypeCredit{$i}' value='C'/>Cr
						</td>
						<td align='left' class=''><input type='text' name='amount{$i}' id='amount{$i}' onkeyup='removeChar(this);' size='15' style='text-align:right' onfocus='if(this.value==\"0.00\") this.value=\"\";' onblur='numberFormat(\"amount{$i}\",this.value,2,\",\",\".\"); if((this.value==\"\") || (this.value==0)) this.value=\"0.00\"; AddTabRow(); totalDRCRShow();' value=\"0.00\" /></td>
					</tr>";
			$i++;			
			$generateTable .= "
								</table>
								<table align='center' border='0' cellpadding='3' cellspacing='1' width='95%' class='tablesorter' style='font-size:75%'>
								<tr valign='top' style='font-weight:bold;'>
									<td width='75%' colspan='3' align='right'>&nbsp;</td>
									<td width='25%' align='left'>______________</td>
							    </tr>
								<tr valign='top' style='font-weight:bold;'>
									<td width='75%' colspan='3'  align='right' class=''>Total Debit : </td>
									<td width='25%'  align='left' class=''><span id='DRTOTAL'>".number_format($totalDebitBalance,2)."</span></td>
								</tr>
								<tr valign='top' style='font-weight:bold'>
									<td width='75%' colspan='3' align='right'>&nbsp;</td>
									<td width='25%' align='right'>______________</td>
							    </tr>
								<tr valign='top' style='font-weight:bold;' >
									<td width='75%'  align='right' colspan='3'>Total Credit : </td>
									<td width='25%'  align='right'><span id='CRTOTAL'>".number_format($totalCreditBalance,2)."</span></td>
								</tr>
								<tr valign='top' >
									<td  align='left' valign='top' class='' colspan='5'>
										<table width='100%' border='0' cellspacing='2' cellpadding='2'>
										   <tr valign='top'  style='font-weight:bold; text-align:center;' >
												<td width='13%' align='left' class=''>Particular</td>
												<td width='1%' align='left' class=''> : </td>
												<td width='86%' align='left' class=''>
												<textarea style='width:600px;' rows='3' cols='100' name='particulars' id='particulars'>{$narration}</textarea>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr valign='top'>
									<td align='center' colspan='5'>
									<input type='hidden' name='fundCode' id='fundCode' value='{$fundCode}'/>
									<input type='hidden' name='effectedAtBank' id='effectedAtBank' value='n'/>
									<input type='hidden' name='frm' id='frm' value='{$frm}'/>
									<input type='hidden' name='existingVoucherNumber' id='existingVoucherNumber' value='{$voucherNumber}'/>
									<input type='hidden' name='totalDrAmount' id='totalDrAmount' value='0'/>
									<input type='hidden' name='totalCrAmount' id='totalCrAmount' value='0'/>
									<input type='hidden' name='NumberOfRows' id='NumberOfRows' value='{$i}'/>
									<input type='submit' name='insertPayment' id='insertPayment' value='Submit' onclick='return doValidationAccountsEditForm();'/>
									<input type='reset' name='reset' id='reset' value='Reset' /></td>
								</tr>
							 ";
			$generateTable .= "</tbody></table>";
			//echo $generateTable;
			echo json_encode($generateTable);exit;
		}
		
		public function getCoaTable() {
			if(!$this->coaTable) {
				$sm = $this->getServiceLocator();
				$this->coaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->coaTable;
		}
		
		public function getInvestorManagementTable() {
			if(!$this->investorManagementTable) {
				$sm = $this->getServiceLocator();
				$this->investorManagementTable = $sm->get('InvestorService\Model\InvestorManagementTable');
			}
			return $this->investorManagementTable;
		}
		
		public function getVoucherTable() {
			if(!$this->voucherTable) {
				$sm = $this->getServiceLocator();
				$this->voucherTable = $sm->get('Accounts\Model\VoucherTable');
			}
			return $this->voucherTable;
		}
		
		public function getInvestorFundTable() {
			if(!$this->investorFundTable) {
				$sm = $this->getServiceLocator();
				$this->investorFundTable = $sm->get('InvestorService\Model\InvestorFundTable');
			}
			return $this->investorFundTable;
		}
		public function getCompanyTable() {
			if(!$this->companyTable) {
				$sm = $this->getServiceLocator();
				$this->companyTable = $sm->get('Company\Model\CompanyTable');
			}
			return $this->companyTable;
		}
		public function getTrialBalanceTable() {
			if(!$this->trialBalanceTable) {
				$sm = $this->getServiceLocator();
				$this->trialBalanceTable = $sm->get('Accounts\Model\TrialBalanceTable');
			}
			return $this->trialBalanceTable;
		}
		public function getBranchTable() {
			if(!$this->branchTable) {
				$sm = $this->getServiceLocator();
				$this->branchTable = $sm->get('CompanyInformation\Model\BranchTable');
			}
			return $this->branchTable;
		}
	}
?>