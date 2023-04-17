<?php

namespace SegnalazioniBundle\MantisConnect;

class AttachmentData {
  public $id; // integer
  public $filename; // string
  public $size; // integer
  public $content_type; // string
  public $date_submitted; // dateTime
  public $download_url; // anyURI
  public $user_id; // integer
}