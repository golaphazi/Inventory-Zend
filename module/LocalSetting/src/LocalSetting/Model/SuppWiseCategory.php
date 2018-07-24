<?php
	namespace LocalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class SuppWiseCategory implements InputFilterAwareInterface {
		public $SUPP_WISE_CATEGORY_ID;
		public $CATEGORY_ID;
		public $SUPPLIER_INFO_ID;
		public $NAME;
		public $IS_SUPPLY;
		public $START_DATE;
		public $END_DATE;
		public $CATEGORY_NAME;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->CATEGORY_ID 			= (!empty($data['CATEGORY_ID'])) ? $data['CATEGORY_ID'] : null;
			$this->SUPP_WISE_CATEGORY_ID = (!empty($data['SUPP_WISE_CATEGORY_ID'])) ? $data['SUPP_WISE_CATEGORY_ID'] : null;
			$this->CATEGORY_NAME 		= (!empty($data['CATEGORY_NAME'])) ? $data['CATEGORY_NAME'] : null;
			$this->SUPPLIER_INFO_ID 	= (!empty($data['SUPPLIER_INFO_ID'])) ? $data['SUPPLIER_INFO_ID'] : null;
			$this->IS_SUPPLY 			= (!empty($data['IS_SUPPLY'])) ? $data['IS_SUPPLY'] : null;
			$this->START_DATE 			= (!empty($data['START_DATE'])) ? $data['START_DATE'] : null;
			$this->END_DATE 			= (!empty($data['END_DATE'])) ? $data['END_DATE'] : null;
			$this->NAME 				= (!empty($data['NAME'])) ? $data['NAME'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
		
		public function setInputFilter(InputFilterInterface $inputFilter) {
			throw new \Exception("Not used");
		}
		
		public function getInputFilter() {
			//echo 'hiii';die();
			if(!$this->inputFilter) {
				$inputFilter = new InputFilter();
				$factory	 = new InputFactory();
				
				$this->inputFilter = $inputFilter;
				return $this->inputFilter;
			}
		}
	}
?>	