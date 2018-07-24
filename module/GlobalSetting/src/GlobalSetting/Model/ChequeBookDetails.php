<?php
	namespace GlobalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class ChequeBookDetails implements InputFilterAwareInterface {
		public $CHEQUE_BOOK_DETAILS_ID;
		public $ACCOUNT_DETAILS_ID;
		public $CHEQUE_NO_RANGE_FROM;
		public $CHEQUE_NO_RANGE_TO;
		public $CHEQUE_NO_RANGE;
		public $ACCOUNT_NAME;
		public $ACCOUNT_NO;
		public $ACCOUNT_TYPE;
		public $BRANCH_NAME;
		public $ORG_NAME;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->CHEQUE_BOOK_DETAILS_ID	= (!empty($data['CHEQUE_BOOK_DETAILS_ID'])) ? $data['CHEQUE_BOOK_DETAILS_ID'] : null;
			$this->ACCOUNT_DETAILS_ID 		= (!empty($data['ACCOUNT_DETAILS_ID'])) ? $data['ACCOUNT_DETAILS_ID'] : null;
			$this->CHEQUE_NO_RANGE_FROM 	= (!empty($data['CHEQUE_NO_RANGE_FROM'])) ? $data['CHEQUE_NO_RANGE_FROM'] : null;
			$this->CHEQUE_NO_RANGE_TO 		= (!empty($data['CHEQUE_NO_RANGE_TO'])) ? $data['CHEQUE_NO_RANGE_TO'] : null;
			$this->CHEQUE_NO_RANGE 			= (!empty($data['CHEQUE_NO_RANGE'])) ? $data['CHEQUE_NO_RANGE'] : null;
			$this->ACCOUNT_NAME 			= (!empty($data['ACCOUNT_NAME'])) ? $data['ACCOUNT_NAME'] : null;
			$this->ACCOUNT_NO 				= (!empty($data['ACCOUNT_NO'])) ? $data['ACCOUNT_NO'] : null;
			$this->ACCOUNT_TYPE 			= (!empty($data['ACCOUNT_TYPE'])) ? $data['ACCOUNT_TYPE'] : null;
			$this->BRANCH_NAME 				= (!empty($data['BRANCH_NAME'])) ? $data['BRANCH_NAME'] : null;
			$this->ORG_NAME 				= (!empty($data['ORG_NAME'])) ? $data['ORG_NAME'] : null;
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
					'name' 		=> 'CHEQUE_BOOK_DETAILS_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' 		=> 'ACCOUNT_DETAILS_ID',
					'required'	=> true,
				)));
				
				$inputFilter->add($factory->createInput(array(
					'name' => 'CHEQUE_NO_RANGE_FROM',
					'required' => false,
					'filters' => array(
						array('name' => 'StripTags'),
						array('name' => 'StringTrim'),
					),
					'validators' => array(
						array(
						  'name' =>'NotEmpty', 
							'options' => array(
								'messages' => array(
									\Zend\Validator\NotEmpty::IS_EMPTY => 'From range can not be empty.' 
								),
							),
						),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' => 'CHEQUE_NO_RANGE_TO',
					'required' => false,
					'filters' => array(
						array('name' => 'StripTags'),
						array('name' => 'StringTrim'),
					),
					'validators' => array(
						array(
						  'name' =>'NotEmpty', 
							'options' => array(
								'messages' => array(
									\Zend\Validator\NotEmpty::IS_EMPTY => 'To range can not be empty.' 
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