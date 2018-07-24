<?php
	namespace Accounts\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	class PaymentTransactionTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}	
		
		public function savePaymentTransaction(PaymentTransaction $paymenttransaction) {
			//echo '<pre>';print_r($paymenttransaction);die();
			//return $returnVoucher	= "BR1-2014-000001"; die();
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("Y-m-d", strtotime($businessDate));
			$recordDate 	= $this->session->recdate;//date("Y-m-d H:i:s", strtotime($businessDate));die();
			$userId 		= $this->session->userid;
			
			$getMaxSql		= '';
			$SUPPLIER_FLAG	= '';
			$ZONE_FLAG		= '';
			$SR_FLAG		= '';
			$RETAILER_FLAG	= '';
			$NARRATION		= '';
			$debitAmount 	= 0.00;
			$creditAmount	= 0.00;
			$balanceAmount	= 0.00;
			$status = 0;			
			if(($paymenttransaction->TRANSACTION_FLAG != '') && ($paymenttransaction->SUPPLIER_INFO_ID > 0)) {
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'BUY') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(SUPPLIER_FLAG),0) AS MAX_FLAG_NO, 
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.SUPPLIER_FLAG = (SELECT MAX(SUPPLIER_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.SUPPLIER_info_ID = ".$paymenttransaction->SUPPLIER_INFO_ID.") 
												AND PTT.SUPPLIER_info_ID = ".$paymenttransaction->SUPPLIER_INFO_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												),0) AS BALANCE, 
											SF.NAME AS NAME
									FROM 
											i_payment_transaction PT, ls_supplier_info SF
									WHERE 
											PT.SUPPLIER_INFO_ID = ".$paymenttransaction->SUPPLIER_INFO_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		SF.SUPPLIER_INFO_ID = PT.SUPPLIER_INFO_ID
					";
					$debitAmount 	= $paymenttransaction->AMOUNT;
					$creditAmount	= 0.00;
					$NARRATION		= 'Purchase Product from Supplier: ';
				}
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'PAID') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(SUPPLIER_FLAG),0) AS MAX_FLAG_NO, 
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.SUPPLIER_FLAG = (SELECT MAX(SUPPLIER_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.SUPPLIER_info_ID = ".$paymenttransaction->SUPPLIER_INFO_ID." )
												AND PTT.SUPPLIER_info_ID = ".$paymenttransaction->SUPPLIER_INFO_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												) ,0) AS BALANCE, 
											SF.NAME AS NAME
									FROM 
											i_payment_transaction PT, ls_supplier_info SF
									WHERE 
											PT.SUPPLIER_INFO_ID = ".$paymenttransaction->SUPPLIER_INFO_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		SF.SUPPLIER_INFO_ID = PT.SUPPLIER_INFO_ID
					";
					$debitAmount 	= 0.00;
					$creditAmount	= $paymenttransaction->AMOUNT;
					$NARRATION		= 'Paid to Supplier: ';
				}
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'RECEIVE') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(SUPPLIER_FLAG),0) AS MAX_FLAG_NO, 
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.SUPPLIER_FLAG = (SELECT MAX(SUPPLIER_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.SUPPLIER_info_ID = ".$paymenttransaction->SUPPLIER_INFO_ID.")
												AND PTT.SUPPLIER_info_ID = ".$paymenttransaction->SUPPLIER_INFO_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												) ,0) AS BALANCE,
											SF.NAME AS NAME
									FROM 
											i_payment_transaction PT, ls_supplier_info SF
									WHERE 
											PT.SUPPLIER_INFO_ID = ".$paymenttransaction->SUPPLIER_INFO_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		SF.SUPPLIER_INFO_ID = PT.SUPPLIER_INFO_ID
					";
					$debitAmount 	= $paymenttransaction->AMOUNT;
					$creditAmount	= 0.00;
					$NARRATION		= 'Received from Supplier: ';
				}
				$getMaxStatement	= $this->tableGateway->getAdapter()->createStatement($getMaxSql);
				$getMaxStatement->prepare();
				$getMaxResult		= $getMaxStatement->execute();					
				if ($getMaxResult instanceof ResultInterface && $getMaxResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($getMaxResult);
				}					
				foreach($resultSet as $resultSetData) {
					$maxFlagNo 	= $resultSetData->MAX_FLAG_NO;
					$BALANCE	= $resultSetData->BALANCE;
					$NAME		= $resultSetData->NAME;
					
				}
				if($maxFlagNo) {
					$maxFlagNo	= $maxFlagNo + 1;
				} else {
					$maxFlagNo	= 1;
				}
				if($debitAmount > 0){
					$balanceAmount	= $BALANCE + $debitAmount;
				} else {
					$balanceAmount	= $BALANCE - $creditAmount;
				}
				
				$data = array(
								'SUPPLIER_INFO_ID' 	=> $paymenttransaction->SUPPLIER_INFO_ID,
								'BRANCH_ID' 		=> $paymenttransaction->BRANCH_ID,
								'DEBIT' 			=> $debitAmount,
								'CREDIT' 			=> $creditAmount,
								'BALANCE' 			=> $balanceAmount,
								'TRANSACTION_FLAG' 	=> $paymenttransaction->TRANSACTION_FLAG,
								'SUPPLIER_FLAG' 	=> $maxFlagNo,
								'NARRATION'			=> $NARRATION.$NAME,
								'BUSINESS_DATE' 	=> $businessDate,
								'RECORD_DATE' 		=> $recordDate,
								'OPERATE_BY' 		=> $userId,
							);
				if($this->tableGateway->insert($data)) {				
					//return true;
					$status = 1;
				} else {
					//return false;	
					$status = 0;
				}
			}
			if(($paymenttransaction->TRANSACTION_FLAG != '') && ($paymenttransaction->ZONE_ID > 0)) {
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'SALE') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(ZONE_FLAG),0) AS MAX_ZONEFLAG_NO, 
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.ZONE_FLAG = (SELECT MAX(ZONE_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.ZONE_ID = ".$paymenttransaction->ZONE_ID." )
												AND PTT.ZONE_ID = ".$paymenttransaction->ZONE_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												),0) AS BALANCE,
											ZF.NAME AS NAME
									FROM 
											i_payment_transaction PT, ls_zone_info ZF
									WHERE 
											PT.ZONE_ID = ".$paymenttransaction->ZONE_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		ZF.ZONE_ID = PT.ZONE_ID
					";
					$debitAmount 	= 0.00;
					$creditAmount	= $paymenttransaction->AMOUNT;
					$NARRATION		= 'Sold Product to Zone: ';
				}
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'RECEIVE') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(ZONE_FLAG),0) AS MAX_ZONEFLAG_NO,
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.ZONE_FLAG = (SELECT MAX(ZONE_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.ZONE_ID = ".$paymenttransaction->ZONE_ID." )
												AND PTT.ZONE_ID = ".$paymenttransaction->ZONE_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												), 0) AS BALANCE, 
											ZF.NAME AS NAME
									FROM 
											i_payment_transaction PT, ls_zone_info ZF
									WHERE 
											PT.ZONE_ID = ".$paymenttransaction->ZONE_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		ZF.ZONE_ID = PT.ZONE_ID
					";
					$debitAmount 	= $paymenttransaction->AMOUNT;
					$creditAmount	= 0.00;
					$NARRATION		= 'Received from Zone: ';
				}
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'PAID') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(ZONE_FLAG),0) AS MAX_ZONEFLAG_NO, 
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.ZONE_FLAG = (SELECT MAX(ZONE_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.ZONE_ID = ".$paymenttransaction->ZONE_ID." )
												AND PTT.ZONE_ID = ".$paymenttransaction->ZONE_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												),0) AS BALANCE,
											ZF.NAME AS NAME
									FROM 
											i_payment_transaction PT, ls_zone_info ZF
									WHERE 
											PT.ZONE_ID = ".$paymenttransaction->ZONE_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		ZF.ZONE_ID = PT.ZONE_ID
					";
					$debitAmount 	= 0.00;
					$creditAmount	= $paymenttransaction->AMOUNT;
					$NARRATION		= 'Paid from Zone: ';
				}
				$getMaxStatement	= $this->tableGateway->getAdapter()->createStatement($getMaxSql);
				$getMaxStatement->prepare();
				$getMaxResult		= $getMaxStatement->execute();					
				if ($getMaxResult instanceof ResultInterface && $getMaxResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($getMaxResult);
				}					
				foreach($resultSet as $resultSetData) {
					$maxZoneFlagNo 	= $resultSetData->MAX_ZONEFLAG_NO;
					$BALANCE	= $resultSetData->BALANCE;
					$NAME		= $resultSetData->NAME;
					
				}
				if($maxZoneFlagNo) {
					$maxZoneFlagNo	= $maxZoneFlagNo + 1;
				} else {
					$maxZoneFlagNo	= 1;
				}
				if($debitAmount > 0){
					$balanceAmount	= $BALANCE + $debitAmount;
				} else {
					$balanceAmount	= $BALANCE - $creditAmount;
				}				
				$data = array(
								'ZONE_ID' 			=> $paymenttransaction->ZONE_ID,
								'BRANCH_ID' 		=> $paymenttransaction->BRANCH_ID,
								'DEBIT' 			=> $debitAmount,
								'CREDIT' 			=> $creditAmount,
								'BALANCE' 			=> $balanceAmount,
								'TRANSACTION_FLAG' 	=> $paymenttransaction->TRANSACTION_FLAG,
								'ZONE_FLAG' 		=> $maxZoneFlagNo,
								'NARRATION'			=> $NARRATION.$NAME,
								'BUSINESS_DATE' 	=> $businessDate,
								'RECORD_DATE' 		=> $recordDate,
								'OPERATE_BY' 		=> $userId,
							);
				if($this->tableGateway->insert($data)) {				
					//return true;
					$status = 1;
				} else {
					//return false;	
					$status = 0;
				}
			}
			if(($paymenttransaction->TRANSACTION_FLAG != '') && ($paymenttransaction->EMPLOYEE_ID > 0)) {
				//echo 'hi there';die();
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'SALE') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(SR_FLAG),0) AS MAX_SRFLAG_NO, 
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.SR_FLAG = (SELECT MAX(SR_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.EMPLOYEE_ID = ".$paymenttransaction->EMPLOYEE_ID." )
												AND PTT.EMPLOYEE_ID = ".$paymenttransaction->EMPLOYEE_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												),0) AS BALANCE,
											EMPINFO.EMPLOYEE_NAME AS NAME
									FROM 
											i_payment_transaction PT, hrms_employee_personal_info EMPINFO
									WHERE 
											PT.EMPLOYEE_ID = ".$paymenttransaction->EMPLOYEE_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		EMPINFO.EMPLOYEE_ID = PT.EMPLOYEE_ID
					";
					$debitAmount 	= 0.00;
					$creditAmount	= $paymenttransaction->AMOUNT;
					$NARRATION		= 'Sold Product by sr: ';
				}
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'RECEIVE') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(SR_FLAG),0) AS MAX_SRFLAG_NO, 
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.SR_FLAG = (SELECT MAX(SR_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.EMPLOYEE_ID = ".$paymenttransaction->EMPLOYEE_ID." )
												AND PTT.EMPLOYEE_ID = ".$paymenttransaction->EMPLOYEE_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												),0) AS BALANCE,
											EMPINFO.EMPLOYEE_NAME AS NAME
									FROM 
											i_payment_transaction PT, hrms_employee_personal_info EMPINFO
									WHERE 
											PT.EMPLOYEE_ID = ".$paymenttransaction->EMPLOYEE_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		EMPINFO.EMPLOYEE_ID = PT.EMPLOYEE_ID
					";
					$debitAmount 	= $paymenttransaction->AMOUNT;
					$creditAmount	= 0.00;
					$NARRATION		= 'Received from sr: ';
				}
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'PAID') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(SR_FLAG),0) AS MAX_SRFLAG_NO,
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.SR_FLAG = (SELECT MAX(SR_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.EMPLOYEE_ID = ".$paymenttransaction->EMPLOYEE_ID." )
												AND PTT.EMPLOYEE_ID = ".$paymenttransaction->EMPLOYEE_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												),0) AS BALANCE,
											EMPINFO.EMPLOYEE_NAME AS NAME
									FROM 
											i_payment_transaction PT, hrms_employee_personal_info EMPINFO
									WHERE 
											PT.EMPLOYEE_ID = ".$paymenttransaction->EMPLOYEE_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		EMPINFO.EMPLOYEE_ID = PT.EMPLOYEE_ID
					";
					$debitAmount 	= 0.00;
					$creditAmount	= $paymenttransaction->AMOUNT;
					$NARRATION		= 'Received from sr: ';
				}
				$getMaxStatement	= $this->tableGateway->getAdapter()->createStatement($getMaxSql);
				$getMaxStatement->prepare();
				$getMaxResult		= $getMaxStatement->execute();					
				if ($getMaxResult instanceof ResultInterface && $getMaxResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($getMaxResult);
				}					
				foreach($resultSet as $resultSetData) {
					$maxSRFlagNo 	= $resultSetData->MAX_SRFLAG_NO;
					$BALANCE	= $resultSetData->BALANCE;
					$NAME		= $resultSetData->NAME;					
				}
				if($maxSRFlagNo) {
					$maxSRFlagNo	= $maxSRFlagNo + 1;
				} else {
					$maxSRFlagNo	= 1;
				}
				if($debitAmount > 0){
					$balanceAmount	= $BALANCE + $debitAmount;
				} else {
					$balanceAmount	= $BALANCE - $creditAmount;
				}
				
				$data = array(
								'EMPLOYEE_ID' 		=> $paymenttransaction->EMPLOYEE_ID,
								'BRANCH_ID' 		=> $paymenttransaction->BRANCH_ID,
								'DEBIT' 			=> $debitAmount,
								'CREDIT' 			=> $creditAmount,
								'BALANCE' 			=> $balanceAmount,
								'TRANSACTION_FLAG' 	=> $paymenttransaction->TRANSACTION_FLAG,
								'SR_FLAG' 			=> $maxSRFlagNo,
								'NARRATION'			=> $NARRATION.$NAME,
								'BUSINESS_DATE' 	=> $businessDate,
								'RECORD_DATE' 		=> $recordDate,
								'OPERATE_BY' 		=> $userId,
							);
				if($this->tableGateway->insert($data)) {				
					//return true;
					$status = 1;
				} else {
					//return false;	
					$status = 0;
				}
			}
			if(($paymenttransaction->TRANSACTION_FLAG != '') && ($paymenttransaction->RETAILER_ID > 0)) {
				//echo 'hi there';die();
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'SALE') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(RETAILER_FLAG),0) AS MAX_RETAILERFLAG_NO, 
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
														FROM i_payment_transaction PTT
														WHERE PTT.RETAILER_FLAG = (SELECT MAX(RETAILER_FLAG) 
																					FROM i_payment_transaction PT 
																					WHERE PT.RETAILER_ID = ".$paymenttransaction->RETAILER_ID." ) 
														AND PTT.RETAILER_ID = ".$paymenttransaction->RETAILER_ID."
														ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
													  ) ,0) AS BALANCE,
											RETINFO.NAME AS NAME
									FROM 
											i_payment_transaction PT, ls_retailer_info RETINFO
									WHERE 
											PT.RETAILER_ID = ".$paymenttransaction->RETAILER_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		RETINFO.RETAILER_ID = PT.RETAILER_ID
					";
					$debitAmount 	= 0.00;
					$creditAmount	= $paymenttransaction->AMOUNT;
					$NARRATION		= 'Sold Product to retailer: ';
				}
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'RECEIVE') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(RETAILER_FLAG),0) AS MAX_RETAILERFLAG_NO, 
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.RETAILER_FLAG = (SELECT MAX(RETAILER_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.RETAILER_ID = ".$paymenttransaction->RETAILER_ID." )
												AND PTT.RETAILER_ID = ".$paymenttransaction->RETAILER_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												),0) AS BALANCE,
											RETINFO.NAME AS NAME
									FROM 
											i_payment_transaction PT, ls_retailer_info RETINFO
									WHERE 
											PT.RETAILER_ID = ".$paymenttransaction->RETAILER_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		RETINFO.RETAILER_ID = PT.RETAILER_ID
					";
					$debitAmount 	= $paymenttransaction->AMOUNT;
					$creditAmount	= 0.00;
					$NARRATION		= 'Received from retailer: ';
				}
				if(strtoupper($paymenttransaction->TRANSACTION_FLAG) == 'PAID') {
					$getMaxSql	= "
									SELECT
											COALESCE(MAX(RETAILER_FLAG),0) AS MAX_RETAILERFLAG_NO, 
											COALESCE((SELECT COALESCE( PTT.BALANCE, 0 )
												FROM i_payment_transaction PTT
												WHERE PTT.RETAILER_FLAG = (SELECT MAX(RETAILER_FLAG)
																			FROM i_payment_transaction PT
																			WHERE PT.RETAILER_ID = ".$paymenttransaction->RETAILER_ID." )
												AND PTT.RETAILER_ID = ".$paymenttransaction->RETAILER_ID."
												ORDER BY PTT.PAYMENT_TRANSACTION_ID LIMIT 1
												) ,0) AS BALANCE,
											RETINFO.NAME AS NAME
									FROM 
											i_payment_transaction PT, ls_retailer_info RETINFO
									WHERE 
											PT.RETAILER_ID = ".$paymenttransaction->RETAILER_ID."
									AND 	PT.BRANCH_ID 	= ".$paymenttransaction->BRANCH_ID."
									AND		RETINFO.RETAILER_ID = PT.RETAILER_ID
					";
					$debitAmount 	= 0.00;
					$creditAmount	= $paymenttransaction->AMOUNT;
					$NARRATION		= 'Paid to retailer: ';
				}
				$getMaxStatement	= $this->tableGateway->getAdapter()->createStatement($getMaxSql);
				$getMaxStatement->prepare();
				$getMaxResult		= $getMaxStatement->execute();					
				if ($getMaxResult instanceof ResultInterface && $getMaxResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($getMaxResult);
				}					
				foreach($resultSet as $resultSetData) {
					$maxRetailerFlagNo 	= $resultSetData->MAX_RETAILERFLAG_NO;
					$BALANCE	= $resultSetData->BALANCE;
					$NAME		= $resultSetData->NAME;					
				}
				if($maxRetailerFlagNo) {
					$maxRetailerFlagNo	= $maxRetailerFlagNo + 1;
				} else {
					$maxRetailerFlagNo	= 1;
				}
				if($debitAmount > 0){
					$balanceAmount	= $BALANCE + $debitAmount;
				} else {
					$balanceAmount	= $BALANCE - $creditAmount;
				}
				
				$data = array(
								'RETAILER_ID' 		=> $paymenttransaction->RETAILER_ID,
								'BRANCH_ID' 		=> $paymenttransaction->BRANCH_ID,
								'DEBIT' 			=> $debitAmount,
								'CREDIT' 			=> $creditAmount,
								'BALANCE' 			=> $balanceAmount,
								'TRANSACTION_FLAG' 	=> $paymenttransaction->TRANSACTION_FLAG,
								'RETAILER_FLAG' 	=> $maxRetailerFlagNo,
								'NARRATION'			=> $NARRATION.$NAME,
								'BUSINESS_DATE' 	=> $businessDate,
								'RECORD_DATE' 		=> $recordDate,
								'OPERATE_BY' 		=> $userId,
							);
				if($this->tableGateway->insert($data)) {				
					//return true;
					$status = 1;
				} else {
					//return false;	
					$status = 0;
				}
			}
			//echo "<pre>"; print_r($data);die();			
			return $status;
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
		
		public function getRetailerPrevAmountForBS($currAssetDetailsAccountCode,$balDate) {				
			$fromDate	= date('Y-m-d',strtotime($balDate));
			$startDate = date('Y-m-d', strtotime($fromDate .' -1 day'));
			$bal = 0.00;
			if($startDate <= '2015-11-01') {
				$select 	= "SELECT COALESCE(PT.BALANCE,0) AS BALANCE
								FROM i_payment_transaction PT, ls_retailer_info retinfo
								WHERE retinfo.RECEIVABLE_COA = '".$currAssetDetailsAccountCode."'
								AND retinfo.retailer_id = PT.retailer_id							
								AND PT.BUSINESS_DATE < '".$startDate."' 
								";
				$stmt = $this->tableGateway->getAdapter()->createStatement($select);
				$stmt->prepare();
				$result = $stmt->execute();			
				if ($result instanceof ResultInterface && $result->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($result);
				}				
				foreach($resultSet as $getPDidss) {
					$bal = abs($getPDidss['BALANCE']);		
				}
			} else {
				$bal = 0.00;
			}			
			return $bal;
		}
	}
?>