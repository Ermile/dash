<?php
namespace lib\utility;

/**
 * db Tables Creator class
 * Version 2.5
 * 17 Feb 2016
 */
class dbTables
{
  /**
  ***********************************************************************************
  CAUTIONS : IF YOU DON'T KNOW WHAT'S THIS, PLEASE DON'T RUN IT!
  *
  *THIS CLASS READ DATABASE AND CREATE A PHP FILE FROM IT FOR CREATING FORM
  ***********************************************************************************
  **/

  public static $translation = array();

  /**
   * Create a db files in specefic folder
   * @param  [type] $_default default value for using to create a sql files
   * @return [type]           create a one file for each table
   */
  public static function create($_default = null)
  {
    ob_start();

    $output = "<!DOCTYPE html><meta charset='UTF-8'/><title>Create file from db</title><body style='padding:0 1%;direction:ltr;'>";

    // init database folder address
    $folder = database.db_name;
    // create db folder name if not exist
    if (!file_exists($folder))
      mkdir($folder, 0777, true);


    // declare public variables for sql connection
    $connect = @mysqli_connect("localhost", db_user, db_pass, db_name);
    if($connect === false)
    {
      return false;
    }
    $qTables = $connect->query("SHOW TABLES FROM ".db_name);


    /**
     * loop until end of tables. this loop create a final result
     */
    while ($row = $qTables->fetch_object())
    {
      $TableName   = $row->{'Tables_in_'.db_name};
      $TablePrefix = substr($TableName, 0, -1);
      $content     = "<?php\n". "namespace database\\".db_name.";\n" . "class $TableName\n{\n";
      $fn          = "\n";

      // save table name and it to translation list
      $output .= '<h2>'.$TableName.'</h2><pre><ul style="margin:0 20px;padding:0 10px;">';
      self::$translation['Table '.$TableName]       = $TableName;
      self::$translation[substr($TableName, 0, -1)] = $TablePrefix;

      /**
       * Count number of char of each string
       * this values use to creaste a better alignment in sql files
       */
      $counter_name = 0;
      $counter_type = 0;
      $qCOL1        = $connect->query("DESCRIBE `$TableName`");
      while ($mycrow1 = $qCOL1->fetch_object())
      {
        $tmp_type = self::type_checker($mycrow1->Type, $mycrow1->Default, $TableName);
        if(mb_strlen($tmp_type) > $counter_type)
          $counter_type = mb_strlen($tmp_type);

        if(mb_strlen($mycrow1->Field) > $counter_name)
          $counter_name = mb_strlen($mycrow1->Field);
      }


      /**
       * create file of each table
       * this loop fetch all fileds in table
       *
       * for fields from currect table except foreign key
       * we remove the table prefix, then show ramained text for name and for label we replace _ with space
       * for foreign key we remove second part of text after _
       * and show only the name of table without last char
       *
       * some example is listed below in posts table
       * filedname           label
       * $id              => id
       * $post_title      => title
       * $user_id         => user_
       * $user_idcustomer => user_customer
       */
      $qCOL = $connect->query("DESCRIBE `$TableName`");
      while ($crow = $qCOL->fetch_object())
      {
        // declare function properties that use in function
        $myfield        = $crow->Field;
        $field_name     = self::field_userFriendly($myfield, 'name');
        $field_style    = self::field_userFriendly($myfield, 'type');
        $txt_fn         = "\n\tpublic function $myfield()";
        $txt_child      = null;
        // declare properties of field that show on above of sql files
        $varProp            = array();
        $varProp['null']    = $crow->Null;
        $varProp['show']    = 'YES';
        $varProp['label']   = self::field_userFriendly($myfield, 'label');
        $varProp['type']    = self::type_checker($crow->Type, $crow->Default);
        $varProp['foreign'] = null;

        // html properties for using in field function and create a properties
        $html_prop          = self::setproperty($crow, $field_name, $field_style);

        // set foreign options for connection and default value
        if($field_style === 'foreign')
        {
          $relaton_table      = substr($field_name, 0, strpos($field_name, '_')).'s';
          $relaton_prefix     = substr($field_name, 0, -1);
          $varProp['foreign'] = $relaton_table. "@id!";
          $html_prop['type']  = 'select';

          if($relaton_table == $TableName)
            $varProp['foreign'] = null;
          elseif($relaton_table == "users")
            $varProp['foreign'] .= $relaton_prefix."_displayname";
          elseif($relaton_table == "posts"    || $relaton_table == "logitems"     || $relaton_table == "terms" )
            $varProp['foreign'] .= $relaton_prefix."_title";
          elseif($relaton_table == "countrys" || $relaton_table == "provinces"    || $relaton_table == "citys")
            $varProp['foreign'] .= $relaton_prefix."_name";
          elseif($relaton_table == "receipts" || $relaton_table == "transactions" || $relaton_table == "papers" || $relaton_table == "files")
            $varProp['foreign'] .= "id";
          else
            $varProp['foreign'] .= "id";
        }
        elseif($field_name === 'parent')
        {
          $varProp['foreign'] = $TableName."@id!".$TablePrefix.'_title';
          $html_prop['type']  = 'select';
        }


        // if is array return from hyml prop set some change
        // else on field like id or date_modified skip it
        if(is_array($html_prop))
        {
          // for common type of field use # for call symbols
          if(  $field_name == 'title'    || $field_name == 'slug'     || $field_name == 'desc'
            || $field_name == 'website'  || $field_name == 'mobile'   || $field_name == 'tel'
            || $field_name == 'pass'     || $field_name == 'password' || $field_name == 'email'
          )
            $html_prop['form'] = '#'. $field_name;

          // set require if not allow null value in mysql
          if($crow->Null === 'NO')
          {
            if($field_name == 'pass')
            {
              unset($html_prop['required']);
            }
            else
            {
              $html_prop['required'] = null;
            }
          }

          // if type is foreign force select type
          // if($varProp['foreign'])
          //   $html_prop['type'] = 'select';

          // remove ununse property for select and radio
          if($html_prop['type'] == 'select' || $html_prop['type'] == 'radio')
          {
            $txt_child = "\t".'$this->setChild();' . "\n\t";
            unset($html_prop['min']);
            unset($html_prop['max']);
          }
        }


        // if specefic value pass with function overwrite on with created value
        // overwrite form default value given as function parameter
        if(isset($_default[$myfield]))
        {
          // overwrite properties
          if(is_array($_default[$myfield]['prop']))
            $varProp = $_default[$myfield]['prop'];

          // overwrite html elements and function objects
          if(is_array($_default[$myfield]['html']))
            $html_prop = $_default[$myfield]['html'];

          // overwrite foreign values
          if(isset($_default[$myfield]['foreign']))
            $varProp['foreign'] = $_default[$myfield]['foreign'];
        }


        // create variables list on top of each file
        $variable = 'public $' . $myfield . str_repeat(' ', $counter_name + 1 - mb_strlen($myfield)). '= [';
        $myspace  = 2;
        foreach ($varProp as $prop => $value)
        {
          $myspace = $myspace *2;
          if($value)
            @$variable .= "'".$prop."'=>'".$value."'".str_repeat(' ', $myspace - mb_strlen($value)).",";
        }
        $variable = "\t". rtrim(substr($variable, 0, -1)) . "];\n";
        $content .=  $variable ;

        // set property in array and text character for create final result
        if(is_array($html_prop))
        {
          // add some tab and new line for better show
          $txt_fn .= "\n\t{\n\t\t". '$this';

          foreach ($html_prop as $property => $value)
            $txt_fn .= '->'. $property.'('. ($value? "'".$value."'": null). ')';

          $txt_fn .= ";\n\t";
        }
        else
          $txt_fn .= "{";

        // if is not normal type then show it in comment above field function
        if($field_style !== 'normal')
          $txt_fn = "\t//".str_repeat('-', 80). $field_style . $txt_fn;

        $fn      .= $txt_fn. $txt_child . "}\n";

        $output .= '<li style="list-style-type:disc;">'.$variable.'</li>';
        self::$translation[$myfield]  = $varProp['label'];
      }
      $output .= '</ul></pre>';

      $content .= $fn . "}\n?>";
      file_put_contents($folder."/$TableName.php", $content);
    }
    $connect->close();

    // create translation file for gettext
    $translation_output  = '<?php'."\n".'private function transtext()'."\n{\n";
    foreach (self::$translation as $key => $value)
    {
      if(substr($key, 0, 6)=='Table ')
        $translation_output .= "\n\t// ". str_repeat('-', 60). " $key\n";

      @$translation_output .= "\t".'echo T_'.'("'.$value.'");'.str_repeat(' ',20-mb_strlen($value)).'// '.$key."\n";
    }
    $translation_output .= "\n}\n?>";
    file_put_contents($folder."/translation.php", $translation_output);

    // show final result!
    $output .= "<br/><br/><hr/><h1>Finish..!</h1>";
    $output .= "<p class='alert alert-success'>Convert db to file and create translation file completed!</p>";
    // return final output
    return $output;
  }


