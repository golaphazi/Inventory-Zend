<?php
	namespace Accounts\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Accounts\Form\PaymentEntryForm;
	
	use Accounts\Model\Master;
	use Accounts\Model\Child;
	use Accounts\Model\Voucher;
	use Accounts\Model\PaymentTransaction;
	
	use Zend\Session\Container as SessionContainer;
	
	class PaymentEntryController extends AbstractActionController {
		protected $dbAdapter;
		protected $coaTable;
		protected $voucherTable;
		protected $masterTable;
		protected $childTable;
		protected $trialBalanceTable;
		protected $companyTable;
		protected $moneyMarketOrganizationTable;
		protected $paymentTransactionTable;
		protected $supplierInformationTable;
		protected $retailerInformationTable;
		
		function returnDates($fromdate, $todate) {
			$fromdate = \DateTime::createFromFormat('d-m-Y', $fromdate);
			$todate = \DateTime::createFromFormat('d-m-Y', $todate);
			return new \DatePeriod(
				$fromdate,
				new \DateInterval('P1D'),
				$todate->modify('+1 day')
			);
		}
		
		public function indexAction() {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("d-m-Y", strtotime($businessDate));
			
			$userInfo 						= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID						= $userInfo->id;
			
			$this->layout()->leftMenu 		= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Accounts',$USER_ID);
			$this->layout()->controller 	= $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 	= $this->getRequest();
			$form 		= new PaymentEntryForm('paymententry', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			
			$form->get('submit')->setValue('Add');
			
			if($request->isPost()) {
				$form->setData($request->getPost());
				$postedData 			= $request->getPost();
				//echo "<pre>"; print_r($postedData); die();
				$v_voucher_no_in_out  		= '';
				$v_max_transaction_no 		= 0;
				$v_chq_effected_dt    		= '';
				$v_chq_dt             		= '';
				$v_voucher_type       		= '';
				$v_temp_voucher_type  		= '';
				$v_temp_voucher_no    		= '';
				
				$msg = '';
				$voucher 		= new Voucher();
				$voucherData	= array();
				
				$tm_drawn_on		= (isset($postedData["drawnOn"])) ? $postedData["drawnOn"]:'';
				$tm_auto_tran 		= $postedData["AUTO_TRANSACTION"];
				
				$transStatus   		= (int) 0;
				$msg				= '';
				$flag				= 0;
				$tmTransectionDate	= date("d-m-Y", strtotime($postedData["TRANSACTION_DATE"]));
				$tmNtr              = 'D';
				
				$tmCbjt 		    = '';
				$tmCbCOACode        = '';
				$effected_bank_tran	= '';
				$tmChequeNo 	    = '';
				$tmChqDate 	        = '';
				
				$tcAccCodes       	= array();
				$tcNtrs           	= array();
				$tcNarrations     	= array();
				$tcAmounts        	= array();
				$tcAccCode		  	= '';
				$tcAmount		 	= '';
				$tcNarration	  	= '';
				$tcBranchId			= $postedData["BRANCH_ID"];
				$tcNarration 		= $postedData["PARTICULARS"];
				
				$cbCode				= $postedData["cb_code"];
				$tmInvoiceNo		= $postedData["INVOICE_NO"];
				$tmMoneyReceiptNo 	= $postedData["MONEY_RECEIPT_NO"];
				$paymentTo			= $postedData["PAYMENT_TO"];
				$backDateFlag			= '';
				$transactionMonthYear = date("m-Y", strtotime($tmTransectionDate));
				$businessMonthYear 	  = date("m-Y", strtotime($postedData["chq_date"]));
				if($transactionMonthYear < $businessMonthYear ) {
					$backDateFlag = 'Y';
				}
				
				for($i = 1; $i < $postedData->NUMBER_OF_ROWS; $i++) {
					$coaCode	= $postedData["COA_CODE{$i}"];
					if(isset($postedData["COA_CODE{$i}"]) && isset($postedData["PAYMENT_AMOUNT{$i}"])) {
						$tcAccCode	= $postedData["COA_CODE{$i}"];
						$tcAmount   = $postedData["PAYMENT_AMOUNT{$i}"];
						$tcNtr		= (isset($postedData["VOUCHER_TYPE{$i}"])) ? $postedData["VOUCHER_TYPE{$i}"]:'';
						if(!empty($tmTransectionDate) 
						   && !empty($tcAccCode) 
						   && !empty($tcBranchId) 
						   && !empty($tcAmount)
						   && !empty($tcNtr)) {
							   
							if(substr($tcAccCode,0,3) == '303'){
								$tmCbjt  			= 'C';
								$tmCbCOACode    	= $tcAccCode;
							} else if(substr($tcAccCode,0,3) == '304') {
								$tmCbjt       		= 'B';
								$tmCbCOACode        = $tcAccCode;
								$effected_bank_tran	= $postedData["EFFECTED_AT_BANK"];
								$tmChequeNo   		= (isset($postedData["chequeNo"])) ? $postedData["chequeNo"]:'';
								$tmChqDate			= date("d-m-Y", strtotime($postedData["chq_date"]));
							} else {
								$tcAccCodes[]		= $tcAccCode;
								$tcNtrs[]       	= $tcNtr;
								$tcNarrations[] 	= $tcNarration;
								$tcAmounts[]    	= str_replace(",", "", $tcAmount);   
							}
							$flag 					= 1;
						}
					}
				}
				//echo "<pre>"; print_r($tcAccCodes); print_r($tcNtrs); print_r($tcAmounts); print_r($tcNarrations); die();
				
				// General Accounts Entry Start BY Akhand
				$v_tm_tran_dt_in        	= $tmTransectionDate;
				$v_tm_drown_on_in       	= $tm_drawn_on;
				$v_tm_auto_tran_in     	 	= $tm_auto_tran;
				$v_tm_ntr_in            	= $tmNtr;
				$v_tm_cbjt_in           	= $tmCbjt;
				$v_tm_cb_code_in        	= $tmCbCOACode;
				$v_tm_chq_no_in         	= $tmChequeNo;
				$v_tm_chq_dt_in         	= $tmChqDate;
				
				$v_tm_invoice_no_in			= $tmInvoiceNo;
				$v_tm_money_receipt_no_in	= $tmMoneyReceiptNo;
				$v_cb_code_cond				= $cbCode;
				$v_back_date_flag			= $backDateFlag;
				
				$v_business_date_in			= $tmTransectionDate;
				$v_tc_branch_id_in			= $tcBranchId;
				
				$v_tm_user_id_in			= '';
				$v_effected_bank_tran_in	= $effected_bank_tran;
				$v_trans_status_in_out		= $transStatus;
				$v_msg_out					= $msg;

				$v_tc_ac_code_in			= $tcAccCodes;
				$v_tc_ntr_in				= $tcNtrs;
				$v_tc_narration_in			= $tcNarrations;
				$v_tc_amount_in				= $tcAmounts;
				
				if($v_effected_bank_tran_in = 'y') {
					$v_chq_effected_dt 	= $v_tm_tran_dt_in;
				}
				
				if($v_tm_ntr_in == '') {
					$v_voucher_type 		= 'JV';
				} else {
					if($v_tm_ntr_in == 'D') {
						if ($v_cb_code_cond == 'bank') {
							$v_voucher_type = 'BP';
						} else if($v_cb_code_cond == 'cash') {
							$v_voucher_type = 'CP';
						} else {
							$v_voucher_type = 'CV';
						}
					} else {
						if ($v_cb_code_cond == 'bank') {
							$v_voucher_type = 'BR';
						}else if ($v_cb_code_cond == 'cash') {
							$v_voucher_type = 'CR';
						} else {
							$v_voucher_type = 'CV';
						}
					}		
				}
				
				$voucher 		= new Voucher();
				$master 		= new Master();
				$child		 	= new Child();				
				$paymenttransactionData = array();
				// Voucher Table Data Insert Start BY Akhand
				$voucherData		= array();					
				$voucherData		= array(
					'BRANCH_ID' 			=> $v_tc_branch_id_in,
					'V_YEAR' 				=> date("Y", strtotime($tmTransectionDate)),
					'DEBIT_VOUCHER' 		=> $v_voucher_type,
				);
				$voucher->exchangeArray($voucherData);
				//echo "<pre>"; print_r($voucher);die();
				if($returnVoucher 	= $this->getVoucherTable()->saveVoucher($voucher)) {
					$msg			= $returnVoucher;
					// Master Table Data Insert Start BY Akhand
					$masterData		= array();
					$masterData 	= array(
						'TRAN_NO'				=> '',
						'TRAN_DATE' 			=> $v_tm_tran_dt_in,				
						'VOUCHER_NO' 			=> $returnVoucher,
						'NTR' 					=> $v_tm_ntr_in,
						'CBJT' 					=> $v_tm_cbjt_in,
						'CB_CODE' 				=> $v_tm_cb_code_in,
						'CHEQUE_NO' 			=> $v_tm_chq_no_in,
						'CHEQUE_DATE' 			=> $v_tm_chq_dt_in,
						'EFFECTED_DATE' 		=> $v_chq_effected_dt,
						'RECONCILIATION_FLAG' 	=> $v_effected_bank_tran_in,
						'RECORD_DATE' 			=> '',
						'DRAWN_ON' 				=> $v_tm_drown_on_in,
						'AUTO_TRANSACTION_FLAG' => $v_tm_auto_tran_in,
						'INVOICE_NO' 			=> $v_tm_invoice_no_in,
						'MONEY_RECEIPT_NO' 		=> $v_tm_money_receipt_no_in,
						'BACK_DATE' 			=> $v_back_date_flag,
						'OPERATE_BY' 			=> $v_tm_user_id_in,
					);
					$master->exchangeArray($masterData);
					//echo "<pre>"; print_r($master);die();
					if($returnTransactionNo=$this->getMasterTable()->saveMaster($master)) {												
						for($i=0;$i<sizeof($tcAccCodes);$i++) {
							// Child Table Data Insert Start BY Akhand
							$childData		= array();
							$childData 	= array(
								'TRAN_NO'		=> $returnTransactionNo,
								'AC_CODE' 		=> $tcAccCodes[$i],				
								'BRANCH_ID' 	=> $v_tc_branch_id_in,
								'NTR' 			=> $tcNtrs[$i],	
								'CBJT' 			=> $v_tm_cbjt_in,
								'CB_CODE' 		=> $v_tm_cb_code_in,
								'NARRATION' 	=> $tcNarrations[$i],	
								'AMOUNT' 		=> $tcAmounts[$i],	
								'RECORD_DATE' 	=> '',
							);
							$child->exchangeArray($childData);
							//echo "<pre>"; print_r($child); die();
							if($this->getChildTable()->saveChild($child)) {
								if($paymentTo == 'pts'){
									$SUPPLIER_INFO_ID = $this->getSupplierInformationTable()->fetchSupplierIDForPaymentReceipt($tcAccCodes[$i],'payable');	
									$paymenttransaction = new PaymentTransaction();
									$paymenttransactionData['SUPPLIER_INFO_ID'] 	= $SUPPLIER_INFO_ID;
									$paymenttransactionData['TRANSACTION_FLAG'] 	= 'paid';
									$paymenttransactionData['BRANCH_ID'] 			= $tcBranchId;
									$paymenttransactionData['AMOUNT']				= str_replace(",", "", $tcAmount);							
									$paymenttransaction->exchangeArray($paymenttransactionData);
									$success = $this->getPaymentTransactionTable()->savePaymentTransaction($paymenttransaction);
								} else if($paymentTo == 'ptr'){
									$returnData= $this->getRetailerInformationTable()->fetchRetailerIDForPaymentReceipt($tcAccCodes[$i],'receivable');									
									$paymenttransaction = new PaymentTransaction();
									$paymenttransactionData['RETAILER_ID'] 			= $returnData['RETAILER_ID'];
									$paymenttransactionData['ZONE_ID']	 			= $returnData['ZONE_ID'];
									$paymenttransactionData['EMPLOYEE_ID']	 		= $returnData['EMPLOYEE_ID'];
									$paymenttransactionData['TRANSACTION_FLAG'] 	= 'paid';
									$paymenttransactionData['BRANCH_ID'] 			= $tcBranchId;
									$paymenttransactionData['AMOUNT']				= str_replace(",", "", $tcAmount);							
									$paymenttransaction->exchangeArray($paymenttransactionData);
									$success = $this->getPaymentTransactionTable()->savePaymentTransaction($paymenttransaction);
								} else if($paymentTo == 'pto'){
									
								}
								$success	= true;
							} else {
								$success	= false;
								break;
							}
							// Child Table Data Insert End BY Akhand
						}
					} else {
						$success	= false;
						break;
					}
					// Master Table Data Insert Start BY Akhand
				} else {
					$success	= false;
					//break;
				}
				// Voucher Table Data Insert End BY Akhand
				if($success) {
					$datePeriod = $this->returnDates($tmTransectionDate, $businessDate);
					$totDate = array();
					foreach($datePeriod as $date) {
						$totDate[] = $date->format('d-m-Y');
					}
					//echo "<pre>"; print_r($totDate);die();
					for($i=0;$i<sizeof($totDate);$i++){
						$bDate = $totDate[$i];
						if($this->getTrialBalanceTable()->updateTrialBalance($bDate)) {
							$success	= true;
						} else {
							$success	= false;
						}
					}
					
					if($success) {
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																<tr class='valid_msg'>
																	<td width='100%' style='text-align:center;'>Payment entry successfully saved.".$msg.'&nbsp;<a onclick="if(confirm(\'Are you sure you want to print this vourcher no. '.substr($msg,-16).' \')){return true;} else {return false;};" href="/branchvoucherprint/branchvoucherprint?tranDateFrom=&tranDateTo=&vType=&voucherData='.substr($msg,-16).'&branchID='.$postedData["BRANCH_ID"].'" target="_blank">Print this Voucher</a>'."</td>																
																</tr>
															</table>");	
						return $this->redirect()->toRoute('paymententry');
					} else {
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																<tr class='error_msg'>
																	<td width='100%' style='text-align:center;'>Payment entry couldn't saved.".$msg.'&nbsp;<a onclick="if(confirm(\'Are you sure you want to print this vourcher no. '.substr($msg,-16).' \')){return true;} else {return false;};" href="/branchvoucherprint/branchvoucherprint?tranDateFrom=&tranDateTo=&vType=&voucherData='.substr($msg,-16).'&branchID='.$postedData["BRANCH_ID"].'" target="_blank">Print this Voucher</a>'."</td>																
																</tr>
															</table>");	
						return $this->redirect()->toRoute('paymententry');	
					}
				} else {
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
															<tr class='error_msg'>
																<td width='100%' style='text-align:center;'>Payment entry couldn't saved.".$msg.'&nbsp;<a onclick="if(confirm(\'Are you sure you want to print this vourcher no. '.substr($msg,-16).' \')){return true;} else {return false;};" href="/branchvoucherprint/branchvoucherprint?tranDateFrom=&tranDateTo=&vType=&voucherData='.substr($msg,-16).'&branchID='.$postedData["BRANCH_ID"].'" target="_blank">Print this Voucher</a>'."</td>																
															</tr>
														</table>");
					return $this->redirect()->toRoute('paymententry');
				}
				// General Accounts Entry End BY Akhand
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
			$IPAData = $this->getInvestorManagementTable()->fetchInvestorName($input,$cond);			
			foreach ($IPAData as $selectOption) {
				$ipaNo = $selectOption['PORTFOLIO_CODE'];
				$investorName = $selectOption['INVESTOR_NAME'];
				$str .= "<div align='left' onClick=\"fill_id_code('".$ipaNo."','".$investorName."');\"><b>".$ipaNo.'('.$investorName.')'."</b></div>";
			}
			//echo json_encode($str);
			echo $str;
			exit;
		}
		
		public function getSuggestNarrationAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$cond = '';
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getChildTable()->fetchGeneralAccountsNarration($input,$cond);			
			foreach ($IPAData as $selectOption) {
				$narration = $selectOption['NARRATION'];
				$str .= "<div align='left' onClick=\"fill_narration('".$narration."');\">".$narration."</div>";
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
			$str = '';
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
						//$str .= "<div align='left'>".$row->P_COA_NAME."</div>";
					}
				}
				//echo '<pre>';print_r($data);die();
				//echo json_encode($data);exit;
				//echo $data;
				//echo json_encode($str);
			//}
		}
		public function getAccountsHeadAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower( $_REQUEST['no'] );
			$str='';
			$investorInfoArray 	= array();
			if(!empty($input)){
				$IPAData = $this->getCoaTable()->fetchCOAName($input);			
				foreach ($IPAData as $selectOption) {
					$coaCode = $selectOption['COA_CODE'];
					$coaHead = $selectOption['COA_NAME'];
					$coaCodeHead = $coaCode.",".$coaHead;
					
					if(!empty($coaCode)){
						$str = $coaCodeHead;
					}else{
						$str = '0';
					}
					
				}
			}else{
				$IPAData = $this->getCoaTable()->fetchCOAName($input='');			
				foreach ($IPAData as $selectOption) {
					$coaCode = $selectOption['COA_CODE'];
					$coaHead = $selectOption['COA_NAME'];
					$coaCodeHead = $coaCode.",".$coaHead;
					$str .= "<option value='".$coaHead."'></option>";
				}
			}
			
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
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
		
		public function patmentChequePrintAction() {
			$payto = $_REQUEST['payto'];
			$amount = $_REQUEST['amount'];
			$chequeDate 	= date('d-m-Y',strtotime($_REQUEST['date']));
			$chequeDateTemp	= explode("-",$chequeDate);
			$cDay = $chequeDateTemp[0];
			$cMonth = $chequeDateTemp[1];
			$cYear = $chequeDateTemp[2];
			$data = array();
			$data = array(
							'payto' 	=> $payto,
							//'chequeNo' 		=> $chequeNo,
							'chequeDate' 	=> $cDay.''.$cMonth.''.$cYear,
							'amount' 		=> $amount,
							'amountInWords' => (ucwords($this->convertNumber($amount)))
						);
			$table = '';
			$table .= '<table width="100%" border="0" cellspacing="2" cellpadding="2" style="border: 0.5px dotted; font-family:Tahoma;margin:0px auto 0px auto;font-size:100%;height:280px; background:url(\'../../img/chequePageFormat.png\') no-repeat;">
						<tr valign="top">
							<td colspan="2" align="right" style="padding-right:85px;padding-top:30px;letter-spacing:6.5px">
								<b>'.$data['chequeDate'].'</b>
							</td>
						</tr>
						<tr>
							<td colspan="2" align="left" style="padding-left:80px;padding-top:40px;">
								'.ucwords($data['payto']).'
							</td>
						</tr>
						<tr>
							<td width="60%" align="left" style="padding-left:120px;padding-bottom:120px;">
								'.$data['amountInWords'].'
							</td>
							<td width="40%" align="right" style="padding-right:180px;padding-bottom:120px;">
								'.number_format($data['amount'],2).'
							</td>
						</tr>		
						';
			$table .= "<table>";
			//return array('data' => $data,'reportHead' => '');
			echo $table;exit;
		}
		
		public function getCoaTable() {
			if(!$this->coaTable) {
				$sm = $this->getServiceLocator();
				$this->coaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->coaTable;
		}
		
		public function getMoneyMarketOrganizationTable() {
			if(!$this->moneyMarketOrganizationTable) {
				$sm = $this->getServiceLocator();
				$this->moneyMarketOrganizationTable = $sm->get('GlobalSetting\Model\MoneyMarketOrganizationTable');
			}
			return $this->moneyMarketOrganizationTable;
		}
	
		public function getVoucherTable() {
			if(!$this->voucherTable) {
				$sm = $this->getServiceLocator();
				$this->voucherTable = $sm->get('Accounts\Model\VoucherTable');
			}
			return $this->voucherTable;
		}
		
		public function getMasterTable() {
			if(!$this->masterTable) {
				$sm = $this->getServiceLocator();
				$this->masterTable = $sm->get('Accounts\Model\MasterTable');
			}
			return $this->masterTable;
		}
		public function getPaymentTransactionTable() {
			if(!$this->paymentTransactionTable) {
				$sm = $this->getServiceLocator();
				$this->paymentTransactionTable = $sm->get('Accounts\Model\PaymentTransactionTable');
			}
			return $this->paymentTransactionTable;
		}
		
		public function getChildTable() {
			if(!$this->childTable) {
				$sm = $this->getServiceLocator();
				$this->childTable = $sm->get('Accounts\Model\ChildTable');
			}
			return $this->childTable;
		}
		
		public function getTrialBalanceTable() {
			if(!$this->trialBalanceTable) {
				$sm = $this->getServiceLocator();
				$this->trialBalanceTable = $sm->get('Accounts\Model\TrialBalanceTable');
			}
			return $this->trialBalanceTable;
		}
		public function getSupplierInformationTable() {
			
			if(!$this->supplierInformationTable) {
				$sm = $this->getServiceLocator();
				$this->supplierInformationTable = $sm->get('LocalSetting\Model\SupplierInformationTable');
			}
			return $this->supplierInformationTable;
		}
		public function getRetailerInformationTable() {
			
			if(!$this->retailerInformationTable) {
				$sm = $this->getServiceLocator();
				$this->retailerInformationTable = $sm->get('LocalSetting\Model\RetailerInformationTable');
			}
			return $this->retailerInformationTable;
		}
		
		function convertNumber($num) {
		   $temp = explode(".", $num);
		   $num  = (isset($temp[0])) ? $temp[0] : 0;
		   $dec  = (isset($temp[1])) ? $temp[1] : 0;	
		   $output = "";	 
		   if($num{0} == "-")
		   {
			  $output = "negative ";
			  $num = ltrim($num, "-");
		   }
		   else if($num{0} == "+")
		   {
			  $output = "positive ";
			  $num = ltrim($num, "+");
		   }
		   
		   if($num{0} == "0")
		   {
			  $output .= "zero";
		   }
		   else
		   {
			  $num = str_pad($num, 36, "0", STR_PAD_LEFT);
			  $group = rtrim(chunk_split($num, 3, " "), " ");
			  $groups = explode(" ", $group);
		
			  $groups2 = array();
			  foreach($groups as $g) $groups2[] = $this->convertThreeDigit($g{0}, $g{1}, $g{2});
		
			  for($z = 0; $z < count($groups2); $z++)
			  {
				 if($groups2[$z] != "")
				 {
					$output .= $groups2[$z].$this->convertGroup(11 - $z).($z < 11 && !array_search('', array_slice($groups2, $z + 1, -1))
					 && $groups2[11] != '' && $groups[11]{0} == '0' ? " and " : ", ");
				 }
			  }
		
			  $output = rtrim($output, ", ");
		   }
		
		   if($dec > 0)
		   {
			  $output .= " point";
			  for($i = 0; $i < strlen($dec); $i++) $output .= " ".$this->convertDigit($dec{$i});
		   }
		
		   return $output;
		}
		
		function convertGroup($index) {
		   switch($index)
		   {
			  case 11: return " decillion";
			  case 10: return " nonillion";
			  case 9: return " octillion";
			  case 8: return " septillion";
			  case 7: return " sextillion";
			  case 6: return " quintrillion";
			  case 5: return " quadrillion";
			  case 4: return " trillion";
			  case 3: return " billion";
			  case 2: return " million";
			  case 1: return " thousand";
			  case 0: return "";
		   }
		}
		
		function convertThreeDigit($dig1, $dig2, $dig3) {
		   $output = "";
		
		   if($dig1 == "0" && $dig2 == "0" && $dig3 == "0") return "";
		
		   if($dig1 != "0")
		   {
			  $output .= $this->convertDigit($dig1)." hundred";
			  if($dig2 != "0" || $dig3 != "0") $output .= " and ";
		   }
		
		   if($dig2 != "0") $output .= $this->convertTwoDigit($dig2, $dig3);
		   else if($dig3 != "0") $output .= $this->convertDigit($dig3);
		
		   return $output;
		}
		
		function convertTwoDigit($dig1, $dig2) {
		   if($dig2 == "0")
		   {
			  switch($dig1)
			  {
				 case "1": return "ten";
				 case "2": return "twenty";
				 case "3": return "thirty";
				 case "4": return "forty";
				 case "5": return "fifty";
				 case "6": return "sixty";
				 case "7": return "seventy";
				 case "8": return "eighty";
				 case "9": return "ninety";
			  }
		   }
		   else if($dig1 == "1")
		   {
			  switch($dig2)
			  {
				 case "1": return "eleven";
				 case "2": return "twelve";
				 case "3": return "thirteen";
				 case "4": return "fourteen";
				 case "5": return "fifteen";
				 case "6": return "sixteen";
				 case "7": return "seventeen";
				 case "8": return "eighteen";
				 case "9": return "nineteen";
			  }
		   }
		   else
		   {
			  $temp = $this->convertDigit($dig2);
			  switch($dig1)
			  {
				 case "2": return "twenty-$temp";
				 case "3": return "thirty-$temp";
				 case "4": return "forty-$temp";
				 case "5": return "fifty-$temp";
				 case "6": return "sixty-$temp";
				 case "7": return "seventy-$temp";
				 case "8": return "eighty-$temp";
				 case "9": return "ninety-$temp";
			  }
		   }
		}
			  
		function convertDigit($digit) {
		   switch($digit)
		   {
			  case "0": return "zero";
			  case "1": return "one";
			  case "2": return "two";
			  case "3": return "three";
			  case "4": return "four";
			  case "5": return "five";
			  case "6": return "six";
			  case "7": return "seven";
			  case "8": return "eight";
			  case "9": return "nine";
		   }
		}
	}
?>