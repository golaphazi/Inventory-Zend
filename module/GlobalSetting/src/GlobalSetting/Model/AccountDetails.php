<?php
	namespace GlobalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class AccountDetails implements InputFilterAwareInterface {
		public $ACCOUNT_DETAILS_ID;
		public $ORG_BRANCH_ID;
		public $COMPANY_ID;
		public $ACCOUNT_TYPE_ID;
		public $ACCOUNT_NAME;
		public $ACCOUNT_NO;
		public $INTEREST_RATE;
		public $ACTIVE_DEACTIVE;
		public $BRANCH_NAME;
		public $COMPANY_NAME;
		public $ACCOUNT_TYPE;
		public $ACCOUNT_DETAILS_COA;
		public $RECEIVABLE_COA;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->ACCOUNT_DETAILS_ID 	= (!empty($data['ACCOUNT_DETAILS_ID'])) ? $data['ACCOUNT_DETAILS_ID'] : null;
			$this->ORG_BRANCH_ID 		= (!empty($data['ORG_BRANCH_ID'])) ? $data['ORG_BRANCH_ID'] : null;
			$this->COMPANY_ID 			= (!empty($data['COMPANY_ID'])) ? $data['COMPANY_ID'] : null;
			$this->ACCOUNT_TYPE_ID 		= (!empty($data['ACCOUNT_TYPE_ID'])) ? $data['ACCOUNT_TYPE_ID'] : null;
			$this->ACCOUNT_NAME 		= (!empty($data['ACCOUNT_NAME'])) ? $data['ACCOUNT_NAME'] : null;
			$this->ACCOUNT_NO 			= (!empty($data['ACCOUNT_NO'])) ? $data['ACCOUNT_NO'] : null;
			$this->INTEREST_RATE 		= (!empty($data['INTEREST_RATE'])) ? $data['INTEREST_RATE'] : null;
			$this->ACTIVE_DEACTIVE 		= (!empty($data['ACTIVE_DEACTIVE'])) ? $data['ACTIVE_DEACTIVE'] : null;
			$this->BRANCH_NAME 			= (!empty($data['BRANCH_NAME'])) ? $data['BRANCH_NAME'] : null;
			$this->COMPANY_NAME 		= (!empty($data['COMPANY_NAME'])) ? $data['COMPANY_NAME'] : null;
			$this->ACCOUNT_TYPE 		= (!empty($data['ACCOUNT_TYPE'])) ? $data['ACCOUNT_TYPE'] : null;
			$this->ACCOUNT_DETAILS_COA 	= (!empty($data['ACCOUNT_DETAILS_COA'])) ? $data['ACCOUNT_DETAILS_COA'] : null;
			$this->RECEIVABLE_COA 		= (!empty($data['RECEIVABLE_COA'])) ? $data['RECEIVABLE_COA'] : null;
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
					'name' 		=> 'ACCOUNT_DETAILS_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' 		=> 'ORG_BRANCH_ID',
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
					'name' 		=> 'ACCOUNT_TYPE_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' => 'ACCOUNT_NAME',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Account name can not be empty.' 
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