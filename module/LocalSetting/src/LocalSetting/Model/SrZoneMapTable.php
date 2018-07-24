<?php
	namespace LocalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class SrZoneMapTable {
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
				$resultSetPrototype->setArrayObjectPrototype(new SrZoneMap());*/
				
				$select = "		
						SELECT 
							szm.SR_ZONE_MAP_ID,
									emp.EMPLOYEE_NAME,
								   zi.NAME,
								   szm.START_DATE,
								   szm.END_DATE,
								   szm.BUSINESS_DATE
						FROM 
					ls_sr_zone_map szm, hrms_employee_personal_info emp, ls_zone_info zi
					 where szm.EMPLOYEE_ID = emp.EMPLOYEE_ID
					and szm.ZONE_ID = zi.ZONE_ID
					and szm.END_DATE = '0000-00-00'
					ORDER BY 
					szm.EMPLOYEE_ID ASC
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
		
		public function getSrZoneMap($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('SR_ZONE_MAP_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function srZoneMapExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveSrZoneMap(SrZoneMap $srZoneMap) {
			$this->session = new SessionContainer('post_supply');
			$businessdate = $this->session->businessdate;
			$recdate = $this->session->recdate;
			$userid = $this->session->userid;
			$branchid = 1;//$this->session->branchid;
			$data = array(
				'BRANCH_ID'		=> $branchid,
				'ZONE_ID'		=> $srZoneMap->ZONE_ID,
				'EMPLOYEE_ID' 	=> $srZoneMap->EMPLOYEE_ID,
				'START_DATE' 	=> date('Y-m-d', strtotime($businessdate)),
				'END_DATE' 		=> '',
				'BUSINESS_DATE' => date('Y-m-d', strtotime($businessdate)),
				'RECORD_DATE' 	=> $recdate,
				'OPERATE_BY' 	=> $userid,
			); 
			
			
			$existCheckData = array(
				'BRANCH_ID'		=> $branchid,
				'ZONE_ID' => $srZoneMap->ZONE_ID,
				'EMPLOYEE_ID' => $srZoneMap->EMPLOYEE_ID,
				'START_DATE' 	=> date('Y-m-d', strtotime($businessdate)),
				'END_DATE' 		=> '',
			);
			
			$id = (int) $srZoneMap->SR_ZONE_MAP_ID;
			
			if($id == 0) {
								
				if($this->srZoneMapExist($existCheckData)) {
					throw new \Exception("SR and Zone Map ".$srZoneMap->EMPLOYEE_ID." already exist!");
				} else {
					
					$updateData = array(
									'END_DATE' 		=> date('Y-m-d', strtotime($businessdate)),
								); 
					if($this->tableGateway->update($updateData,array('EMPLOYEE_ID' => $srZoneMap->EMPLOYEE_ID,'ZONE_ID' => $srZoneMap->ZONE_ID))) {
							if($this->tableGateway->insert($data)) {
									$supplierSql = $this->srZoneMapExist($existCheckData);
									$supplierInfoId = (int) $supplierSql->SR_ZONE_MAP_ID;
									
									$CoaData = '';
									$returnData	= array(
											"SR_ZONE_MAP_ID" => $supplierInfoId,
											"COA_DATA" => $CoaData,
									  );
										return $returnData;
								} else {
									return false;	
								}	
					} else {
						if($this->tableGateway->insert($data)) {
								$supplierSql = $this->srZoneMapExist($existCheckData);
								$supplierInfoId = (int) $supplierSql->SR_ZONE_MAP_ID;
								
								$CoaData = '';
								$returnData	= array(
										"SR_ZONE_MAP_ID" => $supplierInfoId,
										"COA_DATA" => $CoaData,
								  );
									return $returnData;
							} else {
								return false;	
							}	
					} 
				}
			} else {
				if($this->getsrZoneMap($id)) {
					$supplierInfoId	= '';
					$nameExist		= '';
					$nameExist 		= $this->srZoneMapExist($existCheckData);
					$supplierInfoId 	=  $nameExist->SR_ZONE_MAP_ID;
					if((!empty($nameExist)) && ($id!=$supplierInfoId)) {
						throw new \Exception("SR Information ".$srZoneMap->EMPLOYEE_ID." already exist!");
					} else {
						if($this->tableGateway->update($data,array('SR_ZONE_MAP_ID' => $id))) {
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
		
		public function updateSrZoneMap($flag, $coaCode, $supplierInfoId) {
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
			
			if($this->tableGateway->update($data,array('SR_ZONE_MAP_ID' => $supplierInfoId))) {
				return true;	
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
		public function deleteSrZoneMap($id) {
			$this->tableGateway->delete(array('SR_ZONE_MAP_ID' => $id));
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