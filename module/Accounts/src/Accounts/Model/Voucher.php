<?php
	namespace Accounts\Model;
	
	class Voucher {
		public $VOUCHER_ID;
		public $BRANCH_ID;
		public $V_YEAR;
		public $V_TYPE;
		public $DEBIT_VOUCHER;
		public $CREDIT_VOUCHER;
		public $JOURNAL_VOUCHER;
		public $CONTRA_VOUCHER;

		public $TRANSACTION_DATE;
		public $PARTICULARS;
		
		public $CHEQUE_NO;
		public $CHEQUE_DATE;
		
		public $COA_CODE;
		public $VOUCHER_TYPE;
		public $PAYMENT_AMOUNT;
		public $EFFECTED_AT_BANK;	
		public $AUTO_TRANSACTION;
		public $DRAWNON;
		
		public $CB_CODE;
		
		public $INVOICE_NO;
		public $MONEY_RECEIPT_NO;
		
		public $CAP_MKT_ORDER_ID;
		
		public $REFPORTFOLIOVOUCHERNO;
		
		public $BUSINESS_DATE;
		public $RECORD_DATE;
		public $OPERATE_BY;	

		
		public function exchangeArray($data) {
			$this->VOUCHER_ID		= (!empty($data['VOUCHER_ID'])) ? $data['VOUCHER_ID'] : null;
			$this->BRANCH_ID		= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->V_YEAR 			= (!empty($data['V_YEAR'])) ? $data['V_YEAR'] : null;
			$this->V_TYPE 			= (!empty($data['V_TYPE'])) ? $data['V_TYPE'] : null;
			$this->DEBIT_VOUCHER 	= (!empty($data['DEBIT_VOUCHER'])) ? $data['DEBIT_VOUCHER'] : null;
			$this->CREDIT_VOUCHER 	= (!empty($data['CREDIT_VOUCHER'])) ? $data['CREDIT_VOUCHER'] : null;
			$this->JOURNAL_VOUCHER 	= (!empty($data['JOURNAL_VOUCHER'])) ? $data['JOURNAL_VOUCHER'] : null;
			$this->CONTRA_VOUCHER 	= (!empty($data['CONTRA_VOUCHER'])) ? $data['CONTRA_VOUCHER'] : null;

			$this->TRANSACTION_DATE 	= (!empty($data['TRANSACTION_DATE'])) ? $data['TRANSACTION_DATE'] : null;
			$this->PARTICULARS 			= (!empty($data['PARTICULARS'])) ? $data['PARTICULARS'] : null;
			
			$this->CHEQUE_NO 			= (!empty($data['CHEQUE_NO'])) ? $data['CHEQUE_NO'] : null;
			$this->CHEQUE_DATE 			= (!empty($data['CHEQUE_DATE'])) ? $data['CHEQUE_DATE'] : null;
			
			$this->COA_CODE 			= (!empty($data['COA_CODE'])) ? $data['COA_CODE'] : null;
			$this->VOUCHER_TYPE 		= (!empty($data['VOUCHER_TYPE'])) ? $data['VOUCHER_TYPE'] : null;
			$this->PAYMENT_AMOUNT 		= (!empty($data['PAYMENT_AMOUNT'])) ? $data['PAYMENT_AMOUNT'] : null;
			$this->EFFECTED_AT_BANK		= (!empty($data['EFFECTED_AT_BANK'])) ? $data['EFFECTED_AT_BANK'] : null;
			$this->AUTO_TRANSACTION		= (!empty($data['AUTO_TRANSACTION'])) ? $data['AUTO_TRANSACTION'] : null;
			$this->DRAWNON				= (!empty($data['DRAWNON'])) ? $data['DRAWNON'] : null;
			
			$this->CB_CODE				= (!empty($data['CB_CODE'])) ? $data['CB_CODE'] : null;
			$this->INVOICE_NO			= (!empty($data['INVOICE_NO'])) ? $data['INVOICE_NO'] : null;
			$this->MONEY_RECEIPT_NO		= (!empty($data['MONEY_RECEIPT_NO'])) ? $data['MONEY_RECEIPT_NO'] : null;
			
			$this->CAP_MKT_ORDER_ID		= (!empty($data['CAP_MKT_ORDER_ID'])) ? $data['CAP_MKT_ORDER_ID'] : null;
			
			$this->REFPORTFOLIOVOUCHERNO = (!empty($data['REFPORTFOLIOVOUCHERNO'])) ? $data['REFPORTFOLIOVOUCHERNO'] : null;
			
			$this->BUSINESS_DATE 	= (!empty($data['BUSINESS_DATE'])) ? $data['BUSINESS_DATE'] : null;
			$this->RECORD_DATE 		= (!empty($data['RECORD_DATE'])) ? $data['RECORD_DATE'] : null;
			$this->OPERATE_BY		= (!empty($data['OPERATE_BY'])) ? $data['OPERATE_BY'] : null;
		}

		
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>	