<?php
	namespace Ibcia\Model;
		
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Db\Sql\Expression;
	
	class BusinessDateTable {
		public $currentBusinessDate = null;
		public $lastBusinessDate = null;
		public $isSODBkupFinished = null;
		public $isSODFinished = null;
		public $isEODBkupFinished = null;
		public $isEODFinished = null;
		public $isBusinessDateClosed = null;
		public $isMktPriceImported = null;
		
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
			
			$selectCurBuisDate 	= $this->tableGateway->getSql()->select();
			$selectCurBuisDate->columns(array(
				'BUSINESS_DATE' 	=> 'BUSINESS_DATE',//new Expression("to_char(BUSINESS_DATE, 'dd-mm-yyyy')"),
				'SOD_BKUP' 			=> 'SOD_BKUP',
				'SOD_FLAG' 			=> 'SOD_FLAG',
				'EOD_BKUP' 			=> 'EOD_BKUP',
				'EOD_FLAG' 			=> 'EOD_FLAG',
				'DATE_CLOSE' 		=> 'DATE_CLOSE',
			));
			$selectCurBuisDate->where(array('DATE_CLOSE' => null));
			$rowSet 	= $this->tableGateway->selectWith($selectCurBuisDate);
			$row 		= $rowSet->current();
			if($row) {
				$this->currentBusinessDate 	= date("d-m-Y", strtotime($row->BUSINESS_DATE));
				$this->isSODBkupFinished 	= $row->SOD_BKUP;
				$this->isSODFinished 		= $row->SOD_FLAG;
				$this->isEODBkupFinished 	= $row->EOD_BKUP;
				$this->isEODFinished 		= $row->EOD_FLAG;
				$this->isBusinessDateClosed = $row->DATE_CLOSE;
			}
		}
		
		/*public function fetchAll() {
			$resultSet = $this->tableGateway->select(function(Select $select){
							$select->order('COMPANY_NAME ASC');
						 });
			return $resultSet;
		}*/
		
		public function getCurrentBusinessDate() {
			if(null === $this->currentBusinessDate) {
				$rowSet 	= $this->tableGateway->select(array('DATE_CLOSE' => null));
				$row 		= $rowSet->current();
				if($row) {
					$this->currentBusinessDate = $row->BUSINESS_DATE;
				}
			}
			
			return $this->currentBusinessDate;
		}
		
		public function getLastBusinessDate() {
			if(null === $this->lastBusinessDate) {
				$lastBusinessDateSql	= "
											SELECT 
													MAX(BUSINESS_DATE) AS lastBusinessDate
											FROM 
													 l_business_date
				";
				$lastBusinessDateStatement	= $this->tableGateway->getAdapter()->createStatement($lastBusinessDateSql);
				$lastBusinessDateStatement->prepare();
				$lastBusinessDateResult 	= $lastBusinessDateStatement->execute();
				
				if ($lastBusinessDateResult instanceof ResultInterface && $lastBusinessDateResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($lastBusinessDateResult);
				}
				
				foreach($resultSet as $resultSetData) {
					$this->lastBusinessDate	= $resultSetData->lastBusinessDate;
				}
			}
			return $this->lastBusinessDate;
		}
		
		public function isSODBkupFinished() {
			if(null === $this->currentBusinessDate) {
				$rowSet 	= $this->tableGateway->select(array('DATE_CLOSE' => null));
				$row 		= $rowSet->current();
				if($row) {
					$this->isSODBkupFinished = $row->SOD_BKUP;
				}
			}
			
			return $this->isSODBkupFinished;
		}
		
		public function isSODFinished() {
			if(null === $this->currentBusinessDate) {
				$rowSet 	= $this->tableGateway->select(array('DATE_CLOSE' => null));
				$row 		= $rowSet->current();
				if($row) {
					$this->isSODFinished = $row->SOD_FLAG;
				}
			}
			
			return $this->isSODFinished;
		}
		
		public function isEODBkupFinished() {
			if(null === $this->currentBusinessDate) {
				$rowSet 	= $this->tableGateway->select(array('DATE_CLOSE' => null));
				$row 		= $rowSet->current();
				if($row) {
					$this->isEODBkupFinished = $row->EOD_BKUP;
				}
			}
			
			return $this->isEODBkupFinished;
		}
		
		public function isEODFinished() {
			if(null === $this->currentBusinessDate) {
				$rowSet 	= $this->tableGateway->select(array('DATE_CLOSE' => null));
				$row 		= $rowSet->current();
				if($row) {
					$this->isEODFinished = $row->SOD_FLAG;
				}
			}
			
			return $this->isEODFinished;
		}
		
		public function isMktPriceImported() {
			if(null === $this->currentBusinessDate) {
				$rowSet 	= $this->tableGateway->select(array('DATE_CLOSE' => null));
				$row 		= $rowSet->current();
				if($row) {
					$this->isMktPriceImported = $row->ATTENDANCE_CLOSE;
				}
			}
			
			return $this->isMktPriceImported;
		}
		
		
		public function businessDateExist($existCheckData) {
			$rowSet 	= $this->tableGateway->select($existCheckData);
			$row 		= $rowSet->current();
			return $row;
		}
		
		public function saveBusinessDate(BusinessDate $businessDate) {
			$data = array(
				'BUSINESS_DATE' 	=> date("Y-m-d", strtotime($businessDate->BUSINESS_DATE)),
				'SOD_BKUP' 			=> 'y',
				'SOD_FLAG' 			=> $businessDate->SOD_FLAG,
				'EOD_BKUP' 			=> 'y',
				'EOD_FLAG' 			=> $businessDate->EOD_FLAG,
				'DATE_CLOSE' 		=> $businessDate->DATE_CLOSE,
			);
				
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'BUSINESS_DATE' 	=> date("Y-m-d", strtotime($businessDate->BUSINESS_DATE)),
			);
			
			if($businessDate->BUSINESS_DATE != null &&
				//$businessDate->SOD_BKUP == null &&
				$businessDate->SOD_FLAG == null &&
				//$businessDate->EOD_BKUP == null &&
				$businessDate->EOD_FLAG == null &&
				$businessDate->DATE_CLOSE == null
				//&& $businessDate->IMPORT_MKT_PRICE == null
				) {
				if($this->businessDateExist($existCheckData)) {
					throw new \Exception("Business date ".$businessDate->BUSINESS_DATE." already exist!");
				} else {
					return $this->tableGateway->insert($data);
				}
			} else {
				if($this->businessDateExist($existCheckData)) {
					return $this->tableGateway->update($data,array('BUSINESS_DATE' => date("Y-m-d", strtotime($businessDate->BUSINESS_DATE))));
				} else {
					throw new \Exception("Date $businessDate->BUSINESS_DATE does not exist!");
				}
			}
		}
		
		public function runSODDbBackup(BusinessDate $businessDate){
			$bDate 		= $businessDate->BUSINESS_DATE;
			$transStatus   		= (int) 0;
			$msg				= '';
			
			$executeSODBackup = "begin PKG_DBBACKUP.BEFORE_SOD_DBBACKUP(:v_business_date_in,
																		 :v_trans_status_in_out,
																		 :v_msg_out); end;";
			
			
			$executeSODBackupStatement = $this->tableGateway->getAdapter()->createStatement($executeSODBackup);
			$executeSODBackupStatement->prepare();
			
			$executeSODBackupStatement->bindParam(':v_business_date_in', $bDate, 10, SQLT_CHR);
			$executeSODBackupStatement->bindParam(':v_trans_status_in_out', $transStatus, 10, OCI_B_INT);
			$executeSODBackupStatement->bindParam(':v_msg_out', $msg, 500, SQLT_CHR);
			
			$executeSODBackupStatement->execute();
			if($transStatus) {
				return $this->saveBusinessDate($businessDate);
			} else {
				return false;
			}
		}
		
		public function runSOD(BusinessDate $businessDate){
			$bDate 				= date("Y-m-d", strtotime($businessDate->BUSINESS_DATE));
			$transStatus   		= (int) 1;
			$msg				= '';
			
			if($transStatus) {
				$data = array(
					'BUSINESS_DATE' 	=> date("Y-m-d", strtotime($businessDate->BUSINESS_DATE)),
					'SOD_BKUP' 			=> 'y',
					'SOD_FLAG' 			=> $businessDate->SOD_FLAG,
					'EOD_BKUP' 			=> 'y',
					'EOD_FLAG' 			=> $businessDate->EOD_FLAG,
					'DATE_CLOSE' 		=> $businessDate->DATE_CLOSE,
				);
					
				//echo "<pre>"; print_r($data); die();
				$existCheckData = array(
					'BUSINESS_DATE' 	=> date("Y-m-d", strtotime($businessDate->BUSINESS_DATE)),
				);
				
				if($businessDate->BUSINESS_DATE != null &&
					//$businessDate->SOD_BKUP == null &&
					$businessDate->SOD_FLAG == null &&
					//$businessDate->EOD_BKUP == null &&
					$businessDate->EOD_FLAG == null &&
					$businessDate->DATE_CLOSE == null
					//&& $businessDate->IMPORT_MKT_PRICE == null
					) {
					if($this->businessDateExist($existCheckData)) {
						throw new \Exception("Business date ".$businessDate->BUSINESS_DATE." already exist!");
					} else {
						return $this->tableGateway->insert($data);
					}
				} else {
					if($this->businessDateExist($existCheckData)) {
						return $this->tableGateway->update($data,array('BUSINESS_DATE' => date("Y-m-d", strtotime($businessDate->BUSINESS_DATE))));
					} else {
						throw new \Exception("Date $businessDate->BUSINESS_DATE does not exist!");
					}
				}
			} else {
				return false;
			}
		}
		
		public function runEODDbBackup(BusinessDate $businessDate){
			$bDate 		= $businessDate->BUSINESS_DATE;
			$transStatus   		= (int) 0;
			$msg				= '';
			
			$executeSODBackup = "begin PKG_DBBACKUP.BEFORE_EOD_DBBACKUP(:v_business_date_in,
																		 :v_trans_status_in_out,
																		 :v_msg_out); end;";
			
			
			$executeSODBackupStatement = $this->tableGateway->getAdapter()->createStatement($executeSODBackup);
			$executeSODBackupStatement->prepare();
			
			$executeSODBackupStatement->bindParam(':v_business_date_in', $bDate, 10, SQLT_CHR);
			$executeSODBackupStatement->bindParam(':v_trans_status_in_out', $transStatus, 10, OCI_B_INT);
			$executeSODBackupStatement->bindParam(':v_msg_out', $msg, 500, SQLT_CHR);
			
			$executeSODBackupStatement->execute();
			if($transStatus) {
				return $this->saveBusinessDate($businessDate);
			} else {
				return false;
			}
		}
		
		public function runEOD(BusinessDate $businessDate){
			$bDate 				= date("Y-m-d", strtotime($businessDate->BUSINESS_DATE));
			$existCheckData = array(
									'BUSINESS_DATE' 	=> '$bDate',
							  );
			$transStatus   		= (($businessDate->EOD_FLAG == null) && ($this->businessDateExist($existCheckData))) ? (int) 1 : (int) 0;
			$msg				= '';
			
			//Check EOD Allready Processed Start By Akhand
			$CHECK_EOD_PROCESSED	= 0;
			$checkEODProcessedSql	= "
										SELECT 
												COUNT(EOD.BUSINESS_DATE) AS CHECK_EOD_PROCESSED
										FROM 
												l_business_date	EOD
										WHERE 
												EOD.BUSINESS_DATE	= '".$bDate."'
										AND 	EOD.EOD_FLAG IS NOT NULL
			";
			$checkEODProcessedStatement	= $this->tableGateway->getAdapter()->createStatement($checkEODProcessedSql);
			$checkEODProcessedStatement->prepare();
			$checkEODProcessedResult 	= $checkEODProcessedStatement->execute();
			
			if ($checkEODProcessedResult instanceof ResultInterface && $checkEODProcessedResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($checkEODProcessedResult);
			}
			
			foreach($resultSet as $resultSetData) {
				$CHECK_EOD_PROCESSED	= $resultSetData->CHECK_EOD_PROCESSED;
			}
			if($CHECK_EOD_PROCESSED) {
				return true;	
			} else {
				//Fiscal Year Insert & Update Start By Akhand
				$FISCAL_YEAR_DATE	= date("d-m", strtotime($businessDate->BUSINESS_DATE));
				$FISCAL_YEAR_START	= date("Y", strtotime($businessDate->BUSINESS_DATE));
				$FISCAL_YEAR_END	= $FISCAL_YEAR_START+1;
				$FISCAL_YEAR		= $FISCAL_YEAR_START."-".$FISCAL_YEAR_END;
				$FISCAL_START		= $FISCAL_YEAR_START."-07-01";
				$FISCAL_END			= $FISCAL_YEAR_END."-06-30";
				
				if($FISCAL_YEAR_DATE == '30-06') {
					$updateFiscalYearSql	= "
												UPDATE 
														l_fiscal_year FY
												SET 
														FY.FISCAL_YEAR_CLOSING	= ''
												WHERE 
														FY.FISCAL_YEAR_CLOSING IS NULL
					";
					$updateFiscalYearStatement	= $this->tableGateway->getAdapter()->createStatement($updateFiscalYearSql);
					$updateFiscalYearStatement->prepare();
					$updateFiscalYearStatement->execute();
					
					$insertFiscalYearSql	= "
												INSERT INTO 
														l_fiscal_year (
																		FISCAL_YEAR, 
																		FISCAL_MONTH, 
																		FISCAL_START, 
																		FISCAL_END
														)
														  VALUES (
																	'".$FISCAL_YEAR."',
																	'jul-jun',
																	'".$FISCAL_START."',
																	'".$FISCAL_END."'
														)
					";
					$insertFiscalYearStatement	= $this->tableGateway->getAdapter()->createStatement($insertFiscalYearSql);
					$insertFiscalYearStatement->prepare();
					$insertFiscalYearStatement->execute();
					
					$transStatus	= 1;
				} else {
					$transStatus	= 1;
				}
				//Fiscal Year Insert & Update End By Akhand
			}
			//Check EOD Allready Processed Start By Akhand
			
			if($transStatus) {
				return ($businessDate->EOD_FLAG == null) ? (($bDate === $this->currentBusinessDate) ? $this->saveBusinessDate($businessDate) : 
																																				true) : 
																																					    $this->saveBusinessDate($businessDate);
			} else {
				return false;
			}
		}
		
		public function transectionStart() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->beginTransaction();
		}
		
		public function transectionEnd() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->commit();
		}
		
		public function transectionInterrupted() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->rollback();
		}
	}
?>	