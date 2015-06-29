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

function convert_file($filename)                   // takes a file_path as input, returns the corresponding converted code as a string
{
    global $conf;
    global $t_infos;
    global $parser,$traverser,$prettyPrinter;
    global $debug_mode;

    try
    {
        $t_infos= array();
        $source = trim(file_get_contents($filename));
        $source = file_get_contents($filename);
        fprintf(STDERR,"Converting %s%s",$filename,PHP_EOL);
        // var_dump( token_get_all($source));    exit;
        $stmts  = $parser->parse($source/*.PHP_EOL.PHP_EOL*/);  // PHP-Parser returns the syntax tree

        if ($debug_mode) var_dump($stmts);
        
        $stmts  = $traverser->traverse($stmts);                 //  Use PHP-Parser function to traverse the syntax tree and convert functions
        $code   = $prettyPrinter->prettyPrintFile($stmts);      //  Use PHP-Parser function to output the converted source, taking the modified converted syntax tree as input
        $code   = trim($code);

        ksort($t_infos);
        $log = '';
        foreach($t_infos as $line_number => $t_info)
        {
            $infos = $warning = $str = '';
            foreach($t_info as $str)
            {
                $str = sprintf("\t%s%s",str_replace(PHP_EOL,PHP_EOL."\t",$str),PHP_EOL);
                if (strpos($str,'TODO')!==false)
                {
                    if ($warning=='') $warning = sprintf("Warning: Line %4d:%s",$line_number,PHP_EOL);
                    $warning .= $str;
                }
                else
                {
                    if ($infos=='') $infos = sprintf("Information: Line %4d:%s",$line_number,PHP_EOL);
                    $infos .= $str;
                }
            }
            if (!$conf->silent) $log .= $infos;
            $log .= $warning;
        }
        fprintf(STDERR,"%s",$log);
        //print_r($t_infos);

        
        //  var_dump($stmts);
        $endcode = substr($code,6);

        $code  = '<?php'.PHP_EOL;
        $code .= $conf->get_comment();                                          // comment converted source
        $code .= $endcode;
        return $code;
    }
    catch (Exception $e)
    {
        fprintf(STDERR,"Converter Parse Error [%s]:%s\t%s%s", $filename,PHP_EOL, $e->getMessage(),PHP_EOL);
        return null;
    }
}

function check_config_file($filename)                       // self-explanatory
{
    for($ok=false;;)
    {
        if (!file_exists($filename)) return false;
        if (!is_readable($filename))
        {
            fprintf(STDERR,"Warning:[%s] is not readable!%s",$filename,PHP_EOL);
            return false;
        }
        $fp     = fopen($filename,"r"); if($fp===false) break;
        $line   = trim(fgets($fp));     if ($line!='<?php')                                     { fclose($fp); break; }
        $line   = trim(fgets($fp));     if ($line!='// YAK Pro - mysql to mysqli: Config File') { fclose($fp); break; }
        fclose($fp);
        $ok     = true;
        break;
    }
    if (!$ok && $display_warning) fprintf(STDERR,"Warning:[%S] is not a valid yakpro-mtm config file!%s\tCheck if file is php, and if magic line is present!%s",$filename,PHP_EOL,PHP_EOL);
    return $ok;
}

function create_context_directories($target_directory)      // self-explanatory
{
    foreach( array("$target_directory/yakpro-mtm","$target_directory/yakpro-mtm/converted") as $dir)
    {
        if (!file_exists($dir)) mkdir($dir,0777,true);
        if (!file_exists($dir))
        {
            fprintf(STDERR,"Error:\tCannot create directory [%s]%s",$dir,PHP_EOL);
            exit(-1);
        }
    }
    $target_directory = realpath($target_directory);
    if (!file_exists("$target_directory/yakpro-mtm/.yakpro-mtm-directory")) touch("$target_directory/yakpro-mtm/.yakpro-mtm-directory");
}


function remove_directory($path)                            // self-explanatory
{
    if ($dp = opendir($path))
    {
        while (($entry = readdir($dp)) !==  false )
        {
            if ($entry ==  ".") continue;
            if ($entry == "..") continue;

                 if (is_link("$path/$entry"))   unlink("$path/$entry" );            // remove symbolic links first, to not dereference...
            else if (is_dir ("$path/$entry"))   remove_directory("$path/$entry");
            else                                unlink("$path/$entry" );
        }
        closedir($dp);
        rmdir($path);
    }
}

