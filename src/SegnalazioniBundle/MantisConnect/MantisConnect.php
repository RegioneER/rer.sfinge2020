<?php

namespace SegnalazioniBundle\MantisConnect;

class MantisConnect extends \SoapClient {

  private static $classmap = array(
    'ObjectRef' => ObjectRef::class,
    'AccountData' => AccountData::class,
    'AttachmentData' => AttachmentData::class,
    'ProjectAttachmentData' => ProjectAttachmentData::class,
    'RelationshipData' => RelationshipData::class,
    'IssueNoteData' => IssueNoteData::class,
    'IssueData' => IssueData::class,
    'IssueHeaderData' => IssueHeaderData::class,
    'ProjectData' => ProjectData::class,
    'ProjectVersionData' => ProjectVersionData::class,
    'FilterData' => FilterData::class,
    'CustomFieldDefinitionData' => CustomFieldDefinitionData::class,
    'CustomFieldLinkForProjectData' => CustomFieldLinkForProjectData::class,
    'CustomFieldValueForIssueData' => CustomFieldValueForIssueData::class,
    'TagData' => TagData::class,
    'TagDataSearchResult' => TagDataSearchResult::class,
    'ProfileData' => ProfileData::class,
    'ProfileDataSearchResult' => ProfileDataSearchResult::class,
		'FilterSearchData' => FilterSearchData::class,
);

  public function __construct($wsdl = "http://segnalazioni.formalazio.it/api/soap/mantisconnect.php?wsdl", $options = array()) {
    foreach(self::$classmap as $key => $value) {
      if(!isset($options['classmap'][$key])) {
        $options['classmap'][$key] = $value;
      }
    }
    parent::__construct($wsdl, $options);
  }

