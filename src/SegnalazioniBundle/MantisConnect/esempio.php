<?php

require_once "MantisConnect.php";

$Mantis = new MantisConnect();

$ViewState = new ObjectRef();
$ViewState->id = 50;
$ViewState->name = "private";

$IssueNoteData = new IssueNoteData();
$IssueNoteData->reporter = "Fintemista";
$IssueNoteData->text = $Trace;
$IssueNoteData->view_state = $ViewState;

$Nota = (int)$_REQUEST["Mantis"];

$Mantis->mc_issue_note_add("Fintemista", "Fintemista", $Nota, $IssueNoteData);


