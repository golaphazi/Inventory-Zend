<?php
	namespace GlobalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class DesignationTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select	= new Select('gs_designation');
				$select->order('DESIGNATION ASC');
				
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new Designation());
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
		
		public function getDesignation($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('DESIGNATION_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function getDesignationForSelect() {
			$getTblDataSql   = "SELECT * FROM gs_designation ORDER BY DESIGNATION ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function designationExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveDesignation(Designation $designation) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$data = array(
				'DESIGNATION' 		=> $designation->DESIGNATION,
				'DESCRIPTION' 		=> $designation->DESCRIPTION,
				'BUSINESS_DATE' 	=> $businessDate,
				'RECORD_DATE' 		=> $recDate,
				'OPERATE_BY' 		=> $userId,
			);
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'DESIGNATION' => $designation->DESIGNATION,
			);
			$id = (int) $designation->DESIGNATION_ID;
			
			if($id == 0) {
				if($this->designationExist($existCheckData)) {
					throw new \Exception("Designation ".$designation->DESIGNATION." already exist!");
				} else {
					if($this->tableGateway->insert($data)) {
						return true;	
					} else {
						return false;	
					}
				}
			} else {
				if($this->getdesignation($id)) {
					$existingDesigId	= '';
					$designationExist	= '';
					$designationExist 	= $this->designationExist($existCheckData);
					$existingDesigId 	=  $designationExist->DESIGNATION_ID;
					if((!empty($designationExist)) && ($id!=$existingDesigId)) {
						throw new \Exception("Designation ".$designation->DESIGNATION." already exist!");
					} else {
						if($this->tableGateway->update($data,array('DESIGNATION_ID' => $id))) {
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
		
		public function getEmpNameInfo($designationId) {
			$select = "SELECT 
								DISTINCT empInfo.EMPLOYEE_ID,
								empInfo.EMPLOYEE_NAME 
						FROM 
								hrms_employee_personal_info empInfo, hrms_employee_posting_info empPosting
						WHERE empPosting.DESIGNATION_ID = '". $designationId."' AND  empInfo.EMPLOYEE_ID = empPosting.EMPLOYEE_ID";
				$stmt = $this->tableGateway->getAdapter()->createStatement($select);
				$stmt->prepare();
				$result = $stmt->execute();
				
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			return $resultSet;
		}
		
		
		public function deleteDesignation($id) {
			$this->tableGateway->delete(array('DESIGNATION_ID' => $id));
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