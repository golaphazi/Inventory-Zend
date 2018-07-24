<?php
	namespace GlobalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class AccBudget implements InputFilterAwareInterface {
		public $BUDGET_ID;
		public $BRANCH_ID;
		public $BUDGET_ACC_NAME;
		public $BUDGET_ACC_CODE;
		public $FISCAL_YEAR;
		public $BUDGET_AMOUNT;
		public $PARENT_COA;
		public $BRANCHAMOUNT;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->BUDGET_ID 			= (!empty($data['BUDGET_ID'])) ? $data['BUDGET_ID'] : null;
			$this->BRANCH_ID 			= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->BUDGET_ACC_NAME 		= (!empty($data['BUDGET_ACC_NAME'])) ? $data['BUDGET_ACC_NAME'] : null;
			$this->BUDGET_ACC_CODE 		= (!empty($data['BUDGET_ACC_CODE'])) ? $data['BUDGET_ACC_CODE'] : null;
			$this->FISCAL_YEAR 			= (!empty($data['FISCAL_YEAR'])) ? $data['FISCAL_YEAR'] : null;
			$this->BUDGET_AMOUNT 		= (!empty($data['BUDGET_AMOUNT'])) ? $data['BUDGET_AMOUNT'] : null;
			$this->PARENT_COA 			= (!empty($data['PARENT_COA'])) ? $data['PARENT_COA'] : null;
			$this->BRANCHAMOUNT 		= (!empty($data['BRANCHAMOUNT'])) ? $data['BRANCHAMOUNT'] : null;
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
					'name' 		=> 'BUDGET_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' 		=> 'BRANCH_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'BUDGET_ACC_NAME',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Account Head can not be empty.' 
								),
							),
						),
					),
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'BUDGET_ACC_CODE',
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
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Accounts code can not be empty.' 
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
									'stringLengthTooShort' => 'Please enter code must 9 character!', 
									'stringLengthTooLong' => 'Please enter code must 9 character!',
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