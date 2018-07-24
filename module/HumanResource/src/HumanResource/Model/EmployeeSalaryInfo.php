<?php
	namespace HumanResource\Model;
	
	class EmployeeSalaryInfo {
		public $EMPLOYEE_SALARY_ID;
		public $EMPLOYEE_ID;
		public $SALARY_AMOUNT;
		
		public function exchangeArray($data) {
			$this->EMPLOYEE_SALARY_ID 	= (!empty($data['EMPLOYEE_SALARY_ID'])) ? $data['EMPLOYEE_SALARY_ID'] : null;
			$this->EMPLOYEE_ID 			= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->SALARY_AMOUNT 		= (!empty($data['SALARY_AMOUNT'])) ? $data['SALARY_AMOUNT'] : 0;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>	