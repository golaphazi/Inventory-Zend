<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class RelationForm extends Form {
		protected $relationTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('relation');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationRelForm();');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'RELATION_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'RELATION',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'RELATION',
					'class'=>'FormTextTypeInput',
					'maxlength'=> '150',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'relationship name here',
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
					'placeholder' => 'relation description here',
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