  /**
   * this function return the type of field
   * @param  [type] $_type
   * @param  [type] $_def
   * @param  [type] $_table
   * @return [type]
   */
  public static function type_checker($_type, $_def, $_table = null)
  {
    // global $translation;
    $_def     = $_def ? "!$_def" : null;
    preg_match("/^([^(]*)(\((.*)\))?/", $_type, $tmp_type);
    $_type   = $tmp_type[1];
    $f_length = isset($tmp_type[3]) ? $tmp_type[3] : null;

    if($_type == 'enum')
    {
      $f_length     = preg_replace("[']", "", $f_length);
      if($_table)
      {
        $enum_values = explode(",",$f_length);
        foreach ($enum_values as $key => $value)
        {
          if($value)
            self::$translation['Enum '.$value] = $value;
        }
      }
    }

    return "$_type@$f_length{$_def}";
    // return ("'type' => '$_type@$f_length{$_def}'");
  }


  /**
   * this function set needed property of fields for HTML5
   * @param  [type] $_arg
   * @return [type]
   */
  public static function setproperty($_arg, $_name, $_style)
  {
    if($_name === 'id' || $_name === 'modified' || $_name === 'password' || $_name === 'meta' || $_name === 'createdate')
      return false;


    $f_type      = $_arg->Type;
    // for add new HTML5 feature to forms
    preg_match("/^([^(]*)(\((.*)\))?/", $f_type, $tmp_type);
    $f_length    = isset($tmp_type[3]) ? $tmp_type[3] : null;
    $f_dotpos    = strpos($f_length,',');
    $f_dotpos    = $f_dotpos?$f_dotpos:mb_strlen($f_length);
    $f_len       = substr($f_length, 0, $f_dotpos);
    $f_length    = $f_length;
    // $mymax    = "->maxlength('".$f_length."')";
    $result      = array();

    $result['form'] = null;
    $result['type'] = 'text';
    $result['name'] = $_name;


    switch ($tmp_type[1])
    {
      case 'enum':
        $result['type'] = 'radio';
        break;

      case 'timestamp':
        $result['type'] = 'text';
        break;
      case 'text':
      case 'mediumtext':
        $result['type'] = 'textarea';
        break;

      case 'smallint':
      case 'tinyint':
      case 'int':
      case 'bigint':
      case 'decimal':
      case 'float':
        $result['type'] = 'number';
        if($_name === 'barcode' || substr($f_type, mb_strlen($f_type)-8) == "zerofill")
        {
          $result['min'] = '1'.str_repeat("0",$f_len-1);
          // $result['pattern'] = ".{$f_len,}";
        }
        elseif( substr($f_type, mb_strlen($f_type)-8) == "unsigned")
          $result['min'] = 0;

        $result['max'] = str_repeat("9",$f_len);
        break;

      case 'varchar':
      case 'char':
        if($f_len>110)
          $result['type'] = 'textarea';
        else
          if($_name     == 'tel')       $result['type'] = 'tel';
          elseif($_name == 'pass')      $result['type'] = 'password';
          elseif($_name == 'password')  $result['type'] = 'password';
          elseif($_name == 'website')   $result['type'] = 'url';
          elseif($_name == 'email')     $result['type'] = 'email';
          elseif($_name == 'province')  $result['type'] = 'select';
          else                          $result['type'] = 'text';
        $result['maxlength'] = $f_len;
        break;

      case 'datetime':
      case 'date':
      case 'time':
        $result['type'] = 'text';
        break;

      case 'year':
        $result['type'] = 'number';
        $result['min']  = 0;
        $result['max']  = 9999;
        break;

      default:
        return ("N-A: Create Error, Please check for new datatype");
        break;
    }

    return $result;
  }


