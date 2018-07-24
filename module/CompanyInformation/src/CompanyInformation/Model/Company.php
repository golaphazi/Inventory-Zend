<?php
	namespace CompanyInformation\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class Company implements InputFilterAwareInterface {
		public $COMPANY_ID;
		public $COMPANY_NAME;
		public $COMPANY_CODE;
		public $ADDRESS;
		public $PHONE;
		public $FAX;
		public $EMAIL;
		public $WEB;
		public $ACTIVE_DEACTIVE;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->COMPANY_ID 		= (!empty($data['COMPANY_ID'])) ? $data['COMPANY_ID'] : null;
			$this->COMPANY_NAME 	= (!empty($data['COMPANY_NAME'])) ? $data['COMPANY_NAME'] : null;
			$this->COMPANY_CODE 	= (!empty($data['COMPANY_CODE'])) ? $data['COMPANY_CODE'] : null;
			$this->ADDRESS 			= (!empty($data['ADDRESS'])) ? $data['ADDRESS'] : null;
			$this->PHONE 			= (!empty($data['PHONE'])) ? $data['PHONE'] : null;
			$this->FAX 				= (!empty($data['FAX'])) ? $data['FAX'] : null;
			$this->EMAIL 			= (!empty($data['EMAIL'])) ? $data['EMAIL'] : null;
			$this->WEB 				= (!empty($data['WEB'])) ? $data['WEB'] : null;
			$this->ACTIVE_DEACTIVE 	= (!empty($data['ACTIVE_DEACTIVE'])) ? $data['ACTIVE_DEACTIVE'] : null;
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
					'name' 		=> 'COMPANY_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'COMPANY_NAME',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Company name can not be empty.' 
								),
							),
						),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'COMPANY_CODE',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Company code can not be empty.' 
								),
							),
						),
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'min' => 3,
								'max' => 3,
								'messages' => array(
									'stringLengthTooShort' => 'Please enter company code must 3 character!', 
									'stringLengthTooLong' => 'Please enter company code must 3 character!',
								),
							),
						),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'ADDRESS',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Company address can not be empty.' 
								),
							),
						),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'PHONE',
					'required' => false,
					'filters' => array(
						array('name' => 'StripTags'),
						array('name' => 'StringTrim'),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'FAX',
					'required' => false,
					'filters' => array(
						array('name' => 'StripTags'),
						array('name' => 'StringTrim'),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'EMAIL',
					'required' => false,
					'filters' => array(
						array('name' => 'StripTags'),
						array('name' => 'StringTrim'),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'WEB',
					'required' => false,
					'filters' => array(
						array('name' => 'StripTags'),
						array('name' => 'StringTrim'),
					),
				)));
				
				$this->inputFilter = $inputFilter;
				
				return $this->inputFilter;
			}
		}
	}
?>	