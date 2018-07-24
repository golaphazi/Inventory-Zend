<?php
	namespace GlobalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class MoneyMarketOrganization implements InputFilterAwareInterface {
		public $ORG_ID;
		public $ORG_TYPE_ID;
		public $ORG_NAME;
		public $ORG_ADDRESS;
		public $ORG_PHONE;
		public $ORG_FAX;
		public $ORG_EMAIL;
		public $ORG_WEB;
		public $ACTIVE_DEACTIVE;
		public $ORG_TYPE;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->ORG_ID 			= (!empty($data['ORG_ID'])) ? $data['ORG_ID'] : null;
			$this->ORG_TYPE_ID 		= (!empty($data['ORG_TYPE_ID'])) ? $data['ORG_TYPE_ID'] : null;
			$this->ORG_NAME 		= (!empty($data['ORG_NAME'])) ? $data['ORG_NAME'] : null;
			$this->ORG_ADDRESS 		= (!empty($data['ORG_ADDRESS'])) ? $data['ORG_ADDRESS'] : null;
			$this->ORG_PHONE 		= (!empty($data['ORG_PHONE'])) ? $data['ORG_PHONE'] : null;
			$this->ORG_FAX 			= (!empty($data['ORG_FAX'])) ? $data['ORG_FAX'] : null;
			$this->ORG_EMAIL 		= (!empty($data['ORG_EMAIL'])) ? $data['ORG_EMAIL'] : null;
			$this->ORG_WEB 			= (!empty($data['ORG_WEB'])) ? $data['ORG_WEB'] : null;
			$this->ACTIVE_DEACTIVE 	= (!empty($data['ACTIVE_DEACTIVE'])) ? $data['ACTIVE_DEACTIVE'] : null;
			$this->ORG_TYPE 		= (!empty($data['ORG_TYPE'])) ? $data['ORG_TYPE'] : null;
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
				
				/*$inputFilter->add($factory->createInput(array(
					'name' 		=> 'ORG_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));*/
				/*$inputFilter->add($factory->createInput(array(
					'name' 		=> 'ORG_TYPE_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'ORG_NAME',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Organization name can not be empty.' 
								),
							),
						),
					),
				)));*/
				
				/*$inputFilter->add($factory->createInput(array(
					'name' => 'P_COA_CODE',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Portfolio COA code can not be empty.' 
								),
							),
						),
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'min' => 9,
								'max' => 9,
								'messages' => array(
									'stringLengthTooShort' => 'Please enter company code must 9 character!', 
									'stringLengthTooLong' => 'Please enter company code must 9 character!',
								),
							),
						),
					),
				)));*/
				
				$this->inputFilter = $inputFilter;
				
				return $this->inputFilter;
			}
		}
	}
?>	