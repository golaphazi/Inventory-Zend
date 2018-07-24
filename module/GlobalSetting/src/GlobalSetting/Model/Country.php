<?php
	namespace GlobalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class Country implements InputFilterAwareInterface {
		public $COUNTRY_ID;
		public $COUNTRY;
		public $SHORT_NAME;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->COUNTRY_ID 	= (!empty($data['COUNTRY_ID'])) ? $data['COUNTRY_ID'] : null;
			$this->COUNTRY 		= (!empty($data['COUNTRY'])) ? $data['COUNTRY'] : null;
			$this->SHORT_NAME 		= (!empty($data['SHORT_NAME'])) ? $data['SHORT_NAME'] : null;
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
					'name' 		=> 'COUNTRY_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' => 'COUNTRY',
					'required' => true,
					'filters' => array(
						array('name' => 'StripTags'),
						array('name' => 'StringTrim'),
					),
					'validators' => array(
						array(
						  'name' =>'NotEmpty', 
							'options' => array(
								'messages' => array(
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Country can not be empty.' 
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