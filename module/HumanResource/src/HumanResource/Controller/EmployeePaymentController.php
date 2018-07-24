<?php
	namespace HumanResource\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use HumanResource\Form\EmployeePaymentForm;
	
	use Accounts\Model\TrialBalanceTable;
	use Accounts\Model\Master;
	use Accounts\Model\Child;
	use Accounts\Model\Voucher;
	
	use Zend\Session\Container as SessionContainer;
	
	class EmployeePaymentController extends AbstractActionController {
		protected $dbAdapter;
		protected $branchTable;
		protected $coaTable;
		protected $voucherTable;
		protected $masterTable;
		protected $childTable;
		protected $trialBalanceTable;
		protected $employeePersonalInfoTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$request = $this->getRequest();
			$form = new EmployeePaymentForm('employeepayment', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Employee Payment');
			
			if($request->isPost()) {
				$postedData		= $request->getPost();
				//echo "<pre>"; print_r($postedData); die();
				
				$AMOUNT				= str_replace(",", "",$postedData['AMOUNT']);
				$PAYABLE_COA_NAME	= $postedData['PAYABLE_COA_NAME'];
				$PAYABLE_COA_CODE	= $postedData['PAYABLE_COA_CODE'];
				$EMPLOYEE_ID		= $postedData['EMPLOYEE_ID'];
				$EMPLOYEE_NAME		= $postedData['EMPLOYEE_NAME'];
				
				$this->getEmployeePersonalInfoTable()->transectionStart();
				
				$chartOfAccountDisplayPayment	= '';
				$voucherTypeIndex				= 0;
				$oddClass						= "#EEE9E9";
				$evenClass						= "#F7F4F4";
				if($AMOUNT>0) {
					$this->session 				= new SessionContainer('post_supply');
					$businessDate				= date("Y-m-d", strtotime($this->session->businessdate));
					$voucherTypeIndex++;
					
					$chartOfAccountDisplayPayment	= "
					<table cellspacing='0' class='vtbl' >
						<tr class='head'>
							<td colspan='4' align='center'>
								Payment Voucher for Employee ".$EMPLOYEE_NAME."
							</td>
						</tr>
						<tr class='head'>
							<td width='55%' align='left'>Account Head</td>
							<td width='15%' align='center'>Account Code</td>
							<td width='15%' align='center'>Dr/Cr</td>
							<td width='15%' align='right'>Amount</td>
						</tr>
					";
					
					$CASH_IN_HAND_COA_NAME			= 'Cash in Hand';
					$CASH_IN_HAND_COA_CODE			= '303001001';
					
					$CASH_IN_HAND_DEBIT_CHECKED		= "";
					$CASH_IN_HAND_CREDIT_CHECKED	= "checked='checked'";
					$CASH_IN_HAND_DEBIT_DISABLED	= "disabled='disabled'";
					$CASH_IN_HAND_CREDIT_DISABLED	= "";
					$CASH_IN_HAND_AMOUNT_ALIGN		= "right";
					$CASH_IN_HAND_VOUCHER_TYPE		= "C";
					
					$PAYMENT_VOUCHER_COA_CODE['COA_CODE'][]			= $CASH_IN_HAND_COA_CODE;
					$PAYMENT_VOUCHER_COA_CODE['VOUCHER_TYPE'][]		= $CASH_IN_HAND_VOUCHER_TYPE;
					$PAYMENT_VOUCHER_COA_CODE['VOUCHER_AMOUNT'][]	= abs($AMOUNT);
					
					$chartOfAccountDisplayPayment	.= "
					<tr class='{$oddClass}'>
						<td align='left'>
							{$CASH_IN_HAND_COA_NAME}
						</td>
						<td align='center'>																												
							<input type='hidden' name='COA_CODE[]' value='{$CASH_IN_HAND_COA_CODE}'/>
							{$CASH_IN_HAND_COA_CODE}
						</td>
						<td align='center'>
						  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$CASH_IN_HAND_DEBIT_CHECKED} {$CASH_IN_HAND_DEBIT_DISABLED}/>Dr 
						  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$CASH_IN_HAND_CREDIT_CHECKED} {$CASH_IN_HAND_CREDIT_DISABLED}/>Cr
						</td>
						<td align='{$CASH_IN_HAND_AMOUNT_ALIGN}'>
							<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($AMOUNT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
						</td>
					</tr>
					";
					
					$voucherTypeIndex++;
					$PAYABLE_COA_NAME		= $postedData['PAYABLE_COA_NAME'];
					$PAYABLE_COA_CODE		= $postedData['PAYABLE_COA_CODE'];
					
					$PAYMENT_DEBIT_CHECKED		= "checked='checked'";
					$PAYMENT_CREDIT_CHECKED		= "";
					$PAYMENT_DEBIT_DISABLED		= "";
					$PAYMENT_CREDIT_DISABLED	= "disabled='disabled'";
					$PAYMENT_AMOUNT_ALIGN		= "left";
					$PAYMENT_VOUCHER_TYPE		= "D";
					
					$PAYMENT_VOUCHER_COA_CODE['COA_CODE'][]			= $PAYABLE_COA_CODE;
					$PAYMENT_VOUCHER_COA_CODE['VOUCHER_TYPE'][]		= $PAYMENT_VOUCHER_TYPE;
					$PAYMENT_VOUCHER_COA_CODE['VOUCHER_AMOUNT'][]	= abs($AMOUNT);
					
					$chartOfAccountDisplayPayment	.= "
					<tr class='{$oddClass}'>
						<td align='left'>
							{$PAYABLE_COA_NAME}
						</td>
						<td align='center'>																												
							<input type='hidden' name='COA_CODE[]' value='{$PAYABLE_COA_CODE}'/>
							{$PAYABLE_COA_CODE}
						</td>
						<td align='center'>
						  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$PAYMENT_DEBIT_CHECKED} {$PAYMENT_DEBIT_DISABLED}/>Dr 
						  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$PAYMENT_CREDIT_CHECKED} {$PAYMENT_CREDIT_DISABLED}/>Cr
						</td>
						<td align='{$PAYMENT_AMOUNT_ALIGN}'>
							<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($AMOUNT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
						</td>
					</tr>
					";
					
					$chartOfAccountDisplayPayment	.= "		
						<tr class='oddClass'>
							<td align='left'>Particulars</td>
							<td align='left' colspan='3'>
								<textarea name='PARTICULARS' cols='100' rows='2'>
									Amount Paid to - ".$EMPLOYEE_NAME."
								</textarea>
							</td>
						</tr>
						<tr class='evenClass'>
							<td align='left' colspan='4'>&nbsp;</td>
						</tr>
					</table>
					";
					
					//echo $chartOfAccountDisplayPayment;die();
				
					$PARTICULARS 				= "Amount Paid to - ".$EMPLOYEE_NAME."";
					
					$v_voucher_no_in_out  		= '';
					$v_max_transaction_no 		= 0;
					$v_chq_effected_dt    		= '';
					$v_chq_dt             		= '';
					$v_voucher_type       		= '';
					$v_temp_voucher_type  		= '';
					$v_temp_voucher_no    		= '';
					
					$msg 			= '';
					$voucher 		= new Voucher();
					$voucherData	= array();
					
					$tm_drawn_on		= 'N/A';
					$tm_auto_tran 		= 'y';
					
					$transStatus   		= (int) 0;
					$msg				= '';
					$flag				= 0;
					$tmTransectionDate	= $businessDate;
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
					$tcBranchId			= 1;
					$tcNarration 		= $PARTICULARS;
					
					$cbCode					= 'cash';
					$tmInvoiceNo			= 'Auto';
					$tmMoneyReceiptNo 		= 'Auto';
					$backDateFlag			= '';
					
					
					$PAYMENT_ALL_COA_CODE		= $PAYMENT_VOUCHER_COA_CODE["COA_CODE"];
					//echo "<pre>"; print_r($ALL_COA_CODE); die();
					foreach($PAYMENT_ALL_COA_CODE as $index=>$PAYMENT_ALL_COA_CODE_VALUE) {
						$tcAccCode	= $PAYMENT_VOUCHER_COA_CODE["COA_CODE"][$index];
						$tcAmount   = str_replace(",", "", $PAYMENT_VOUCHER_COA_CODE["VOUCHER_AMOUNT"][$index]);
						$tcNtr		= $PAYMENT_VOUCHER_COA_CODE["VOUCHER_TYPE"][$index];;
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
								$effected_bank_tran	= '';
								$tmChequeNo   		= '';
								$tmChqDate			= '';
							} else {
								$tcAccCodes[]		= $tcAccCode;
								$tcNtrs[]       	= $tcNtr;
								$tcNarrations[] 	= $tcNarration;
								$tcAmounts[]    	= str_replace(",", "", $tcAmount);   
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
							} else {
								$v_voucher_type = 'CP';
							}
						} else {
							if ($v_cb_code_cond == 'bank') {
								$v_voucher_type = 'BR';
							} else {
								$v_voucher_type = 'CR';
							}
						}		
					}
					
					$voucher 		= new Voucher();
					$master 		= new Master();
					$child		 	= new Child();
					// Voucher Table Data Insert Start BY Akhand
					$voucherData		= array();					
					$voucherData		= array(
						'BRANCH_ID' 			=> $v_tc_branch_id_in,
						'V_YEAR' 				=> date("Y", strtotime($tmTransectionDate)),
						'DEBIT_VOUCHER' 		=> $v_voucher_type,
					);
					$voucher->exchangeArray($voucherData);
					//echo "<pre>"; print_r($voucher);die();
					if($returnVoucher = $this->getVoucherTable()->saveVoucher($voucher)) {
						// Master Table Data Insert Start BY Akhand
						$masterData	= array();
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
							'STOCK_ORDER_ID' 		=> '',
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
						break;
					}
				}	
			
				// Finally Suuccess Message Start By Akhand
					if($success) {
						if($this->getTrialBalanceTable()->updateTrialBalance($v_tm_tran_dt_in)) {
							$this->getEmployeePersonalInfoTable()->transectionEnd();
							$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Employee Amount Paid Successfully!</h4></td>		
																</tr>
															</table>");
						} else {
							$this->getEmployeePersonalInfoTable()->transectionInterrupted();
							$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Sorry! There is system error in accounts entry.!</h4></td>
																</tr>
															</table>");
						}
							return $this->redirect()->toRoute('employeepayment');
					} else {
						$this->getEmployeePersonalInfoTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Sorry! There is system error.!</h4></td>															
																</tr>
															</table>");
						return $this->redirect()->toRoute('employeepayment');
					}
					// Finally Suuccess Message End By Akhand
			}
			
			return array(	
							'form' => $form,
							'flashMessages' => $this->flashMessenger()->getMessages());
		}
		
		public function fetchEmployeeNameAction() {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("Y-m-d", strtotime($businessDate));
			$investorInfoArray 	= array();
			$input 	= strtolower($_REQUEST['queryString']);
			$str	= '';
			if(!empty($input)){
				
				$IPAData 			= $this->getEmployeePersonalInfoTable()->fetchEmployeeName($input);			
				foreach ($IPAData as $selectOption) {
					$EMPLOYEE_ID 		= $selectOption['EMPLOYEE_ID'];
					$LEDGER_BALANCE 	= 0;//$this->getEmployeePersonalInfoTable()->getEmployeeLedgerBlance($businessDate,$DEALER_ID);
					$EMPLOYEE_NAME 		= $selectOption['EMPLOYEE_NAME'];
					$MOBILE_NUMBER 		= $selectOption['MOBILE_NUMBER'];
					$EMPLOYEE_TYPE 		= $selectOption['EMPLOYEE_TYPE'];
					$PERMANENT_ADDRESS 	= $selectOption['PERMANENT_ADDRESS'];
					$DIVISION_NAME 		= $selectOption['DIVISION_NAME'];
					$DESIGNATION_NAME	= $selectOption['DESIGNATION'];
					$PAYABLE_COA_CODE	= $selectOption['PAYABLE_COA_CODE'];
					$PAYABLE_COA_NAME	= $selectOption['PAYABLE_COA_NAME'];
					if(!empty($EMPLOYEE_ID)){
						$str = "".$EMPLOYEE_ID.",".$LEDGER_BALANCE.",".$EMPLOYEE_NAME.",".$EMPLOYEE_TYPE.",".$PERMANENT_ADDRESS.",".$MOBILE_NUMBER.",".$DESIGNATION_NAME.",".$DIVISION_NAME.",".$PAYABLE_COA_CODE.",".$PAYABLE_COA_NAME."";
					}else{
						$str = '';
					}
					
					//$str .= "<div align='left' onClick=\"fill_id_code('".$EMPLOYEE_ID."','".$LEDGER_BALANCE."','".$EMPLOYEE_NAME."','".$EMPLOYEE_TYPE."','".$PERMANENT_ADDRESS."','".$MOBILE_NUMBER."','".$DESIGNATION_NAME."','".$DIVISION_NAME."','".$PAYABLE_COA_CODE."','".$PAYABLE_COA_NAME."');\"><b>".$EMPLOYEE_NAME."</b></div>";
				}
			}else{
				$IPAData 			= $this->getEmployeePersonalInfoTable()->fetchEmployeeName($input='');
				foreach ($IPAData as $selectOption) {
					$EMPLOYEE_ID 		= $selectOption['EMPLOYEE_ID'];					
					$EMPLOYEE_NAME 		= $selectOption['EMPLOYEE_NAME'];					
					$str .= "<option value='".$EMPLOYEE_NAME."'></option>";
					//$str .= "<option value='fff'></option>";
					
				}
			}
			
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		
		public function getCoaTable() {
			if(!$this->coaTable) {
				$sm = $this->getServiceLocator();
				$this->coaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->coaTable;
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
		
		public function getEmployeePersonalInfoTable() {
			if(!$this->employeePersonalInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeePersonalInfoTable 	= $sm->get('HumanResource\Model\EmployeePersonalInfoTable');
			}
			return $this->employeePersonalInfoTable;
		}
	}
?>