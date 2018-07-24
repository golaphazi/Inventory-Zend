<?php
	namespace Accounts\Model;
	
	class PaymentTransaction {
		public $PAYMENT_TRANSACTION_ID;
		public $BRANCH_ID;
		public $SUPPLIER_INFO_ID;
		public $ZONE_ID;
		public $EMPLOYEE_ID;
		public $RETAILER_ID;
		public $DEBIT;
		public $CREDIT;
		public $BALANCE;
		public $TRANSACTION_FLAG;
		public $SUPPLIER_FLAG;		
		public $ZONE_FLAG;
		public $SR_FLAG;		
		public $RETAILER_FLAG;
		public $NARRATION;
		public $BUSINESS_DATE;
		public $RECORD_DATE;
		public $OPERATE_BY;
		public $AMOUNT;

		
		public function exchangeArray($data) {
			$this->PAYMENT_TRANSACTION_ID		= (!empty($data['PAYMENT_TRANSACTION_ID'])) ? $data['PAYMENT_TRANSACTION_ID'] : null;
			$this->BRANCH_ID					= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->SUPPLIER_INFO_ID 			= (!empty($data['SUPPLIER_INFO_ID'])) ? $data['SUPPLIER_INFO_ID'] : null;
			$this->ZONE_ID 						= (!empty($data['ZONE_ID'])) ? $data['ZONE_ID'] : null;
			$this->EMPLOYEE_ID 					= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->RETAILER_ID 					= (!empty($data['RETAILER_ID'])) ? $data['RETAILER_ID'] : null;
			$this->DEBIT 						= (!empty($data['DEBIT'])) ? $data['DEBIT'] : null;
			$this->CREDIT 						= (!empty($data['CREDIT'])) ? $data['CREDIT'] : null;
			$this->BALANCE 						= (!empty($data['BALANCE'])) ? $data['BALANCE'] : null;
			$this->AMOUNT 						= (!empty($data['AMOUNT'])) ? $data['AMOUNT'] : null;
			$this->TRANSACTION_FLAG 			= (!empty($data['TRANSACTION_FLAG'])) ? $data['TRANSACTION_FLAG'] : null;
			$this->SUPPLIER_FLAG 				= (!empty($data['SUPPLIER_FLAG'])) ? $data['SUPPLIER_FLAG'] : null;
			$this->ZONE_FLAG 					= (!empty($data['ZONE_FLAG'])) ? $data['ZONE_FLAG'] : null;
			$this->SR_FLAG 						= (!empty($data['SR_FLAG'])) ? $data['SR_FLAG'] : null;			
			$this->RETAILER_FLAG 				= (!empty($data['RETAILER_FLAG'])) ? $data['RETAILER_FLAG'] : null;
			$this->NARRATION 					= (!empty($data['NARRATION'])) ? $data['NARRATION'] : null;
			$this->DRAWNON						= (!empty($data['DRAWNON'])) ? $data['DRAWNON'] : null;			
			$this->BUSINESS_DATE 				= (!empty($data['BUSINESS_DATE'])) ? $data['BUSINESS_DATE'] : null;
			$this->RECORD_DATE 					= (!empty($data['RECORD_DATE'])) ? $data['RECORD_DATE'] : null;
			$this->OPERATE_BY					= (!empty($data['OPERATE_BY'])) ? $data['OPERATE_BY'] : null;
		}

		
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>	