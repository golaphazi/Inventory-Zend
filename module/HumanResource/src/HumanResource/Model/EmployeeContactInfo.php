<?php
	namespace HumanResource\Model;
	
	class EmployeeContactInfo {
		public $EMPLOYEE_CONTACT_ID;
		public $EMPLOYEE_ID;
		public $CONTACT_ADDRESS_FIRST;
		public $CONTACT_ADDRESS_SECOND;
		public $TELEPHONE;
		public $EMAIL_ADDRESS;
		
		public function exchangeArray($data) {
			$this->EMPLOYEE_CONTACT_ID 		= (!empty($data['EMPLOYEE_CONTACT_ID'])) ? $data['EMPLOYEE_CONTACT_ID'] : null;
			$this->EMPLOYEE_ID 				= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->CONTACT_ADDRESS_FIRST 	= (!empty($data['CONTACT_ADDRESS_FIRST'])) ? $data['CONTACT_ADDRESS_FIRST'] : null;
			$this->CONTACT_ADDRESS_SECOND 	= (!empty($data['CONTACT_ADDRESS_SECOND'])) ? $data['CONTACT_ADDRESS_SECOND'] : null;
			$this->TELEPHONE 				= (!empty($data['TELEPHONE'])) ? $data['TELEPHONE'] : null;
			$this->EMAIL_ADDRESS 			= (!empty($data['EMAIL_ADDRESS'])) ? $data['EMAIL_ADDRESS'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>	