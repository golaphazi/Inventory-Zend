<?php
	namespace Inventory\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class SRStockOrder implements InputFilterAwareInterface {
		public $SR_STOCK_DIST_ID;
		public $ORDER_NO;
		public $EMPLOYEE_ID;
		public $SR_TOTAL_AMOUNT;
		public $SR_DISCOUNT_AMOUNT;
		public $COND;
		public $DISCOUNT_TYPE;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->SR_STOCK_DIST_ID 			= (!empty($data['SR_STOCK_DIST_ID'])) ? $data['SR_STOCK_DIST_ID'] : null;
			$this->ORDER_NO 					= (!empty($data['ORDER_NO'])) ? $data['ORDER_NO'] : null;
			$this->EMPLOYEE_ID 					= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->DISCOUNT_TYPE 				= (!empty($data['DISCOUNT_TYPE'])) ? $data['DISCOUNT_TYPE'] : null;
			$this->SR_TOTAL_AMOUNT 				= (!empty($data['SR_TOTAL_AMOUNT'])) ? $data['SR_TOTAL_AMOUNT'] : null;
			$this->SR_DISCOUNT_AMOUNT 			= (!empty($data['SR_DISCOUNT_AMOUNT'])) ? $data['SR_DISCOUNT_AMOUNT'] : null;
			$this->COND 						= (!empty($data['COND'])) ? $data['COND'] : null;
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