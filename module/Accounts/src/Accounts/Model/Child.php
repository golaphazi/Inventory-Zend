<?php
	namespace Accounts\Model;
	
	class Child {
		public $CHILD_ID;
		public $TRAN_NO;
		public $AC_CODE;
		public $BRANCH_ID;
		public $NTR;
		public $CBJT;
		public $CB_CODE;
		public $NARRATION;
		public $AMOUNT;
		public $BUSINESS_DATE;
		public $RECORD_DATE;
		public $v_voucher_no_in_out;
		public $v_temp_voucher_no;
		public $v_temp_voucher_type;
		
		public $OPERATE_BY;
		
		public function exchangeArray($data) {
			$this->CHILD_ID 				= (!empty($data['CHILD_ID'])) ? $data['CHILD_ID'] : null;
			$this->TRAN_NO 					= (!empty($data['TRAN_NO'])) ? $data['TRAN_NO'] : null;
			$this->AC_CODE 					= (!empty($data['AC_CODE'])) ? $data['AC_CODE'] : null;
			$this->BRANCH_ID 				= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->NTR						= (!empty($data['NTR'])) ? $data['NTR'] : null;
			$this->CBJT 					= (!empty($data['CBJT'])) ? $data['CBJT'] : null;
			$this->CB_CODE 					= (!empty($data['CB_CODE'])) ? $data['CB_CODE'] : null;
			$this->NARRATION 				= (!empty($data['NARRATION'])) ? $data['NARRATION'] : null;
			$this->AMOUNT 					= (!empty($data['AMOUNT'])) ? $data['AMOUNT'] : null;
			$this->BUSINESS_DATE 			= (!empty($data['BUSINESS_DATE'])) ? $data['BUSINESS_DATE'] : null;
			$this->RECORD_DATE 				= (!empty($data['RECORD_DATE'])) ? $data['RECORD_DATE'] : null;
			$this->v_voucher_no_in_out 		= (!empty($data['v_voucher_no_in_out'])) ? $data['v_voucher_no_in_out'] : null;
			$this->v_temp_voucher_no 		= (!empty($data['v_temp_voucher_no'])) ? $data['v_temp_voucher_no'] : null;
			$this->v_temp_voucher_type 		= (!empty($data['v_temp_voucher_type'])) ? $data['v_temp_voucher_type'] : null;
			$this->OPERATE_BY 				= (!empty($data['OPERATE_BY'])) ? $data['OPERATE_BY'] : null;
		}
	}
?>	