<?php

namespace SegnalazioniBundle\MantisConnect;

class ProjectData {
  public $id; // integer
  public $name; // string
  public $status; // ObjectRef
  public $enabled; // boolean
  public $view_state; // ObjectRef
  public $access_min; // ObjectRef
  public $file_path; // string
  public $description; // string
  public $subprojects; // ProjectDataArray
  public $inherit_global; // boolean
}