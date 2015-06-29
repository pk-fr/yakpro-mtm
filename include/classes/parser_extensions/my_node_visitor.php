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

class MyNodeVisitor extends PhpParser\NodeVisitorAbstract       // see PHP-Parser for documentation!
{                                                               
    public function leaveNode(PhpParser\Node $node)
    {
        global $conf;
        global $t_infos;
        global $debug_mode;

        $node_modified = false;

        if ($node instanceof PhpParser\Node\Expr\FuncCall )
        {
            if (isset($node->name->parts))              // not set when indirect call (i.e.function name is a variable value!)
            {
                $parts = $node->name->parts;
                $name  = $parts[count($parts)-1];
                if ( is_string($name) && (strlen($name) !== 0) && (substr($name,0,6)=='mysql_') )
                {
                    switch($name)
                    {
                        case 'mysql_data_seek':                   //  mysqli_ directly equivalent to mysql_
                        case 'mysql_fetch_assoc':                         // different behaviour for NULL values ... returns NULL instead of empty string for mysql
                        case 'mysql_fetch_row':                           // different behaviour for NULL values ... returns NULL instead of empty string for mysql
                        case 'mysql_fetch_object':                        // different behaviour for NULL values ... returns NULL instead of empty string for mysql
                        case 'mysql_fetch_lengths':
                        case 'mysql_field_seek':
                        case 'mysql_free_result':
                        case 'mysql_freeresult':
                        case 'mysql_get_client_info':
                            $new_name = str_replace(
                                                    array('mysql_freeresult'),
                                                    array('mysql_free_result'),
                                                    $name);

                            $new_name = str_replace('mysql_','mysqli_',$new_name);
                            $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                            $node->name->parts[count($parts)-1] = $new_name;
                            $node_modified = true;
                            break;

                        case 'mysql_affected_rows':                 // need $link in mysqli_ ... was optional in mysql_
                        case 'mysql_client_encoding':
                        case 'mysql_close':
                        case 'mysql_errno':
                        case 'mysql_error':
                        case 'mysql_insert_id':
                        case 'mysql_get_host_info':
                        case 'mysql_get_proto_info':
                        case 'mysql_get_server_info':
                        case 'mysql_info':
                        case 'mysql_num_rows':
                        case 'mysql_numrows':
                        case 'mysql_ping':
                        case 'mysql_thread_id':
                        case 'mysql_stat':
                      
                            $new_name = str_replace(
                                                    array('mysql_client_encoding'    ,'mysql_numrows' ),
                                                    array('mysqli_character_set_name','mysql_num_rows'),
                                                    $name);

                            $new_name = str_replace('mysql_','mysqli_',$new_name);

                            $t_args = $node->args;
                            switch(count($t_args))
                            {
                                case 0:
                                    if (!isset($conf->default_link_name) || ($conf->default_link_name==''))
                                    {
                                        $new_name = PHP_EOL.'// yakpro-mtm TODO: unknown link parameter:'.PHP_EOL.$new_name;
                                        $node->args[0] = new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable('????? link'));
                                        $t_infos[$node->name->getLine()][] = "$name -> $new_name(????? link)";
                                        $node->name->parts[count($parts)-1] = $new_name;
                                        $node_modified = true;
                                       break;
                                    }
                                    $node->args[0] = new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable($conf->default_link_name));
                                    $t_infos[$node->name->getLine()][] = "$name() -> $new_name(\${$conf->default_link_name})";
                                    break;
                                case 1:
                                    $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                                    break;
                                default:
                                    $new_name = "//yakpro-mtm TODO: incorrect parameter number: $new_name";
                                    $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                            }
                            $node->name->parts[count($parts)-1] = $new_name;
                            $node_modified = true;
                            break;
                            
                        case 'mysql_query':                                // need $link as 1st parameter  in mysqli_   ...    was the optional 2nd parameter in mysql_
                        case 'mysql_unbuffered_query':      // same as mysql_query : TODO use MYSQLI_USE_RESULT mode  and mysqli_free_result() before another query!
                        case 'mysql_select_db':
                        case 'mysql_selectdb':
                        case 'mysql_set_charset':
                        case 'mysql_real_escape_string':
                        case 'mysql_escape_string':
                            $new_name = str_replace('mysql_','mysqli_',$name);
                            switch($name)
                            {
                                case 'mysql_unbuffered_query':
                                    $new_name =  PHP_EOL.'// yakpro-mtm TODO: for mysql_unbuffered_query behaviour, use MYSQLI_USE_RESULT mode  and mysqli_free_result() before executing another query!'.PHP_EOL.'mysqli_query';
                                    break;
                                case 'mysql_escape_string':
                                    $new_name = 'mysqli_real_escape_string';
                                    break;
                                case 'mysql_selectdb':
                                    $new_name = 'mysqli_select_db';
                                    break;
                            }
                            $t_args = $node->args;
                            switch(count($t_args))
                            {
                                case 1:
                                    if (!isset($conf->default_link_name) || ($conf->default_link_name==''))
                                    {
                                        $new_name = PHP_EOL.'// yakpro-mtm TODO: unknown link parameter:'.PHP_EOL.$new_name;
                                        $node->args[1] = $node->args[0];
                                        $node->args[0] = new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable('????? link'));
                                        $t_infos[$node->name->getLine()][] = "$name -> $new_name(????? link)";
                                        $node->name->parts[count($parts)-1] = $new_name;
                                        $node_modified = true;
                                        break;
                                    }
                                    $node->args[1] = $node->args[0];
                                    $node->args[0] = new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable($conf->default_link_name));
                                    $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(\${$conf->default_link_name},...)";
                                    break;
                                case 2:
                                    $tmp = $node->args[1]; $node->args[1] = $node->args[0]; $node->args[0] = $tmp;
                                    $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                                    break;
                                default:
                                    $new_name = "//yakpro-mtm TODO: incorrect parameter number: $new_name";
                                    $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                            }
                            $node->name->parts[count($parts)-1] = $new_name;
                            $node_modified = true;
                            break;

                        case 'mysql_num_fields':
                            $new_name = 'mysqli_field_count';
                            if (!isset($conf->default_link_name) || ($conf->default_link_name==''))
                            {
                                $node->args[0] = new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable('????? link'));
                                $new_name = PHP_EOL.'// yakpro-mtm TODO: unknown link parameter:'.PHP_EOL.$new_name;
                                $t_infos[$node->name->getLine()][] = "$name -> $new_name(????? link)";
                                $node->name->parts[count($parts)-1] = $new_name;
                                $node_modified = true;
                                break;
                            }
                            $node->args[0] = new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable($conf->default_link_name));
                            $t_infos[$node->name->getLine()][] = "$name(result) -> $new_name(link)";
                            $node->name->parts[count($parts)-1] = $new_name;
                            $node_modified = true;
                            break;

                        case 'mysql_fetch_field':
                            $new_name = PHP_EOL.'// yakpro-mtm TODO: verify object properties names (different from mysql ones)'.PHP_EOL.'mysqli_fetch_field_direct';
                            $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                            $node->name->parts[count($parts)-1] = $new_name;
                            $node_modified = true;
                            break;
                            
                        case 'mysql_field_flags':       // use mysqli_fetch_field_direct properties
                        case 'mysql_field_len':
                        case 'mysql_field_name':
                        case 'mysql_field_table':
                        case 'mysql_field_type':
                            switch($name)
                            {
                                case 'mysql_field_flags':   $property = 'flags';    break;
                                case 'mysql_field_len':     $property = 'length';   break;
                                case 'mysql_field_name':    $property = 'name';     break;
                                case 'mysql_field_table':   $property = 'table';    break;
                                case 'mysql_field_type':    $property = 'type';     break;
                            }
                            $new_name   = 'mysqli_fetch_field_direct';
                            $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)[$property]";
                            $node->name->parts[count($parts)-1] = $new_name;
                            $condition  = new PhpParser\Node\Expr\Assign(new PhpParser\Node\Expr\Variable('t_yakpro_mtm_tmp'), $node);
                            $if         = new PhpParser\Node\Expr\PropertyFetch(new PhpParser\Node\Expr\Variable('t_yakpro_mtm_tmp'),$property);
                            $else       = new PhpParser\Node\Expr\ConstFetch(new PhpParser\Node\Name('false'));
                            $node       = new PhpParser\Node\Expr\Ternary($condition,$if,$else);
                            $node_modified = true;
                            break;
                            
                        case 'mysql_db_query':                      // mysql_db_query ($dbname, $query [, $link=NULL] ) ==> mysqli_select_db($link, $dbname) ? mysqli_query($link, $query) : false;
                            $new_name   = 'mysqli_select_db';
                            $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...) ? mysqli_query(...) : false ";
                            $node->name->parts[count($parts)-1] = $new_name;
                            $t_args = $node->args;
                            switch(count($t_args))
                            {
                                case 2:
                                     if (!isset($conf->default_link_name) || ($conf->default_link_name==''))
                                    {
                                        $new_name = PHP_EOL.'// yakpro-mtm TODO: unknown link parameter:'.PHP_EOL.$new_name;
                                        $link = new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable('????? link'));
                                        break;
                                    }
                                    $link = new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable($conf->default_link_name));
                                    break;
                               case 3:
                                    $link = $node->args[2];
                                    break;
                                default:
                                    $new_name = "//yakpro-mtm TODO: incorrect parameter number: $name";
                                    $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                                    $node->name->parts[count($parts)-1] = $new_name;
                                    $node_modified = true;
                                    break 2;
                            }
                            $t_args_0   = array();
                            $t_args_0[0]= $link;
                            $t_args_0[1]= $t_args[0];
                            $condition  = new PhpParser\Node\Expr\FuncCall(new PhpParser\Node\Name($new_name),$t_args_0);
                            $t_args_0[1]= $t_args[1];
                            $if         = new PhpParser\Node\Expr\FuncCall(new PhpParser\Node\Name('mysqli_query'),$t_args_0);
                            $else       = new PhpParser\Node\Expr\ConstFetch(new PhpParser\Node\Name('false'));
                            $node       = new PhpParser\Node\Expr\Ternary($condition,$if,$else);
                            $node_modified = true;
                            break;
                            
                         case 'mysql_result':                           // mysql_result($result, $number, $field)  ==> mysqli_data_seek($result, $number) ? mysqli_fetch_array($result)[$field] : false
                            $new_name   = 'mysqli_data_seek';
                            $new_name   = "// yakpro-mtm TODO: verify that you didn't use mysql_result(\$index,'tablename.fieldname') 2nd parameter style".PHP_EOL.$new_name;
                            
                            $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...) ? mysqli_fetch_assoc(...) : null ";
                            $node->name->parts[count($parts)-1] = $new_name;
                            $t_args = $node->args;
                            $t_args_0   = array();
                            $t_args_0[0]= $t_args[0];
                            $t_args_0[1]= $t_args[1];
                            $condition  = new PhpParser\Node\Expr\FuncCall(new PhpParser\Node\Name($new_name),$t_args_0);

                            $t_args_0   = array();
                            $t_args_0[0]= $t_args[0];
                            $condition2 = new PhpParser\Node\Expr\Assign(new PhpParser\Node\Expr\Variable('t_yakpro_mtm_tmp'), new PhpParser\Node\Expr\FuncCall(new PhpParser\Node\Name('mysqli_fetch_array'),$t_args_0));

                            if (isset($t_args[2]))
                            {
                                $t_args_0   = array();
                                $t_args_0[0]= $t_args[2];
                                $t_args_0[1]= new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable('t_yakpro_mtm_tmp'));
                                $condition3 = new PhpParser\Node\Expr\FuncCall(new PhpParser\Node\Name('array_key_exists'),$t_args_0);
                                $if3        = new PhpParser\Node\Expr\ArrayDimFetch(new PhpParser\Node\Expr\Variable('t_yakpro_mtm_tmp'),$t_args[2]->value);
                                $else3      = new PhpParser\Node\Expr\ConstFetch(new PhpParser\Node\Name('false'));
                                $if2        = new PhpParser\Node\Expr\Ternary($condition3,$if3,$else3);
                            }
                            else
                            {
                                $if2 = new PhpParser\Node\Expr\Variable('t_yakpro_mtm_tmp');
                            }
                            $else2      = new PhpParser\Node\Expr\ConstFetch(new PhpParser\Node\Name('false'));
                            $if         = new PhpParser\Node\Expr\Ternary($condition2,$if2,$else2);
                            $else       = new PhpParser\Node\Expr\ConstFetch(new PhpParser\Node\Name('false'));
                            $node       = new PhpParser\Node\Expr\Ternary($condition,$if,$else);
                            $node_modified = true;
                            break;

                        case 'mysql_db_name':                             // no mysqli_ equivalent function : manually need to rewrite with SQL queries.
                        case 'mysql_dbname':
                        case 'mysql_list_dbs':
                        case 'mysql_listdbs':
                        case 'mysql_list_processes':
                        case 'mysql_list_fields':
                        case 'mysql_listfields':
                        case 'mysql_list_tables':
                        case 'mysql_listtables':
                        case 'mysql_tablename':
                            $new_name = PHP_EOL.'// yakpro-mtm TODO: No mysqli equivalent function! (deprecated): use SQL queries instead!'.PHP_EOL.$name;
                            $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                            $node->name->parts[count($parts)-1] = $new_name;
                            $node_modified = true;
                            break;
                            
                        case 'mysql_create_db':                         // no mysqli_ equivalent function : attempt to rewrite with SQL queries.
                        case 'mysql_createdb':                          // link the optional 2nd parameter in mysql_
                        case 'mysql_drop_db':
                        case 'mysql_dropdb':
                            switch($name)
                            {
                                case 'mysql_create_db':
                                case 'mysql_createdb':
                                    $action = "CREATE DATABASE";
                                    break;
                                case 'mysql_drop_db':
                                case 'mysql_dropdb':
                                    $action = "DROP DATABASE";
                                    break;
                            }
                            $new_name = 'mysqli_query';
                            $t_args = $node->args;
                            switch(count($t_args))
                            {
                                case 1:
                                    $dbname = $node->args[0]->value;
                                    if (!isset($conf->default_link_name) || ($conf->default_link_name==''))
                                    {
                                        $node->args[1] = new PhpParser\Node\Arg(new PhpParser\Node\Expr\BinaryOp\Concat(new PhpParser\Node\Expr\BinaryOp\Concat(new PhpParser\Node\Scalar\String_($action.' `'),$dbname),new PhpParser\Node\Scalar\String_('`')));
                                        $node->args[0] = new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable('????? link'));
                                        $new_name = PHP_EOL.'// yakpro-mtm TODO: unknown link parameter:'.PHP_EOL.$new_name;
                                        $t_infos[$node->name->getLine()][] = "$name -> $new_name(????? link)";
                                        $node->name->parts[count($parts)-1] = $new_name;
                                        $node_modified = true;
                                       break;
                                    }
                                    $node->args[1] = new PhpParser\Node\Arg(new PhpParser\Node\Expr\BinaryOp\Concat(new PhpParser\Node\Expr\BinaryOp\Concat(new PhpParser\Node\Scalar\String_($action.' `'),$dbname),new PhpParser\Node\Scalar\String_('`')));
                                    $node->args[0] = new PhpParser\Node\Arg(new PhpParser\Node\Expr\Variable($conf->default_link_name));
                                    $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(\${$conf->default_link_name},...)";
                                    break;
                                case 2:
                                    $dbname = $node->args[0]->value;
                                    $node->args[0] = $node->args[1];
                                    $node->args[1]  = new PhpParser\Node\Arg(new PhpParser\Node\Expr\BinaryOp\Concat(new PhpParser\Node\Expr\BinaryOp\Concat(new PhpParser\Node\Scalar\String_($action.' `'),$dbname),new PhpParser\Node\Scalar\String_('`')));
                                    $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                                    break;
                                default:
                                    $new_name = "//yakpro-mtm TODO: incorrect parameter number: $name";
                                    $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                            }
                            $node->name->parts[count($parts)-1] = $new_name;
                            $node_modified = true;
                            break;
                        
                        case 'mysql_connect':
                        case 'mysql_pconnect':      // ignore persistent  and process as mysql_connect ...
                            $t_infos[$node->name->getLine()][] = "$name(...) -> mysqli_connect(...)";
                            unset($node->args[4]); unset($node->args[3]);   // remove new_link and flags parameters
                            $t_args = $node->args;
                            if (count($t_args))
                            {
                                $t_mtm_tmp  = new PhpParser\Node\Expr\Assign(new PhpParser\Node\Expr\Variable('t_yakpro_mtm_tmp'), new PhpParser\Node\Expr\FuncCall(new PhpParser\Node\Name('explode'),array(new PhpParser\Node\Scalar\String_(':'),$t_args[0]->value)));
                                $condition  = new PhpParser\Node\Expr\BinaryOp\Greater(new PhpParser\Node\Expr\FuncCall(new PhpParser\Node\Name('count'),array($t_mtm_tmp)), new PhpParser\Node\Scalar\LNumber(1));
                                $t_if_args  = array();
                                $t_if_args[]= new PhpParser\Node\Expr\ArrayDimFetch(new PhpParser\Node\Expr\Variable('t_yakpro_mtm_tmp'),new PhpParser\Node\Scalar\LNumber(0));
                                $t_if_args[]= $t_args[1];
                                $t_if_args[]= $t_args[2];
                                $t_if_args[]= new PhpParser\Node\Scalar\String_('');
                                $t_if_args[]= new PhpParser\Node\Expr\ArrayDimFetch(new PhpParser\Node\Expr\Variable('t_yakpro_mtm_tmp'),new PhpParser\Node\Scalar\LNumber(1));
                                $if         = new PhpParser\Node\Expr\FuncCall(new PhpParser\Node\Name('mysqli_connect'),$t_if_args);
                                $else       = new PhpParser\Node\Expr\FuncCall(new PhpParser\Node\Name('mysqli_connect'),$t_args);
                                $node       = new PhpParser\Node\Expr\Ternary($condition,$if,$else);
                                $node_modified = true;
                            }
                            else
                            {
                                $node->name->parts[count($parts)-1] = 'mysqli_connect';
                                $node_modified = true;
                            }
                            break;
                            
                        case 'mysql_fetch_array':
                            $new_name = str_replace('mysql_','mysqli_',$name);
                            $t_infos[$node->name->getLine()][] = "$name(...) -> $new_name(...)";
                            $t_args = $node->args;
                            switch(count($t_args))
                            {
                                case 1:
                                    break;
                                case 2:
                                    $arg = $t_args[1]->value;
                                    $ok = false;
                                    if ($arg instanceof PhpParser\Node\Expr\ConstFetch)
                                    {
                                        $argname = $arg->name->parts[count($arg->name->parts) -1];
                                        switch($argname)
                                        {
                                            case 'MYSQL_ASSOC': $argname = 'MYSQLI_ASSOC';  $ok = true; break;
                                            case 'MYSQL_BOTH':  $argname = 'MYSQLI_BOTH';   $ok = true; break;
                                            case 'MYSQL_NUM':   $argname = 'MYSQLI_NUM';    $ok = true; break;
                                            default:
                                        }
                                    }
                                    if ($ok)
                                    {
                                        $arg->name->parts[count($arg->name->parts) -1] = $argname;
                                    }
                                    else
                                    {
                                        $new_name = PHP_EOL.'//yakpro-mtm TODO: verify second parameter value!'.PHP_EOL.$new_name;
                                    }
                                    break;
                                default:
                                    $new_name = "//yakpro-mtm TODO: incorrect parameter number: $name";
                            }
                            $node->name->parts[count($parts)-1] = $new_name;
                            $node_modified = true;
                            break;
                    }
                }
            }
        }
        if ($node_modified) return $node;
    }
}

?>
