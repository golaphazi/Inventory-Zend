<?php
	namespace Accounts\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	class VoucherTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}

		public function insertContraEntry($Voucher) {
			//echo 'hi therer';die();
			//echo "<pre>"; print_r($Voucher);die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate		= date("d-m-Y", strtotime($this->session->businessdate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$tm_drawn_on		= (isset($Voucher->DRAWNON)) ? $Voucher->DRAWNON:'';
			$tm_auto_tran 		= $Voucher->AUTO_TRANSACTION;
			
			$transStatus   		= (int) 0;
			$msg				= '';
			$flag				= 0;
			$tmTransectionDate	= date("d-m-Y", strtotime($Voucher->TRANSACTION_DATE));
			$tmNtr              = '';
			
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
			$tcPortfolioCode	= $Voucher->BRANCH_ID;
			$tcNarration 		= $Voucher->PARTICULARS;
			$CAP_MKT_ORDER_ID	= '';
			
			$cbCode				= $Voucher->CB_CODE;
			$tmInvoiceNo		= $Voucher->INVOICE_NO;
			$tmMoneyReceiptNo 	= $Voucher->MONEY_RECEIPT_NO;
			$backDateFlag			= '';
			$transactionMonthYear = date("m-Y", strtotime($tmTransectionDate));
			$businessMonthYear 	  = date("m-Y", strtotime($businessDate));
			if($transactionMonthYear < $businessMonthYear ) {
				$backDateFlag = 'Y';
			}
			$refPortfolioVoucherNo	= $Voucher->REFPORTFOLIOVOUCHERNO;
			for($i = 0; $i < sizeof($Voucher->COA_CODE); $i++) {
				$pcoaCode	= $Voucher->COA_CODE[$i];
				if(isset($Voucher->COA_CODE[$i]) && isset($Voucher->PAYMENT_AMOUNT[$pcoaCode])) {
					$tcAccCode	= $Voucher->COA_CODE[$i];
					$tcAmount   = $Voucher->PAYMENT_AMOUNT[$pcoaCode];
					$tcNtr		= (isset($Voucher->VOUCHER_TYPE[$tcAccCode])) ? $Voucher->VOUCHER_TYPE[$tcAccCode]:'';
					
					if(!empty($tmTransectionDate) 
					   && !empty($tcAccCode) 
					   && !empty($tcPortfolioCode) 
					   && !empty($tcAmount)
					   && !empty($tcNtr)) {
						if((substr($tcAccCode,0,3) == '303') && (strtoupper($tcNtr) == 'C')){
							$tmNtr				= 'D';
							$tmCbjt  			= 'C';
							$tmCbCOACode        = $tcAccCode;
						} else if((substr($tcAccCode,0,3) == '304') && (strtoupper($tcNtr) == 'C')) {
						   	$tmNtr				= 'D';
							$tmCbjt       		= 'B';
							$tmCbCOACode        = $tcAccCode;
							$tmEffectedAtBank   = $Voucher->EFFECTED_AT_BANK;
							$tmChequeNo   		= (isset($Voucher->CHEQUE_NO)) ? $Voucher->CHEQUE_NO:'';
							$tmChqDate    		= date("d-m-Y", strtotime($Voucher->CHEQUE_DATE));
						} else {
							$paymentCodes[] = array('paymentCOACode'=>$tcAccCode,
										'paymentAmount'=>$tcAmount,
										'portfolioCode'=>$tcPortfolioCode,
										'voucherType'=>$tcNtr,
										'particulars'=>$tcNarration
										); 
							
							$tcAccCodes[]	= $tcAccCode;
							$tcNtrs[]       = $tcNtr;
							$tcNarrations[] = $tcNarration;
							$tcAmounts[]    = str_replace(",", "", $tcAmount);   
						}
						$flag 				= 1;
					}
				}
			}

			if($tmNtr == ''){
				$v_voucher_type = 'JV';
			} else {
				if(lower($tmNtr) == 'd'){
					//$v_voucher_type = 'PV';
					
				}
			}
			
			//echo "<pre>"; print_r($tcAccCodes); print_r($tcNtrs); print_r($tcAmounts); print_r($tcNarrations); print_r($paymentCodes); die();
			
			if($flag) {
				$insertPaymentSql = "begin PKG_GENERAL_ACCOUNTS_ENTRY.SP_ACCOUNTS_ENTRY(:v_tm_tran_dt_in,
																				:v_tm_drown_on_in,
																				:v_tm_auto_tran_in,
																				:v_tm_ntr_in,
																				:v_tm_cbjt_in,
																				:v_tm_cb_code_in,
																				:v_tm_chq_no_in,
																				:v_tm_chq_dt_in,
																				:v_tm_invoice_no_in,
																				:v_tm_money_receipt_no_in,
																				:v_cb_code_cond,
																				:v_back_date_flag,
																				:v_business_date_in,
																				:v_tc_branch_id_in,
																				:v_tm_user_id_in,
																				:v_effected_bank_tran_in,
																				:v_tc_ac_code_in,
																				:v_tc_ntr_in,
																				:v_tc_narration_in,
																				:v_tc_amount_in,
																				:v_trans_status_in_out,
																				:v_cap_mkt_order_id,
																				:v_ref_portfolio_voucher_no,
																				:v_msg_out); end;";
				
				$insertPaymentStatement = $this->tableGateway->getAdapter()->createStatement($insertPaymentSql);
				$insertPaymentStatement->prepare();
				
				$insertPaymentStatement->bindParam(':v_tm_tran_dt_in', $tmTransectionDate, 10, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_tm_drown_on_in', $tm_drawn_on, 100, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_tm_auto_tran_in', $tm_auto_tran, 1, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_tm_ntr_in', $tmNtr, 1, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_tm_cbjt_in', $tmCbjt, 1, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_tm_cb_code_in', $tmCbCOACode, 9, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_tm_chq_no_in', $tmChequeNo, 20, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_tm_chq_dt_in', $tmChqDate, 10, SQLT_CHR);
				
				$insertPaymentStatement->bindParam(':v_tm_invoice_no_in', $tmInvoiceNo, 50, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_tm_money_receipt_no_in', $tmMoneyReceiptNo, 50, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_cb_code_cond', $cbCode, 20, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_back_date_flag', $backDateFlag, 1, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_ref_portfolio_voucher_no', $refPortfolioVoucherNo, 100, SQLT_CHR);
				
				$insertPaymentStatement->bindParam(':v_business_date_in', $tmTransectionDate, 10, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_tc_branch_id_in', $tcPortfolioCode, 10, SQLT_CHR);
				
				$insertPaymentStatement->bindParam(':v_tm_user_id_in', $userId, 10, OCI_B_INT);
				
				$insertPaymentStatement->bindParam(':v_effected_bank_tran_in', $effected_bank_tran, 1, SQLT_CHR);
				$insertPaymentStatement->bindParam(':v_trans_status_in_out', $transStatus, 10, OCI_B_INT);
				$insertPaymentStatement->bindParam(':v_cap_mkt_order_id', $CAP_MKT_ORDER_ID, 10, OCI_B_INT);
				$insertPaymentStatement->bindParam(':v_msg_out', $msg, 500, SQLT_CHR);

				$insertPaymentStatement->bindParam(':v_tc_ac_code_in', $tcAccCodes, count($tcAccCodes), OCI_B_INT, -1);
				$insertPaymentStatement->bindParam(':v_tc_ntr_in', $tcNtrs, count($tcNtrs), SQLT_CHR, -1);
				$insertPaymentStatement->bindParam(':v_tc_narration_in', $tcNarrations, count($tcNarrations), SQLT_CHR, -1);
				$insertPaymentStatement->bindParam(':v_tc_amount_in', $tcAmounts, count($tcAmounts), SQLT_CHR, -1);
				
				$insertPaymentStatement->execute();
				
				if(!$transStatus) {
					return false;
				} else {
					/*if($this->updateGeneralTrialBalance()) {
						return $msg;//return true;
					} else {
						$msg	= "<span class='errorMsg'>Sorry! There is a system error.</span>";
						//return false;
					}*/
					return $msg;
				}
			} else {
				return false;
			}
		}
		
		public function saveVoucher(Voucher $voucher) {
			//echo '<pre>';print_r($voucher);die();
			//return $returnVoucher	= "BR1-2014-000001"; die();
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("Y-m-d", strtotime($businessDate));
			$recordDate 	= $this->session->recdate;//date("Y-m-d H:i:s", strtotime($businessDate));die();
			$userId 		= $this->session->userid;
			
			//Get Fiscal Year Start By Akhand
			$v_fiscal_year	= '';
			$FIRST_FY		= '';
			$LAST_FY		= '';
			$V_TYPE			= '';
			
			$fiscalYearSql	= "
								
								SELECT 
										SUBSTR(FY.FISCAL_START, 3,2)	AS FIRST_FY,
										SUBSTR(FY.FISCAL_END, 3, 2)		AS LAST_FY
								FROM 
										l_fiscal_year FY
								WHERE 
										FY.FISCAL_START	<= '".$businessDate."'
								AND 	FY.FISCAL_END	>= '".$businessDate."'
			";
			$fiscalYearStatement	= $this->tableGateway->getAdapter()->createStatement($fiscalYearSql);
			$fiscalYearStatement->prepare();
			$fiscalYearResult		= $fiscalYearStatement->execute();
			
			if ($fiscalYearResult instanceof ResultInterface && $fiscalYearResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($fiscalYearResult);
			}
			//print_r($resultSet);
			foreach($resultSet as $resultSetData) {
				$FIRST_FY		= $resultSetData->LAST_FY;
				$LAST_FY		= $resultSetData->LAST_FY;
				$v_fiscal_year	= $FIRST_FY.$LAST_FY;
			}
			//echo $LAST_FY; exit();
			if($v_fiscal_year) {
				// Get Max Id Start By Akhand
				$maxVoucherNumber	= "";
				$getMaxSql			= '';
				
				if($voucher->DEBIT_VOUCHER != '') {
					if(strtoupper($voucher->DEBIT_VOUCHER) == 'BP') {
						$getMaxSql	= "
										SELECT
												MAX(DEBIT_VOUCHER) AS MAX_VOUCHER 
										FROM 
												a_voucher VN
										WHERE 
												VN.V_YEAR 		= ".$v_fiscal_year."
										AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
										AND 	VN.V_TYPE 		= 'BP'		
						";
						$V_TYPE			= 'BP';	
					}
					if(strtoupper($voucher->DEBIT_VOUCHER) == 'CP') {
						$getMaxSql	= "
										SELECT
												MAX(DEBIT_VOUCHER) AS MAX_VOUCHER 
										FROM 
												a_voucher VN
										WHERE 
												VN.V_YEAR 		= ".$v_fiscal_year."
										AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
										AND 	VN.V_TYPE 		= 'CP'		
						";
						$V_TYPE			= 'CP';
					}	
				} else if($voucher->CREDIT_VOUCHER != '') {
					if(strtoupper($voucher->CREDIT_VOUCHER) == 'BR') {
						$getMaxSql	= "
										SELECT
												MAX(CREDIT_VOUCHER) AS MAX_VOUCHER 
										FROM 
												a_voucher VN
										WHERE 
												VN.V_YEAR 		= ".$v_fiscal_year."
										AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
										AND 	VN.V_TYPE 		= 'BR'		
						";
						$V_TYPE			= 'BR';	
					}
					if(strtoupper($voucher->CREDIT_VOUCHER) == 'CR') {
						$getMaxSql	= "
										SELECT
												MAX(CREDIT_VOUCHER) AS MAX_VOUCHER 
										FROM 
												a_voucher VN
										WHERE 
												VN.V_YEAR 		= ".$v_fiscal_year."
										AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
										AND 	VN.V_TYPE 		= 'CR'		
						";
						$V_TYPE			= 'CR';
					}
				} else if($voucher->JOURNAL_VOUCHER != '') {
					$getMaxSql	= "
									SELECT
											MAX(JOURNAL_VOUCHER) AS MAX_VOUCHER 
									FROM 
											a_voucher VN
									WHERE 
											VN.V_YEAR 		= ".$v_fiscal_year."
									AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
									AND 	VN.V_TYPE 		= 'JV'		
					";
					$V_TYPE			= 'JV';
				} else {
					$getMaxSql	= "
									SELECT
											MAX(CONTRA_VOUCHER) AS MAX_VOUCHER 
									FROM 
											a_voucher VN
									WHERE 
											VN.V_YEAR 		= ".$v_fiscal_year."
									AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
									AND 	VN.V_TYPE 		= 'CV'		
					";
					$V_TYPE			= 'CV';
				}
				$getMaxStatement		= $this->tableGateway->getAdapter()->createStatement($getMaxSql);
				$getMaxStatement->prepare();
				$getMaxResult 			= $getMaxStatement->execute();
				
				if ($getMaxResult instanceof ResultInterface && $getMaxResult->isQueryResult()) {
					$resultSet 	= new ResultSet();
					$resultSet->initialize($getMaxResult);
				}
				foreach($resultSet as $MAX_ID) {
					$maxVoucherNumber = $MAX_ID->MAX_VOUCHER;
				}
				if($maxVoucherNumber) {
					$maxVoucherNumber	= $maxVoucherNumber+1;	
				} else {
					$maxVoucherNumber	= 1;	
				}
				if($voucher->DEBIT_VOUCHER != '') {
					$maxDebitVoucherNumber		= $maxVoucherNumber;

					$maxCreditVoucherNumber		= '';
					$maxJournalVoucherNumber	= '';
					$maxContraVoucherNumber		= '';
				} else if($voucher->CREDIT_VOUCHER != '') {
					$maxDebitVoucherNumber		= '';
					$maxCreditVoucherNumber		= $maxVoucherNumber;
					$maxJournalVoucherNumber	= '';
					$maxContraVoucherNumber		= '';
				} else if($voucher->JOURNAL_VOUCHER != '') {
					$maxDebitVoucherNumber		= '';
					$maxCreditVoucherNumber		= '';
					$maxJournalVoucherNumber	= $maxVoucherNumber;
					$maxContraVoucherNumber		= '';
				} else {
					$maxDebitVoucherNumber		= '';
					$maxCreditVoucherNumber		= '';
					$maxJournalVoucherNumber	= '';
					//echo 'aaa';
					$maxContraVoucherNumber		= $maxVoucherNumber;
				}
				// Get Max Id End By Akhand
				$data = array(
					'BRANCH_ID' 		=> $voucher->BRANCH_ID,
					'V_YEAR' 			=> $v_fiscal_year,
					'V_TYPE' 			=> $V_TYPE,
					'DEBIT_VOUCHER' 	=> $maxDebitVoucherNumber,
					'CREDIT_VOUCHER' 	=> $maxCreditVoucherNumber,
					'JOURNAL_VOUCHER' 	=> $maxJournalVoucherNumber,
					'CONTRA_VOUCHER' 	=> $maxContraVoucherNumber,
					'BUSINESS_DATE' 	=> $businessDate,
					'RECORD_DATE' 		=> $recordDate,
					'OPERATE_BY' 		=> $userId,
				);
				
				//echo "<pre>"; print_r($data);die();
				if($this->tableGateway->insert($data)) {
					// Get Max Id Start By Akhand
					$getMaxSql		= '';
					$returnVoucher	= "";
					if($voucher->DEBIT_VOUCHER != '') {
						if(strtoupper($voucher->DEBIT_VOUCHER) == 'BP') {
							$getMaxSql	= "
											SELECT
													LPAD(MAX(DEBIT_VOUCHER), 6, '0') AS MAX_VOUCHER_NUMBER
											FROM 
													a_voucher VN
											WHERE 
													VN.V_YEAR 		= ".$v_fiscal_year."
											AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
											AND 	VN.V_TYPE 		= 'BP'		
							";	
						}
						if(strtoupper($voucher->DEBIT_VOUCHER) == 'CP') {
							$getMaxSql	= "
											SELECT
													LPAD(MAX(DEBIT_VOUCHER), 6, '0') AS MAX_VOUCHER_NUMBER
											FROM 
													a_voucher VN
											WHERE 
													VN.V_YEAR 		= ".$v_fiscal_year."
											AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
											AND 	VN.V_TYPE 		= 'CP'		
							";
						}
					} else if($voucher->CREDIT_VOUCHER != '') {
						if(strtoupper($voucher->CREDIT_VOUCHER) == 'BR') {
							$getMaxSql	= "
											SELECT
													LPAD(MAX(CREDIT_VOUCHER), 6, '0') AS MAX_VOUCHER_NUMBER
											FROM 
													a_voucher VN
											WHERE 
													VN.V_YEAR 		= ".$v_fiscal_year."
											AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
											AND 	VN.V_TYPE 		= 'BR'		
							";	
						}
						if(strtoupper($voucher->CREDIT_VOUCHER) == 'CR') {
							$getMaxSql	= "
											SELECT
													LPAD(MAX(CREDIT_VOUCHER), 6, '0') AS MAX_VOUCHER_NUMBER
											FROM 
													a_voucher VN
											WHERE 
													VN.V_YEAR 		= ".$v_fiscal_year."
											AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
											AND 	VN.V_TYPE 		= 'CR'		
							";
						}
					} else if($voucher->JOURNAL_VOUCHER != '') {
						$getMaxSql	= "
										SELECT
												LPAD(MAX(JOURNAL_VOUCHER), 6, '0') AS MAX_VOUCHER_NUMBER
										FROM 
												a_voucher VN
										WHERE 
												VN.V_YEAR 		= ".$v_fiscal_year."
										AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
										AND 	VN.V_TYPE 		= 'JV'		
						";
					} else {
						$getMaxSql	= "
										SELECT
												LPAD(MAX(CONTRA_VOUCHER), 6, '0') AS MAX_VOUCHER_NUMBER
										FROM 
												a_voucher VN
										WHERE 
												VN.V_YEAR 		= ".$v_fiscal_year."
										AND 	VN.BRANCH_ID 	= ".$voucher->BRANCH_ID."
										AND 	VN.V_TYPE 		= 'CV'		
						";
					}
					$getMaxStatement		= $this->tableGateway->getAdapter()->createStatement($getMaxSql);
					$getMaxStatement->prepare();
					$getMaxResult 			= $getMaxStatement->execute();
					if ($getMaxResult instanceof ResultInterface && $getMaxResult->isQueryResult()) {
						$resultSet 	= new ResultSet();
						$resultSet->initialize($getMaxResult);
					}
					foreach($resultSet as $MAX_ID) {
						$returnVoucher = $MAX_ID->MAX_VOUCHER_NUMBER;
					}
					
					if($voucher->DEBIT_VOUCHER != '') {
						if(strtoupper($voucher->DEBIT_VOUCHER) == 'BP') {
							return $returnVoucher	= "BP". $voucher->BRANCH_ID."-".$v_fiscal_year."-".$returnVoucher;	
						}
						if(strtoupper($voucher->DEBIT_VOUCHER) == 'CP') {
							return $returnVoucher	= "CP". $voucher->BRANCH_ID."-".$v_fiscal_year."-".$returnVoucher;	
						}
					} else if($voucher->CREDIT_VOUCHER != '') {
						if(strtoupper($voucher->CREDIT_VOUCHER) == 'BR') {
							return $returnVoucher	= "BR". $voucher->BRANCH_ID."-".$v_fiscal_year."-".$returnVoucher;	
						}
						if(strtoupper($voucher->CREDIT_VOUCHER) == 'CR') {
							return $returnVoucher	= "CR". $voucher->BRANCH_ID."-".$v_fiscal_year."-".$returnVoucher;	
						}
					} else if($voucher->JOURNAL_VOUCHER != '') {
						return $returnVoucher	= "JV". $voucher->BRANCH_ID."-".$v_fiscal_year."-".$returnVoucher;
					} else {
						return $returnVoucher	= "CV". $voucher->BRANCH_ID."-".$v_fiscal_year."-".$returnVoucher;
					}
					// Get Max Id End By Akhand
				} else {
					return false;	
				}
			} else {
				return false;	
			}
			//Get Fiscal Year End By Akhand
		}
		
		public function fetchBranchWiseVoucherDetails($branchID,$cond) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "SELECT 		DISTINCT
												DATE_FTDT,
												VOUCHER_NO_,
												PARTICULARS_,
												NARRATION_,
												DEBIT_,
												CREDIT_,
												COA_CODE_,
												INVOICE_NO,
												MONEY_RECEIPT_NO
									FROM 	
												view_daily_transaction 
									WHERE 	
												FUND_ID_='".$branchID."' 
									{$cond}
									ORDER BY DATE_FTDT, VOUCHER_NO_, DEBIT_ DESC
									";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function fetchBranchWiseVoucherNumber($branchID,$VCondition,$cond) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "SELECT 	
											DISTINCT VOUCHER_NO_
								FROM 	
											view_daily_transaction 
								WHERE 	
											FUND_ID_='".$branchID."'  
								{$VCondition} 
								{$cond} 
								order by 
								VOUCHER_NO_ asc
								";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function fetchBranchWiseDistVoucherNo($input,$cond) {
			$getTblDataSql   = "
								SELECT 
										DISTINCT 
												VOUCHER_NO_ 
								FROM 
										view_daily_transaction 
								WHERE 	LOWER(VOUCHER_NO_) like '%".$input."%' {$cond} 
								ORDER BY 
										VOUCHER_NO_ ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function updateTransactionMaster($updateDetails) {
			//echo '<pre>';print_r($updateDetails);die();
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("Y-m-d", strtotime($businessDate));
			$msg			= '';

			//INVOICE_NO 			= q'[".$updateDetails[0]['tmInvoiceNo']."]',
			//MONEY_RECEIPT_NO 	= q'[".$updateDetails[0]['tmMoneyReceiptNo']."]'
			$updateTMSql = "UPDATE
									a_transaction_master
							SET
									NTR 		= '".$updateDetails[0]['tmNtr']."',
									CBJT 		= '".$updateDetails[0]['tmCbjt']."',
									CB_CODE 	= '".$updateDetails[0]['tmCbCOACode']."',
									CHQ_NO 		= '".$updateDetails[0]['tmChequeNo']."',
									CHQ_DATE 	= ".date("Y-m-d", strtotime($updateDetails[0]['tmChqDate'])).",
									DRAWN_ON 	= '".$updateDetails[0]['drawnOn']."'
									
							WHERE
									VOUCHER_NO 	= '".$updateDetails[0]['existingVoucherNumber']."'
			";
			$updateTMStatement = $this->tableGateway->getAdapter()->createStatement($updateTMSql);
			$updateTMStatement->prepare();
			if(!$updateTMStatementResult = $updateTMStatement->execute()) {
				return false;
			} else {
				//echo 'else e dhukce for delete';die();
				$deleteTransactionChildQuery = "	
												DELETE 
														FROM 
																a_transaction_child
														WHERE 
																TRAN_NO IN(	SELECT 
																					TRAN_NO 
																			FROM 
																					a_transaction_master 
																			WHERE 
																					VOUCHER_NO = '".$updateDetails[0]['existingVoucherNumber']."'
																		 )
				";
				$deleteTCStatement = $this->tableGateway->getAdapter()->createStatement($deleteTransactionChildQuery);
				$deleteTCStatement->prepare();
				if(!$deleteTMStatementResult = $deleteTCStatement->execute()) {
					return false;
				} else {
					return true;
				}
				//$this->tableGateway->adapter->getDriver()->getConnection()->commit();
			}
		}
		
		public function saveTransactionChild($insertDetails) {
			$insertTransactionChildQuery = "
												INSERT INTO 
															a_transaction_child
																(	
																	TRAN_NO,
																	BRANCH_ID,
																	AC_CODE,
																	NTR,
																	CBJT,
																	CB_CODE,
																	NARRATION,
																	AMOUNT,
																	RECORD_DATE
																)
															VALUES
																(
																	(SELECT 
																			TRAN_NO 
																	FROM 
																			a_transaction_master 
																	WHERE 
																			VOUCHER_NO = '".$insertDetails['existingVoucherNumber']."'),
																	'".$insertDetails['tcPortfolioCode']."',
																	'".$insertDetails['tcAccCode']."',
																	'".$insertDetails['tcvoucherType']."',
																	'".$insertDetails['tmCbjt']."',
																	'".$insertDetails['tmCbCOACode']."',
																	'[".$insertDetails['tcNarration']."',
																	'".$insertDetails['tcAmount']."',
																	CURRENT_TIMESTAMP
																)
			";
			$insertTCStatement = $this->tableGateway->getAdapter()->createStatement($insertTransactionChildQuery);
			$insertTCStatement->prepare();
			if(!$insertTMStatementResult = $insertTCStatement->execute()) {
				return false;
			} else {
				return true;
			}
		}
		
		public function removeAccountEntry($fundCode,$voucherPart2,$cond,$transactionNumber) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("Y-m-d", strtotime($businessDate));
			$msg			= '';
			$deleteVNSql = "DELETE
								FROM
										a_voucher
								WHERE
										BRANCH_ID	= '".$fundCode."'
								AND		V_YEAR		= '".$voucherPart2."' 
								{$cond}
							";
			$deleteVNStatement = $this->tableGateway->getAdapter()->createStatement($deleteVNSql);
			$deleteVNStatement->prepare();
			if(!$deleteVNSStatementResult = $deleteVNStatement->execute()) {
				return false;
			} else {
				$deleteTransactionChildQuery = "	
												DELETE
														FROM
																a_transaction_child
														WHERE
																TRAN_NO	= '".$transactionNumber."'
											    ";
				$deleteTCStatement = $this->tableGateway->getAdapter()->createStatement($deleteTransactionChildQuery);
				$deleteTCStatement->prepare();
				if(!$deleteTCStatementResult = $deleteTCStatement->execute()) {
					return false;
				} else {
					$deleteTransactionMasterQuery = "	
													DELETE
															FROM
																	a_transaction_master
															WHERE
																	TRAN_NO	= '".$transactionNumber."'
													";
					$deleteTMStatement = $this->tableGateway->getAdapter()->createStatement($deleteTransactionMasterQuery);
					$deleteTMStatement->prepare();
					if(!$deleteTMStatementResult = $deleteTMStatement->execute()) {
						return false;
					} else {
						return true;
					}
				}
			}
		}		
		public function fetchSupplierWiseOBBalance($suppInfoID,$transDateFrom) {
			$stockCond	= " AND PT.BRANCH_ID = 1";
			$getTblDataSql   = "SELECT COALESCE( PT.BALANCE, 0 ) AS OPENING_BAL
									FROM i_payment_transaction PT, c_branch BRANCH
									WHERE PT.SUPPLIER_INFO_ID = '".$suppInfoID."'
									AND BRANCH.BRANCH_ID = PT.BRANCH_ID
									AND PT.BUSINESS_DATE = (SELECT MAX( PTT.BUSINESS_DATE )
															FROM i_payment_transaction PTT
															WHERE PTT.SUPPLIER_INFO_ID = '".$suppInfoID."'
															AND PTT.BRANCH_ID =1
															AND PTT.BUSINESS_DATE <= '".date("Y-m-d", strtotime($transDateFrom))."' )
									{$stockCond}
									ORDER by PT.payment_transaction_id DESC
									LIMIT 1 
									";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			$obBal = 0.00;
			foreach($resultSet as $getPDidss) {
				$obBal = $getPDidss['OPENING_BAL'];			
			}
			return $obBal;
		}
		public function getSupplierWiseLedgerDetails($supplierInfoID,$tranDateFrom,$tranDateTo) {
			$stockCond	= " AND PT.BRANCH_ID = 1";
			$pDate		= date('Y-m-d',strtotime($tranDateTo));
			$toDate 	= "'$pDate'";			
			$fromDate	= date('Y-m-d',strtotime($tranDateFrom));
			$fDate 		= "'$fromDate'";
			$cond 		= '';
			$cond 		.=" AND PT.BUSINESS_DATE BETWEEN {$fDate} AND {$toDate}";	
			$select 	= "SELECT PT.PAYMENT_TRANSACTION_ID,
							   COALESCE(PT.DEBIT,0) AS DEBIT,
							   COALESCE(PT.CREDIT,0) AS CREDIT,
							   COALESCE(PT.BALANCE,0) AS BALANCE,
							   PT.NARRATION,
							   PT.BUSINESS_DATE,
							   PT.TRANSACTION_FLAG
						FROM  i_payment_transaction PT,
							  c_branch BRANCH
						WHERE PT.SUPPLIER_INFO_ID	=  '".$supplierInfoID."'
						AND BRANCH.BRANCH_ID 		= PT.BRANCH_ID					
						{$cond}
						{$stockCond}
						ORDER BY PT.SUPPLIER_FLAG ASC";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			return $resultSet;
		}		
		public function fetchRetailerWiseOBBalance($retailerID,$transDateFrom) {
			$stockCond	= " AND PT.BRANCH_ID = 1";
			$getTblDataSql   = "SELECT COALESCE( PT.BALANCE, 0 ) AS OPENING_BAL
									FROM i_payment_transaction PT, c_branch BRANCH
									WHERE PT.RETAILER_ID = '".$retailerID."'
									AND BRANCH.BRANCH_ID = PT.BRANCH_ID
									AND PT.BUSINESS_DATE = (SELECT MAX( PTT.BUSINESS_DATE )
															FROM i_payment_transaction PTT
															WHERE PTT.RETAILER_ID = '".$retailerID."'
															AND PTT.BRANCH_ID =1
															AND PTT.BUSINESS_DATE <= '".date("Y-m-d", strtotime($transDateFrom))."' )
									{$stockCond}
									ORDER by PT.payment_transaction_id DESC
									LIMIT 1 
									";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			$obBal = 0.00;
			foreach($resultSet as $getPDidss) {
				$obBal = $getPDidss['OPENING_BAL'];			
			}
			return $obBal;
		}
		public function getRetailerWiseLedgerDetails($retailerID,$tranDateFrom,$tranDateTo) {
			$stockCond	= " AND PT.BRANCH_ID = 1";
			$pDate		= date('Y-m-d',strtotime($tranDateTo));
			$toDate 	= "'$pDate'";			
			$fromDate	= date('Y-m-d',strtotime($tranDateFrom));
			$fDate 		= "'$fromDate'";
			$cond 		= '';
			$cond 		.=" AND PT.BUSINESS_DATE BETWEEN {$fDate} AND {$toDate}";	
			
			$select 	= "SELECT PT.PAYMENT_TRANSACTION_ID,
							   COALESCE(PT.DEBIT,0) AS DEBIT,
							   COALESCE(PT.CREDIT,0) AS CREDIT,
							   COALESCE(PT.BALANCE,0) AS BALANCE,
							   PT.NARRATION,
							   PT.BUSINESS_DATE,
							   PT.TRANSACTION_FLAG
						FROM  i_payment_transaction PT,
							  c_branch BRANCH
						WHERE PT.RETAILER_ID	=  '".$retailerID."'
						AND BRANCH.BRANCH_ID 		= PT.BRANCH_ID	
						
											
						{$cond}
						{$stockCond}
						ORDER BY PT.RETAILER_FLAG ASC";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			return $resultSet;
		}
		public function fetchSRWiseOBBalance($employeeID,$transDateFrom) {
			$stockCond	= " AND PT.BRANCH_ID = 1";
			$getTblDataSql   = "SELECT 
										COALESCE( PT.BALANCE, 0 ) AS OPENING_BAL
								FROM 
										i_payment_transaction PT,
										c_branch BRANCH
								WHERE 
										PT.EMPLOYEE_ID = '".$employeeID."'
										AND BRANCH.BRANCH_ID = PT.BRANCH_ID
										AND PT.BUSINESS_DATE = (SELECT MAX( PTT.BUSINESS_DATE )
																FROM 
																		i_payment_transaction PTT
																WHERE 
																		PTT.EMPLOYEE_ID = '".$employeeID."'
																		AND PTT.BRANCH_ID =1
																		AND PTT.BUSINESS_DATE <= '".date("Y-m-d", strtotime($transDateFrom))."' )
									{$stockCond}
									ORDER by PT.payment_transaction_id DESC
									LIMIT 1 
									";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			$obBal = 0.00;
			foreach($resultSet as $getPDidss) {
				$obBal = $getPDidss['OPENING_BAL'];			
			}
			return $obBal;
		}
		
		# ---------- opening Balence -----------------#
		public function fetchSRWiseRetilerOBBalance($employeeID,$transDateFrom) {
			//echo $employeeID;exit();
			$stockCond	= " AND PT.BRANCH_ID = 1";
			$getTblDataSql   = "SELECT 
										COALESCE( PT.BALANCE, 0 ) AS OPENING_BAL
									FROM 
											i_payment_transaction PT,
											c_branch BRANCH
									WHERE 
											PT.RETAILER_ID = '".$employeeID."'
											AND BRANCH.BRANCH_ID = PT.BRANCH_ID
											AND PT.BUSINESS_DATE = (SELECT 
																			MAX( PTT.BUSINESS_DATE )
																	FROM 
																			i_payment_transaction PTT
																	WHERE 
																			PTT.RETAILER_ID = '".$employeeID."'
																			AND PTT.BRANCH_ID =1
																			AND PTT.BUSINESS_DATE <= '".date("Y-m-d", strtotime($transDateFrom))."' )
									{$stockCond}
									ORDER by PT.payment_transaction_id DESC
									LIMIT 1 
									";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			$obBal = 0.00;
			foreach($resultSet as $getPDidss) {
				$obBal = $getPDidss['OPENING_BAL'];			
			}
			return $obBal;
		}
		
		public function fetchSRWiseRetilerOBBalanceDetials($employeeID,$transDateFrom) {
			//echo $employeeID;exit();
			$stockCond	= " AND PT.BRANCH_ID = 1";
			$getTblDataSql   = "SELECT 
										SUM( PT.BALANCE) AS OPENING_BAL
									FROM 
											i_payment_transaction PT,
											c_branch BRANCH
									WHERE 
											PT.RETAILER_ID = '".$employeeID."'
											AND BRANCH.BRANCH_ID = PT.BRANCH_ID
											AND PT.BUSINESS_DATE  <= '".date("Y-m-d", strtotime($transDateFrom))."'
									{$stockCond}
									ORDER by PT.RETAILER_ID DESC
									LIMIT 1 
									";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			$obBal = 0.00;
			foreach($resultSet as $getPDidss) {
				$obBal = $getPDidss['OPENING_BAL'];			
			}
			return $obBal;
		}
		public function fetchSRWiseRetilerCBBalance($employeeID,$transDateFrom) {
			//echo $employeeID;exit();
			$stockCond	= " AND PT.BRANCH_ID = 1";
			$getTblDataSql   = "SELECT 
										COALESCE( PT.BALANCE, 0 ) AS OPENING_BAL
									FROM 
											i_payment_transaction PT,
											c_branch BRANCH
									WHERE 
											PT.RETAILER_ID = '".$employeeID."'
											AND BRANCH.BRANCH_ID = PT.BRANCH_ID
											AND PT.BUSINESS_DATE = (SELECT 
																			MAX( PTT.BUSINESS_DATE )
																	FROM 
																			i_payment_transaction PTT
																	WHERE 
																			PTT.RETAILER_ID = '".$employeeID."'
																			AND PTT.BRANCH_ID =1
																			AND PTT.BUSINESS_DATE >= '".date("Y-m-d", strtotime($transDateFrom))."' )
									{$stockCond}
									ORDER by PT.payment_transaction_id DESC
									LIMIT 1 
									";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			$obBal = 0.00;
			foreach($resultSet as $getPDidss) {
				$obBal = $getPDidss['OPENING_BAL'];			
			}
			return $obBal;
		}
		
		#----------- transtaion table -----------#
		public function getTranstionChild($PAYABLE_COAR,$RECEIVABLE_COAR,$tranDateFrom,$tranDateTo){
			
			$pDate		= date('Y-m-d',strtotime($tranDateTo));
			$toDate 	= "'$pDate'";			
			$fromDate	= date('Y-m-d',strtotime($tranDateFrom));
			$fDate 		= "'$fromDate'";
			$cond 		= '';
			$cond 		.=" AND TM.TRAN_DATE BETWEEN {$fDate} AND {$toDate}";	
			
			$select		= "SELECT 
									TC.AMOUNT,
									TC.NTR,
									TC.NARRATION,
									TC.RECORD_DATE,
									TM.VOUCHER_NO
							FROM
									a_transaction_child TC,
									a_transaction_master TM
										
							WHERE
									TC.AC_CODE = {$RECEIVABLE_COAR}
									AND TC.TRAN_NO = TM.TRAN_NO
									$cond
							ORDER BY TC.CHILD_ID ASC
							";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			return $resultSet;
		}
		public function getSRWiseLedgerDetails($employeeID,$tranDateFrom,$tranDateTo) {
			$stockCond	= " AND PT.BRANCH_ID = 1";
			$pDate		= date('Y-m-d',strtotime($tranDateTo));
			$toDate 	= "'$pDate'";			
			$fromDate	= date('Y-m-d',strtotime($tranDateFrom));
			$fDate 		= "'$fromDate'";
			$cond 		= '';
			$cond 		.=" AND PT.BUSINESS_DATE BETWEEN {$fDate} AND {$toDate}";	
			if(empty($employeeID)){
				$whereEm = '';
			}else{
				$whereEm = 'PT.EMPLOYEE_ID	=  '.$employeeID.'
						AND';
			}
			$select 	= "SELECT PT.PAYMENT_TRANSACTION_ID,
							   COALESCE(PT.DEBIT,0) AS DEBIT,
							   COALESCE(PT.CREDIT,0) AS CREDIT,
							   COALESCE(PT.BALANCE,0) AS BALANCE,
							   PT.NARRATION,
							   PT.BUSINESS_DATE,
							   PT.TRANSACTION_FLAG
						FROM  i_payment_transaction PT,
							  c_branch BRANCH
						WHERE {$whereEm} 
								BRANCH.BRANCH_ID 	= PT.BRANCH_ID					
								{$cond}
								{$stockCond}
						ORDER BY PT.SR_FLAG ASC";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			return $resultSet;
		}
		public function getRetailerCurrentBalance($retailerID,$tranDateFrom,$branchID) {					
			$fromDate	= date('Y-m-d',strtotime($tranDateFrom));
			$fDate 		= "'$fromDate'";
			//$cond 		= '';
			//$cond 		.=" AND PT.BUSINESS_DATE BETWEEN {$fDate} AND {$toDate}";	
			$select 	= "SELECT COALESCE(i_payment_transaction.BALANCE,0) AS BALANCE
							FROM i_payment_transaction
							WHERE retailer_id = '".$retailerID."'
							AND BRANCH_ID = '".$branchID."'
							AND retailer_flag = (SELECT max( pt.retailer_flag )
												FROM i_payment_transaction pt
												WHERE pt.retailer_id = '".$retailerID."'
												AND pt.BRANCH_ID = '".$branchID."'
												)
							";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			$bal = 0.00;
			foreach($resultSet as $getPDidss) {
				$bal = $getPDidss['BALANCE'];			
			}
			return $bal;
			//return $resultSet;
		}
		public function getSRCurrentBalance($srID,$tranDateFrom,$branchID) {					
			$fromDate	= date('Y-m-d',strtotime($tranDateFrom));
			$fDate 		= "'$fromDate'";
			//$cond 		= '';
			//$cond 		.=" AND PT.BUSINESS_DATE BETWEEN {$fDate} AND {$toDate}";	
			$select 	= "SELECT COALESCE(i_payment_transaction.BALANCE,0) AS BALANCE
							FROM i_payment_transaction
							WHERE employee_id = '".$srID."'
							AND BRANCH_ID = '".$branchID."'
							AND sr_flag = (SELECT max( pt.sr_flag )
												FROM i_payment_transaction pt
												WHERE pt.employee_id = '".$srID."'
												AND pt.BRANCH_ID = '".$branchID."'
												)
							";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			$bal = 0.00;
			foreach($resultSet as $getPDidss) {
				$bal = $getPDidss['BALANCE'];			
			}
			return $bal;
			//return $resultSet;
		}
		public function getSupplerCurrentBalance($srID,$branchID,$tranDateFrom) {					
			$fromDate	= date('Y-m-d',strtotime($tranDateFrom));
			$fDate 		= "'$fromDate'";
			//$cond 		= '';
			//$cond 		.=" AND PT.BUSINESS_DATE BETWEEN {$fDate} AND {$toDate}";	
			$select 	= "SELECT COALESCE(i_payment_transaction.BALANCE,0) AS BALANCE
							FROM i_payment_transaction
							WHERE SUPPLIER_INFO_ID = '".$srID."'
							AND BRANCH_ID = '".$branchID."'
							AND SUPPLIER_FLAG = (SELECT max( pt.SUPPLIER_FLAG )
												FROM i_payment_transaction pt
												WHERE pt.SUPPLIER_INFO_ID = '".$srID."'
												AND pt.BRANCH_ID = '".$branchID."'
												)
							";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			$bal = 0.00;
			foreach($resultSet as $getPDidss) {
				$bal = $getPDidss['BALANCE'];			
			}
			return $bal;
			//return $resultSet;
		}
	}
?>