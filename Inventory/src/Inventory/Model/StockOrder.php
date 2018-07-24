<?php
	namespace Inventory\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class StockOrder implements InputFilterAwareInterface {
		public $STOCK_ORDER_ID;
		public $ORDER_NO;
		public $TOTAL_AMOUNT;
		public $DISCOUNT_AMOUNT;
		public $NET_AMOUNT;
		public $PAYMENT_AMOUNT;
		public $REMAINING_AMOUNT;
		public $COND;
		public $DISCOUNT_TYPE;
		public $ORDER_TYPE;
		public $LESS_DESCRIPTION;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->STOCK_ORDER_ID 			= (!empty($data['STOCK_ORDER_ID'])) ? $data['STOCK_ORDER_ID'] : null;
			$this->ORDER_NO 				= (!empty($data['ORDER_NO'])) ? $data['ORDER_NO'] : null;
			$this->TOTAL_AMOUNT 			= (!empty($data['TOTAL_AMOUNT'])) ? $data['TOTAL_AMOUNT'] : null;
			$this->DISCOUNT_AMOUNT 			= (!empty($data['DISCOUNT_AMOUNT'])) ? $data['DISCOUNT_AMOUNT'] : null;
			$this->NET_AMOUNT 				= (!empty($data['NET_AMOUNT'])) ? $data['NET_AMOUNT'] : null;
			$this->PAYMENT_AMOUNT 			= (!empty($data['PAYMENT_AMOUNT'])) ? $data['PAYMENT_AMOUNT'] : null;
			$this->REMAINING_AMOUNT 		= (!empty($data['REMAINING_AMOUNT'])) ? $data['REMAINING_AMOUNT'] : null;
			$this->DISCOUNT_TYPE 			= (!empty($data['DISCOUNT_TYPE'])) ? $data['DISCOUNT_TYPE'] : null;
			$this->COND 					= (!empty($data['COND'])) ? $data['COND'] : null;
			$this->ORDER_TYPE 				= (!empty($data['ORDER_TYPE'])) ? $data['ORDER_TYPE'] : null;
			$this->LESS_DESCRIPTION 		= (!empty($data['LESS_DESCRIPTION'])) ? $data['LESS_DESCRIPTION'] : null;
			
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