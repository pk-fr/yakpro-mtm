# YAK Pro - mysql to mysqli converter

**YAK Pro** stands for **Y**et **A**nother **K**iller **Pro**duct.

Free, Open Source, Published under the MIT License.  

This tool parses php with the best existing php parser [PHP-Parser](https://github.com/nikic/PHP-Parser),  
which is an awesome php parsing library written by [nikic](https://github.com/nikic).

You just have to download the zip archive and uncompress it under the PHP-Parser subdirectory.  
or make a git clone ...

The yakpro-mtm.cnf self-documented file contains configuration options!
Take a look at it!  

Demo : [yakpro-mtm demo](http://mysql-to-mysqli.yakpro.com/?demo).

Prerequisites:  php 5.3 or higher, [PHP-Parser](https://github.com/nikic/PHP-Parser).


## Why a mysql to mysqli converter?

Legacy php access to MySql was made using the mysql extension interface.  
The mysql extension is deprecated as of PHP 5.5.0, but many legacy php programs still use mysql.  
The comming php 7 will completly remove the mysql extension, which is a bacwkard compatibility break!

There are a few alternatives for people wanting to port their php scripts on php 7.  
1) Rewrite their software with PDO, which is a database abstraction layer.  
   The programming logic is not the same, it requires a lot of work.  
2) Rewrite their software with mysqli, which have 2 interfaces styles;  
   - an object oriented one.  
   - a procedural one.
   
This converter, converts mysql to mysqli using the procedural form, which is very similar to the mysql one.  

Unfortunately, there are many changes between the mysql calls and the mysqli ones.
Among them, youw will find;
 - new parameters (many mysqli functions now require the "link" parameter).
 - change of parameters ordering.
 

### YAK Pro - mysql to mysqli converter Main Features:  

- If your software uses always the same "link" parameter, you can specify it.
- Recursivly converts a project's directory.
- Makefile like, timestamps based mechanism, to re-convert only files that were changed since last convert.



## Setup:
    Put the downloaded files where you want...
        chmod a+x yakpro-mtm.php     would be helpfull...

    It would be a good idea to create a symbolic link named yakpro-mtm in /usr/local/bin,
    pointing to the yakpro-mtm.php file.

    Put the PHP-Parser directory at the same level that the yakpro-mtm.php file.

    Modify a copy of the yakpro-mtm.cnf to fit your needs...
    Read the "Configuration file loading algorithm" section of this document
    to choose the best location suiting your needs!

    That's it! You're done!

####

## Usage:

`yakpro-mtm`
Converts according configuration file!
(See configuration file loading algorithm)

`yakpro-mtm source_filename`
Converts code to stdout

`yakpro-mtm source_filename -o target_filename`
Converts code to target_filename

`yakpro-mtm source_directory -o target_directory`
Recursivly converts code to target_directory/yakpro-mtm (creates it if not already exists).

`yakpro-mtm --config-file config_file_path`
According to config_file_path.

`yakpro-mtm --clean`
Requires target_directory to be present in your config file!  
Recursivly removes target_directory/yakpro-mtm


## Configuration file loading algorithm:
(the first found is used)

    --config-file argument value
    YAKPRO_MTM_CONFIG_FILE environnement variable value if exists and not empty.

    filename selection:
           YAKPRO_MTM_CONFIG_FILENAME environnement variable value if exists and not empty,
           yakpro-mtm.cnf otherwise.

     file is then searched in the following directories:
            YAKPRO_MTM_CONFIG_DIRECTORY  environnement variable value if exists and not empty.
            current_working_directory
            current_working_directory/config
            home_directory
            home_directory/config
            /usr/local/YAK/yakpro-mtm
            source_code_directory/default_conf_filename

      if no config file is found, default values are used.

      You can find the default config file as an example in the yakpro-mtm.cnf file of the
      repository.
      Do not modify it directly because it will be overwritten at each update!
      Use your own yakpro-mtm.cnf file (for example in the root directory of your project)

      When working on directories, if you make some changes in one or several source files,
      yakpro-mtm uses timestamps to only convert files that where changed.
      since last convert.
      This can saves you a lot of time.

      caveats: does not delete files that are no more present...
               use --clean  command line parameter, and then re-convert all!

## Other command line options:
(override config file settings)

    --silent                        do not display Information level messages.
    --debug                         (internal debugging use) displays the syntax tree.
    
    --no-default-link-name          do not use a default link name
    --default-link-name  name       uses name as the default link name (do not prepend a $ sign)

    --indent-mode  mode             specify converted files indent mode ( standard or yakpro ).
    
    -h or
    --help                          displays help.

####

    
   
