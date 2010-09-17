<?php

/*
+--------------------------------------------------------------------------
|   D-Site Miscelanious functions module
|   ========================================
|   Copyright (c) 2004 - 2005 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
|
|   Miscelanious functions
|
*---------------------------------------------------------------------------
*/

class mod_misc {

        //---------------------------------------------------
        // checks if an element of array was in this array
        //---------------------------------------------------

        function is_used($tok, $values = array()) {

                if (is_array($values)) {

                        foreach ($values as $val) {

                                if ($val == $tok) {

                                        return true;
                                }
                        }
                }

                return false;
        }

        //---------------------------------------------------
        // checks if an element of array is empry
        //---------------------------------------------------

        function is_empty($data = array(), $values = array()) {

                $result = false;

                foreach ($values as $v) {

                        if (empty($data[$v])) {

                                $result = true;
                        }
                }

                return $result;
        }

        //**********************************************/
        // copy_dir
        //
        // Copies to contents of a dir to a new dir, creating
        // destination dir if needed.
        //
        //**********************************************/

        function copy_dir($from_path, $to_path, $mode = 0777)
        {

                // Strip off trailing slashes...

                $from_path = preg_replace( "#/$#", "", $from_path);
                $to_path   = preg_replace( "#/$#", "", $to_path);

                if ( ! is_dir($from_path) )
                {
                        $this->errors = "Could not locate directory '$from_path'";
                        return FALSE;
                }

                if ( ! is_dir($to_path) )
                {
                        if ( ! @mkdir($to_path, $mode) )
                        {
                                $this->errors = "Could not create directory '$to_path' please check the CHMOD permissions and re-try";
                                return FALSE;
                        }
                        else
                        {
                                @chmod($to_path, $mode);
                        }
                }

                $this_path = getcwd();

                if (is_dir($from_path))
                {
                        chdir($from_path);
                        $handle=opendir('.');
                        while (($file = readdir($handle)) !== false)
                        {
                                if (($file != ".") && ($file != ".."))
                                {
                                        if (is_dir($file))
                                        {

                                                $this->copy_dir($from_path."/".$file, $to_path."/".$file);

                                                chdir($from_path);
                                        }

                                        if ( is_file($file) )
                                        {
                                                copy($from_path."/".$file, $to_path."/".$file);
                                                @chmod($to_path."/".$file, 0777);
                                        }
                                }
                        }
                        closedir($handle);
                }

                if ($this->errors == "")
                {
                        return TRUE;
                }
        }

        //**********************************************/
        // rm_dir
        //
        // Removes directories, if non empty, removes
        // content and directories
        // (Code based on annotations from the php.net
        // manual by pal@degerstrom.com)
        //**********************************************/

        function rm_dir($file)
        {

                $errors = 0;

                // Remove trailing slashes..

                $file = preg_replace( "#/$#", "", $file );

                if ( file_exists($file) )
                {
                        // Attempt CHMOD

                        @chmod($file, 0777);

                        if ( is_dir($file) )
                        {
                                $handle = opendir($file);

                                while (($filename = readdir($handle)) !== false)
                                {
                                        if (($filename != ".") && ($filename != ".."))
                                        {
                                                $this->rm_dir($file."/".$filename);
                                        }
                                }

                                closedir($handle);

                                if ( ! @rmdir($file) )
                                {
                                        $errors++;
                                }
                        }
                        else
                        {
                                if ( ! @unlink($file) )
                                {
                                        $errors++;
                                }
                        }
                }

                if ($errors == 0)
                {
                        return TRUE;
                }
                else
                {
                        return FALSE;
                }
        }
}