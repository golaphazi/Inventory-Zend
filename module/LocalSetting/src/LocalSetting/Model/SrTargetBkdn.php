<?php
	namespace LocalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class SrTargetBkdn implements InputFilterAwareInterface {
		public $SR_TARGET_ID;
		public $TARGET_FROM;
		public $TARGET_TO;
		public $TARGET_VALUE;
		public $SR_TARGET_BKDN_ID;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->SR_TARGET_ID			= (!empty($data['SR_TARGET_ID'])) ? $data['SR_TARGET_ID'] : null;
			$this->TARGET_FROM 			= (!empty($data['TARGET_FROM'])) ? $data['TARGET_FROM'] : null;
			$this->TARGET_TO 			= (!empty($data['TARGET_TO'])) ? $data['TARGET_TO'] : null;
			$this->TARGET_VALUE 		= (!empty($data['TARGET_VALUE'])) ? $data['TARGET_VALUE'] : null;
			$this->SR_TARGET_BKDN_ID	= (!empty($data['SR_TARGET_BKDN_ID'])) ? $data['SR_TARGET_BKDN_ID'] : null;
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