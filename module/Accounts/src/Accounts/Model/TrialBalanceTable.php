<?php
	namespace Accounts\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	class TrialBalanceTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function updateTrialBalance($businessDate) {
			$businessDate 	= date("Y-m-d", strtotime($businessDate));
			//Delete All Current Date Trial Balance Data Start By Akhand
			$deleteTrialBalData = "	
									DELETE 
									FROM 
											a_trial_bal
									WHERE 
											BALANCE_DATE	= '".$businessDate."'
									";
			$deleteTrialBalDataStatement 	= $this->tableGateway->getAdapter()->createStatement($deleteTrialBalData);
			$deleteTrialBalDataStatement->prepare();
			$deleteTrialBalDataResult 		= $deleteTrialBalDataStatement->execute();
			//Delete All Current Date Trial Balance Data End By Akhand
			
			//Active Branch Data Start By Akhand
			$getActiveBranchSql   		= "SELECT BRANCH_ID FROM c_branch";
			$getActiveBranchStatement 	= $this->tableGateway->getAdapter()->createStatement($getActiveBranchSql);
			$getActiveBranchStatement->prepare();
			$getActiveBranchResult 		= $getActiveBranchStatement->execute();
			if ($getActiveBranchResult instanceof ResultInterface && $getActiveBranchResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getActiveBranchResult);
			}
			
			foreach($resultSet as $resultData) {
				$BRANCH_ID	= $resultData['BRANCH_ID'];
				// Declaration of Without Cash at Bank & Cash in Hand coa Record Cursore Start By Akhand
				$getWithoutCashBankDataSql	= "
											SELECT 
													TC.AC_CODE 		AC_CODE,
													TC.BRANCH_ID 	FUND_CODE,
													SUM(IF(SUBSTR(TC.NTR, 1, 1)='D',
														TC.AMOUNT,
														-TC.AMOUNT)) BAL_AMOUNT
											FROM 
													a_transaction_master	TM,
													a_transaction_child		TC
											WHERE 
													TM.TRAN_DATE = '".$businessDate."'
											AND 	TM.TRAN_NO = TC.TRAN_NO
											AND 	SUBSTR(TC.AC_CODE, 1, 3) NOT IN ('303', '304')
											AND 	TC.BRANCH_ID = ".$BRANCH_ID."
											GROUP BY
													TC.AC_CODE, 
													TC.BRANCH_ID
				";
				$getWithoutCashBankDataStatement 	= $this->tableGateway->getAdapter()->createStatement($getWithoutCashBankDataSql);
				$getWithoutCashBankDataStatement->prepare();
				$getWithoutCashBankDataResult 		= $getWithoutCashBankDataStatement->execute();
				if ($getWithoutCashBankDataResult instanceof ResultInterface && $getWithoutCashBankDataResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($getWithoutCashBankDataResult);
				}
				foreach($resultSet as $resultWithoutCashBankData) {
					$AC_CODE	= $resultWithoutCashBankData['AC_CODE'];
					$FUND_CODE	= $resultWithoutCashBankData['FUND_CODE'];
					$BAL_AMOUNT	= $resultWithoutCashBankData['BAL_AMOUNT'];
					
					if($BAL_AMOUNT>0) {
						$insertTrialBalData = "	
												INSERT INTO a_trial_bal
													(BRANCH_ID,
													AC_CODE,
													BALANCE_DATE,
													SNTB,
													SA_AMOUNT,
													TBNTB,
													TB_AMOUNT,
													CBNTB,
													CB_AMOUNT)
												VALUES
													(".$FUND_CODE.",
													".$AC_CODE.",
													'".date("Y-m-d", strtotime($businessDate))."',
													'D',
													".abs($BAL_AMOUNT).",
													'D',
													".abs($BAL_AMOUNT).",
													'D',
													".abs($BAL_AMOUNT).")
												";
					} else {
						$insertTrialBalData = "	
												INSERT INTO a_trial_bal
													(BRANCH_ID,
													AC_CODE,
													BALANCE_DATE,
													SNTB,
													SA_AMOUNT,
													TBNTB,
													TB_AMOUNT,
													CBNTB,
													CB_AMOUNT)
												VALUES
													(".$FUND_CODE.",
													".$AC_CODE.",
													'".date("Y-m-d", strtotime($businessDate))."',
													'C',
													".abs($BAL_AMOUNT).",
													'C',
													".abs($BAL_AMOUNT).",
													'C',
													".abs($BAL_AMOUNT).")
												";	
					}
					$insertTrialBalDataStatement 	= $this->tableGateway->getAdapter()->createStatement($insertTrialBalData);
					$insertTrialBalDataStatement->prepare();
					$insertTrialBalDataResult 		= $insertTrialBalDataStatement->execute();
				}
				// Declaration of Without Cash at Bank & Cash in Hand coa Record Cursore End By Akhand
				
				// Declaration of Cash at Bank & Cash in Hand coa Record Cursore Start By Akhand
				$getWithCashBankDataSql	= "
											SELECT 
													TC.CB_CODE 		AC_CODE,
													TC.BRANCH_ID 	FUND_CODE,
													SUM(IF(TC.CBJT='J',
														IF(SUBSTR(TC.NTR, 1, 1)='C',
															   TC.AMOUNT,
															   -TC.AMOUNT),
														IF(SUBSTR(TC.NTR, 1, 1)='D',
															   TC.AMOUNT,
															   -TC.AMOUNT))) BAL_AMOUNT
											FROM 
													a_transaction_master TM,
													a_transaction_child TC
											WHERE 
													TM.TRAN_DATE = '".$businessDate."'
											AND 	TM.TRAN_NO = TC.TRAN_NO
											AND 	SUBSTR(TC.CB_CODE, 1, 3) IN ('303', '304')
											AND 	TC.BRANCH_ID = ".$BRANCH_ID."
											GROUP BY
													TC.CB_CODE, 
													TC.BRANCH_ID
				";
				$getWithCashBankDataStatement 	= $this->tableGateway->getAdapter()->createStatement($getWithCashBankDataSql);
				$getWithCashBankDataStatement->prepare();
				$getWithCashBankDataResult 		= $getWithCashBankDataStatement->execute();
				if ($getWithCashBankDataResult instanceof ResultInterface && $getWithCashBankDataResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($getWithCashBankDataResult);
				}
				foreach($resultSet as $resultWithCashBankData) {
					$AC_CODE	= $resultWithCashBankData['AC_CODE'];
					$FUND_CODE	= $resultWithCashBankData['FUND_CODE'];
					$BAL_AMOUNT	= $resultWithCashBankData['BAL_AMOUNT'];
					
					if($BAL_AMOUNT>0) {
						$insertTrialBalCBData = "	
												INSERT INTO a_trial_bal
													(BRANCH_ID,
													AC_CODE,
													BALANCE_DATE,
													SNTB,
													SA_AMOUNT,
													TBNTB,
													TB_AMOUNT,
													CBNTB,
													CB_AMOUNT)
												VALUES
													(".$FUND_CODE.",
													".$AC_CODE.",
													'".date("Y-m-d", strtotime($businessDate))."',
													'C',
													".abs($BAL_AMOUNT).",
													'C',
													".abs($BAL_AMOUNT).",
													'C',
													".abs($BAL_AMOUNT).")
												";
					} else {
						$insertTrialBalCBData = "	
												INSERT INTO a_trial_bal
													(BRANCH_ID,
													AC_CODE,
													BALANCE_DATE,
													SNTB,
													SA_AMOUNT,
													TBNTB,
													TB_AMOUNT,
													CBNTB,
													CB_AMOUNT)
												VALUES
													(".$FUND_CODE.",
													".$AC_CODE.",
													'".date("Y-m-d", strtotime($businessDate))."',
													'D',
													".abs($BAL_AMOUNT).",
													'D',
													".abs($BAL_AMOUNT).",
													'D',
													".abs($BAL_AMOUNT).")
												";				
					}
					$insertTrialBalCBDataStatement 	= $this->tableGateway->getAdapter()->createStatement($insertTrialBalCBData);
					$insertTrialBalCBDataStatement->prepare();
					$insertTrialBalCBDataResult 	= $insertTrialBalCBDataStatement->execute();
				}
				// Declaration of Cash at Bank & Cash in Hand coa Record Cursore End By Akhand
			}
			//Active Branch Data End By Akhand
			
			// Previous Date Without Cash at Bank & Cash in Hand coa Record Cursore Start By Akhand
			$PREVIOUS_DATE	= date('Y-m-d', strtotime('-1 day', strtotime($businessDate)));
			$getPreviousDateWithoutCashBankDataSql	= "
														SELECT 
																AC_CODE 		AC_CODE,
																BRANCH_ID 		FUND_CODE,
																SUM(IF(SNTB='D',
																	SA_AMOUNT,
																	-SA_AMOUNT)) BAL_AMOUNT
														FROM 
																a_trial_bal
														WHERE 
																BALANCE_DATE between '".$PREVIOUS_DATE."' and '".$businessDate."'
														AND 	SUBSTR(AC_CODE, 1, 3) NOT IN ('303', '304')
														GROUP BY
																AC_CODE, 
																BRANCH_ID
			";
			$getPreviousDateWithoutCashBankDataStatement 	= $this->tableGateway->getAdapter()->createStatement($getPreviousDateWithoutCashBankDataSql);
			$getPreviousDateWithoutCashBankDataStatement->prepare();
			$getPreviousDateWithoutCashBankDataResult 		= $getPreviousDateWithoutCashBankDataStatement->execute();
			if ($getPreviousDateWithoutCashBankDataResult instanceof ResultInterface && $getPreviousDateWithoutCashBankDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getPreviousDateWithoutCashBankDataResult);
			}
			foreach($resultSet as $resultPreviousDateWithoutCashBankData) {
				$AC_CODE	= $resultPreviousDateWithoutCashBankData['AC_CODE'];
				$FUND_CODE	= $resultPreviousDateWithoutCashBankData['FUND_CODE'];
				$BAL_AMOUNT	= $resultPreviousDateWithoutCashBankData['BAL_AMOUNT'];
				
				if($BAL_AMOUNT>0) {
					//Count Existing Data Exist Start By Akhand 
					$EXISTING_DATA_FOUND	= 0;
					$getExistingDataSql	= "
											SELECT 
													COUNT(AC_CODE) 		AC_CODE
											FROM 
													a_trial_bal
											WHERE 
													BALANCE_DATE 	= '".$businessDate."'
											AND 	AC_CODE			= ".$AC_CODE."
											AND		BRANCH_ID		= ".$FUND_CODE."
					";
					$getExistingDataStatement 	= $this->tableGateway->getAdapter()->createStatement($getExistingDataSql);
					$getExistingDataStatement->prepare();
					$getExistingDataResult 		= $getExistingDataStatement->execute();
					if ($getExistingDataResult instanceof ResultInterface && $getExistingDataResult->isQueryResult()) {
						$resultSet = new ResultSet();
						$resultSet->initialize($getExistingDataResult);
					}
					foreach($resultSet as $resultExistingDataResult) {
						$EXISTING_DATA_FOUND	= $resultExistingDataResult['AC_CODE'];
					}
					//Count Existing Data Exist End By Akhand
					if($EXISTING_DATA_FOUND>0) {
						$insertUpdateTrialBalData = "	
														UPDATE 
																a_trial_bal
														SET 
																SA_AMOUNT = ".abs($BAL_AMOUNT).",
																SNTB      = 'D',
																CB_AMOUNT = ".abs($BAL_AMOUNT).",
																CBNTB     = 'D'
														WHERE 
																BALANCE_DATE 	= '".$businessDate."'
														AND 	AC_CODE			= ".$AC_CODE."
														AND		BRANCH_ID		= ".$FUND_CODE."
													";
					} else {
						$insertUpdateTrialBalData = "	
													INSERT INTO a_trial_bal
														(BALANCE_DATE,
														 AC_CODE,
														 BRANCH_ID,
														 SNTB,
														 SA_AMOUNT,
														 CBNTB,
														 CB_AMOUNT)
													VALUES
														(
														'".date("Y-m-d", strtotime($businessDate))."',
														".$AC_CODE.",
														".$FUND_CODE.",
														'D',
														".abs($BAL_AMOUNT).",
														'D',
														".abs($BAL_AMOUNT).")
													";	
					}
					$insertUpdateTrialBalDataStatement 	= $this->tableGateway->getAdapter()->createStatement($insertUpdateTrialBalData);
					$insertUpdateTrialBalDataStatement->prepare();
					$insertUpdateTrialBalDataResult 		= $insertUpdateTrialBalDataStatement->execute();
				} else {
					//Count Existing Data Exist Start By Akhand 
					$EXISTING_DATA_FOUND	= 0;
					$getExistingDataSql	= "
											SELECT 
													COUNT(AC_CODE) 		AC_CODE
											FROM 
													a_trial_bal
											WHERE 
													BALANCE_DATE 	= '".$businessDate."'
											AND 	AC_CODE			= ".$AC_CODE."
											AND		BRANCH_ID		= ".$FUND_CODE."
					";
					$getExistingDataStatement 	= $this->tableGateway->getAdapter()->createStatement($getExistingDataSql);
					$getExistingDataStatement->prepare();
					$getExistingDataResult 		= $getExistingDataStatement->execute();
					if ($getExistingDataResult instanceof ResultInterface && $getExistingDataResult->isQueryResult()) {
						$resultSet = new ResultSet();
						$resultSet->initialize($getExistingDataResult);
					}
					foreach($resultSet as $resultExistingDataResult) {
						$EXISTING_DATA_FOUND	= $resultExistingDataResult['AC_CODE'];
					}
					//Count Existing Data Exist End By Akhand
					if($EXISTING_DATA_FOUND>0) {
						$insertUpdateTrialBalData = "	
														UPDATE 
																a_trial_bal
														SET 
																SA_AMOUNT = ".abs($BAL_AMOUNT).",
																SNTB      = 'C',
																CB_AMOUNT = ".abs($BAL_AMOUNT).",
																CBNTB     = 'C'
														WHERE 
																BALANCE_DATE 	= '".$businessDate."'
														AND 	AC_CODE			= ".$AC_CODE."
														AND		BRANCH_ID		= ".$FUND_CODE."
													";
					} else {
						$insertUpdateTrialBalData = "	
													INSERT INTO a_trial_bal
														(BALANCE_DATE,
														 AC_CODE,
														 BRANCH_ID,
														 SNTB,
														 SA_AMOUNT,
														 CBNTB,
														 CB_AMOUNT)
													VALUES
														(
														'".date("Y-m-d", strtotime($businessDate))."',
														".$AC_CODE.",
														".$FUND_CODE.",
														'C',
														".abs($BAL_AMOUNT).",
														'C',
														".abs($BAL_AMOUNT).")
													";	
					}
					$insertUpdateTrialBalDataStatement 	= $this->tableGateway->getAdapter()->createStatement($insertUpdateTrialBalData);
					$insertUpdateTrialBalDataStatement->prepare();
					$insertUpdateTrialBalDataResult 		= $insertUpdateTrialBalDataStatement->execute();	
				}
			}
			// Previous Date Without Cash at Bank & Cash in Hand coa Record Cursore End By Akhand
			
			// Previous Date With Cash at Bank & Cash in Hand coa Record Cursore Start By Akhand
			$PREVIOUS_DATE	= date('Y-m-d', strtotime('-1 day', strtotime($businessDate)));
			$getPreviousDateWithoutCashBankDataSql	= "
														SELECT 
																AC_CODE 		AC_CODE,
																BRANCH_ID 		FUND_CODE,
																SUM(IF(SNTB='D',
																	-SA_AMOUNT,
																	SA_AMOUNT)) BAL_AMOUNT
														FROM 
																a_trial_bal
														WHERE 
																BALANCE_DATE between '".$PREVIOUS_DATE."' and '".$businessDate."'
														AND 	SUBSTR(AC_CODE, 1, 3) IN ('303', '304')
														GROUP BY
																AC_CODE, 
																BRANCH_ID
			";
			$getPreviousDateWithoutCashBankDataStatement 	= $this->tableGateway->getAdapter()->createStatement($getPreviousDateWithoutCashBankDataSql);
			$getPreviousDateWithoutCashBankDataStatement->prepare();
			$getPreviousDateWithoutCashBankDataResult 		= $getPreviousDateWithoutCashBankDataStatement->execute();
			if ($getPreviousDateWithoutCashBankDataResult instanceof ResultInterface && $getPreviousDateWithoutCashBankDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getPreviousDateWithoutCashBankDataResult);
			}
			foreach($resultSet as $resultPreviousDateWithoutCashBankData) {
				$AC_CODE	= $resultPreviousDateWithoutCashBankData['AC_CODE'];
				$FUND_CODE	= $resultPreviousDateWithoutCashBankData['FUND_CODE'];
				$BAL_AMOUNT	= $resultPreviousDateWithoutCashBankData['BAL_AMOUNT'];
				
				if($BAL_AMOUNT>0) {
					//Count Existing Data Exist Start By Akhand 
					$EXISTING_DATA_FOUND	= 0;
					$getExistingDataSql	= "
											SELECT 
													COUNT(AC_CODE) 		AC_CODE
											FROM 
													a_trial_bal
											WHERE 
													BALANCE_DATE 	= '".$businessDate."'
											AND 	AC_CODE			= ".$AC_CODE."
											AND		BRANCH_ID		= ".$FUND_CODE."
					";
					$getExistingDataStatement 	= $this->tableGateway->getAdapter()->createStatement($getExistingDataSql);
					$getExistingDataStatement->prepare();
					$getExistingDataResult 		= $getExistingDataStatement->execute();
					if ($getExistingDataResult instanceof ResultInterface && $getExistingDataResult->isQueryResult()) {
						$resultSet = new ResultSet();
						$resultSet->initialize($getExistingDataResult);
					}
					foreach($resultSet as $resultExistingDataResult) {
						$EXISTING_DATA_FOUND	= $resultExistingDataResult['AC_CODE'];
					}
					//Count Existing Data Exist End By Akhand
					if($EXISTING_DATA_FOUND>0) {
						$insertUpdateTrialBalData = "	
														UPDATE 
																a_trial_bal
														SET 
																SA_AMOUNT = ".abs($BAL_AMOUNT).",
																SNTB      = 'C',
																CB_AMOUNT = ".abs($BAL_AMOUNT).",
																CBNTB     = 'C'
														WHERE 
																BALANCE_DATE 	= '".$businessDate."'
														AND 	AC_CODE			= ".$AC_CODE."
														AND		BRANCH_ID		= ".$FUND_CODE."
													";
					} else {
						$insertUpdateTrialBalData = "	
													INSERT INTO a_trial_bal
														(BALANCE_DATE,
														 AC_CODE,
														 BRANCH_ID,
														 SNTB,
														 SA_AMOUNT,
														 CBNTB,
														 CB_AMOUNT)
													VALUES
														(
														'".date("Y-m-d", strtotime($businessDate))."',
														".$AC_CODE.",
														".$FUND_CODE.",
														'C',
														".abs($BAL_AMOUNT).",
														'C',
														".abs($BAL_AMOUNT).")
													";	
					}
					$insertUpdateTrialBalDataStatement 	= $this->tableGateway->getAdapter()->createStatement($insertUpdateTrialBalData);
					$insertUpdateTrialBalDataStatement->prepare();
					$insertUpdateTrialBalDataResult 		= $insertUpdateTrialBalDataStatement->execute();
				} else {
					//Count Existing Data Exist Start By Akhand 
					$EXISTING_DATA_FOUND	= 0;
					$getExistingDataSql	= "
											SELECT 
													COUNT(AC_CODE) 		AC_CODE
											FROM 
													a_trial_bal
											WHERE 
													BALANCE_DATE 	= '".$businessDate."'
											AND 	AC_CODE			= ".$AC_CODE."
											AND		BRANCH_ID		= ".$FUND_CODE."
					";
					$getExistingDataStatement 	= $this->tableGateway->getAdapter()->createStatement($getExistingDataSql);
					$getExistingDataStatement->prepare();
					$getExistingDataResult 		= $getExistingDataStatement->execute();
					if ($getExistingDataResult instanceof ResultInterface && $getExistingDataResult->isQueryResult()) {
						$resultSet = new ResultSet();
						$resultSet->initialize($getExistingDataResult);
					}
					foreach($resultSet as $resultExistingDataResult) {
						$EXISTING_DATA_FOUND	= $resultExistingDataResult['AC_CODE'];
					}
					//Count Existing Data Exist End By Akhand
					if($EXISTING_DATA_FOUND>0) {
						$insertUpdateTrialBalData = "	
														UPDATE 
																a_trial_bal
														SET 
																SA_AMOUNT = ".abs($BAL_AMOUNT).",
																SNTB      = 'D',
																CB_AMOUNT = ".abs($BAL_AMOUNT).",
																CBNTB     = 'D'
														WHERE 
																BALANCE_DATE 	= '".$businessDate."'
														AND 	AC_CODE			= ".$AC_CODE."
														AND		BRANCH_ID		= ".$FUND_CODE."
													";
					} else {
						$insertUpdateTrialBalData = "	
													INSERT INTO a_trial_bal
														(BALANCE_DATE,
														 AC_CODE,
														 BRANCH_ID,
														 SNTB,
														 SA_AMOUNT,
														 CBNTB,
														 CB_AMOUNT)
													VALUES
														(
														'".date("Y-m-d", strtotime($businessDate))."',
														".$AC_CODE.",
														".$FUND_CODE.",
														'D',
														".abs($BAL_AMOUNT).",
														'D',
														".abs($BAL_AMOUNT).")
													";	
					}
					$insertUpdateTrialBalDataStatement 	= $this->tableGateway->getAdapter()->createStatement($insertUpdateTrialBalData);
					$insertUpdateTrialBalDataStatement->prepare();
					$insertUpdateTrialBalDataResult 		= $insertUpdateTrialBalDataStatement->execute();	
				}
			}
			// Previous Date With Cash at Bank & Cash in Hand coa Record Cursore End By Akhand
			return true;
		}
		
		// Voucher Report Start By Akhand
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
			$getTblDataSql   = "SELECT 
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
		
		public function fetchBranchWiseVoucherDetails($branchID,$cond) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "
								SELECT 		
										DISTINCT
												DATE_FTDT,
												VOUCHER_NO_,
												PARTICULARS_,
												NARRATION_,
												DEBIT_,
												CREDIT_,
												COA_CODE_,
												INVOICE_NO,
												MONEY_RECEIPT_NO,
												CHQ_NO,
												CHQ_DATE,
												DRAWN_ON_
								FROM 	
										view_daily_transaction 
								WHERE 	
										FUND_ID_='".$branchID."' 
										{$cond}
								ORDER BY 
										DATE_FTDT, 
										VOUCHER_NO_, 
										DEBIT_ DESC
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
		// Voucher Report End By Akhand
		
		// Cash Book Report Start By Akhand
		public function fetchCashBookDetails($accCode,$transDateFrom,$branch_id) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "
								SELECT 
								COALESCE(SUM(IF(AMOUNT_='D',AMOUNT_,-AMOUNT_)),0) AS OPENING_BAL								
								FROM view_cash_book
								WHERE FUND_L = '".$branch_id."'
								AND CB_CODE_T = '{$accCode}'
								AND DATE_FTDT < '".date("Y-m-d", strtotime($transDateFrom))."'
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
		
		public function fetchViewCashBookDetails($branchName,$cond) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "
									SELECT 	
											DATE_FTDT AS TRAN_DATE,
											VOUCHER_NO_,
											PARTICULARS_,
											NARRATION_,
											IF (sign(AMOUNT_)= -1, AMOUNT_,0) DEBIT_,
											IF (sign(AMOUNT_)= -1, 0,AMOUNT_) CREDIT_,
											CB_CODE_T
									FROM 	view_cash_book 
									WHERE 	
											FUND_L='".$branchName."' 
									{$cond}
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
		
		public function fetchSuggestedRefCOACode($input) {
			$getTblDataSql   = "SELECT COA_NAME FROM gs_coa where COA_CODE = '".$input."'";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		// Cash Book Report End By Akhand
		
		// Bank Book Report Start By Akhand
		public function fetchBankBookDetails($accCode,$transDateFrom,$branch_id) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "SELECT 
								COALESCE(IF(CBNTB='D',CB_AMOUNT,-CB_AMOUNT)) AS OPENING_BAL								
								FROM a_trial_bal
								WHERE BRANCH_ID = '".$branch_id."'
								AND AC_CODE = '{$accCode}'
								AND BUSINESS_DATE = (SELECT MAX(TB.BUSINESS_DATE)
															   FROM a_trial_bal TB
															   WHERE TB.AC_CODE = '{$accCode}'
															   AND TB.BRANCH_ID = '".$branch_id."'
															   AND TB.BUSINESS_DATE < '".date("Y-m-d", strtotime($transDateFrom))."')";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function fetchViewBankBookDetails($branchName,$cond) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "
									SELECT 	
											DATE_FTDT AS TRAN_DATE,
											VOUCHER_NO_,
											PARTICULARS_,
											NARRATION_,
											ABS(IF (sign(AMOUNT_)= -1, AMOUNT_,0)) DEBIT_,
											IF (sign(AMOUNT_)= -1, 0,AMOUNT_) CREDIT_,
											CB_CODE_T,
											CHQ_NO,
											CHQ_FTDT,
											DRAWN_ON
									FROM 	view_bank_book 
									WHERE 	
											FUND_L='".$branchName."' 
									{$cond}
									ORDER BY view_bank_book.DATE_FTDT, view_bank_book.RECORD_DATE ASC								
								";
								//ORDER BY TRAN_DATE ASC
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		// Bank Book Report End By Akhand
		
		//Financial Ststement Report Start By Akhand
		public function fetchBranchTrialBalAccCode($branchID) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "SELECT DISTINCT
									  ACCOUNT_CODE,
									  HEAD
								  FROM   view_trial_balance 
								  WHERE   
									  view_trial_balance.BRANCH_ID='".$branchID."'
								  ORDER BY ACCOUNT_CODE ASC
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
		
		public function fetchOpeningBalance($accCode1,$fromDate,$branch_id,$branchName) {
			$cMonth = date("m",strtotime($fromDate));
			$cYear = date("Y",strtotime($fromDate));
			$openingBalance = 0.00;
			$fromDate		= date('Y-m-d',strtotime($fromDate));
			// Income Expense Date Cond Start ///
			$getFSYSDSql   = "SELECT T.FISCAL_START FROM l_fiscal_year T WHERE '".$fromDate."' BETWEEN T.FISCAL_START AND T.FISCAL_END";
			$getFSYSDStatement = $this->tableGateway->getAdapter()->createStatement($getFSYSDSql);
			$getFSYSDStatement->prepare();
			$getFSYSDResult 	= $getFSYSDStatement->execute();
			if ($getFSYSDResult instanceof ResultInterface && $getFSYSDResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getFSYSDResult);
			}
			//return $resultSet;
			$fiscalYearStartDate = '';
			foreach ($resultSet as $fiscalYearStartDateData) {
				$fiscalYearStartDate = date('Y-m-d',strtotime($fiscalYearStartDateData['FISCAL_START']));
			}
			$endDateForIncomeExpense = date('Y-m-d', strtotime($fromDate .' -1 day'));
			$cond = '';
			if ((substr($accCode1,0,1) == '5') || (substr($accCode1,0,1) == '6')) {
				$fiscalYearStartDate 	= "'".$fiscalYearStartDate."'";
				$endDateForIncomeExpense 	= "'".$endDateForIncomeExpense."'";
				$cond = "AND DATE_FTMY BETWEEN $fiscalYearStartDate AND $endDateForIncomeExpense";
				$getTblDataSql   = "SELECT										
											SUM(DEBIT_-CREDIT_) AS OPENING_LEDGER
									FROM 	view_ledger 
									WHERE 	
											FUND_L='".$branchName."' 
									AND ACCOUNT_CODE_T = '".$accCode1."' 
									{$cond}
				";
				$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
				$getTblDataStatement->prepare();
				$getTblDataResult 	= $getTblDataStatement->execute();
				if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($getTblDataResult);
				}
				$openingLedger = 0.00;
				foreach ($resultSet as $openingBalanceInfoData) {
					$openingLedger = $openingBalanceInfoData['OPENING_LEDGER'];
					$openingBalance = $openingLedger;
				}
			} 
			// Income Expense Date Cond End ///
			else {
				$lastmonth = $cMonth-1;
				$lastDayOfPreMonth = date("Y-m-t", mktime(0, 0, 0, $lastmonth, 1, $cYear));
				$getTblDataSql   = "SELECT IF(TB.CBNTB = 'D', TB.CB_AMOUNT, -TB.CB_AMOUNT) OPENING_BAL
										  FROM a_trial_bal TB, c_branch P
										 WHERE TB.AC_CODE = '".$accCode1."'
										   AND TB.BRANCH_ID = P.BRANCH_ID
										   AND (P.BRANCH_ID) = '".$branch_id."'
										   AND TB.BALANCE_DATE =
											   (SELECT MAX(MTB.BALANCE_DATE)
												  FROM a_trial_bal MTB
												 WHERE MTB.AC_CODE = TB.AC_CODE
												   AND MTB.BRANCH_ID = TB.BRANCH_ID
												   AND MTB.BALANCE_DATE <= '".$lastDayOfPreMonth."')
				";
				$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
				$getTblDataStatement->prepare();
				$getTblDataResult 	= $getTblDataStatement->execute();
				if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($getTblDataResult);
				}
				//return $resultSet;
				foreach ($resultSet as $openingBalanceInfoData) {
					$openingBalance = $openingBalanceInfoData['OPENING_BAL'];
				}
				
				$startDate = date('Y-m-d', strtotime($fromDate .' -1 day'));
				/*$cDay = date("d",strtotime($fromDate));
				if($cDay >1){
					$lastDay = $cDay-1;
				} else {
					$lastDay = $cDay;
				}
				$endDate = $lastDay.'-'.$cMonth.'-'.$cYear;				
				//$endDate = date('d-m-Y', strtotime($fromDate .' -1 day'));*/
				$cond = '';
				
				$transDateFrom = date('Y-m-d',strtotime($startDate));
				
				$cond = "AND DATE_FTMY <='$transDateFrom'";
				$getTblDataSql   = "SELECT										
											SUM(DEBIT_-CREDIT_) AS OPENING_LEDGER
									FROM 	view_ledger 
									WHERE 	
											FUND_L='".$branchName."' 
									AND ACCOUNT_CODE_T = '".$accCode1."' 
									{$cond}
				";
				$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
				$getTblDataStatement->prepare();
				$getTblDataResult 	= $getTblDataStatement->execute();
				if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($getTblDataResult);
				}
				$openingLedger = 0.00;
				foreach ($resultSet as $openingBalanceInfoData) {
					$openingLedger = $openingBalanceInfoData['OPENING_LEDGER'];
					$openingBalance = $openingBalance + $openingLedger;
				}
			}
			//return $resultSet;
			return $openingBalance;
		}
		
		public function fetchFromViewLedger($branchName,$cond) {
			$getTblDataSql   = "SELECT 	
										DATE_FTMY DATE_FTMY,
										VOUCHER_NO_,
										PARTICULARS_,
										NARRATION_,
										ACCOUNT_CODE_T,
										HEAD_T,
										DEBIT_,
										CREDIT_
								FROM 	view_ledger 
								WHERE 	
										FUND_L='".$branchName."' 
								{$cond}";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function fetchSumBankBookAmount($accCode,$branch_id,$condforTranDetails) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "SELECT COALESCE(SUM(IF(AMOUNT_='D', AMOUNT_, -AMOUNT_)), 0) AS SUMOFBANKAMOUNT
								  FROM view_bank_book
								 WHERE FUND_L = '".$branch_id."'
								   AND CB_CODE_T = '".$accCode."'
								   AND DATE_FTDT {$condforTranDetails}
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
		
		public function fetchSumCashBookAmount($accCode,$branch_id,$condforTranDetails) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "SELECT COALESCE(SUM(IF(AMOUNT_= 'D', AMOUNT_, -AMOUNT_)), 0) AS SUMOFCASHAMOUNT
								  FROM view_cash_book
								 WHERE FUND_L = '".$branch_id."'
								   AND CB_CODE_T = '".$accCode."'
								   AND DATE_FTDT {$condforTranDetails}
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
		
		public function fetchBranchTranDetailsDrCrData($accountCode,$branchID,$condforTranDetails) {
			$getTblDataSql   = "
								SELECT 
										COALESCE(SUM(IF(TC.NTR = 'D', TC.AMOUNT, -TC.AMOUNT)), 0) TRANDETAILS_DC
								FROM 
										a_transaction_child TC, 
										a_transaction_master TM, 
										c_branch P
								WHERE 
										TC.AC_CODE 			= '".$accountCode."'
								AND 	TC.BRANCH_ID 		= P.BRANCH_ID
								AND 	LOWER(P.BRANCH_ID) 	= '".$branchID."'
								AND 	TC.TRAN_NO 			= TM.TRAN_NO 
								AND 	TM.TRAN_DATE 
								{$condforTranDetails}
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
		
		public function fetchSumFromViewLedger($accountCode,$branchID,$condforTranDetails) {
			$getTblDataSql   = "
									SELECT 
											COALESCE(SUM(DEBIT_ - CREDIT_),0) AS AMOUNT
									FROM 
											view_ledger
									WHERE 
											FUND_L 			= '".$branchID."'
									AND 	ACCOUNT_CODE_T 	= '".$accountCode."'
									AND 	DATE_FTMY 
									{$condforTranDetails}
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
		//Financial Ststement Report End By Akhand
		
		// Income Ststement Report Start BY Akhand
		public function fetchBranchIncomeCOAHead($branchName,$cond) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "SELECT 	
												HEAD_T,
												COALESCE(SUM(CA_AMNT_),0) AMOUNT
										FROM 	view_income_statement 
										WHERE 	INC_OR_EXP_='INCOME' 
										AND		FUND_L='".$branchName."'
										{$cond}
										GROUP BY HEAD_T
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
		
		public function fetchBranchExpenditureCOAHead($branchName,$cond) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "SELECT 	
												HEAD_T,
												COALESCE(SUM(CA_AMNT_),0) AMOUNT
										FROM 	view_income_statement 
										WHERE 	INC_OR_EXP_='EXPENDITURE' 
										AND		FUND_L='".$branchName."'
										{$cond}
										GROUP BY HEAD_T
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
		// Income Ststement Report End BY Akhand
		
		// Daily Transaction Report Start BY Akhand
		public function fetchFromViewDailyTransaction($branchName,$cond) {
			$getTblDataSql   = "SELECT 		DISTINCT
											DATE_FTDT,
											VOUCHER_NO_,
											PARTICULARS_,
											NARRATION_,
											DEBIT_,
											CREDIT_
								FROM 	
											view_daily_transaction 
								WHERE 	
											FUND_L='".$branchName."' 
									{$cond} 
								ORDER BY VOUCHER_NO_, DEBIT_ DESC
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
		// Daily Transaction Report End BY Akhand
		
		// Bank Reconcile Report Start BY Akhand
		public function fetchBankReconReport($branchName,$cond) {
			$getTblDataSql   = "SELECT 	
										DATE_FTDT,
										VOUCHER_NO_,
										PARTICULARS_,
										NARRATION_,
										IF (sign(AMOUNT_)= -1, AMOUNT_,0) DEBIT_,
										IF (sign(AMOUNT_)= -1, 0,AMOUNT_) CREDIT_,
										CB_CODE_T,
										CHQ_NO,
										BANK_FTDT,
										TRAN_NO_
								FROM 	view_bank_book 
								WHERE 	
										FUND_L='".$branchName."' 
								{$cond}
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
		// Bank Reconcile Report End BY Akhand
		
		// Balance Sheet Report Start BY Akhand
		public function fetchSumOfCurrAssetFromBalanceSheet($branchID,$balDate) {
			$getTblDataSql   = "SELECT 
										SUM(AMOUNT_) AS TOTCURRASSETAMOUNT
								FROM 	view_balance_sheet 
								WHERE 	BS_HEAD_='Current Asset'
								AND		PORTFOLIO_L='".$branchID."' 
								AND 	DATE_DT = '".$balDate."'
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
		public function fetchNonCASumAmountFromBS($branchID,$balDate) {
			$getTblDataSql   = "SELECT 	SUM(AMOUNT_) AS TOTSUMAMOUNT
									FROM 	view_balance_sheet 
									WHERE 	BS_HEAD_='Non Current Assets' 
									AND		PORTFOLIO_L='".$branchID."' 
									AND 	DATE_DT = '".$balDate."'
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
		public function fetchNonCurrAssetFromBalanceSheet($branchID,$balDate) {
			$getTblDataSql   = "SELECT 	
											ACCOUNT_HEAD_,
											ACCOUNT_CODE_,
											AMOUNT_
									FROM 	view_balance_sheet 
									WHERE 	BS_HEAD_='Non Current Assets' 
									AND		PORTFOLIO_L='".$branchID."' 
									AND 	DATE_DT = '".$balDate."'
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
		
		public function fetchNonCurrentAssetsecondStep($branchID,$nonCurrAssetCoaCode,$balDate1) {
			$getTblDataSql = "SELECT dredistcoa.COA_CODE, gs_coa.COA_NAME
								  FROM (SELECT DISTINCT SUBSTR(COA_CODE, 1, 6) AS COA_CODE
										  FROM (SELECT TB.AC_CODE AS COA_CODE
												  FROM a_trial_bal TB, c_branch P, gs_coa AC
												 WHERE TB.BRANCH_ID = P.BRANCH_ID
												   AND P.BRANCH_ID = '".$branchID."'
												   AND COALESCE(TB.SA_AMOUNT, 0) > 0
												   AND SUBSTR(TB.AC_CODE, 1, 3) =
													   (SELECT DISTINCT SUBSTR(COA_CODE, 1, 3)
														  FROM gs_coa
														 WHERE COA_CODE = '".$nonCurrAssetCoaCode."')
												    AND 	TB.BALANCE_DATE = '".$balDate1."'
												   AND TB.AC_CODE = AC.COA_CODE
												 ORDER BY TB.AC_CODE ASC)test_coa) dredistcoa,
									   gs_coa
								  WHERE CONCAT(SUBSTR(dredistcoa.COA_CODE, 1, 6),'000') = gs_coa.COA_CODE";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchNonCurrAssetSecStepSumAmount($branchID,$nonCurrAssetCoaCode,$balDate1) {
			$getTblDataSql   = "SELECT SUM(IF(TB.SNTB= 'D', COALESCE(TB.SA_AMOUNT, 0), -COALESCE(TB.SA_AMOUNT, 0))) AMOUNT
								  FROM a_trial_bal TB, c_branch P, gs_coa AC
								 WHERE TB.BRANCH_ID = P.BRANCH_ID
								   AND P.BRANCH_ID =
									   (SELECT BRANCH_ID FROM c_branch WHERE BRANCH_ID = '".$branchID."')
								   AND COALESCE(TB.SA_AMOUNT, 0) > 0
								   AND SUBSTR(TB.AC_CODE, 1, 6) =
									   (SELECT DISTINCT SUBSTR(COA_CODE, 1, 6)
										  FROM gs_coa
										 WHERE SUBSTR(COA_CODE, 1, 6) = '".$nonCurrAssetCoaCode."')
								   AND TB.BALANCE_DATE <= '".$balDate1."'
								   AND TB.AC_CODE = AC.COA_CODE
								 ORDER BY TB.AC_CODE ASC
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
		public function fetchNonCurrentAssetThirdStep($branchID,$nonCurrAssetCOACodeSecondStep,$balDate1) {
			$getTblDataSql   = "SELECT AC.COA_NAME,
										   TB.AC_CODE,
										   IF(TB.SNTB= 'D', COALESCE(TB.SA_AMOUNT, 0), -COALESCE(TB.SA_AMOUNT, 0)) AMOUNT
									  FROM a_trial_bal TB, c_branch P, gs_coa AC
									 WHERE TB.BRANCH_ID = P.BRANCH_ID
									   AND P.BRANCH_ID =
										   (SELECT BRANCH_ID
											  FROM c_branch
											 WHERE BRANCH_ID = '".$branchID."')
									   AND COALESCE(TB.SA_AMOUNT, 0) > 0
									   AND SUBSTR(TB.AC_CODE, 1, 6) =
										   (SELECT DISTINCT SUBSTR(COA_CODE, 1, 6)
											  FROM gs_coa
											 WHERE SUBSTR(COA_CODE, 1, 6) = '".$nonCurrAssetCOACodeSecondStep."')
									   AND TB.BALANCE_DATE = '".$balDate1."'
									   AND TB.AC_CODE = AC.COA_CODE
									 ORDER BY TB.AC_CODE ASC
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
		public function fetchCurrAssetFromBalanceSheet($branchID,$balDate) {
			$getTblDataSql   = "SELECT 	
										ACCOUNT_HEAD_,
										AMOUNT_,
										ACCOUNT_CODE_
								FROM 	view_balance_sheet 
								WHERE 	BS_HEAD_='Current Asset'
								AND		PORTFOLIO_L='".$branchID."' 
								AND 	DATE_DT = '".$balDate."'
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
		public function fetchSumOfEquityAttToOwnerFromBS($branchID,$balDate) {
			$getTblDataSql   = "SELECT 
										SUM(AMOUNT_) TOTALEQUITY
								FROM 	view_balance_sheet 
								WHERE 	BS_HEAD_= 'Equity attributable to owners'
								AND		PORTFOLIO_L='".$branchID."' 
								AND 	DATE_DT = '".$balDate."'
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
		
		public function fetchEquityAttToOwnSecStep($branchID,$balDate1,$equityAttToOwnCOACode) {
			$getTblDataSql   = "SELECT dredistcoa.COA_CODE, gs_coa.COA_NAME
								  FROM (SELECT DISTINCT SUBSTRING(COA_CODE, 1, 6) AS COA_CODE
										  FROM (SELECT TB.AC_CODE AS COA_CODE
												  FROM a_trial_bal TB, c_branch P, gs_coa AC
												 WHERE TB.BRANCH_ID = P.BRANCH_ID
												   AND P.BRANCH_ID = '".$branchID."'
												   AND COALESCE(TB.SA_AMOUNT, 0) > 0
												   AND SUBSTRING(TB.AC_CODE, 1, 3) =
													   (SELECT DISTINCT SUBSTRING(COA_CODE, 1, 3)
														  FROM gs_coa
														 WHERE COA_CODE = '".$equityAttToOwnCOACode."')
												    AND 	TB.BALANCE_DATE = '".$balDate1."'
												   AND TB.AC_CODE = AC.COA_CODE
												 ORDER BY TB.AC_CODE ASC)test_coa) dredistcoa,
									   gs_coa
								  WHERE CONCAT(SUBSTRING(dredistcoa.COA_CODE, 1, 6), '000') = gs_coa.COA_CODE
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
		public function fetchEqAttToOwnrSecStepSumAmount($branchID,$balDate1,$equityAttToOwnerCoaCode) {
			$getTblDataSql   = "SELECT SUM(IF(TB.SNTB= 'D', COALESCE(TB.SA_AMOUNT, 0), -COALESCE(TB.SA_AMOUNT, 0))) AMOUNT
								  FROM a_trial_bal TB, c_branch P, gs_coa AC
								 WHERE TB.BRANCH_ID = P.BRANCH_ID
								   AND P.BRANCH_ID =
									   (SELECT BRANCH_ID FROM c_branch WHERE BRANCH_ID = '".$branchID."')
								   AND COALESCE(TB.SA_AMOUNT, 0) > 0
								   AND SUBSTR(TB.AC_CODE, 1, 6) =
									   (SELECT DISTINCT SUBSTR(COA_CODE, 1, 6)
										  FROM gs_coa
										 WHERE SUBSTR(COA_CODE, 1, 6) = '".$equityAttToOwnerCoaCode."')
								   AND TB.BALANCE_DATE <= '".$balDate1."'
								   AND TB.AC_CODE = AC.COA_CODE
								 ORDER BY TB.AC_CODE ASC
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
		
		public function fetchCurrLiabSumAmountFromBS($branchID,$balDate) {
			$getTblDataSql   = "SELECT 	SUM(AMOUNT_) AS TOTLIABAMOUNT
								FROM 	view_balance_sheet 
								WHERE 	BS_HEAD_= 'Current liabilities'
								AND		PORTFOLIO_L='".$branchID."' 
								AND 	DATE_DT = '".$balDate."'
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
		
		public function fetchCurrLiabFromBS($branchID,$balDate) {
			$getTblDataSql   = "SELECT 	
										ACCOUNT_HEAD_,
										AMOUNT_,
										ACCOUNT_CODE_
								FROM 	view_balance_sheet 
								WHERE 	BS_HEAD_= 'Current liabilities'
								AND		PORTFOLIO_L='".$branchID."' 
								AND 	DATE_DT = '".$balDate."'
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
		
		public function fetchLiabSecStepFromBalShet($branchID,$balDate1,$CurrentLiabilitiesCOACode) {
			$getTblDataSql   = "SELECT dredistcoa.COA_CODE, gs_coa.COA_NAME
								  FROM (SELECT DISTINCT SUBSTR(COA_CODE, 1, 6) AS COA_CODE
										  FROM (SELECT TB.AC_CODE AS COA_CODE
												  FROM a_trial_bal TB, c_branch P, gs_coa AC
												 WHERE TB.BRANCH_ID = P.BRANCH_ID
												   AND P.BRANCH_ID = '".$branchID."'
												   AND COALESCE(TB.SA_AMOUNT, 0) > 0
												   AND SUBSTR(TB.AC_CODE, 1, 3) =
													   (SELECT DISTINCT SUBSTR(COA_CODE, 1, 3)
														  FROM gs_coa
														 WHERE COA_CODE = '".$CurrentLiabilitiesCOACode."')
												    AND 	TB.BALANCE_DATE = '".$balDate1."'
												   AND TB.AC_CODE = AC.COA_CODE
												 ORDER BY TB.AC_CODE ASC)test_coa) dredistcoa,
									   gs_coa
								  WHERE CONCAT(SUBSTR(dredistcoa.COA_CODE, 1, 6), '000') = gs_coa.COA_CODE
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
		
		public function fetchLiabSecStepSumAmount($branchID,$balDate1,$currLiabilitiesCoaCode) {
			$getTblDataSql   = "SELECT SUM(IF(TB.SNTB= 'D', COALESCE(TB.SA_AMOUNT, 0), -COALESCE(TB.SA_AMOUNT, 0))) AMOUNT
								  FROM a_trial_bal TB, c_branch P, gs_coa AC
								 WHERE TB.BRANCH_ID = P.BRANCH_ID
								   AND P.BRANCH_ID =
									   (SELECT BRANCH_ID FROM c_branch WHERE BRANCH_ID = '".$branchID."')
								   AND COALESCE(TB.SA_AMOUNT, 0) > 0
								   AND SUBSTR(TB.AC_CODE, 1, 6) =
									   (SELECT DISTINCT SUBSTR(COA_CODE, 1, 6)
										  FROM gs_coa
										 WHERE SUBSTR(COA_CODE, 1, 6) = '".$currLiabilitiesCoaCode."')
								   AND TB.BALANCE_DATE <= '".$balDate1."'
								   AND TB.AC_CODE = AC.COA_CODE
								 ORDER BY TB.AC_CODE ASC
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
		// Balance Sheet Report End BY Akhand
		
		public function fetchDistinctVoucherNoList($branchName,$VCondition,$cond) {
			$getTblDataSql   = "SELECT 	
											DISTINCT VOUCHER_NO_
								FROM 	
											view_daily_transaction 
								WHERE 	
											FUND_L			= '".$branchName."'
								AND			AUTO_TRAN_FLAG_ = 'n'			
								{$VCondition} 
								{$cond} 
								order by 
											VOUCHER_NO_ ASC
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
		
		public function fetchTranNofromTransaction($branchName,$cond) {
			$getTblDataSql   = "SELECT 
									DISTINCT 		
												TRAN_NO
									FROM 	
											view_daily_transaction 
									WHERE 	
											FUND_L='".$branchName."' 
									{$cond}
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
		
		public function fetchTranDetailsForAccEdit($branchName,$cond) {
			$getTblDataSql   = "SELECT 		
											DISTINCT
											DATE_FTDT,
											VOUCHER_NO_,
											PARTICULARS_,
											NARRATION_,
											DEBIT_,
											CREDIT_,
											COA_CODE_,
											DRAWN_ON_,
											CHQ_NO,
											CHQ_DATE,
											FUND_ID_
								FROM 	
											view_daily_transaction 
								WHERE 	
											FUND_L='".$branchName."' 
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
	}
?>	