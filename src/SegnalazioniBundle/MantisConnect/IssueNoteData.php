<?php

namespace SegnalazioniBundle\MantisConnect;

class IssueNoteData {
  public $id; // integer
  public $reporter; // AccountData
  public $text; // string
  public $view_state; // ObjectRef
  public $date_submitted; // dateTime
  public $last_modified; // dateTime
  public $time_tracking; // integer
  public $note_type; // integer
  public $note_attr; // string
}