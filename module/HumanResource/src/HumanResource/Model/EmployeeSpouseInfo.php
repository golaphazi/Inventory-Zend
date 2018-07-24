<?php
	namespace HumanResource\Model;
	
	class EmployeeSpouseInfo {
		public $EMPLOYEE_SPOUSE_ID;
		public $EMPLOYEE_ID;
		public $SPOUSE_NAME;
		public $OCCUPATION_ID;
		public $SPOUSE_ADDRESS;
		
		public function exchangeArray($data) {
			$this->EMPLOYEE_SPOUSE_ID 	= (!empty($data['EMPLOYEE_SPOUSE_ID'])) ? $data['EMPLOYEE_SPOUSE_ID'] : null;
			$this->EMPLOYEE_ID 			= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->SPOUSE_NAME 			= (!empty($data['SPOUSE_NAME'])) ? $data['SPOUSE_NAME'] : null;
			$this->OCCUPATION_ID 		= (!empty($data['OCCUPATION_ID'])) ? $data['OCCUPATION_ID'] : null;
			$this->SPOUSE_ADDRESS 		= (!empty($data['SPOUSE_ADDRESS'])) ? $data['SPOUSE_ADDRESS'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>	