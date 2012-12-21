<?php

require("config.php");
require_once(WWW_DIR."/lib/postprocess.php");

$postprocess = new PostProcess();
$postprocess->processNfos();
$postprocess->processMusic();
$postprocess->processBooks();
$postprocess->processGames();
$postprocess->processTv();
$postprocess->processAdditional();
$postprocess->processMusicFromMediaInfo();

//$postprocess = new PostProcess(true);
//$postprocess->processMovies();
//$thpostproce->processMusic();
//$postprocess->processBooks();
//$thpostprocs->processGames();
//$thpostproces>processTv();
//$thpostrcess->processAdditional();
//$postprocess->processMusicFromMediaInfo();
//$postprocess->processOtherMiscCategory();
//$postprocess->processUnknownCategory();
?>
