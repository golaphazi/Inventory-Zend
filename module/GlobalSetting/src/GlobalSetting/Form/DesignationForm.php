<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class DesignationForm extends Form {
		protected $designationTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('designation');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationDsgForm();');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'DESIGNATION_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'DESIGNATION',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'DESIGNATION',
					'class'=>'FormTextTypeInput',
					'maxlength'=> '150',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input designation name',
					
				),
			));
			$this->add(array(
				'name' => 'DESCRIPTION',
				'type' => 'Textarea',
				'attributes' => array(
					'id' => 'DESCRIPTION',
					'class'=>'FormTextAreaTypeInput',
					'maxlength'=> '250',
					'rows' => '3',
					'cols' => '24',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'designation description here',
				),
				'options' => array(
					//'label' => 'Description : ',
				),
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style' => 'font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));
		}
	}
?>	