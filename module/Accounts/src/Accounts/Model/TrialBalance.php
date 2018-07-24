<?php
	namespace Accounts\Model;
	
	class TrialBalance {
		public $TRIAL_BAL_ID;
		public $BRANCH_ID;
		public $AC_CODE;
		public $BALANCE_DATE;
		public $SNTB;
		public $SA_AMOUNT;
		public $TBNTB;
		public $TB_AMOUNT;
		public $CBNTB;
		public $CB_AMOUNT;
		public $BUSINESS_DATE;
		public $RECORD_DATE;
		public $OPERATE_BY;
		
		public function exchangeArray($data) {
			$this->TRIAL_BAL_ID 	= (!empty($data['TRIAL_BAL_ID'])) ? $data['TRIAL_BAL_ID'] : null;
			$this->BRANCH_ID 		= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->AC_CODE 			= (!empty($data['AC_CODE'])) ? $data['AC_CODE'] : null;
			$this->BALANCE_DATE 	= (!empty($data['BALANCE_DATE'])) ? $data['BALANCE_DATE'] : null;
			$this->SNTB				= (!empty($data['SNTB'])) ? $data['SNTB'] : null;
			$this->SA_AMOUNT 		= (!empty($data['SA_AMOUNT'])) ? $data['SA_AMOUNT'] : null;
			$this->TBNTB 			= (!empty($data['TBNTB'])) ? $data['TBNTB'] : null;
			$this->TB_AMOUNT 		= (!empty($data['TB_AMOUNT'])) ? $data['TB_AMOUNT'] : null;
			$this->CBNTB 			= (!empty($data['CBNTB'])) ? $data['CBNTB'] : null;
			$this->CB_AMOUNT 		= (!empty($data['CB_AMOUNT'])) ? $data['CB_AMOUNT'] : null;
			$this->BUSINESS_DATE 	= (!empty($data['BUSINESS_DATE'])) ? $data['BUSINESS_DATE'] : null;
			$this->RECORD_DATE 		= (!empty($data['RECORD_DATE'])) ? $data['RECORD_DATE'] : null;
			$this->OPERATE_BY 		= (!empty($data['OPERATE_BY'])) ? $data['OPERATE_BY'] : null;
		}
	}
?>	