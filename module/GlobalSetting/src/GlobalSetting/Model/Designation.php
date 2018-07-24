<?php
	namespace GlobalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class Designation implements InputFilterAwareInterface {
		public $DESIGNATION_ID;
		public $DESIGNATION;
		public $DESCRIPTION;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->DESIGNATION_ID 	= (!empty($data['DESIGNATION_ID'])) ? $data['DESIGNATION_ID'] : null;
			$this->DESIGNATION 		= (!empty($data['DESIGNATION'])) ? $data['DESIGNATION'] : null;
			$this->DESCRIPTION 		= (!empty($data['DESCRIPTION'])) ? $data['DESCRIPTION'] : null;
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
					'name' 		=> 'DESIGNATION_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' => 'DESIGNATION',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Designation can not be empty.' 
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