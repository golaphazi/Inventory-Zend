<?php
	namespace LocalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	use Accounts\Model\Voucher;
	class SupplierInformationTable {
		protected $tableGateway;
		protected $voucherTable;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select	= new Select('ls_supplier_info');
				$select->order('NAME ASC');
				
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new SupplierInformation());
				// create a new pagination adapter object
				$paginatorAdapter 	= new DbSelect($select,$this->tableGateway->getAdapter(),$resultSetPrototype);
				$paginator 			= new Paginator($paginatorAdapter);
				return $paginator;
			}
			
			if (null === $select)
			$select	= new Select();
			$select->from($this->table);
			$resultSet = $this->selectWith($select);
			$resultSet->buffer();
			return $resultSet;
		}
		
		public function getSupplierInformation($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('SUPPLIER_INFO_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function supplierInformationExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveSupplierInformation(SupplierInformation $supplierInformation) {
			$this->session = new SessionContainer('post_supply');
			$businessdate = $this->session->businessdate;
			$recdate = $this->session->recdate;
			$userid = $this->session->userid;
			$branchid = $this->session->branchid;
			$data = array(
				'BRANCH_ID'		=> $branchid,
				'NAME' 			=> $supplierInformation->NAME,
				'SHORT_NAME' 	=> $supplierInformation->SHORT_NAME,
				'ADDRESS' 		=> $supplierInformation->ADDRESS,
				'PHONE' 		=> $supplierInformation->PHONE,
				'FAX' 			=> $supplierInformation->FAX,
				'MOBILE' 		=> $supplierInformation->MOBILE,
				'WEB' 			=> $supplierInformation->WEB,
				'EMAIL' 		=> $supplierInformation->EMAIL,
				'BUSINESS_DATE' => date('Y-m-d', strtotime($businessdate)),
				'RECORD_DATE' 	=> $recdate,
				'OPERATE_BY' 	=> $userid,
			);
			
			
			//echo "<pre>"; print_r($data); die();
			
			//Receivable Chart of Account Generate Start
			$maxReceiveableCOACode = '';
			$selectMaxRcvCOA = "SELECT 
										COALESCE(MAX(substr(COA_CODE,1,9)),302001000)+1  AS MAX_RECEIVABLE_COA_CODE
									FROM
											gs_coa 		
									WHERE
										  substr(COA_CODE,1,9) BETWEEN '302001000' AND '302001999'";
			$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
			$selectMaxRcvCOAStatement->prepare();
			$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
			
			if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($selectMaxRcvCOAResult);
			}
			
			foreach($resultSet as $resultMaxRcvCOA) {
				$maxReceiveableCOACode	= $resultMaxRcvCOA->MAX_RECEIVABLE_COA_CODE;
			}
			//echo $maxReceiveableCOACode;die();			
			
			$RECEIVABLE_COA_CODE 	= $maxReceiveableCOACode;
			$RECEIVABLE_COA_NAME 	= "Receivable from - ".$supplierInformation->NAME;
			$RECEIVABLE_AUTO_COA 	= 'y';			
			$marketWiseDivCOASql = "	
									SELECT 
											CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID,
											C.COA_ID AS RECEIVABLE_COA_ID,
											C.CASH_FLOW_HEAD AS RECEIVABLE_CASH_FLOW_HEAD
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '302001000'
									ORDER BY 
											C.RGT		
								";
			$marketWiseDivCOA		= $this->tableGateway->getAdapter()->createStatement($marketWiseDivCOASql);
			$marketWiseDivCOA->prepare();
			$marketWiseDivCOAResult = $marketWiseDivCOA->execute();
			if ($marketWiseDivCOAResult instanceof ResultInterface && $marketWiseDivCOAResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($marketWiseDivCOAResult);
			}
			foreach($resultSet as $resultMaxDivCOA) {
				$RECEIVABLE_COMPANY_ID		= $resultMaxDivCOA->RECEIVABLE_COMPANY_ID;
				$RECEIVABLE_COA_ID			= $resultMaxDivCOA->RECEIVABLE_COA_ID;
				$RECEIVABLE_CASH_FLOW_HEAD	= $resultMaxDivCOA->RECEIVABLE_CASH_FLOW_HEAD;
			}
			//Receivable Chart of Account Generate End
			//Payable Chart of Account Generate Start
			$maxPayableCOACode = '';
			$selectMaxPayCOA = "SELECT
										COALESCE(MAX(substr(COA_CODE,1,9)),201001000)+1  AS MAX_RECEIVABLE_COA_CODE
										
									FROM
											gs_coa 		
									WHERE
										  substr(COA_CODE,1,9) BETWEEN '201001000' AND '201001999'";
			$selectMaxPayCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxPayCOA);
			$selectMaxPayCOAStatement->prepare();
			$selectMaxPayCOAResult 	= $selectMaxPayCOAStatement->execute();
			
			if ($selectMaxPayCOAResult instanceof ResultInterface && $selectMaxPayCOAResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($selectMaxPayCOAResult);
			}
			
			foreach($resultSet as $resultMaxPayCOA) {
				$maxPayableCOACode	= $resultMaxPayCOA->MAX_RECEIVABLE_COA_CODE;
			}
			//echo $maxPayableCOACode;die();			
			
			$PAYABLE_COA_CODE 	= $maxPayableCOACode;
			$PAYABLE_COA_NAME 	= "Payable to - ".$supplierInformation->NAME;
			$PAYABLE_AUTO_COA 	= 'y';			
			$marketWiseDivCOASql = "	
									SELECT 
											CN.COMPANY_ID AS PAYABLE_COMPANY_ID,
											C.COA_ID AS PAYABLE_COA_ID,
											C.CASH_FLOW_HEAD AS PAYABLE_CASH_FLOW_HEAD
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '201001000'
									ORDER BY 
											C.RGT		
								";
			$marketWiseDivCOA		= $this->tableGateway->getAdapter()->createStatement($marketWiseDivCOASql);
			$marketWiseDivCOA->prepare();
			$marketWiseDivCOAResult = $marketWiseDivCOA->execute();
			if ($marketWiseDivCOAResult instanceof ResultInterface && $marketWiseDivCOAResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($marketWiseDivCOAResult);
			}
			foreach($resultSet as $resultMaxDivCOA) {
				$PAYABLE_COMPANY_ID		= $resultMaxDivCOA->PAYABLE_COMPANY_ID;
				$PAYABLE_COA_ID			= $resultMaxDivCOA->PAYABLE_COA_ID;
				$PAYABLE_CASH_FLOW_HEAD	= $resultMaxDivCOA->PAYABLE_CASH_FLOW_HEAD;
			}
			//Payable Chart of Account Generate End
			
			$CoaData = array();
			$CoaData = array(
							"COMPANY_ID"=>array(
													$RECEIVABLE_COMPANY_ID,
													$PAYABLE_COMPANY_ID,
												),
							"PARENT_COA"=>array(
													$RECEIVABLE_COA_ID,
													$PAYABLE_COA_ID,
												),
							"CASH_FLOW_HEAD"=>array(
													$RECEIVABLE_CASH_FLOW_HEAD,
													$PAYABLE_CASH_FLOW_HEAD,
												),
							"COA_CODE"=>array(
													$RECEIVABLE_COA_CODE,
													$PAYABLE_COA_CODE,
												),
							"COA_NAME"=>array(
													$RECEIVABLE_COA_NAME,
													$PAYABLE_COA_NAME,
												),
							"AUTO_COA"=>array(
													$RECEIVABLE_AUTO_COA,
													$PAYABLE_AUTO_COA,
												),
						);
			
			
			$existCheckData = array(
				'NAME' 			=> $supplierInformation->NAME,
				'BRANCH_ID'		=> $branchid,
			);
			
			$id = (int) $supplierInformation->SUPPLIER_INFO_ID;
			
			if($id == 0) {
								
				if($this->supplierInformationExist($existCheckData)) {
					throw new \Exception("Supplier Information ".$supplierInformation->NAME." already exist!");
				} else {
					if($this->tableGateway->insert($data)) {
							$supplierSql = $this->supplierInformationExist($existCheckData);
							$supplierInfoId = (int) $supplierSql->SUPPLIER_INFO_ID;
							$returnData	= array(
									"SUPPLIER_INFO_ID" => $supplierInfoId,
									"COA_DATA" => $CoaData,
							  );
							return $returnData;
					} else {
						return false;	
					}
				}
			} else {
				if($this->getsupplierInformation($id)) {
					$supplierInfoId	= '';
					$nameExist		= '';
					$nameExist 		= $this->supplierInformationExist($existCheckData);
					$supplierInfoId 	=  $nameExist->SUPPLIER_INFO_ID;
					if((!empty($nameExist)) && ($id!=$supplierInfoId)) {
						throw new \Exception("Supplier Information ".$supplierInformation->NAME." already exist!");
					} else {
						if($this->tableGateway->update($data,array('SUPPLIER_INFO_ID' => $id))) {
							return true;	
						} else {
							return false;	
						} 
					}
				} else {
					throw new \Exception("ID $id does not exist!");
				}
			}
		}
		
		public function updateSupplierInformation($flag, $coaCode, $supplierInfoId) {
			$this->session = new SessionContainer('post_supply');
			$businessdate = $this->session->businessdate;
			$recdate = $this->session->recdate;
			$userid = $this->session->userid;
			if($flag == 'p'){
				$data = array(
								'PAYABLE_COA' 			=> $coaCode,
							);
			}else if($flag == 'r'){
				$data = array(
								'RECEIVABLE_COA' 	=> $coaCode,
							);
			}
			
			if($this->tableGateway->update($data,array('SUPPLIER_INFO_ID' => $supplierInfoId))) {
				return true;	
			} else {
				return false;	
			} 
		
			
		}
		
		public function fetchViewSupplierInfoDetails($cond) {
			$select = "SELECT 
						SUPPLIER_INFO_ID,
						   BRANCH_ID,
						   NAME,
						   ADDRESS,
						   PHONE,
						   FAX,
						   MOBILE,
						   WEB,
						   EMAIL,
						   PAYABLE_COA,
						   RECEIVABLE_COA,
						   BUSINESS_DATE
					
						FROM 
							ls_supplier_info
						   WHERE {$cond }
						ORDER BY 
						NAME ASC
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
		
		
		
		public function getSuppInfo() {
			$select = "		
						SELECT 
								SUPPLIER_INFO_ID,NAME
						FROM 
								ls_supplier_info
						ORDER BY 
								NAME ASC
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
		public function getSupplierDetails($id) {
			if(!empty($id)){
				$select = "		
						SELECT 
								SUPPLIER_INFO_ID,NAME,MOBILE,ADDRESS
						FROM 
								ls_supplier_info
						WHERE ls_supplier_info.SUPPLIER_INFO_ID = '".$id."'
						ORDER BY 
								NAME ASC
			";
			}else{
			$select = "		
						SELECT 
								SUPPLIER_INFO_ID,NAME,MOBILE,ADDRESS
						FROM 
								ls_supplier_info
						
						ORDER BY 
								NAME ASC
			";
			}
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			return $resultSet;
		}
		public function getSuppCOACode($suppID) {
			$select = "		
						SELECT ls_supplier_info.PAYABLE_COA,GS_PAY.COA_NAME,ls_supplier_info.SHORT_NAME,ls_supplier_info.RECEIVABLE_COA, GS_REC.COA_NAME AS RECEIVABLE_COA_NAME
						FROM 
								ls_supplier_info, gs_coa GS_PAY, gs_coa GS_REC
						WHERE	ls_supplier_info.SUPPLIER_INFO_ID = '".$suppID."'
						AND GS_PAY.COA_CODE = ls_supplier_info.PAYABLE_COA
						AND GS_REC.COA_CODE = ls_supplier_info.RECEIVABLE_COA
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
		
		public function getSuppCOACodeAmountModel($suppID) {
			$this->session = new SessionContainer('post_supply');
			$businessDate = $this->session->businessdate;			
			$branchid = 1;//$this->session->branchid;		
			$fromDate = $businessDate;
			
			$balance = $this->getSupplerCurrentBalance($suppID,$branchid,$fromDate);	
				if($balance > 0){
					$balancePlace = number_format($balance,2);
				} else {
					$balancePlace = '('.number_format(abs($balance),2).')';
				}
			
			return $balancePlace;
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
		public function fetchSupplierListforLedger($input,$cond) {
			$getTblDataSql   = "SELECT 
										SUPPINFO.SUPPLIER_INFO_ID,
										SUPPINFO.NAME,
										SUPPINFO.SHORT_NAME,
										SUPPINFO.MOBILE,
										SUPPINFO.PAYABLE_COA,
										SUPPINFO.RECEIVABLE_COA
								FROM 
										ls_supplier_info  SUPPINFO										
								WHERE 	LOWER(SUPPINFO.NAME) like '".$input."%' {$cond}
								ORDER BY LOWER(NAME) ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchSupplierListforLedger1($input) {
			if(!empty($input)){
				$getTblDataSql   = "SELECT 
										SUPPINFO.SUPPLIER_INFO_ID,
										SUPPINFO.NAME,
										SUPPINFO.SHORT_NAME,
										SUPPINFO.MOBILE,
										SUPPINFO.PAYABLE_COA,
										SUPPINFO.RECEIVABLE_COA
								FROM 
										ls_supplier_info  SUPPINFO										
								WHERE 	LOWER(SUPPINFO.NAME) = '".$input."'
								ORDER BY LOWER(NAME) ASC";
			}else{
				$getTblDataSql   = "SELECT 
										SUPPINFO.SUPPLIER_INFO_ID,
										SUPPINFO.NAME,
										SUPPINFO.SHORT_NAME,
										SUPPINFO.MOBILE,
										SUPPINFO.PAYABLE_COA,
										SUPPINFO.RECEIVABLE_COA
								FROM 
										ls_supplier_info  SUPPINFO										
								
								ORDER BY LOWER(NAME) ASC";
			}
			
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchSupplierIDForPaymentReceipt($coaCode,$type) {
			$cond = '';
			if($type == 'payable'){
				$cond = "SUPPINFO.PAYABLE_COA = '".$coaCode."'";
			} else {
				$cond = "SUPPINFO.RECEIVABLE_COA = '".$coaCode."'";
			}
			$getTblDataSql   = "SELECT 
										SUPPINFO.SUPPLIER_INFO_ID
								FROM 	ls_supplier_info  SUPPINFO										
								WHERE 	{$cond}
								";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}			
			foreach($resultSet as $resultMaxRcvCOA) {
				$maxReceiveableCOACode	= $resultMaxRcvCOA->SUPPLIER_INFO_ID;
			}
			return $maxReceiveableCOACode;
		}
		
		
		public function deleteSupplierInformation($id) {
			$this->tableGateway->delete(array('SUPPLIER_INFO_ID' => $id));
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