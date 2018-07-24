<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\InputFilter;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class OrganizationTypeForm extends Form {
		protected $organizationTypeTable;
		protected $dbAdapter;
		protected $inputFilter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('organizationType');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationOrganizationTypeForm();');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'ORG_TYPE_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'ORG_TYPE',
				'type' => 'Text',
				'attributes' => array(					
					'id' => 'ORG_TYPE',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input organization type here',
				),
			));
			$this->add(array(
				'name' => 'DESCRIPTION',
				'type' => 'textarea',
				'attributes' => array(					
					'id' => 'DESCRIPTION',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input organization type description here if available'
				),
				
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style'=> "font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
			));
			$this->setInputFilter($this->createInputFilter());
		}
		
		 public function createInputFilter()
		{
			$inputFilter = new InputFilter\InputFilter();
	 
			//orgtype
			$orgtype = new InputFilter\Input('ORG_TYPE');
			$orgtype->setRequired(true);
			$inputFilter->add($orgtype);
	 
			return $inputFilter;
		}
	}
?>	