  /**
   * change field name with condition and return new user friendly name
   * some example is listed below in posts table
   *
   * filedname           label
   * $id              => id
   * $post_title      => title
   * $user_id         => user_
   * $user_idcustomer => user_customer
   *
   * @param  [type] $_fieldname filed raw name
   * @param  string $_export    export part needed
   * @return [type]             user friendly name has changed
   */
  public static function field_userFriendly($_fieldname, $_export = 'name')
  {
    $_fieldname = mb_strtolower($_fieldname);

    // check for _ exist in name or not
    $tmp_pos    = strpos($_fieldname, '_');
    if($tmp_pos)
    {
      $suffix = substr($_fieldname, $tmp_pos + 1);
      $prefix = substr($_fieldname, 0, $tmp_pos);

      // if is foreign key like user_id or permission_id
      // change it to user_ or permission_
      if($suffix === 'id')
      {
        $myname  = $prefix.'_';
        $mylabel = $prefix;
        $mytype  = 'foreign';
      }

      // if especial foreign key like user_idcustomer
      // change it to user_customer
      elseif(substr($suffix, 0, 2) === 'id')
      {
        $myname  = $prefix.'_'.substr($suffix, 2);
        $mylabel = $prefix.' '.substr($suffix, 2);
        $mytype  = 'foreign';
      }

      // for normal field like user_firstname or user_gender
      // change it to firstname or gender
      else
      {
        $myname  = $suffix;
        $mylabel = $suffix;
        $mytype  = 'normal';
      }
    }
    // in field like id return id
    else
    {
      $myname   = $_fieldname;
      $mylabel  = $_fieldname;
      $mytype  = 'id';
    }

    $result = array('name' => $myname, 'label' => $mylabel, 'type' => $mytype );

    return $result[$_export];
  }
}
