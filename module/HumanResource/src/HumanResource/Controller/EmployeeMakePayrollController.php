<?php	
	namespace HumanResource\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use HumanResource\Form\EmployeeMakePayrollForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	use Accounts\Model\TrialBalanceTable;
	use Accounts\Model\Master;
	use Accounts\Model\Child;
	use Accounts\Model\Voucher;
	
	use Zend\Session\Container as SessionContainer;
	
	class EmployeeMakePayrollController extends AbstractActionController {
		protected $employeePersonalInfoTable;
		protected $voucherTable;
		protected $masterTable;
		protected $childTable;
		protected $trialBalanceTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$CONTROLLER_NAME	= 'Joining';
			$EMPLOYEE_DATA 		= $this->getEmployeePersonalInfoTable()->getAllEmployeePersonalInfo($CONTROLLER_NAME);
						
			$select 	= new Select();
			$order_by 	= $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id'; 
			$order 		= $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
			$select->order($order_by . ' ' . $order);
			
			$request 			= $this->getRequest();			
			$form 				= new EmployeeMakePayrollForm('employeemakepayroll', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			
			// Employee Make Payroll Start By Akhand
			if($request->isPost()) {
				$form->setData($request->getPost());
				
				// Set All Valid Data Start By Akhand
				if($form->isValid()) {
					$postedData 				= $request->getPost();
					//echo "<pre>"; print_r($postedData); die();
					$this->getEmployeePersonalInfoTable()->transectionStart();
					
					$this->session 		= new SessionContainer('post_supply');
					$businessDate 		= $this->session->businessdate;
					//$businessDate 		= date("d-M-Y", strtotime($businessDate));
					
					$EMPLOYEE_IDS		= $postedData['EMPLOYEE_ID'];
					$MONTH				= date("M", strtotime($postedData['MONTH']));
					$YEAR				= $postedData['YEAR'];
					$MONTH_YEAR			= $MONTH."-".$YEAR;
					
					$success			= 0;
					$salaryDetails		= array();
					$employeeWiseSalary	= array();
					$oddClass			= "#EEE9E9";
					$evenClass			= "#F7F4F4";
					
					foreach($EMPLOYEE_IDS as $EMPLOYEE_ID) {
						$EMPLOYEE_ID		= $EMPLOYEE_ID;
						$EMPLOYEE_NAME		= $postedData["EMPLOYEE_NAME_{$EMPLOYEE_ID}"];
						$SALARY_AMOUNT		= $postedData["SALARY_AMOUNT_{$EMPLOYEE_ID}"];
						$PAYABLE_COA_CODE	= $postedData["PAYABLE_COA_CODE_{$EMPLOYEE_ID}"];
						$PAYABLE_COA_NAME	= $postedData["PAYABLE_COA_NAME_{$EMPLOYEE_ID}"];
						$EXPENSE_COA_CODE	= "601002001";
						$EXPENSE_COA_NAME	= "Salary and Allowances";
						
						$salaryDetails = array(
							"PAYABLE_COA_CODE"	=> $PAYABLE_COA_CODE,
							"PAYABLE_COA_NAME"	=> $PAYABLE_COA_NAME,
							"EXPENSE_COA_CODE"	=> $EXPENSE_COA_CODE,
							"EXPENSE_COA_NAME"	=> $EXPENSE_COA_NAME,
							"SALARY_AMOUNT"		=> $SALARY_AMOUNT,
							"EMPLOYEE_ID"		=> $EMPLOYEE_ID,
							"EMPLOYEE_NAME"		=> $EMPLOYEE_NAME,
							"BRANCH_ID"			=> $postedData['BRANCH_ID'],
							"MONTH_YEAR"		=> $MONTH_YEAR,
						);
						array_push($employeeWiseSalary,$salaryDetails);
					}
					//echo "<pre>"; print_r($employeeWiseSalary);
					
					$chartOfAccountDisplay	= '';
					$PARTICULARS_SALARY		= "";
						
					$EXPENSE_COA_CODE		= "";
					$EXPENSE_COA_NAME		= "";
					
					$PAYABLE_COA_CODE		= "";
					$PAYABLE_COA_NAME		= "";
					
					$voucherTypeIndex 		= 0;
					
					for($c=0;$c<sizeof($employeeWiseSalary);$c++) {
						$VOUCHER_COA_CODE['COA_CODE']			= array();
						$VOUCHER_COA_CODE['VOUCHER_TYPE']		= array();
						$VOUCHER_COA_CODE['VOUCHER_AMOUNT']		= array();
						
						$chartOfAccountDisplay	= "
						<table cellspacing='0' class='vtbl' >
							<tr class='head'>
								<td colspan='4' align='center'>
									Voucher for Employee Salary
								</td>
							</tr>
							<tr class='head'>
								<td width='55%' align='left'>Account Head</td>
								<td width='15%' align='center'>Account Code</td>
								<td width='15%' align='center'>Dr/Cr</td>
								<td width='15%' align='right'>Amount</td>
							</tr>
						";
						
						$BRANCH_ID				= $employeeWiseSalary[$c]['BRANCH_ID'];
						$PAYABLE_COA_CODE		= $employeeWiseSalary[$c]['PAYABLE_COA_CODE'];
						$PAYABLE_COA_NAME		= $employeeWiseSalary[$c]['PAYABLE_COA_NAME'];
						$EXPENSE_COA_CODE		= $employeeWiseSalary[$c]['EXPENSE_COA_CODE'];
						$EXPENSE_COA_NAME		= $employeeWiseSalary[$c]['EXPENSE_COA_NAME'];
						$SALARY_AMOUNT			= $employeeWiseSalary[$c]['SALARY_AMOUNT'];
						$EMPLOYEE_ID			= $employeeWiseSalary[$c]['EMPLOYEE_ID'];
						$EMPLOYEE_NAME			= $employeeWiseSalary[$c]['EMPLOYEE_NAME'];
						$MONTH_YEAR				= $employeeWiseSalary[$c]['MONTH_YEAR'];
						
						$PAYABLE_HEAD_NAME			= $PAYABLE_COA_NAME;
						$PAYABLE_HEAD_CODE			= $PAYABLE_COA_CODE;
					
						$PAYABLE_DEBIT_CHECKED		= "";
						$PAYABLE_CREDIT_CHECKED		= "checked='checked'";
						$PAYABLE_DEBIT_DISABLED		= "disabled='disabled'";
						$PAYABLE_CREDIT_DISABLED	= "";
						$PAYABLE_AMOUNT_ALIGN		= "right";
						$PAYABLE_VOUCHER_TYPE		= "C";
						
						$VOUCHER_COA_CODE['COA_CODE'][]			= $PAYABLE_COA_CODE;
						$VOUCHER_COA_CODE['VOUCHER_TYPE'][]		= $PAYABLE_VOUCHER_TYPE;
						$VOUCHER_COA_CODE['VOUCHER_AMOUNT'][]	= abs($SALARY_AMOUNT);
						
						$chartOfAccountDisplay	.= "
						<tr class='{$oddClass}'>
							<td align='left'>
								{$PAYABLE_HEAD_NAME}
							</td>
							<td align='center'>																												
								<input type='hidden' name='COA_CODE[]' value='{$PAYABLE_HEAD_CODE}'/>
								{$PAYABLE_HEAD_CODE}
							</td>
							<td align='center'>
							  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$PAYABLE_DEBIT_CHECKED} {$PAYABLE_DEBIT_DISABLED}/>Dr 
							  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$PAYABLE_CREDIT_CHECKED} {$PAYABLE_CREDIT_DISABLED}/>Cr
							</td>
							<td align='{$PAYABLE_AMOUNT_ALIGN}'>
								<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($SALARY_AMOUNT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
							</td>
						</tr>
						";
						$voucherTypeIndex++;
						
						$EXPENSE_DEBIT_CHECKED		= "checked='checked'";
						$EXPENSE_CREDIT_CHECKED		= "";
						$EXPENSE_DEBIT_DISABLED		= "";
						$EXPENSE_CREDIT_DISABLED	= "disabled='disabled'";
						$EXPENSE_AMOUNT_ALIGN		= "left";
						$EXPENSE_VOUCHER_TYPE		= "D";
						
						$VOUCHER_COA_CODE['COA_CODE'][]			= $EXPENSE_COA_CODE;
						$VOUCHER_COA_CODE['VOUCHER_TYPE'][]		= $EXPENSE_VOUCHER_TYPE;
						$VOUCHER_COA_CODE['VOUCHER_AMOUNT'][]	= abs($SALARY_AMOUNT);
						
						$chartOfAccountDisplay	.= "
						<tr class='{$oddClass}'>
							<td align='left'>
								{$EXPENSE_COA_NAME}
							</td>
							<td align='center'>																												
								<input type='hidden' name='COA_CODE[]' value='{$EXPENSE_COA_CODE}'/>
								{$EXPENSE_COA_CODE}
							</td>
							<td align='center'>
							  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$EXPENSE_DEBIT_CHECKED} {$EXPENSE_DEBIT_DISABLED}/>Dr 
							  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$EXPENSE_CREDIT_CHECKED} {$EXPENSE_CREDIT_DISABLED}/>Cr
							</td>
							<td align='{$EXPENSE_AMOUNT_ALIGN}'>
								<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($SALARY_AMOUNT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
							</td>
						</tr>
						";
						
						$PARTICULARS_SALARY	= " ".$EMPLOYEE_NAME."";
						$PARTICULARS_SALARY	= "Salary Payable to ".$PARTICULARS_SALARY." on ".$MONTH_YEAR."";
						
						$chartOfAccountDisplay	.= "		
							<tr class='oddClass'>
								<td align='left'>Particulars</td>
								<td align='left' colspan='3'>
									<textarea name='PARTICULARS' cols='100' rows='2'>
										".$PARTICULARS_SALARY." 
									</textarea>
								</td>
							</tr>
							<tr class='evenClass'>
								<td align='left' colspan='4'>&nbsp;</td>
							</tr>
						</table>
						";
						
						$PARTICULARS 				= $PARTICULARS_SALARY;
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
						
						$tm_drawn_on		= 'N/A';
						$tm_auto_tran 		= 'y';
						
						$transStatus   		= (int) 0;
						$msg				= '';
						$flag				= 0;
						$tmTransectionDate	= date("d-m-Y", strtotime($businessDate));
						$tmNtr              = '';
						
						$tmCbjt 		    = 'J';
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
						$tcBranchId			= $BRANCH_ID;
						$tcNarration 		= $PARTICULARS;
						
						$cbCode					= '';
						$tmInvoiceNo			= 'Auto';
						$tmMoneyReceiptNo 		= 'Auto';
						$backDateFlag			= '';
						
						
						$ALL_COA_CODE		= $VOUCHER_COA_CODE["COA_CODE"];
						//echo "<pre>"; print_r($ALL_COA_CODE); die();
						foreach($ALL_COA_CODE as $index=>$ALL_COA_CODE_VALUE) {
							$tcAccCodes[]		= $VOUCHER_COA_CODE["COA_CODE"][$index];
							$tcNtrs[]       	= $VOUCHER_COA_CODE["VOUCHER_TYPE"][$index];
							$tcNarrations[] 	= $PARTICULARS;
							$tcAmounts[]    	= str_replace(",", "", $VOUCHER_COA_CODE["VOUCHER_AMOUNT"][$index]);
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
							'JOURNAL_VOUCHER' 		=> $v_voucher_type,
						);
						$voucher->exchangeArray($voucherData);
						//echo "<pre>"; print_r($voucher);die();
						if($returnVoucher = $this->getVoucherTable()->saveVoucher($voucher)) {
							//echo $returnVoucher;die();
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
								'STOCK_ORDER_ID' 		=> $stockOrderId,
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
						// Voucher Table Data Insert End BY Akhand
					}
					
					if($success) {
						if($this->getTrialBalanceTable()->updateTrialBalance($businessDate)) {
							$this->getEmployeePersonalInfoTable()->transectionEnd();
							$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Employee Salary Saved Successfully!</h4></td>		
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
					} else {
						$this->getEmployeePersonalInfoTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Sorry! There is system error!</h4></td>
															</tr>
														</table>");
					}
					return $this->redirect()->toRoute('employeemakepayroll');
				}
				// Set All Valid Data End By Akhand
			}
			// Employee Make Payroll End By Akhand
			
			return new ViewModel(array(
				'form' 				=> $form,
				'investorprofiles' 	=> $EMPLOYEE_DATA,
				'order_by' 			=> $order_by,
				'order' 			=> $order,
				'flashMessages' 	=> $this->flashMessenger()->getMessages(),
			));
		}
		
		public function getEmployeePersonalInfoTable() {
			if(!$this->employeePersonalInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeePersonalInfoTable 	= $sm->get('HumanResource\Model\EmployeePersonalInfoTable');
			}
			return $this->employeePersonalInfoTable;
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
	}
?>