function confirm($str)                                  // self-explanatory not yet used ... rfu
{
    global $conf;
    if (!$conf->confirm) return true;
    for(;;)
    {
        fprintf(STDERR,"%s [y/n] : ",$str);
        $r = strtolower(trim(fgets(STDIN)));
        if ($r=='y')    return true;
        if ($r=='n')    return false;
    }
}

function convert_directory($source_dir,$target_dir,$keep_mode=false)   // self-explanatory recursive convert
{
    global $conf;

    if (!$dp = opendir($source_dir))
    {
        fprintf(STDERR,"Error:\t [%s] directory does not exists!%s",$source_dir,PHP_EOL);
        exit(-1);
    }
    $t_dir  = array();
    $t_file = array();
    while (($entry = readdir($dp)) !== false)
    {
        if ($entry == "." || $entry == "..")    continue;

        $new_keep_mode = $keep_mode;

        $source_path = "$source_dir/$entry";    $source_stat = @lstat($source_path);
        $target_path = "$target_dir/$entry";    $target_stat = @lstat($target_path);
        if ($source_stat===false)
        {
            fprintf(STDERR,"Error:\t cannot stat [%s] !%s",$source_path,PHP_EOL);
            exit(-1);
        }

        if (isset($conf->t_skip) && is_array($conf->t_skip) && in_array($source_path,$conf->t_skip))    continue;

        if (is_link($source_path))
        {
            if ( ($target_stat!==false) && is_link($target_path) && ($source_stat['mtime']<=$target_stat['mtime']) )    continue;
            if (  $target_stat!==false  )
            {
                if (is_dir($target_path))   directory_remove($target_path);
                else
                {
                    if (unlink($target_path)===false)
                    {
                        fprintf(STDERR,"Error:\t cannot unlink [%s] !%s",$target_path,PHP_EOL);
                        exit(-1);
                    }
                }
            }
            @symlink(readlink($source_path), $target_path);     // Do not warn on non existing symbolinc link target!
            if (strtolower(PHP_OS)=='linux')    $x = `touch '$target_path' --no-dereference --reference='$source_path' `;
            continue;
        }
        if (is_dir($source_path))
        {
            if ($target_stat!==false)
            {
                if (!is_dir($target_path))
                {
                    if (unlink($target_path)===false)
                    {
                        fprintf(STDERR,"Error:\t cannot unlink [%s] !%s",$target_path,PHP_EOL);
                        exit(-1);
                    }
                }
            }
            if (!file_exists($target_path)) mkdir($target_path,0777, true);
            if (isset($conf->t_keep) && is_array($conf->t_keep) && in_array($source_path,$conf->t_keep))    $new_keep_mode = true;
            convert_directory($source_path,$target_path,$new_keep_mode);
            continue;
        }
        if(is_file($source_path))
        {
            if ( ($target_stat!==false) && is_dir($target_path) )                               directory_remove($target_path);
            if ( ($target_stat!==false) && ($source_stat['mtime']<=$target_stat['mtime']) )     continue;                       // do not process if source timestamp is not greater than target

            $extension  = pathinfo($source_path,PATHINFO_EXTENSION);

            $keep = $keep_mode;
            if (isset($conf->t_keep) && is_array($conf->t_keep) && in_array($source_path,$conf->t_keep))    $keep = true;
            if (!in_array($extension,$conf->t_convert_php_extension) )                                      $keep = true;

            if ($keep)
            {
                file_put_contents($target_path,file_get_contents($source_path));
            }
            else
            {
                $converted_str =  convert_file($source_path);
                if ($converted_str===null)
                {
                    if (isset($conf->abort_on_error))
                    {
                        fprintf(STDERR, "Aborting...%s",PHP_EOL);
                        exit;
                    }
                }
                file_put_contents($target_path,$converted_str.PHP_EOL);
            }
            if ($keep) file_put_contents($target_path,file_get_contents($source_path));
            touch($target_path,$source_stat['mtime']);
            continue;
        }
    }
    closedir($dp);
}

?>
