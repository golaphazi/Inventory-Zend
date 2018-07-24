<?php
	namespace HumanResource\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	use Zend\Session\Container as SessionContainer;
	
	class EmployeeSalaryInfoTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function saveEmployeeSalaryInfo(EmployeeSalaryInfo $employeeSalaryInfo) {
			//echo "<pre>"; print_r($employeeSalaryInfo); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate 		= date("d-M-Y", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$id 	= (int) $employeeSalaryInfo->EMPLOYEE_SALARY_ID;
			$data 	= array(
				'EMPLOYEE_ID' 		=> $employeeSalaryInfo->EMPLOYEE_ID,	
				'SALARY_AMOUNT' 	=> $employeeSalaryInfo->SALARY_AMOUNT,
				'BUSINESS_DATE' 	=> $businessDate,
				'RECORD_DATE' 		=> $recDate,
				'OPERATE_BY' 		=> $userId,
				'START_DATE' 		=> $businessDate,
			);
			
			if($id == 0) {
				if($this->tableGateway->insert($data)) {
					return true;
				} else {
					return false;	
				}
			} else {
				$data1['END_DATE'] 		= $businessDate;
				if($this->tableGateway->update($data1,array('EMPLOYEE_SALARY_ID' => $id))){
					if($this->tableGateway->insert($data)) {
						return true;
					} else {
						return false;	
					}
				} else {
					return false;	
				}
			}
		}
	}
?>	