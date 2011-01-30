<?php

/**
 * @author nicolaas [at] sunnysideup.co.nz
 *
 */

class FrontEndCMS extends SiteTreeDecorator {

	protected static $member_email_fieldname_array = array("Email", "AlternativeEmail");
		static function set_member_email_fieldname_array (array $var) {self::$member_email_fieldname_array = $var;}

	protected static $group_title = 'Business Members';
		static function set_group_title ($var) { self::$group_title = $var;}

	protected static $group_code = 'business-members';
		static function set_group_code ($var) { self::$group_code = $var;}

	protected static $access_code = 'ACCESS_BUSINESS';
		static function set_access_code ($var) { self::$access_code = $var;}

	protected static $other_groups_to_which_members_should_be_added = array("forum-members");
		static function set_other_groups_to_which_members_should_be_added (array $var) {self::$other_groups_to_which_members_should_be_added = $var;}

	protected static $filter_many_many_table_in_CMS = true;
		static function set_filter_many_many_table_in_CMS ($var) { self::$filter_many_many_table_in_CMS = $var;}

	protected static $editable_child_combination = array("BusinessPage" => "ProductPage");
		static function set_editable_child_combination (array $var) {self::$editable_child_combination = $var;}

	protected static $editor_details_tab_name = "Editors";
		static function set_editor_details_tab_name ($var) { self::$editor_details_tab_name = $var;}
		static function get_editor_details_tab_name ($var) { return $editor_details_tab_name;}

	private static $is_editor = array();

	/**
	 * Fields and CMS
	 */

	public function extraStatics() {
		$emailArray = array();
		if(is_array(self::$member_email_fieldname_array) && count(self::$member_email_fieldname_array)) {
			foreach(self::$member_email_fieldname_array as $email) {
				$emailArray[$email] = "Varchar(255)";
			}
			return array(
				'db' => $emailArray,
				'many_many' => array(
					'Members' => 'Member'
				)
			);
		}
		else {
			user_error('You need to specify at least one  email field in static $member_email_fieldname_array', E_USER_NOTICE);
		}
	}

	public function updateCMSFields(FieldSet &$fields) {
		// TO DO: this should not be added if the fields are front-end.
		if($this->isEditablePage()) {
			foreach(self::$member_email_fieldname_array as $field) {
				$fields->addFieldToTab("Root.Content.".self::$editor_details_tab_name, new TextField($field, $field));
			}
			if(self::$filter_many_many_table_in_CMS) {
				$list = $this->owner->Members();
				$memberIdArray = array();
				foreach($list as $item) {
					if($item instanceOf Member) {
						$memberIdArray[$item->ID] = $item->MemberID;
					}
				}
				if(count($memberIdArray)) {
					$filterString = implode(",", $memberIdArray);
					$sourceFilter = "`MemberID` IN (".$filterString.')';
				}
				else {
					$sourceFilter = "`MemberID` < 0";
				}
			}
			else {
				$sourceFilter = "";
			}
			$membersField = new ManyManyComplexTableField(
				$controller = $this->owner,
				$name = 'Members',
				$sourceClass = 'Member', //Classname
				$fieldList = array(
					'Email'=>'Email',
					'FirstName'=>'First Name',
					'Surname'=>'Surname'
				),
				'getCMSFields',
				$sourceFilter
			);
			$membersField->setPermissions(array());
			$membersField->pageSize = 500;
			$membersField->setParentClass($this->owner->class);
			$fields->addFieldToTab("Root.Content.".self::$editor_details_tab_name, new HeaderField('Please note: These members will be able to edit this page',4));
			$fields->addFieldToTab("Root.Content.".self::$editor_details_tab_name, $membersField);
		}
		return $fields; //needed??
	}

