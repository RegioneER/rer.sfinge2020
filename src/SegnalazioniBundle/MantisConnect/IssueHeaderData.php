<?php

namespace SegnalazioniBundle\MantisConnect;

class IssueHeaderData {
  public $id; // integer
  public $view_state; // integer
  public $last_updated; // dateTime
  public $project; // integer
  public $category; // string
  public $priority; // integer
  public $severity; // integer
  public $status; // integer
  public $reporter; // integer
  public $summary; // string
  public $handler; // integer
  public $resolution; // integer
  public $attachments_count; // integer
  public $notes_count; // integer
}