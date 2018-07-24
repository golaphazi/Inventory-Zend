<?php
	namespace Ibcia\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class BusinessDate implements InputFilterAwareInterface {
		public $BUSINESS_DATE;
		public $SOD_BKUP;
		public $SOD_FLAG;
		public $EOD_BKUP;
		public $EOD_FLAG;
		public $DATE_CLOSE;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->BUSINESS_DATE 	= (!empty($data["BUSINESS_DATE"])) ? $data["BUSINESS_DATE"] : null;
			$this->SOD_BKUP 		= (!empty($data["SOD_BKUP"])) ? $data["SOD_BKUP"] : null;
			$this->SOD_FLAG 		= (!empty($data["SOD_FLAG"])) ? $data["SOD_FLAG"] : null;
			$this->EOD_BKUP 		= (!empty($data["EOD_BKUP"])) ? $data["EOD_BKUP"] : null;
			$this->EOD_FLAG 		= (!empty($data["EOD_FLAG"])) ? $data["EOD_FLAG"] : null;
			$this->DATE_CLOSE 		= (!empty($data["DATE_CLOSE"])) ? $data["DATE_CLOSE"] : null;
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
				
				$inputFilter->add($factory->createInput(array(
					'name' 		=> 'BUSINESS_DATE',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'StripTags'),
						array('name' => 'StringTrim'),
					),
					'validators' => array(
						array(
						  'name' =>'NotEmpty', 
							'options' => array(
								'messages' => array(
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Please, select a business date.'
								),
							),
						),
					),
				)));
				
				$this->inputFilter = $inputFilter;
				
				return $this->inputFilter;
			}
		}
	}
?>	