<?php
	namespace LocalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class ZoneInformationTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select	= new Select('ls_zone_info');
				$select->order('NAME ASC');
				
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new ZoneInformation());
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
		
		public function getZoneInformation($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('ZONE_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function zoneInformationExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function fetchViewZoneInfoDetails($cond) {
			$select = "SELECT 
						ZONE_ID,
						   BRANCH_ID,
						   NAME,
						   SHORT_NAME,
						   ADDRESS,
						   BUSINESS_DATE
					
						FROM 
							ls_zone_info
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
		
		public function saveZoneInformation(ZoneInformation $zoneInformation) {
			$this->session = new SessionContainer('post_supply');
			$businessdate = $this->session->businessdate;
			$recdate = $this->session->recdate;
			$userid = $this->session->userid; 
			$branchid = 1;//$this->session->branchid;
			$data = array(
				'BRANCH_ID'		=> $branchid,
				'NAME' 			=> $zoneInformation->NAME,
				'SHORT_NAME' 	=> $zoneInformation->SHORT_NAME,
				'ADDRESS' 		=> $zoneInformation->ADDRESS,
				'BUSINESS_DATE' => date('Y-m-d', strtotime($businessdate)),
				'RECORD_DATE' 	=> $recdate,
				'OPERATE_BY' 	=> $userid,
			); 
						
			$existCheckData = array(
				'NAME' => $zoneInformation->NAME,
				'BRANCH_ID'		=> $branchid,
			);
			
			$id = (int) $zoneInformation->ZONE_ID;
			
			if($id == 0) {
								
				if($this->zoneInformationExist($existCheckData)) {
					throw new \Exception("Zone Information ".$zoneInformation->NAME." already exist!");
				} else {
					if($this->tableGateway->insert($data)) {
							$supplierSql = $this->zoneInformationExist($existCheckData);
							$supplierInfoId = (int) $supplierSql->ZONE_ID;
							
							$CoaData = '';
							$returnData	= array(
									"ZONE_ID" => $supplierInfoId,
									"COA_DATA" => $CoaData,
							  );
							return $returnData;
					} else {
						return false;	
					}
				}
			} else {
				if($this->getzoneInformation($id)) {
					$supplierInfoId	= '';
					$nameExist		= '';
					$nameExist 		= $this->zoneInformationExist($existCheckData);
					$supplierInfoId 	=  $nameExist->ZONE_ID;
					if((!empty($nameExist)) && ($id!=$supplierInfoId)) {
						throw new \Exception("Zone Information ".$zoneInformation->NAME." already exist!");
					} else {
						if($this->tableGateway->update($data,array('ZONE_ID' => $id))) {
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
		
		
		public function fetchViewZoneWiseSrInfoDetails($cond) {
			$select = "SELECT 
						  zi.NAME,
						  empInfo.EMPLOYEE_TYPE,
						  empInfo.EMPLOYEE_NAME,
						  empInfo.FATHER_NAME,
						  empInfo.MOTHER_NAME,
						  empInfo.GENDER,
						  empInfo.BLOOD_GROUP,
						  empInfo.RELIGION,
						  empInfo.BUSINESS_DATE
					
						FROM 
							 hrms_employee_personal_info empInfo, ls_sr_zone_map zm, ls_zone_info zi
						   WHERE {$cond }
						   AND empInfo.EMPLOYEE_ID = zm.EMPLOYEE_ID
						   AND zi.ZONE_ID = zm.ZONE_ID
						   AND zm.END_DATE = '0000-00-00'
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
		public function getZoneListForSRMap() {
			$select = "		
						SELECT 
								ls_zone_info.ZONE_ID,ls_zone_info.NAME,ls_zone_info.SHORT_NAME,ls_zone_info.ADDRESS,c_branch.BRANCH_NAME AS BRANCHNAME
						FROM 
								ls_zone_info,c_branch
						WHERE	c_branch.BRANCH_ID = ls_zone_info.BRANCH_ID
						ORDER BY 
								ls_zone_info.NAME ASC
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
		public function deleteZoneInformation($id) {
			$this->tableGateway->delete(array('ZONE_ID' => $id));
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