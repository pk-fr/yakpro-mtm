#!/usr/bin/php
<?php
//========================================================================
// Author:  Pascal KISSIAN
// Resume:  http://pascal.kissian.net
//
// Copyright (c) 2015 Pascal KISSIAN
//
// Published under the MIT License
//          Consider it as a proof of concept!
//          No warranty of any kind.
//          Use and abuse at your own risks.
//========================================================================

require_once 'include/check_version.php';

require_once 'PHP-Parser/lib/bootstrap.php';

require_once 'include/classes/config.php';
require_once 'include/classes/parser_extensions/my_pretty_printer.php';
require_once 'include/classes/parser_extensions/my_node_visitor.php';
require_once 'include/functions.php';

include      'include/retrieve_config_and_arguments.php';

if ($clean_mode && file_exists("$target_directory/yakpro-mtm/.yakpro-mtm-directory") )
{
    if (!$conf->silent) fprintf(STDERR,"Info:\tRemoving directory\t= [%s]%s","$target_directory/yakpro-mtm",PHP_EOL);
    remove_directory("$target_directory/yakpro-mtm");
    exit;
}

$parser             = new PhpParser\Parser(new PhpParser\Lexer\Emulative);      // $parser = new PhpParser\Parser(new PhpParser\Lexer);
$traverser          = new PhpParser\NodeTraverser;

if ($conf->prettyPrinter=='YAK Pro')    $prettyPrinter      = new myPrettyprinter;
else                                    $prettyPrinter      = new PhpParser\PrettyPrinter\Standard;

$traverser->addVisitor(new MyNodeVisitor);

switch($process_mode)
{
    case 'file':
        $converted_str =  convert_file($source_file);
        if ($converted_str===null)  { exit;                              }
        if ($target_file==='')      { echo $converted_str.PHP_EOL; exit; }
        file_put_contents($target_file,$converted_str.PHP_EOL);
        exit;
    case 'directory':
        if (isset($conf->t_skip) && is_array($conf->t_skip)) foreach($conf->t_skip as $key=>$val) $conf->t_skip[$key] = "$source_directory/$val";
        if (isset($conf->t_keep) && is_array($conf->t_keep)) foreach($conf->t_keep as $key=>$val) $conf->t_keep[$key] = "$source_directory/$val";
        convert_directory($source_directory,"$target_directory/yakpro-mtm/converted");
        exit;
}

?>
