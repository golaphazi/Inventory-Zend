<?php
	namespace LocalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class SrRetailerMapTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				/*$select	= new Select('ls_sr_zone_map');
				$select->order('EMPLOYEE_ID ASC');
				
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new SrRetailerMap());*/
				
				$select = "		
						SELECT 
							srm.SR_RETAILER_MAP_ID,
							emp.EMPLOYEE_NAME,
						   ri.NAME,
						   srm.START_DATE,
						   srm.END_DATE,
						   srm.BUSINESS_DATE,
						   ri.SHOP_NAME
						FROM 
						 ls_sr_retailer_map srm, hrms_employee_personal_info emp, ls_retailer_info ri
						WHERE srm.EMPLOYEE_ID = emp.EMPLOYEE_ID
						AND srm.RETAILER_ID = ri.RETAILER_ID
						AND srm.END_DATE = '0000-00-00'
						ORDER BY 
						srm.EMPLOYEE_ID ASC
				";
				$stmt = $this->tableGateway->getAdapter()->createStatement($select);
				$stmt->prepare();
				$result = $stmt->execute();
				
				if ($result instanceof ResultInterface && $result->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($result);
				}
				
				
				// create a new pagination adapter object
				/*$paginatorAdapter 	= new DbSelect($result,$this->tableGateway->getAdapter(),$resultSetPrototype);
				$paginator 			= new Paginator($paginatorAdapter);
				return $paginator;*/
				
				return $resultSet;
			}
			
			if (null === $select)
			$select	= new Select();
			$select->from($this->table);
			$resultSet = $this->selectWith($select);
			$resultSet->buffer();
			return $resultSet;
		}
		
	
		public function getSrRetailerMap($id='33') {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('SR_RETAILER_MAP_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function srRetailerMapExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveSrRetailerMap(SrRetailerMap $srRetailerMap) {
			$this->session = new SessionContainer('post_supply');
			$businessdate = $this->session->businessdate;
			$recdate = $this->session->recdate;
			$userid = $this->session->userid;
			$branchid = 1;//$this->session->branchid;
			$data = array(
				'BRANCH_ID'		=> $branchid,
				'RETAILER_ID'	=> $srRetailerMap->RETAILER_ID,
				'EMPLOYEE_ID' 	=> $srRetailerMap->EMPLOYEE_ID,
				'START_DATE' 	=> date('Y-m-d', strtotime($businessdate)),
				'END_DATE' 		=> 'Active',
				'BUSINESS_DATE' => date('Y-m-d', strtotime($businessdate)),
				'RECORD_DATE' 	=> $recdate,
				'OPERATE_BY' 	=> $userid,
			); 
			
			
			$existCheckData = array(
				'BRANCH_ID'		=> $branchid,
				'RETAILER_ID' => $srRetailerMap->RETAILER_ID,
				'EMPLOYEE_ID' => $srRetailerMap->EMPLOYEE_ID,
				'START_DATE' 	=> date('Y-m-d', strtotime($businessdate)),
				'END_DATE' 		=> 'Active',
			);
			
			$id = (int) $srRetailerMap->SR_RETAILER_MAP_ID;
			
			if($id == 0) {
								
				if($this->srRetailerMapExist($existCheckData)) {
					throw new \Exception("SR and Retailer Map ".$srRetailerMap->EMPLOYEE_ID." already exist!");
				} else {
					
					$updateData = array(
									//'END_DATE' 		=> date('Y-m-d', strtotime($businessdate)),
									'END_DATE' 		=> 'Active',
								); 
					if($this->tableGateway->update($updateData,array('EMPLOYEE_ID' => $srRetailerMap->EMPLOYEE_ID,'RETAILER_ID' => $srRetailerMap->RETAILER_ID))) {
							if($this->tableGateway->insert($data)) {
									$supplierSql = $this->srRetailerMapExist($existCheckData);
									$supplierInfoId = (int) $supplierSql->SR_RETAILER_MAP_ID;
									
									$CoaData = '';
									$returnData	= array(
											"SR_RETAILER_MAP_ID" => $supplierInfoId,
											"COA_DATA" => $CoaData,
									  );
										return $returnData;
								} else {
									return false;	
								}	
					} else {
						if($this->tableGateway->insert($data)) {
								$supplierSql = $this->srRetailerMapExist($existCheckData);
								$supplierInfoId = (int) $supplierSql->SR_RETAILER_MAP_ID;
								
								$CoaData = '';
								$returnData	= array(
										"SR_RETAILER_MAP_ID" => $supplierInfoId,
										"COA_DATA" => $CoaData,
								  );
									return $returnData;
							} else {
								return false;	
							}	
					} 	
				}
			} else {
				if($this->getsrRetailerMap($id)) {
					$supplierInfoId	= '';
					$nameExist		= '';
					$nameExist 		= $this->srRetailerMapExist($existCheckData);
					$supplierInfoId 	=  $nameExist->SR_RETAILER_MAP_ID;
					if((!empty($nameExist)) && ($id!=$supplierInfoId)) {
						throw new \Exception("SR Information ".$srRetailerMap->EMPLOYEE_ID." already exist!");
					} else {
						if($this->tableGateway->update($data,array('SR_RETAILER_MAP_ID' => $id))) {
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
		
		public function updateSrRetailerMap($flag, $coaCode, $supplierInfoId) {
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
			
			if($this->tableGateway->update($data,array('SR_RETAILER_MAP_ID' => $supplierInfoId))) {
				return true;	
			} else {
				return false;	
			} 
		
			
		}
		
		public function getRetailerListForMapUpdate($id){
			$select = "		
						SELECT 
								ls_sr_retailer_map.END_DATE
						FROM 
								ls_sr_retailer_map
						WHERE   ls_sr_retailer_map.SR_RETAILER_MAP_ID = {$id}
						
						ORDER BY 
								ls_sr_retailer_map.END_DATE ASC
					";
			
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			foreach($resultSet as $getPDidss) {
				$status = $getPDidss['END_DATE'];			
			}
			if($status == '0000-00-00' OR $status == 'Active'){
				$satatuD = 'DeActive';
			}else{
				$satatuD = 'Active';
			}
			$data = array(
							'END_DATE' 	=> $satatuD,
						);
			if($this->tableGateway->update($data,array('SR_RETAILER_MAP_ID' => $id))) {
				return $satatuD;	
			} else {
				return false;	
			} 
		}
		
		#------------- add and remove ---------
		public function getRetailerListForMapUpdateInsert($id,$reId,$srID){
			$this->session = new SessionContainer('post_supply');
			$businessdate = $this->session->businessdate;
			$recdate = $this->session->recdate;
			$userid = $this->session->userid;
			$MAPID ='';
			$select = "		
						SELECT 
								ls_sr_retailer_map.END_DATE,
								ls_sr_retailer_map.SR_RETAILER_MAP_ID
						FROM 
								ls_sr_retailer_map
								
						WHERE   ls_sr_retailer_map.EMPLOYEE_ID = {$srID}
								AND ls_sr_retailer_map.RETAILER_ID = {$reId}
						ORDER BY 
								ls_sr_retailer_map.END_DATE ASC
					";
			
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			foreach($resultSet as $getPDidss) {
				$status = $getPDidss['END_DATE'];			
				$MAPID 	= $getPDidss['SR_RETAILER_MAP_ID'];			
			}
			if(strlen($MAPID)>0){
				if($status == '0000-00-00' OR $status == 'Active'){
					$satatuD = 'DeActive Successfully';
					$statusS = 'DeActive';
				}else{
					$satatuD = 'Active Successfully';
					$statusS = 'Active';
				}
				$data = array(
								'END_DATE' 	=> $statusS,
							);
				$success = $this->tableGateway->update($data,array('SR_RETAILER_MAP_ID' => $MAPID));			
			}else{
				$data1 = array(
						'BRANCH_ID'		=> '1',
						'RETAILER_ID'	=> $reId,
						'EMPLOYEE_ID' 	=> $srID,
						'START_DATE' 	=> date('Y-m-d', strtotime($businessdate)),
						'END_DATE' 		=> 'Active',
						'BUSINESS_DATE' => date('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 	=> $recdate,
						'OPERATE_BY' 	=> $userid,
					); 
				$satatuD = 'Added Successfully';
				
				$success = $this->tableGateway->insert($data1);
				
			}
			
			if($success) {
				return $satatuD;	
			} else {
				return false;	
			} 
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
		public function deleteSrRetailerMap($id) {
			$this->tableGateway->delete(array('SR_RETAILER_MAP_ID' => $id));
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