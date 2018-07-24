<?php
	namespace Ibcia\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class Holiday implements InputFilterAwareInterface {
		public $HOLIDAY_ID;
		public $HOLIDAY_DATE;
		public $HOLIDAY_TYPE;
		public $HOLIDAY_DESCRIPTION;
		public $JSHD;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->HOLIDAY_ID 			= (!empty($data["HOLIDAY_ID"])) ? $data["HOLIDAY_ID"] : null;
			$this->HOLIDAY_DATE 		= (!empty($data["HOLIDAY_DATE"])) ? $data["HOLIDAY_DATE"] : null;
			$this->JSHD 				= (!empty($data["JSHD"])) ? $data["JSHD"] : null;
			$this->HOLIDAY_TYPE 		= (!empty($data["HOLIDAY_TYPE"])) ? $data["HOLIDAY_TYPE"] : null;
			$this->HOLIDAY_DESCRIPTION 	= (!empty($data["HOLIDAY_DESCRIPTION"])) ? $data["HOLIDAY_DESCRIPTION"] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
		
		public function setInputFilter(InputFilterInterface $inputFilter) {
			throw new \Exception("Not used");
		}
		
		public function getInputFilter() {
			/*if(!$this->inputFilter) {
				$inputFilter = new InputFilter();
				$factory	 = new InputFactory();
				
				$inputFilter->add($factory->createInput(array(
					'name' 		=> 'HOLIDAY_DATE',
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
			}*/
			$this->inputFilter = new InputFilter();
			
			return $this->inputFilter;
		}
	}
?>	