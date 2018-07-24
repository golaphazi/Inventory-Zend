<?php
	namespace CompanyInformation\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class Branch implements InputFilterAwareInterface {
		public $BRANCH_ID;
		public $BRANCH_NAME;
		public $COMPANY_ID;
		public $COMPANY_NAME;
		public $BRANCH_CODE;
		public $ADDRESS;
		public $PHONE;
		public $FAX;
		public $EMAIL;
		public $WEB;
		public $ACTIVE_DEACTIVE;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->BRANCH_ID 		= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->BRANCH_NAME 		= (!empty($data['BRANCH_NAME'])) ? $data['BRANCH_NAME'] : null;
			$this->COMPANY_ID 		= (!empty($data['COMPANY_ID'])) ? $data['COMPANY_ID'] : null;
			$this->COMPANY_NAME 	= (!empty($data['COMPANY_NAME'])) ? $data['COMPANY_NAME'] : null;
			$this->BRANCH_CODE 		= (!empty($data['BRANCH_CODE'])) ? $data['BRANCH_CODE'] : null;
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
					'name' 		=> 'BRANCH_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'BRANCH_NAME',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Branch name can not be empty.' 
								),
							),
						),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'BRANCH_CODE',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Branch code can not be empty.' 
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
									'stringLengthTooShort' => 'Please enter branch code must 3 character!', 
									'stringLengthTooLong' => 'Please enter branch code must 3 character!',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Branch address can not be empty.' 
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