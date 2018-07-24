<?php
	namespace Inventory\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class ConsumptionEntry implements InputFilterAwareInterface {
		public $BRANCH_ID;
		public $CONSUMPTION_ID;
		public $CONSUMPTION_NO;
		public $CATEGORY_ID;
		public $CATEGORY;
		public $QUANTITY;
		public $RATE;
		public $TOTAL_AMOUNT;
		public $NET_AMOUNT;
		public $CONSUMPTION_STATUS;
		public $RECORD_DATE;
		public $NET_PAYMENT;
		public $CAT_PRICE_ID;
		public $COA_CODE;
		public $COA_NAME;
		public $NumberOfRows;
		
		
		public $isProduction;		
		public $P_CATEGORY;
		public $P_CATEGORY_ID;
		public $P_COA_CODE;
		public $P_COA_NAME;
		public $P_CAT_PRICE_ID;
		public $P_QUANTITY;
		public $P_RATE;
		public $P_TOTAL_AMOUNT;
		public $P_NET_PAYMENT;
		public $ManufecturedNumberOfRows;
		
		
		
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->BRANCH_ID 			= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->CONSUMPTION_ID 		= (!empty($data['CONSUMPTION_ID'])) ? $data['CONSUMPTION_ID'] : null;
			$this->CONSUMPTION_NO 		= (!empty($data['CONSUMPTION_NO'])) ? $data['CONSUMPTION_NO'] : null;
			$this->CATEGORY_ID 			= (!empty($data['CATEGORY_ID'])) ? $data['CATEGORY_ID'] : null;
			$this->CATEGORY 			= (!empty($data['CATEGORY'])) ? $data['CATEGORY'] : null;
			$this->QUANTITY 			= (!empty($data['QUANTITY'])) ? $data['QUANTITY'] : null;
			$this->RATE 				= (!empty($data['RATE'])) ? $data['RATE'] : null;		
			$this->TOTAL_AMOUNT 		= (!empty($data['TOTAL_AMOUNT'])) ? $data['TOTAL_AMOUNT'] : null;
			$this->NET_PAYMENT 			= (!empty($data['NET_PAYMENT'])) ? $data['NET_PAYMENT'] : null;
			$this->CONSUMPTION_STATUS 	= (!empty($data['CONSUMPTION_STATUS'])) ? $data['CONSUMPTION_STATUS'] : null;			
			$this->RECORD_DATE 			= (!empty($data['RECORD_DATE'])) ? $data['RECORD_DATE'] : null;	
			$this->NumberOfRows 		= (!empty($data['NumberOfRows'])) ? $data['NumberOfRows'] : null;
			$this->CAT_PRICE_ID 		= (!empty($data['CAT_PRICE_ID'])) ? $data['CAT_PRICE_ID'] : null;
			$this->COA_CODE 			= (!empty($data['COA_CODE'])) ? $data['COA_CODE'] : null;
			$this->COA_NAME 			= (!empty($data['COA_NAME'])) ? $data['COA_NAME'] : null;
			
			
			$this->P_CATEGORY_ID 		= (!empty($data['P_CATEGORY_ID'])) ? $data['P_CATEGORY_ID'] : null;
			$this->P_CATEGORY 			= (!empty($data['P_CATEGORY'])) ? $data['P_CATEGORY'] : null;
			$this->P_QUANTITY 			= (!empty($data['P_QUANTITY'])) ? $data['P_QUANTITY'] : null;
			$this->P_RATE 				= (!empty($data['P_RATE'])) ? $data['P_RATE'] : null;		
			$this->P_TOTAL_AMOUNT 		= (!empty($data['P_TOTAL_AMOUNT'])) ? $data['P_TOTAL_AMOUNT'] : null;
			$this->P_NET_PAYMENT 		= (!empty($data['P_NET_PAYMENT'])) ? $data['P_NET_PAYMENT'] : null;
			$this->P_CAT_PRICE_ID 		= (!empty($data['P_CAT_PRICE_ID'])) ? $data['P_CAT_PRICE_ID'] : null;
			$this->P_COA_CODE 			= (!empty($data['P_COA_CODE'])) ? $data['P_COA_CODE'] : null;
			$this->P_COA_NAME 			= (!empty($data['P_COA_NAME'])) ? $data['P_COA_NAME'] : null;
			
			$this->ManufecturedNumberOfRows = (!empty($data['ManufecturedNumberOfRows'])) ? $data['ManufecturedNumberOfRows'] : null;
			$this->isProduction 			= (!empty($data['isProduction'])) ? $data['isProduction'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
		
		public function setInputFilter(InputFilterInterface $inputFilter) {
			throw new \Exception("Not used");
		}
		
		public function getInputFilter() {
			if(!$this->inputFilter) {
				$inputFilter = new InputFilter();
				$factory	 = new InputFactory();
				
				$this->inputFilter = $inputFilter;
				
				return $this->inputFilter;
			}
		}
	}
?>	