	//checks if we need to add them to CMS (i.e. do we want this page to be edited at all?
	public function isEditablePage() {
		if($this->canEdit()) {
			if(is_array(self::$editable_child_combination) && count(self::$editable_child_combination)) {
				foreach(self::$editable_child_combination as $key => $value) {
					if($key == $this->owner->class || $this->owner->class == $value) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Can functions
	 */

	//we add this here to do all the checking for canEdit in the DOD.
	public function getCanEditType() {
		return "OnlyTheseUsers";
	}

	public function canEdit($member = null) {
	/* this code maybe useful
		if(Permission::checkMember($member, "ADMIN")) return true;
		// decorated access checks
		$results = $this->extend('canEdit', $member);
		if($results && is_array($results)) if(!min($results)) return false;
		// if page can't be viewed, don't grant edit permissions
		if(!$this->canView()) return false;
		// check for empty spec
		if(!$this->CanEditType || $this->CanEditType == 'Anyone') return true;
		// check for inherit
		if($this->CanEditType == 'Inherit') {
			if($this->ParentID) return $this->Parent()->canEdit($member);
			else return Permission::checkMember($member, 'CMS_ACCESS_CMSMain');
		}
		// check for any logged-in users
		if($this->CanEditType == 'LoggedInUsers' && Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return true;
		// check for specific groups
		if($this->CanEditType == 'OnlyTheseUsers' && $member && $member->inGroups($this->EditorGroups())) return true;
		if($this->Members('MemberID = '.$member->ID)) return true;
		//Debug::message('About to fail');
		return false;
	*/

		// hack to make sure that there is no denial because some other page is called.
		/*
		if(Director::currentPage()->ID != $this->owner->ID) {
			return true;
		}
		*/
		if(isset(self::$is_editor[$this->owner->ID])) {
			return self::$is_editor[$this->owner->ID];
		}
		self::$is_editor[$this->owner->ID] = false;
		if(!$member) {$member = Member::currentUser();}
		if(!$member) {return false;}
		if($member->isAdmin()) {return true;}
		else {
			// Check for business member
			$pageMembers = $this->owner->Members();
			if($pageMembers->count()) {
				foreach($pageMembers as $pageMember) {
					if($pageMember->ID == $member->ID) {
						self::$is_editor[$this->owner->ID] = true;
					}
				}
			}
		}
		return self::$is_editor[$this->owner->ID];
	}

	public function canDelete($member = null) {
		if ($this->dependableChild()) return true;
		return false;
	}

	public function canCreate($member = null) {
		if ($this->dependableChild()) return true;
		return false;
	}

	/**
	 * Links
	 */

	public function Link($action = null) {/* this was required at some stage */
		return $this->owner->Link($action);
	}

	public function AddDependableChildLink() {
	  if($this->addibleChildClass()) {
			return $this->Link('adddependablechild');
		}
	}

	public function EditLink() {
	  if($this->canEdit()) {
			Requirements::javascript("frontendcms/javascript/greybox.js");
			Requirements::css("frontendcms/css/greybox.css");
			Requirements::javascript("frontendcms/javascript/frontendcms.js");
			return this->Link('edit');
		}
	}

	public function DeleteLink() {
	  if($this->canDelete()) {
			return this->Link('deletefromfrontend');
		}
	}

	public function BackLink() {
		return urlencode(Director::absoluteURL($this->Link(), true));
	}

	/**
	 * Status functions
	 */

	public function dependableChild() {
		$member = Member::currentUser();
		if($member) {
			if($member->isAdmin()) {
				return true;
			}
		}
		if(is_array(self::$editable_child_combination) && count(self::$editable_child_combination)) {
			foreach(self::$editable_child_combination as $parentClass => $childClass) {
				if($this->owner->class == $childClass) {
					if($this->owner->Parent()->class == $parentClass) {
						if($this->owner->Parent()->canEdit()) {
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	public function addibleChildClass() {
		if(is_array(self::$editable_child_combination) && count(self::$editable_child_combination)) {
			foreach(self::$editable_child_combination as $parentClass => $childClass) {
				if($this->owner->class == $parentClass) {
					return $childClass;
				}
			}
		}
		return false;
	}

	/**
	*	delete section
	**/

	//nothing here at present - see controller

	/**
	*	add members to pages
	**/

	function onAfterWrite() {
		$fieldArrayValues = array();
		foreach(self::$member_email_fieldname_array as $field) {
			$fieldArrayValues[] = $this->owner->$field;
		}
		//add new members
		foreach ($fieldArrayValues as $email) {
			if ($email) {
				$member = DataObject::get_one('Member', "Email = '$email'");

				if (!$member) {
					$member = new Member();
					$member->Email = $email;
					$member->FirstName = $this->owner->Title;
					$member->Surname = $this->owner->Title;
					$member->Nickname = $this->owner->Title;
					$pwd = Member::create_new_password();
					$member->Password = $pwd;
					$member->write();
				}
				// Add user as BusinessMember
				$this->owner->Members()->add($member);
				foreach(self::$other_groups_to_which_members_should_be_added as $group) {
					Group::addToGroupByName($member, $group);
				}
				Group::addToGroupByName($member, self::$group_code);
			}
		}
		// Delete old members
		$whereString = '';
		foreach(self::$member_email_fieldname_array as $key=>$field) {
			if($key) {
				$whereString .= ' AND ';
			}
			$whereString .= '`Email` != "'.$this->owner->$field.'" ';
		}
		$members = $this->owner->Members($whereString);
		foreach ($members as $member) {
			$member->delete();
		}
		if($editGroup = DataObject::get_one("Group", 'Code = "'.self::$group_code.'"')) {
			$this->owner->EditorGroups()->add($editGroup->ID);
		}
		if($AdminGroup = DataObject::get_one("Group", 'Code = "administrators"')) {
			$this->owner->EditorGroups()->add($AdminGroup->ID);
		}
		parent::onAfterWrite();
	}

	/**
	*	add editor group
	**/

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(!$editorGroup = DataObject::get_one("Group", 'Code = "'.self::$group_code.'"')) {
			$editorGroup = new Group();
			$editorGroup->Code = self::$group_code;
			$editorGroup->Title = self::$group_title;
			$editorGroup->write();
			Permission::grant( $editorGroup->ID, self::$access_code );
			Database::alteration_message(self::$group_title.' - created with access group '.self::$access_code ,"created");
		}
		else if(DB::query('SELECT * FROM Permission WHERE `GroupID` = '.$editorGroup->ID.' AND `Code` LIKE "'.self::$access_code.'"')->numRecords() == 0 ) {
			Permission::grant($editorGroup->ID, self::$access_code);
		}
	}
}

class FrontEndCMS_Controller extends Extension {

	static $allowed_actions = array(
		'edit',
		'frontendcms',
		'dosave',
		'deletefromfrontend',
		'DeleteForm',
		'adddependablechild',
	);

	protected static $requirements_to_block = array();
		static function set_requirements_to_block(array $requirementFileArray) {self::$requirements_to_block = $requirementFileArray;}
		static function add_requirement_to_block($requirementFile) {self::$requirements_to_block[] = $requirementFile;}

	protected static $field_names_to_remove = array();
		function set_field_names_to_remove (array $var) {self::$field_names_to_remove = $var;}

	static function add_field_name_to_remove($field) {
		self::$field_names_to_remove[] = $field;
	}

	protected static $field_names_to_replace = array();
		function set_field_names_to_replace ($var) {self::$field_names_to_replace = $var;}

	static function add_field_names_to_replace($oldFieldName, $newFieldTypeOrObject) {
		self::$field_names_to_replace[$oldFieldName] = $newFieldTypeOrObject;
	}

	protected static $use_cms_fields = false;
		static function set_use_cms_fields($var) {self::$use_cms_fields = $var;}

	protected $frontEndFieldSet = null;

	private $ownerRecord = null;

	/**
	 * Render actions
	 */

	public function edit() {
	  if(!$this->ownerRecord()->canEdit()) return Security::permissionFailure();
		return $this->owner->renderWith(array("EditPagePopup"));
	}

	/**
	 * assisting functions
	 */

	protected function fieldReplacer($fieldSet) {
		if(!$this->frontEndFieldSet) {
			$this->frontEndFieldSet = new FieldSet();
		}
		$i = 0;
		foreach($fieldSet as $field) {
			$i++;
			if("TabSet" == $field->class) {
				$this->frontEndFieldSet->push(new HeaderField("section".$i, "header".$field->rightTitle.$field->leftTitle.$field->title.$field->name ));
				if($field->children) {
					$this->fieldReplacer($field->children);
				}
			}
			else {
				$this->frontEndFieldSet->push($field);
			}
		}
		return $this->frontEndFieldSet;
	}

	private function ownerRecord() {
		if($this->ownerRecord) {
			return $this->ownerRecord;
		}
		else {
			$this->ownerRecord = $this->owner->data();
			return $this->ownerRecord;
		}
	}

	/**
	 * forms
	 **/

	public function frontendcms() {
		$record = $this->ownerRecord();
		if (self::$use_cms_fields) $fieldset = $record->getCMSFields();
		else $fieldset = $record->getFrontEndFields();
		// the next three lines can be removed once the editor fields are no longer added
		if(is_array(FrontEndCMS::$member_email_fieldname_array) && count(FrontEndCMS::$member_email_fieldname_array)) {
			self::add_field_names_to_remove(array(FrontEndCMS::get_editor_details_tab_name()));
		}
		foreach(self::$field_names_to_remove as $fieldName) {
			if($fieldset->dataFieldByName($fieldName)) {
				$fieldset->removeByName($fieldName, $dataFieldOnly = true);
			}
			else {
				$fieldset->removeByName($fieldName, $dataFieldOnly = false);
			}
		}
		foreach(self::$field_names_to_replace as $fieldName => $replacement) {
			if (is_string($replacement)) $newField = new $replacement($fieldName, $fieldName);
			else $newField = $replacement;
			$fieldset->replaceField($fieldName, $newField);
		}
		if (self::$use_cms_fields) {
			Requirements::css('frontendcms/css/FrontEndCMS.css');
			Requirements::css('cms/css/cms_right.css');
			Requirements::css('cms/css/typography.css');
			Requirements::css('cms/css/TinyMCEImageEnhancement.css');
		}
		if(is_array(self::$requirements_to_block) && count(self::$requirements_to_block) ) {
			foreach(self::$requirements_to_block as $file) {
				Requirements::block($file);
			}
		}
		Requirements::block("jsparty/jquery/plugins/greybox/greybox.js");
		Requirements::block("jsparty/jquery/plugins/greybox/greybox.css");
		Requirements::block("frontendcms/javascript/frontendcms.js");
		//Requirements::themedCSS('FrontEndCMS');
		$form = new Form(
			$controller = $this->owner,
			$name = "frontendcms",
			$fields = $fieldset,
			$actions = new fieldSet(new FormAction("dosave", "Save"))
		);
		$form->loadDataFrom($record);
		Requirements::css("cms/css/cms_right.css");
		return $form;
	}

	public function dosave($data, $form) {
		$record = $this->ownerRecord();
		if(!$record->canEdit()) return Security::permissionFailure();

		$form->saveInto($record);
		//$record->write();
		$record->writeToStage('Stage');$record->publish('Stage', 'Live');$record->flushCache();
		//Director::redirect($record->Link());
		return $this->owner->renderWith(
			array("EditPagePopup"),
			array('SavedSuccess'=>true)
		);
	}

	public function LostPasswordForm($number = 1) {
		$number = intval($number);
		if(is_int($number)) {
			$number--;
			if(isset( FrontEndCMS::$member_email_fieldname_array[$number])) {
				$emailFieldName = FrontEndCMS::$member_email_fieldname_array[$number];
				$email = $this->owner->$emailFieldName;
			}
			else {
				user_error('LostPasswordForm number is incorrect.', E_USER_NOTICE);
			}
		}
		else {
			$email = $number["Email"];
		}
		if($email) {
			$form = new ResetFrontEndCMSUserPasswordForm(
				$this,
				'LostPasswordForm',
				new FieldSet(
					new HiddenField('Email', _t('Member.EMAIL', 'Email'), $email),
					new HiddenField('Number', "Number", $number)
				),
				new FieldSet(
					new FormAction(
						'forgotPassword',
						'Send the password reset link to '.$email
					)
				),
				false
			);
			return $form;
		}
	}

	/**
	*	add section
	**/

	public function adddependablechild() {
		$record = $this->ownerRecord();
		if(!$record->canEdit()) {echo "can not edit parent"; return Security::permissionFailure();}
		$classToAddName = $record->addibleChildClass();
		echo $classToAddName;
		if(class_exists($classToAddName) ) {
			$childPage = new $classToAddName();
			$childPage->Title = 'New '.$classToAddName;
			$childPage->ParentID = $record->ID;
			$array = FrontEndCMS::$member_email_fieldname_array;
			if(is_array($array) && count($array)) {
				foreach($array as $field) {
					$childPage->$field = $record->$field;
				}
			}
			if (!$childPage->canCreate()) {echo "can not create child"; return Security::permissionFailure();}
			$childPage->writeToStage('Stage');
			$childPage->publish('Stage', 'Live');
			$childPage->flushCache();
			Director::redirect($childPage->Link('edit'));
		}
	}

	/**
	 * delete section
	 **/

	public function deletefromfrontend() {
		if($this->ownerRecord()->dependableChild()) {
			return array('ShowDeleteForm'=>true);
		}
		else {
			return array();
		}
	}

	public function DeleteForm () {
		$record = $this->ownerRecord();
		if($record->dependableChild()) {
			$title = $this->ownerRecord()->Title;
			$form = new Form(
				$controller = $this->owner,
				$name = "DeleteForm",
				$fields = new FieldSet(new HeaderField("Are you sure you want to delete $title?")),
				$actions = new fieldSet(new FormAction("doDelete", "Yes, Delete It"), new FormAction("cancelDelete", "No, Go back"))
			);
			return $form;
		}
		else {
			return array();
		}
	}

	public function cancelDelete() {
		$record = $this->ownerRecord();
		Director::redirect($record->Link());
	}

	public function doDelete() {
		$record = $this->ownerRecord();
		if (!$record->canDelete()) return Security::permissionFailure();

		$parent = $record->Parent();
		$id = $record->ID;

		$stageRecord = Versioned::get_one_by_stage('SiteTree', 'Stage', "SiteTree.ID = $id");
		if ($stageRecord) $stageRecord->delete();
		$liveRecord = Versioned::get_one_by_stage('SiteTree', 'Live', "SiteTree_Live.ID = $id");
		if ($liveRecord) $liveRecord->delete();
		$record->delete();

		Director::redirect($parent->Link());
	}

	public function Link() {
		$record = $this->ownerRecord();
		return $record->Link();
	}

}

