<?php
	namespace GlobalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class Coa implements InputFilterAwareInterface {
		public $COA_ID;
		public $COMPANY_ID;
		public $COA_NAME;
		public $LFT;
		public $RGT;
		public $COA_CODE;
		public $AUTO_COA;
		public $CASH_FLOW_HEAD;
		public $COMPANY_NAME;
		public $PARENT_COA;
		public $MOTHER_ACCOUNT;
		public $MOTHER_CATEGORY_ID;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->COA_ID 				= (!empty($data['COA_ID'])) ? $data['COA_ID'] : null;
			$this->COMPANY_ID 			= (!empty($data['COMPANY_ID'])) ? $data['COMPANY_ID'] : null;
			$this->COA_NAME 			= (!empty($data['COA_NAME'])) ? $data['COA_NAME'] : null;
			$this->LFT 					= (!empty($data['LFT'])) ? $data['LFT'] : null;
			$this->RGT 					= (!empty($data['RGT'])) ? $data['RGT'] : null;
			$this->COA_CODE 			= (!empty($data['COA_CODE'])) ? $data['COA_CODE'] : null;
			$this->AUTO_COA 			= (!empty($data['AUTO_COA'])) ? $data['AUTO_COA'] : null;
			$this->CASH_FLOW_HEAD 		= (!empty($data['CASH_FLOW_HEAD'])) ? $data['CASH_FLOW_HEAD'] : null;
			$this->COMPANY_NAME 		= (!empty($data['COMPANY_NAME'])) ? $data['COMPANY_NAME'] : null;
			$this->PARENT_COA 			= (!empty($data['PARENT_COA'])) ? $data['PARENT_COA'] : null;
			$this->MOTHER_ACCOUNT 		= (!empty($data['MOTHER_ACCOUNT'])) ? $data['MOTHER_ACCOUNT'] : null;
			$this->MOTHER_CATEGORY_ID 	= (!empty($data['MOTHER_CATEGORY_ID'])) ? $data['MOTHER_CATEGORY_ID'] : null;
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
					'name' 		=> 'COA_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' 		=> 'COMPANY_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' 		=> 'PARENT_COA',
					'required'	=> true,
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'COA_NAME',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'COA name can not be empty.' 
								),
							),
						),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'COA_CODE',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'COA code can not be empty.' 
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
				)));
				
				$this->inputFilter = $inputFilter;
				
				return $this->inputFilter;
			}
		}
	}
?>	