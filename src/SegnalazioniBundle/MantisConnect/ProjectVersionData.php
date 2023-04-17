<?php

namespace SegnalazioniBundle\MantisConnect;

class ProjectVersionData {
  public $id; // integer
  public $name; // string
  public $project_id; // integer
  public $date_order; // dateTime
  public $description; // string
  public $released; // boolean
  public $obsolete; // boolean
}