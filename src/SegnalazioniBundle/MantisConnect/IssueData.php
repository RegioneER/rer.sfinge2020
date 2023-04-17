<?php

namespace SegnalazioniBundle\MantisConnect;

class IssueData {
  public $id; // integer
  public $view_state; // ObjectRef
  public $last_updated; // dateTime
  public $project; // ObjectRef
  public $category; // string
  public $priority; // ObjectRef
  public $severity; // ObjectRef
  public $status; // ObjectRef
  public $reporter; // AccountData
  public $summary; // string
  public $version; // string
  public $build; // string
  public $platform; // string
  public $os; // string
  public $os_build; // string
  public $reproducibility; // ObjectRef
  public $date_submitted; // dateTime
  public $sponsorship_total; // integer
  public $handler; // AccountData
  public $projection; // ObjectRef
  public $eta; // ObjectRef
  public $resolution; // ObjectRef
  public $fixed_in_version; // string
  public $target_version; // string
  public $description; // string
  public $steps_to_reproduce; // string
  public $additional_information; // string
  public $attachments; // AttachmentDataArray
  public $relationships; // RelationshipDataArray
  public $notes; // IssueNoteDataArray
  public $custom_fields; // CustomFieldValueForIssueDataArray
  public $due_date; // dateTime
  public $monitors; // AccountDataArray
  public $sticky; // boolean
  public $tags; // ObjectRefArray
}