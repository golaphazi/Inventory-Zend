<?php
	namespace GlobalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class AccountType implements InputFilterAwareInterface {
		public $ACCOUNT_TYPE_ID;
		public $ACCOUNT_TYPE;
		public $DESCRIPTION;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->ACCOUNT_TYPE_ID 	= (!empty($data['ACCOUNT_TYPE_ID'])) ? $data['ACCOUNT_TYPE_ID'] : null;
			$this->ACCOUNT_TYPE 	= (!empty($data['ACCOUNT_TYPE'])) ? $data['ACCOUNT_TYPE'] : null;
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
					'name' 		=> 'ACCOUNT_TYPE_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' => 'ACCOUNT_TYPE',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Type can not be empty.' 
								),
							),
						),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'ACCOUNT_TYPE',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Account type can not be empty.' 
								),
							),
						),
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								//'min' => 20,
								'max' => 50,
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