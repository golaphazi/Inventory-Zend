<?php
	namespace LocalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class SrRetailerMap implements InputFilterAwareInterface {
		public $SR_RETAILER_MAP_ID;
		public $RETAILER_ID;
		public $EMPLOYEE_ID;
		public $START_DATE;
		public $END_DATE;
		public $BUSINESS_DATE;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->SR_RETAILER_MAP_ID	= (!empty($data['SR_RETAILER_MAP_ID'])) ? $data['SR_RETAILER_MAP_ID'] : null;
			$this->RETAILER_ID 			= (!empty($data['RETAILER_ID'])) ? $data['RETAILER_ID'] : null;
			$this->EMPLOYEE_ID 		= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->START_DATE 		= (!empty($data['START_DATE'])) ? $data['START_DATE'] : null;
			$this->END_DATE 		= (!empty($data['END_DATE'])) ? $data['END_DATE'] : null;
			$this->BUSINESS_DATE	= (!empty($data['BUSINESS_DATE'])) ? $data['BUSINESS_DATE'] : null;
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
					'name' 		=> 'SR_RETAILER_MAP_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' => 'RETAILER_ID',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Zone Name can not be empty!' 
								),
							),
						),
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 150,
								'messages' => array(
									'stringLengthTooShort' => 'Please enter type!', 
									'stringLengthTooLong' => 'Please enter type!',
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