<?php

namespace SegnalazioniBundle\MantisConnect;

class CustomFieldDefinitionData {
  public $field; // ObjectRef
  public $type; // integer
  public $possible_values; // string
  public $default_value; // string
  public $valid_regexp; // string
  public $access_level_r; // integer
  public $access_level_rw; // integer
  public $length_min; // integer
  public $length_max; // integer
  public $advanced; // boolean
  public $display_report; // boolean
  public $display_update; // boolean
  public $display_resolved; // boolean
  public $display_closed; // boolean
  public $require_report; // boolean
  public $require_update; // boolean
  public $require_resolved; // boolean
  public $require_closed; // boolean
}