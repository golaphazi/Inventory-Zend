<?php
	namespace LocalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class RetailerInformationTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select	= new Select('ls_retailer_info');
				//$select->join('ls_zone_info','ls_retailer_info.ZONE_ID = ls_zone_info.ZONE_ID');
				//$select->join('hrms_employee_personal_info','ls_retailer_info.EMPLOYEE_ID = hrms_employee_personal_info.EMPLOYEE_ID');
				$select->order('NAME ASC');
				
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new RetailerInformation());
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
		
		public function getRetailerInformation($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('RETAILER_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function retailerInformationExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveRetailerInformation(RetailerInformation $retailerInformation) {
			$this->session = new SessionContainer('post_supply');
			$businessdate = $this->session->businessdate;
			$recdate = $this->session->recdate;
			$userid = $this->session->userid;
			$branchid = 1;//$this->session->branchid;
			$data = array(
				'BRANCH_ID'		=> $branchid,
				//'ZONE_ID'		=> $retailerInformation->ZONE_ID,
				'ZONE_ID'		=> 0,
				'NAME' 			=> $retailerInformation->NAME,
				'SHOP_NAME' 	=> $retailerInformation->SHOP_NAME,
				'ADDRESS' 		=> $retailerInformation->ADDRESS,
				'PHONE' 		=> $retailerInformation->PHONE,
				'FAX' 			=> $retailerInformation->FAX,
				'MOBILE' 		=> $retailerInformation->MOBILE,
				'WEB' 			=> $retailerInformation->WEB,
				'EMAIL' 		=> $retailerInformation->EMAIL,
				'BUSINESS_DATE' => date('Y-m-d', strtotime($businessdate)),
				'RECORD_DATE' 	=> $recdate,
				'OPERATE_BY' 	=> $userid,
				//'EMPLOYEE_ID'	=> $retailerInformation->EMPLOYEE_ID,
				'EMPLOYEE_ID'	=> 0,
			); 
			
			
			
			//echo "<pre>"; print_r($data); die();
			//302004000
			//Receivable Chart of Account Generate Start
			$maxReceiveableCOACode = '';
			$selectMaxRcvCOA = "SELECT 
										COALESCE(MAX(substr(COA_CODE,1,9)),302004000)+1  AS MAX_RECEIVABLE_COA_CODE
									FROM
											gs_coa 		
									WHERE
										  substr(COA_CODE,1,9) BETWEEN '302004000' AND '302004999'";
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
			$RECEIVABLE_COA_NAME 	= "Receivable from - ".$retailerInformation->NAME;
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
									AND   	C.COA_CODE  = '302004000'
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
			//Payable Chart of Account Generate Start - 201003000
			$maxPayableCOACode = '';
			$selectMaxPayCOA = "SELECT
										COALESCE(MAX(substr(COA_CODE,1,9)),201003000)+1  AS MAX_RECEIVABLE_COA_CODE
										
									FROM
											gs_coa 		
									WHERE
										  substr(COA_CODE,1,9) BETWEEN '201003000' AND '201003999'";
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
			$PAYABLE_COA_NAME 	= "Payable to - ".$retailerInformation->NAME;
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
									AND   	C.COA_CODE  = '201003000'
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
				'BRANCH_ID'		=> $branchid,
				'NAME' 			=> $retailerInformation->NAME,
			);
			
			$id = (int) $retailerInformation->RETAILER_ID;
			
			if($id == 0) {
								
				if($this->retailerInformationExist($existCheckData)) {
					//throw new \Exception("Retailer Information ".$retailerInformation->NAME." already exist!");
					return false;
				} else {
					if($this->tableGateway->insert($data)) {
							$supplierSql = $this->retailerInformationExist($existCheckData);
							$supplierInfoId = (int) $supplierSql->RETAILER_ID;
							
							$returnData	= array(
									"RETAILER_ID" => $supplierInfoId,
									"COA_DATA" => $CoaData,
							  );
							return $returnData;
					} else {
						return false;	
					}
				}
			} else {
				if($this->getretailerInformation($id)) {
					$supplierInfoId	= '';
					$nameExist		= '';
					$nameExist 		= $this->retailerInformationExist($existCheckData);
					$supplierInfoId 	=  $nameExist->RETAILER_ID;
					if((!empty($nameExist)) && ($id!=$supplierInfoId)) {
						//throw new \Exception("Retailer Information ".$retailerInformation->NAME." already exist!");
						return false;
					} else {
						if($this->tableGateway->update($data,array('RETAILER_ID' => $id))) {
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
		
		public function updateRetailerInformation($flag, $coaCode, $supplierInfoId) {
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
			
			if($this->tableGateway->update($data,array('RETAILER_ID' => $supplierInfoId))) {
				return true;	
			} else {
				return false;	
			} 
		
			
		}
		public function getSRList($id) {
			$select = "		
						SELECT 
								ls_sr_zone_map.EMPLOYEE_ID,hrms_employee_personal_info.EMPLOYEE_NAME
						FROM 
								ls_sr_zone_map,hrms_employee_personal_info
						WHERE	hrms_employee_personal_info.EMPLOYEE_ID = ls_sr_zone_map.EMPLOYEE_ID
						AND		ls_sr_zone_map.ZONE_ID = '".$id."'
						AND 	ls_sr_zone_map.END_DATE  = '0000-00-00'
						ORDER BY 
								hrms_employee_personal_info.EMPLOYEE_NAME ASC
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
		public function getRETList($id) {
			$select = "		
						SELECT 
								ls_retailer_info.RETAILER_ID,ls_retailer_info.NAME,ls_retailer_info.SHOP_NAME
						FROM 
								ls_retailer_info
						WHERE	ls_retailer_info.ZONE_ID = '".$id."'
						ORDER BY 
								ls_retailer_info.NAME ASC
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
		public function getSRWiseRETList($id) {
			$select = "		
						SELECT 
								ls_sr_retailer_map.SR_RETAILER_MAP_ID,ls_sr_retailer_map.RETAILER_ID,ls_retailer_info.SHOP_NAME,ls_zone_info.SHORT_NAME AS ZONESHORTNAME
						FROM 
								ls_retailer_info,ls_sr_retailer_map,ls_zone_info
						WHERE	ls_sr_retailer_map.EMPLOYEE_ID= '".$id."'
						AND		ls_retailer_info.RETAILER_ID = ls_sr_retailer_map.RETAILER_ID
						AND		ls_sr_retailer_map.END_DATE = '0000-00-00'
						AND		ls_zone_info.ZONE_ID = ls_retailer_info.ZONE_ID
						ORDER BY 
								ls_retailer_info.NAME ASC
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
		public function getRetCOACode($retID) {
			$select = "		
						SELECT 
								ls_retailer_info.RECEIVABLE_COA,gs_coa.COA_NAME,ls_retailer_info.SHOP_NAME
						FROM 
								ls_retailer_info, gs_coa							
						WHERE	ls_retailer_info.RETAILER_ID = '".$retID."'
						AND gs_coa.COA_CODE = ls_retailer_info.RECEIVABLE_COA
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
		
		public function fetchViewRetailerInfoDetails($cond) {
			$select = "SELECT 
						RETAILER_ID,
						   BRANCH_ID,
						   NAME,
						   SHOP_NAME,
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
							ls_retailer_info
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
		public function deleteRetailerInformation($id) {
			$this->tableGateway->delete(array('RETAILER_ID' => $id));
		}
		
		public function fetchViewSRWiseRetailerInfoDetails($cond) {
			 $select = "SELECT 
						 
						  empInfo.EMPLOYEE_NAME,
						  ri.NAME,
						  ri.SHOP_NAME,
						  ri.ADDRESS,
						  ri.MOBILE,
						  ri.EMAIL,
						  ri.BUSINESS_DATE
					
						FROM 
							 hrms_employee_personal_info empInfo, ls_sr_retailer_map srm, ls_retailer_info ri
						   WHERE {$cond }
						   AND empInfo.EMPLOYEE_ID = srm.EMPLOYEE_ID
						   AND srm.RETAILER_ID = ri.RETAILER_ID
						   AND srm.END_DATE = '0000-00-00'
						ORDER BY 
						empInfo.EMPLOYEE_NAME ASC"; 
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			return $resultSet;
		}
		
		public function insertRETCSVData($table, $tbl_f, $csv_values){	
			$insertCSVSql = "INSERT INTO $table ({$tbl_f}) VALUES({$csv_values})";
			$insertCSVStatement = $this->tableGateway->getAdapter()->createStatement($insertCSVSql);
			$insertCSVStatement->prepare();
			if(!$insertCSVStatement->execute()) {
				return false;
			} else {
				$maxRetailerID= '';
				$maxRetIDSql = "SELECT MAX(ls_retailer_info.RETAILER_ID) AS RETAILER_ID FROM ls_retailer_info";
				$maxRetIDStatement = $this->tableGateway->getAdapter()->createStatement($maxRetIDSql);
				$maxRetIDStatement->prepare();
				$maxRetIDResult = $maxRetIDStatement->execute();
				if ($maxRetIDResult instanceof ResultInterface && $maxRetIDResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($maxRetIDResult);
				}
				foreach($resultSet as $resultMaxRetID) {
					$maxRetailerID	= $resultMaxRetID->RETAILER_ID;
				}					
				//echo $maxRetailerID;die();
				$maxRetailerName= '';
				$maxRetNameSql = "SELECT ls_retailer_info.NAME AS NAME FROM ls_retailer_info WHERE RETAILER_ID = {$maxRetailerID}";
				$maxRetNameStatement = $this->tableGateway->getAdapter()->createStatement($maxRetNameSql);
				$maxRetNameStatement->prepare();
				$maxRetNameResult = $maxRetNameStatement->execute();
				if ($maxRetNameResult instanceof ResultInterface && $maxRetNameResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($maxRetNameResult);
				}
				foreach($resultSet as $resultMaxRetName) {
					$maxRetailerName	= $resultMaxRetName->NAME;
				}
				//Receivable Chart of Account Generate Start
				$maxReceiveableCOACode = '';
				$selectMaxRcvCOA = "SELECT 
											COALESCE(MAX(substr(COA_CODE,1,9)),302004000)+1  AS MAX_RECEIVABLE_COA_CODE
										FROM
												gs_coa 		
										WHERE
											  substr(COA_CODE,1,9) BETWEEN '302004000' AND '302004999'";
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
				$RECEIVABLE_COA_NAME 	= "Receivable from - ".$maxRetailerName;
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
										AND   	C.COA_CODE  = '302004000'
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
				
				//Payable Chart of Account Generate Start - 201003000
				$maxPayableCOACode = '';
				$selectMaxPayCOA = "SELECT
											COALESCE(MAX(substr(COA_CODE,1,9)),201003000)+1  AS MAX_RECEIVABLE_COA_CODE
											
										FROM
												gs_coa 		
										WHERE
											  substr(COA_CODE,1,9) BETWEEN '201003000' AND '201003999'";
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
				$PAYABLE_COA_NAME 	= "Payable to - ".$maxRetailerName;
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
										AND   	C.COA_CODE  = '201003000'
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
				$returnData	= array(
						"RETAILER_ID" => $maxRetailerID,
						"COA_DATA" => $CoaData,
				  );
				return $returnData;
			}
		}
			//AND    (ls_sr_retailer_map.END_DATE = '0000-00-00' OR ls_sr_retailer_map.END_DATE = 'Active')
			public function getRetailerListForMapCheck($id){
				$select = "		
						SELECT 
								ls_retailer_info.RETAILER_ID,ls_retailer_info.NAME,ls_retailer_info.SHOP_NAME,ls_retailer_info.ADDRESS, ls_sr_retailer_map.END_DATE, ls_sr_retailer_map.SR_RETAILER_MAP_ID
						FROM 
								ls_retailer_info, ls_sr_retailer_map
						WHERE   ls_sr_retailer_map.EMPLOYEE_ID = {$id}
						
						AND   ls_sr_retailer_map.RETAILER_ID = ls_retailer_info.RETAILER_ID 
						ORDER BY 
								ls_retailer_info.NAME ASC
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
		
		public function getRetailerListForMap($id) {
			
			$select = "		
						SELECT 
								ls_retailer_info.RETAILER_ID,ls_retailer_info.NAME,ls_retailer_info.SHOP_NAME,ls_retailer_info.ADDRESS
						FROM 
								ls_retailer_info
						ORDER BY 
								ls_retailer_info.NAME ASC
			";
			
			/*
			$select = "		
						SELECT 
								ls_retailer_info.RETAILER_ID,ls_retailer_info.NAME,ls_retailer_info.SHOP_NAME,ls_retailer_info.ADDRESS
						FROM 
								ls_retailer_info
						WHERE ls_retailer_info.RETAILER_ID != (SELECT ls_sr_retailer_map.RETAILER_ID FROM ls_sr_retailer_map WHERE EMPLOYEE_ID = '".$id."' )
						ORDER BY 
								ls_retailer_info.NAME ASC
			";
			*/
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			$result ='';
			if($resultSet){
				$result .='<table border="0" width="100%" class="table" cellpadding="2" cellspacing="2" style="font-size:100%;">';
				foreach($resultSet as $row) {						
						$RETAILER_ID = $row['RETAILER_ID'],
						$NAME  		= $row['NAME'],
						$SHOP_NAME 	= $row['SHOP_NAME'],
						$ADDRESS 	= $row['ADDRESS'],										
					}
					$result .='</table>';
				}
			return $result;
		}
		
		public function fetchRetailerIDForPaymentReceipt($coaCode,$type) {
			$cond = '';
			if($type == 'payable'){
				$cond = "RETINFO.PAYABLE_COA = '".$coaCode."'";
			} else {
				$cond = "RETINFO.RECEIVABLE_COA = '".$coaCode."'";
			}
			$getTblDataSql   = "SELECT 
										RETINFO.RETAILER_ID,
										RETINFO.ZONE_ID,
										RETSRMAP.EMPLOYEE_ID
								FROM 	ls_retailer_info  RETINFO, ls_sr_retailer_map RETSRMAP										
								WHERE 	{$cond}
								AND		RETSRMAP.RETAILER_ID = RETINFO.RETAILER_ID
								AND		RETSRMAP.END_DATE = '0000-00-00'
								";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}			
			foreach($resultSet as $resultMaxRcvCOA) {
				$returnData	= array(
						"RETAILER_ID" => $resultMaxRcvCOA->RETAILER_ID,
						"ZONE_ID" => $resultMaxRcvCOA->ZONE_ID,
						"EMPLOYEE_ID" => $resultMaxRcvCOA->EMPLOYEE_ID,
				  );
			}
			return $returnData;
		}
		public function fetchRetailerListforLedger($input,$cond) {
			if(!empty($input)){
				$getTblDataSql   = "SELECT 
										RETINFO.RETAILER_ID,
										RETINFO.NAME,
										RETINFO.SHOP_NAME,
										RETINFO.MOBILE,
										RETINFO.PAYABLE_COA,
										RETINFO.RECEIVABLE_COA,
										ls_zone_info.NAME AS ZONENAME
								FROM 
										ls_retailer_info RETINFO, ls_zone_info
								WHERE	RETINFO.ZONE_ID = ls_zone_info.ZONE_ID										
								AND 	LOWER(RETINFO.NAME) = '".$input."' {$cond}
								ORDER BY LOWER(RETINFO.NAME) ASC";
			}else{
				$getTblDataSql   = "SELECT 
										RETINFO.RETAILER_ID,
										RETINFO.NAME,
										RETINFO.SHOP_NAME,
										RETINFO.MOBILE,
										RETINFO.PAYABLE_COA,
										RETINFO.RECEIVABLE_COA,
										ls_zone_info.NAME AS ZONENAME
								FROM 
										ls_retailer_info RETINFO, ls_zone_info
								WHERE	RETINFO.ZONE_ID = ls_zone_info.ZONE_ID										
								
								ORDER BY LOWER(RETINFO.NAME) ASC";
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
		public function getRetailerDetails($id) {
			if(strlen($id)>0){
				$select = "		
						SELECT 
								RETINFO.NAME,
								RETINFO.SHOP_NAME,
								RETINFO.MOBILE,
								RETINFO.ADDRESS,
								RETINFO.RETAILER_ID
						FROM 
								ls_retailer_info RETINFO
						WHERE RETINFO.RETAILER_ID = '".$id."'
						ORDER BY RETINFO.NAME ASC
			";
			}else{
				$select = "		
						SELECT 
								RETINFO.NAME,
								RETINFO.SHOP_NAME,
								RETINFO.MOBILE,
								RETINFO.ADDRESS,
								RETINFO.RETAILER_ID
						FROM 
								ls_retailer_info RETINFO
						
						ORDER BY RETINFO.NAME ASC
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