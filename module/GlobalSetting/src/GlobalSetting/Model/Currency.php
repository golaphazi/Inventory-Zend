<?php
	namespace GlobalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class Currency implements InputFilterAwareInterface {
		public $CURRENCY_ID;
		public $CURRENCY_NAME;
		public $ACTIVE_STATUS;		
		public $CREATE_DATE;
		public $LAST_UPDATE;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->CURRENCY_ID 	= (!empty($data['CURRENCY_ID'])) ? $data['CURRENCY_ID'] : null;
			$this->CURRENCY_NAME 	= (!empty($data['CURRENCY_NAME'])) ? $data['CURRENCY_NAME'] : null;
			$this->ACTIVE_STATUS 	= (!empty($data['ACTIVE_STATUS'])) ? $data['ACTIVE_STATUS'] : null;			
			$this->CREATE_DATE 		= (!empty($data['CREATE_DATE'])) ? $data['CREATE_DATE'] : null;
			$this->LAST_UPDATE 		= (!empty($data['LAST_UPDATE'])) ? $data['LAST_UPDATE'] : null;
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
					'name' 		=> 'CURRENCY_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' => 'CURRENCY_NAME',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Currency can not be empty.' 
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