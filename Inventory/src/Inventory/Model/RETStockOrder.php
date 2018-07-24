<?php
	namespace Inventory\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class RETStockOrder implements InputFilterAwareInterface {
		public $RET_STOCK_DIST_ID;
		public $ORDER_NO;
		public $RETAILER_ID;
		public $EMPLOYEE_ID;
		public $RET_TOTAL_AMOUNT;
		public $RET_DISCOUNT_AMOUNT;
		public $COND;
		public $DISCOUNT_TYPE;
		public $RET_TOT_DIS_RECEIVE;
		public $RET_LESS_DESCRIPTION;
				
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->RET_STOCK_DIST_ID 			= (!empty($data['RET_STOCK_DIST_ID'])) ? $data['RET_STOCK_DIST_ID'] : null;
			$this->ORDER_NO 					= (!empty($data['ORDER_NO'])) ? $data['ORDER_NO'] : null;
			$this->RETAILER_ID 					= (!empty($data['RETAILER_ID'])) ? $data['RETAILER_ID'] : null;
			$this->EMPLOYEE_ID 					= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;			
			$this->RET_TOTAL_AMOUNT 			= (!empty($data['RET_TOTAL_AMOUNT'])) ? $data['RET_TOTAL_AMOUNT'] : null;
			$this->RET_DISCOUNT_AMOUNT 			= (!empty($data['RET_DISCOUNT_AMOUNT'])) ? $data['RET_DISCOUNT_AMOUNT'] : null;
			$this->DISCOUNT_TYPE 				= (!empty($data['DISCOUNT_TYPE'])) ? $data['DISCOUNT_TYPE'] : null;
			$this->COND 						= (!empty($data['COND'])) ? $data['COND'] : null;
			$this->RET_TOT_DIS_RECEIVE 			= (!empty($data['RET_TOT_DIS_RECEIVE'])) ? $data['RET_TOT_DIS_RECEIVE'] : null;
			$this->RET_LESS_DESCRIPTION 		= (!empty($data['RET_LESS_DESCRIPTION'])) ? $data['RET_LESS_DESCRIPTION'] : null;
			
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