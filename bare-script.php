<?php
/*
Template Name: Script
*/
?>
<?php 
the_post();
$postOutput = get_the_content();
echo preg_replace('/\r/','',preg_replace('/^.+\n.+\n/', '', preg_replace('/<\/pre>$/', '', $postOutput)));
?>
