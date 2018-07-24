<?php
	namespace LocalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class CategoryPrice implements InputFilterAwareInterface {
		public $CATEGORY_ID;
		public $CAT_PRICE;
		public $BUY_PRICE;
		public $SALE_PRICE;
		public $CAT_PRICE_ID;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->CATEGORY_ID 		= (!empty($data['CATEGORY_ID'])) ? $data['CATEGORY_ID'] : null;
			$this->CAT_PRICE 		= (!empty($data['CAT_PRICE'])) ? $data['CAT_PRICE'] : null;
			$this->BUY_PRICE 		= (!empty($data['BUY_PRICE'])) ? $data['BUY_PRICE'] : null;
			$this->SALE_PRICE 		= (!empty($data['SALE_PRICE'])) ? $data['SALE_PRICE'] : null;
			$this->CAT_PRICE_ID 	= (!empty($data['CAT_PRICE_ID'])) ? $data['CAT_PRICE_ID'] : null;
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