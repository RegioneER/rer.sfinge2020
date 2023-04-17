<?php

namespace SegnalazioniBundle\MantisConnect;

class FilterData {
  public $id; // integer
  public $owner; // AccountData
  public $project_id; // integer
  public $is_public; // boolean
  public $name; // string
  public $filter_string; // string
  public $url; // string
}