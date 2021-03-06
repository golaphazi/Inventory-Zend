<?php
	namespace LocalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class SrTargetBkdnTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				/*$select	= new Select('ls_sr_target');
				$select->order('EMPLOYEE_ID ASC');
				
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new SrTargetBkdn());
				
				// create a new pagination adapter object
				$paginatorAdapter 	= new DbSelect($select,$this->tableGateway->getAdapter(),$resultSetPrototype);
				$paginator 			= new Paginator($paginatorAdapter);
				return $paginator;*/
				/*$select	= new Select('ls_sr_zone_map');
				$select->order('EMPLOYEE_ID ASC');
				
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new SrZoneMap());*/
				
				$select = "		
						SELECT 
							st.SR_TARGET_ID,
							emp.EMPLOYEE_NAME,
							st.REMARKS,
							st.START_DATE,
							st.END_DATE,
							st.BUSINESS_DATE
						FROM 
					ls_sr_target st, hrms_employee_personal_info emp
					where st.EMPLOYEE_ID = emp.EMPLOYEE_ID
					ORDER BY 
					st.EMPLOYEE_ID ASC
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
		
		public function fetchViewSrTargetBkdnInfoDetails($cond) {
			$select = "SELECT 
						srt.SR_TARGET_ID,
						   srt.BRANCH_ID,
						   empInfo.EMPLOYEE_NAME,
						   srt.CALCULATE_IN,
						   srt.TARGET_VALUE,
						   srt.REMARKS,
						   srt.START_DATE,
						   srt.END_DATE,
						   srt.BUSINESS_DATE
					
						FROM 
							 ls_sr_target srt, hrms_employee_personal_info empInfo, hrms_employee_posting_info empPosting, gs_designation desg
						   WHERE {$cond }
						   AND srt.EMPLOYEE_ID = empInfo.EMPLOYEE_ID
						   AND empInfo.EMPLOYEE_ID = empPosting.EMPLOYEE_ID
						   AND empPosting.DESIGNATION_ID = desg.DESIGNATION_ID
						   AND desg.DESIGNATION_ID = 2
						ORDER BY 
						empInfo.EMPLOYEE_NAME ASC
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
		
		public function getSrTargetBkdn($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('SR_TARGET_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function srTargetBkdnExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveSrTargetBkdn(SrTargetBkdn $srTargetBkdn) {
			$this->session = new SessionContainer('post_supply');
			$businessdate = $this->session->businessdate;
			$recdate = $this->session->recdate;
			$userid = $this->session->userid;
			$branchid = $this->session->branchid;
			$data = array(
				'SR_TARGET_ID'		=> $srTargetBkdn->SR_TARGET_ID,
				'TARGET_FROM' 		=> $srTargetBkdn->TARGET_FROM,
				'TARGET_TO' 		=> $srTargetBkdn->TARGET_TO,
				'TARGET_VALUE' 		=> $srTargetBkdn->TARGET_VALUE,
			); 
			
			$existCheckData = array(
				'SR_TARGET_ID'		=> $srTargetBkdn->SR_TARGET_ID,
				'TARGET_FROM' 		=> $srTargetBkdn->TARGET_FROM,
				'TARGET_TO' 		=> $srTargetBkdn->TARGET_TO,
				'TARGET_VALUE' 		=> $srTargetBkdn->TARGET_VALUE,
			);
			
			$id = (int) $srTargetBkdn->SR_TARGET_BKDN_ID;
			
			if($id == 0) {
								
				if($this->srTargetBkdnExist($existCheckData)) {
					throw new \Exception("SR Target Bkdn ".$srTargetBkdn->SR_TARGET_ID." already exist!");
				} else {
					
					if($this->tableGateway->insert($data)) {
						$srTargetBkdnSql = $this->srTargetBkdnExist($existCheckData);
						$srTargetBkdnId = (int) $srTargetBkdnSql->SR_TARGET_BKDN_ID;
						
						$CoaData = '';
						$returnData	= array(
								"SR_TARGET_BKDN_ID" => $srTargetBkdnId,
								"COA_DATA" => $CoaData,
						  );
							return $returnData;
					} else {
						return false;	
					}
					
					
				}
			} else {
				if($this->getsrTargetBkdn($id)) {
					$srTargetBkdnId	= '';
					$nameExist		= '';
					$nameExist 		= $this->srTargetBkdnExist($existCheckData);
					$srTargetBkdnId 	=  $nameExist->SR_TARGET_ID;
					if((!empty($nameExist)) && ($id!=$srTargetBkdnId)) {
						throw new \Exception("SR Target  ".$srTargetBkdn->SR_TARGET_ID." already exist!");
					} else {
						if($this->tableGateway->update($data,array('SR_TARGET_BKDN_ID' => $id))) {
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
			
		public function deleteSrTargetBkdn($id) {
			$this->tableGateway->delete(array('SR_TARGET_ID' => $id));
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