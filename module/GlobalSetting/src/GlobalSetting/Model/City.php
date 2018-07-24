<?php
	namespace GlobalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class City implements InputFilterAwareInterface {
		public $CITY_ID;
		public $CITY;
		public $COUNTRY_ID;
		public $COUNTRY;		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->CITY_ID		= (!empty($data['CITY_ID'])) ? $data['CITY_ID'] : null;
			$this->CITY 		= (!empty($data['CITY'])) ? $data['CITY'] : null;
			$this->COUNTRY_ID 	= (!empty($data['COUNTRY_ID'])) ? $data['COUNTRY_ID'] : null;
			$this->COUNTRY 		= (!empty($data['COUNTRY'])) ? $data['COUNTRY'] : null;
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
					'name' 		=> 'CITY_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'CITY',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'City name can not be empty.' 
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