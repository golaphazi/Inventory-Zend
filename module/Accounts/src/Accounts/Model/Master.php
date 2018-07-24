<?php
	namespace Accounts\Model;
	
	class Master {
		public $MASTER_ID;
		public $TRAN_NO;
		public $TRAN_DATE;
		public $VOUCHER_NO;
		public $NTR;
		public $CBJT;
		public $CB_CODE;
		public $CHEQUE_NO;
		public $CHEQUE_DATE;
		public $RECONCILIATION_FLAG;
		public $EFFECTED_DATE;
		public $DRAWN_ON;
		public $AUTO_TRANSACTION_FLAG;
		
		public $INVOICE_NO;
		public $MONEY_RECEIPT_NO;
		public $BACK_DATE;
		
		public $BUSINESS_DATE;
		public $RECORD_DATE;
		public $OPERATE_BY;
		
		public function exchangeArray($data) {
			$this->MASTER_ID 				= (!empty($data['MASTER_ID'])) ? $data['MASTER_ID'] : null;
			$this->TRAN_NO 					= (!empty($data['TRAN_NO'])) ? $data['TRAN_NO'] : null;
			$this->TRAN_DATE 				= (!empty($data['TRAN_DATE'])) ? $data['TRAN_DATE'] : null;
			$this->VOUCHER_NO 				= (!empty($data['VOUCHER_NO'])) ? $data['VOUCHER_NO'] : null;
			$this->NTR						= (!empty($data['NTR'])) ? $data['NTR'] : null;
			$this->CBJT 					= (!empty($data['CBJT'])) ? $data['CBJT'] : null;
			$this->CB_CODE 					= (!empty($data['CB_CODE'])) ? $data['CB_CODE'] : null;
			$this->CHEQUE_NO 				= (!empty($data['CHEQUE_NO'])) ? $data['CHEQUE_NO'] : null;
			$this->CHEQUE_DATE 				= (!empty($data['CHEQUE_DATE'])) ? $data['CHEQUE_DATE'] : null;
			$this->RECONCILIATION_FLAG 		= (!empty($data['RECONCILIATION_FLAG'])) ? $data['RECONCILIATION_FLAG'] : null;
			$this->EFFECTED_DATE 			= (!empty($data['EFFECTED_DATE'])) ? $data['EFFECTED_DATE'] : null;
			$this->DRAWN_ON 				= (!empty($data['DRAWN_ON'])) ? $data['DRAWN_ON'] : null;
			$this->AUTO_TRANSACTION_FLAG 	= (!empty($data['AUTO_TRANSACTION_FLAG'])) ? $data['AUTO_TRANSACTION_FLAG'] : null;
			
			$this->INVOICE_NO 				= (!empty($data['INVOICE_NO'])) ? $data['INVOICE_NO'] : null;
			$this->MONEY_RECEIPT_NO 		= (!empty($data['MONEY_RECEIPT_NO'])) ? $data['MONEY_RECEIPT_NO'] : null;
			$this->BACK_DATE 				= (!empty($data['BACK_DATE'])) ? $data['BACK_DATE'] : null;
			
			$this->BUSINESS_DATE 			= (!empty($data['BUSINESS_DATE'])) ? $data['BUSINESS_DATE'] : null;
			$this->RECORD_DATE 				= (!empty($data['RECORD_DATE'])) ? $data['RECORD_DATE'] : null;
			$this->OPERATE_BY 				= (!empty($data['OPERATE_BY'])) ? $data['OPERATE_BY'] : null;
		}
	}
?>	