  /**
   *  
   *
   * @param  
   * @return string
   */
  public function mc_version() {
    return $this->__soapCall('mc_version', array(),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for statuses. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_status($username, $password) {
    return $this->__soapCall('mc_enum_status', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for priorities. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_priorities($username, $password) {
    return $this->__soapCall('mc_enum_priorities', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for severities. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_severities($username, $password) {
    return $this->__soapCall('mc_enum_severities', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for reproducibilities. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_reproducibilities($username, $password) {
    return $this->__soapCall('mc_enum_reproducibilities', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for projections. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_projections($username, $password) {
    return $this->__soapCall('mc_enum_projections', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for ETAs. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_etas($username, $password) {
    return $this->__soapCall('mc_enum_etas', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for resolutions. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_resolutions($username, $password) {
    return $this->__soapCall('mc_enum_resolutions', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for access levels. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_access_levels($username, $password) {
    return $this->__soapCall('mc_enum_access_levels', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for project statuses. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_project_status($username, $password) {
    return $this->__soapCall('mc_enum_project_status', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for project view states. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_project_view_states($username, $password) {
    return $this->__soapCall('mc_enum_project_view_states', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for view states. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_view_states($username, $password) {
    return $this->__soapCall('mc_enum_view_states', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for custom field types. 
   *
   * @param string $username
   * @param string $password
   * @return ObjectRefArray
   */
  public function mc_enum_custom_field_types($username, $password) {
    return $this->__soapCall('mc_enum_custom_field_types', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the enumeration for the specified enumeration type. 
   *
   * @param string $username
   * @param string $password
   * @param string $enumeration
   * @return string
   */
  public function mc_enum_get($username, $password, $enumeration) {
    return $this->__soapCall('mc_enum_get', array($username, $password, $enumeration),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Check there exists an issue with the specified id. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_id
   * @return boolean
   */
  public function mc_issue_exists($username, $password, int $issue_id) {
    return $this->__soapCall('mc_issue_exists', array($username, $password, $issue_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the issue with the specified id. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_id
   * @return IssueData
   */
  public function mc_issue_get($username, $password,  $issue_id) {
    return $this->__soapCall('mc_issue_get', array($username, $password, $issue_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the latest submitted issue in the specified project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @return integer
   */
  public function mc_issue_get_biggest_id($username, $password, int $project_id) {
    return $this->__soapCall('mc_issue_get_biggest_id', array($username, $password, $project_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the id of the issue with the specified summary. 
   *
   * @param string $username
   * @param string $password
   * @param string $summary
   * @return integer
   */
  public function mc_issue_get_id_from_summary($username, $password, $summary) {
    return $this->__soapCall('mc_issue_get_id_from_summary', array($username, $password, $summary),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Submit the specified issue details. 
   *
   * @param string $username
   * @param string $password
   * @param IssueData $issue
   * @return integer
   */
  public function mc_issue_add($username, $password, IssueData $issue) {
    return $this->__soapCall('mc_issue_add', array($username, $password, $issue),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Update Issue method. 
   *
   * @param string $username
   * @param string $password
   * @param int $issueId
   * @param IssueData $issue
   * @return boolean
   */
  public function mc_issue_update($username, $password, int $issueId, IssueData $issue) {
    return $this->__soapCall('mc_issue_update', array($username, $password, $issueId, $issue),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Sets the tags for a specified issue. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_id
   * @param TagDataArray $tags
   * @return boolean
   */
  public function mc_issue_set_tags($username, $password, int $issue_id, TagDataArray $tags) {
    return $this->__soapCall('mc_issue_set_tags', array($username, $password, $issue_id, $tags),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Delete the issue with the specified id. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_id
   * @return boolean
   */
  public function mc_issue_delete($username, $password, int $issue_id) {
    return $this->__soapCall('mc_issue_delete', array($username, $password, $issue_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Submit a new note. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_id
   * @param IssueNoteData $note
   * @return integer
   */
  public function mc_issue_note_add($username, $password, $issue_id, IssueNoteData $note) {
    return $this->__soapCall('mc_issue_note_add', array($username, $password, $issue_id, $note),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Delete the note with the specified id. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_note_id
   * @return boolean
   */
  public function mc_issue_note_delete($username, $password, int $issue_note_id) {
    return $this->__soapCall('mc_issue_note_delete', array($username, $password, $issue_note_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Update a specific note of a specific issue. 
   *
   * @param string $username
   * @param string $password
   * @param IssueNoteData $note
   * @return boolean
   */
  public function mc_issue_note_update($username, $password, IssueNoteData $note) {
    return $this->__soapCall('mc_issue_note_update', array($username, $password, $note),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Submit a new relationship. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_id
   * @param RelationshipData $relationship
   * @return integer
   */
  public function mc_issue_relationship_add($username, $password, int $issue_id, RelationshipData $relationship) {
    return $this->__soapCall('mc_issue_relationship_add', array($username, $password, $issue_id, $relationship),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Delete the relationship for the specified issue. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_id
   * @param int $relationship_id
   * @return boolean
   */
  public function mc_issue_relationship_delete($username, $password, int $issue_id, int $relationship_id) {
    return $this->__soapCall('mc_issue_relationship_delete', array($username, $password, $issue_id, $relationship_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Submit a new issue attachment. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_id
   * @param string $name
   * @param string $file_type
   * @param base64Binary $content
   * @return integer
   */
  public function mc_issue_attachment_add($username, $password, $issue_id, $name, $file_type, $content) {
    return $this->__soapCall('mc_issue_attachment_add', array($username, $password, $issue_id, $name, $file_type, $content),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Delete the issue attachment with the specified id. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_attachment_id
   * @return boolean
   */
  public function mc_issue_attachment_delete($username, $password, int $issue_attachment_id) {
    return $this->__soapCall('mc_issue_attachment_delete', array($username, $password, $issue_attachment_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the data for the specified issue attachment. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_attachment_id
   * @return base64Binary
   */
  public function mc_issue_attachment_get($username, $password, int $issue_attachment_id) {
    return $this->__soapCall('mc_issue_attachment_get', array($username, $password, $issue_attachment_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Add a new project to the tracker (must have admin privileges) 
   *
   * @param string $username
   * @param string $password
   * @param ProjectData $project
   * @return integer
   */
  public function mc_project_add($username, $password, ProjectData $project) {
    return $this->__soapCall('mc_project_add', array($username, $password, $project),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Add a new project to the tracker (must have admin privileges) 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @return boolean
   */
  public function mc_project_delete($username, $password, int $project_id) {
    return $this->__soapCall('mc_project_delete', array($username, $password, $project_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Update a specific project to the tracker (must have admin privileges) 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param ProjectData $project
   * @return boolean
   */
  public function mc_project_update($username, $password, int $project_id, ProjectData $project) {
    return $this->__soapCall('mc_project_update', array($username, $password, $project_id, $project),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the id of the project with the specified name. 
   *
   * @param string $username
   * @param string $password
   * @param string $project_name
   * @return integer
   */
  public function mc_project_get_id_from_name($username, $password, $project_name) {
    return $this->__soapCall('mc_project_get_id_from_name', array($username, $password, $project_name),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the issues that match the specified project id and paging details. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param int $page_number
   * @param int $per_page
   * @return IssueDataArray
   */
  public function mc_project_get_issues($username, $password, int $project_id, int $page_number, int $per_page) {
    return $this->__soapCall('mc_project_get_issues', array($username, $password, $project_id, $page_number, $per_page),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the issue headers that match the specified project id and paging details. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param int $page_number
   * @param int $per_page
   * @return IssueHeaderDataArray
   */
  public function mc_project_get_issue_headers($username, $password, int $project_id, int $page_number, int $per_page) {
    return $this->__soapCall('mc_project_get_issue_headers', array($username, $password, $project_id, $page_number, $per_page),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get appropriate users assigned to a project by access level. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param int $access
   * @return AccountDataArray
   */
  public function mc_project_get_users($username, $password, int $project_id, int $access) {
    return $this->__soapCall('mc_project_get_users', array($username, $password, $project_id, $access),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the list of projects that are accessible to the logged in user. 
   *
   * @param string $username
   * @param string $password
   * @return ProjectDataArray
   */
  public function mc_projects_get_user_accessible($username, $password) {
    return $this->__soapCall('mc_projects_get_user_accessible', array($username, $password),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the categories belonging to the specified project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @return StringArray
   */
  public function mc_project_get_categories($username, $password, int $project_id) {
    return $this->__soapCall('mc_project_get_categories', array($username, $password, $project_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Add a category of specific project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param string $p_category_name
   * @return integer
   */
  public function mc_project_add_category($username, $password, int $project_id, $p_category_name) {
    return $this->__soapCall('mc_project_add_category', array($username, $password, $project_id, $p_category_name),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Delete a category of specific project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param string $p_category_name
   * @return integer
   */
  public function mc_project_delete_category($username, $password, int $project_id, $p_category_name) {
    return $this->__soapCall('mc_project_delete_category', array($username, $password, $project_id, $p_category_name),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Rename a category of specific project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param string $p_category_name
   * @param string $p_category_name_new
   * @param int $p_assigned_to
   * @return integer
   */
  public function mc_project_rename_category_by_name($username, $password, int $project_id, $p_category_name, $p_category_name_new, int $p_assigned_to) {
    return $this->__soapCall('mc_project_rename_category_by_name', array($username, $password, $project_id, $p_category_name, $p_category_name_new, $p_assigned_to),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the versions belonging to the specified project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @return ProjectVersionDataArray
   */
  public function mc_project_get_versions($username, $password, int $project_id) {
    return $this->__soapCall('mc_project_get_versions', array($username, $password, $project_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Submit the specified version details. 
   *
   * @param string $username
   * @param string $password
   * @param ProjectVersionData $version
   * @return integer
   */
  public function mc_project_version_add($username, $password, ProjectVersionData $version) {
    return $this->__soapCall('mc_project_version_add', array($username, $password, $version),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Update version method. 
   *
   * @param string $username
   * @param string $password
   * @param int $version_id
   * @param ProjectVersionData $version
   * @return boolean
   */
  public function mc_project_version_update($username, $password, int $version_id, ProjectVersionData $version) {
    return $this->__soapCall('mc_project_version_update', array($username, $password, $version_id, $version),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Delete the version with the specified id. 
   *
   * @param string $username
   * @param string $password
   * @param int $version_id
   * @return boolean
   */
  public function mc_project_version_delete($username, $password, int $version_id) {
    return $this->__soapCall('mc_project_version_delete', array($username, $password, $version_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the released versions that belong to the specified project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @return ProjectVersionDataArray
   */
  public function mc_project_get_released_versions($username, $password, int $project_id) {
    return $this->__soapCall('mc_project_get_released_versions', array($username, $password, $project_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the unreleased version that belong to the specified project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @return ProjectVersionDataArray
   */
  public function mc_project_get_unreleased_versions($username, $password, int $project_id) {
    return $this->__soapCall('mc_project_get_unreleased_versions', array($username, $password, $project_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the attachments that belong to the specified project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @return ProjectAttachmentDataArray
   */
  public function mc_project_get_attachments($username, $password, int $project_id) {
    return $this->__soapCall('mc_project_get_attachments', array($username, $password, $project_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the custom fields that belong to the specified project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @return CustomFieldDefinitionDataArray
   */
  public function mc_project_get_custom_fields($username, $password, int $project_id) {
    return $this->__soapCall('mc_project_get_custom_fields', array($username, $password, $project_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the data for the specified project attachment. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_attachment_id
   * @return base64Binary
   */
  public function mc_project_attachment_get($username, $password, int $project_attachment_id) {
    return $this->__soapCall('mc_project_attachment_get', array($username, $password, $project_attachment_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Submit a new project attachment. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param string $name
   * @param string $title
   * @param string $description
   * @param string $file_type
   * @param base64Binary $content
   * @return integer
   */
  public function mc_project_attachment_add($username, $password, int $project_id, $name, $title, $description, $file_type, $content) {
    return $this->__soapCall('mc_project_attachment_add', array($username, $password, $project_id, $name, $title, $description, $file_type, $content),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Delete the project attachment with the specified id. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_attachment_id
   * @return boolean
   */
  public function mc_project_attachment_delete($username, $password, int $project_attachment_id) {
    return $this->__soapCall('mc_project_attachment_delete', array($username, $password, $project_attachment_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the subprojects ID of a specific project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @return StringArray
   */
  public function mc_project_get_all_subprojects($username, $password, int $project_id) {
    return $this->__soapCall('mc_project_get_all_subprojects', array($username, $password, $project_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the filters defined for the specified project. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @return FilterDataArray
   */
  public function mc_filter_get($username, $password, int $project_id) {
    return $this->__soapCall('mc_filter_get', array($username, $password, $project_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }
  
  public function mc_filter_search_issues($username, $password, $filter_search_data) {
	  return $this->__soapCall('mc_filter_search_issues', array($username, $password, $filter_search_data,1,1),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the issues that match the specified filter and paging details. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param int $filter_id
   * @param int $page_number
   * @param int $per_page
   * @return IssueDataArray
   */
  public function mc_filter_get_issues($username, $password, int $project_id, int $filter_id, int $page_number, int $per_page) {
    return $this->__soapCall('mc_filter_get_issues', array($username, $password, $project_id, $filter_id, $page_number, $per_page),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the issue headers that match the specified filter and paging details. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param int $filter_id
   * @param int $page_number
   * @param int $per_page
   * @return IssueHeaderDataArray
   */
  public function mc_filter_get_issue_headers($username, $password, int $project_id, int $filter_id, int $page_number, int $per_page) {
    return $this->__soapCall('mc_filter_get_issue_headers', array($username, $password, $project_id, $filter_id, $page_number, $per_page),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the value for the specified configuration variable. 
   *
   * @param string $username
   * @param string $password
   * @param string $config_var
   * @return string
   */
  public function mc_config_get_string($username, $password, $config_var) {
    return $this->__soapCall('mc_config_get_string', array($username, $password, $config_var),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Notifies MantisBT of a check-in for the issue with the specified id. 
   *
   * @param string $username
   * @param string $password
   * @param int $issue_id
   * @param string $comment
   * @param boolean $fixed
   * @return boolean
   */
  public function mc_issue_checkin($username, $password, int $issue_id, $comment, $fixed) {
    return $this->__soapCall('mc_issue_checkin', array($username, $password, $issue_id, $comment, $fixed),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get the value for the specified user preference. 
   *
   * @param string $username
   * @param string $password
   * @param int $project_id
   * @param string $pref_name
   * @return string
   */
  public function mc_user_pref_get_pref($username, $password, int $project_id, $pref_name) {
    return $this->__soapCall('mc_user_pref_get_pref', array($username, $password, $project_id, $pref_name),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Get profiles available to the current user. 
   *
   * @param string $username
   * @param string $password
   * @param int $page_number
   * @param int $per_page
   * @return ProfileDataSearchResult
   */
  public function mc_user_profiles_get_all($username, $password, int $page_number, int $per_page) {
    return $this->__soapCall('mc_user_profiles_get_all', array($username, $password, $page_number, $per_page),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Gets all the tags. 
   *
   * @param string $username
   * @param string $password
   * @param int $page_number
   * @param int $per_page
   * @return TagDataSearchResult
   */
  public function mc_tag_get_all($username, $password, int $page_number, int $per_page) {
    return $this->__soapCall('mc_tag_get_all', array($username, $password, $page_number, $per_page),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Creates a tag. 
   *
   * @param string $username
   * @param string $password
   * @param TagData $tag
   * @return integer
   */
  public function mc_tag_add($username, $password, TagData $tag) {
    return $this->__soapCall('mc_tag_add', array($username, $password, $tag),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

  /**
   * Deletes a tag. 
   *
   * @param string $username
   * @param string $password
   * @param int $tag_id
   * @return boolean
   */
  public function mc_tag_delete($username, $password, int $tag_id) {
    return $this->__soapCall('mc_tag_delete', array($username, $password, $tag_id),       array(
            'uri' => 'http://futureware.biz/mantisconnect',
            'soapaction' => ''
           )
      );
  }

}

?>
