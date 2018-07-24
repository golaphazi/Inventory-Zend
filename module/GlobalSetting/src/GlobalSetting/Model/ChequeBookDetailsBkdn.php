<?php
	namespace GlobalSetting\Model;
	
	class ChequeBookDetailsBkdn {
		public $CHEQUE_BOOK_DETAILS_BKDN_ID;
		public $CHEQUE_BOOK_DETAILS_ID;
		public $CHEQUE_NO;
		public $STATUS;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->CHEQUE_BOOK_DETAILS_BKDN_ID 	= (!empty($data['CHEQUE_BOOK_DETAILS_BKDN_ID'])) ? $data['CHEQUE_BOOK_DETAILS_BKDN_ID'] : null;
			$this->CHEQUE_BOOK_DETAILS_ID 		= (!empty($data['CHEQUE_BOOK_DETAILS_ID'])) ? $data['CHEQUE_BOOK_DETAILS_ID'] : null;
			$this->CHEQUE_NO 					= (!empty($data['CHEQUE_NO'])) ? $data['CHEQUE_NO'] : null;
			$this->STATUS 						= (!empty($data['STATUS'])) ? $data['STATUS'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>	