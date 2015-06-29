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

// when we use the word ignore, that means that it is ignored during the obfuscation process (i.e. not obfuscated)

class Config
{
    public $t_convert_php_extension     = array('php');
    public $prettyPrinter               = 'Standard';   // 'YAK Pro' or 'Standard'
    
    public $abort_on_error              = true;         // self explanatory
    public $confirm                     = true;         // rfu : will answer Y on confirmation request (reserved for future use ... or not...)
    public $silent                      = false;        // display or not Information level messages.

    public $t_keep                      = false;        // array of directory or file pathnames to keep 'as is' ...  i.e. not convert.
    public $t_skip                      = false;        // array of directory or file pathnames to skip when exploring source tree structure ... they will not be on target!

    public $source_directory            = null;         // self explanatory
    public $target_directory            = null;         // self explanatory
    
    public $default_link_name           = null;         // name of variable containing database link reference ; for example: 'link'

    private $comment                    = '';


    

    function __construct()
    {
        $this->comment .= "/*   ________________________________________________".PHP_EOL;
        $this->comment .= "    |    Converted by YAK Pro - mysql to mysqli      |".PHP_EOL;
        $this->comment .= "    |  GitHub: https://github.com/pk-fr/yakpro-mtm   |".PHP_EOL;
        $this->comment .= "    |________________________________________________|".PHP_EOL;
        $this->comment .= "*/".PHP_EOL;
    }

    public function get_comment()   { return $this->comment; }
    public function validate()      { }

}

?>
