# Create Contact Form
##### create contact form in url : `example.dev/contact` save & show server request in `example.dev/contact/read`
1. make directory `contact` in `content` directory
2. put `controller & view & display` in `contact` directory *tip : because we don't need `model.php` so don't set that
    - `display.html`'s content :  [Link](https://geeksesi.xyz/upload/view1.text )
    * tip : `display`'s style put in `public_html/static/css` 
3. to Answer Form Request we create 
    * view config function to echo Confirmation alert 
    * controller config function to save Data to `db.text` in : `public_html/db.text`

## view : 
- view class must be extends `\lib\mvc\view` and namespace must be set : `namespace content\contact` like this code : 
```
<?php
namespace content\contact;

class view extends \lib\mvc\view
{
   public function config()
   {
      if(isset($_POST["submit"]))
      {
         $this->data->alert = "OK , Your Message is Send :) ";
      }
   }
   
}
```
## controller :
- controller class must be extends `\lib\mvc\controller` and namespace must be Set : `namespace content\contact` like this code : 
```
<?php
namespace content\contact;

class controller extends \lib\mvc\controller
{
   public function config()
   {
      if(isset($_POST["submit"]))
      {
         $this->display = TRUE;
         $json = json_encode($_POST);
         $db = fopen("db.text", "a+");
         $break = "\n";
        
         fwrite($db, $json);
         fwrite($db, $break);
         fclose($db);
      }
   }
 
}
```
---
---


4. to show db.text content in table in `example.dev/contact/read` url i make `read` directory in `conten/contact` directory and put new `controller & view & display` in `read` directory like this :
## controller : 
```
<?php
namespace content\contact\read;

class controller extends \lib\mvc\controller
{
   public function config()
   {
      $file_size = filesize(root."/public_html/db.text") ;
      if($file_size > 2)
        {
            $this->display = TRUE;
            $this->get(NULL,"table")->ALL();
        }
        else
        {
            $this->display = FALSE;
            echo "sorry but file is empty ";
        }
    }
}
```

## view : 
```
<?php
namespace content\contact\read;

class view extends \lib\mvc\view
{
    public function view_table()
    {
        $file = fopen(root."/public_html/db.text", "r") or die("Unable to open file!");
        $file_size = filesize(root."/public_html/db.text") ;
        $read = fread($file, $file_size );
        fclose($file);
        $array = explode( "\n", $read);
        $i = 0;
        foreach($array as $item)
        {
            $decode = json_decode($item, TRUE);
            $name[$i] = $decode["Name"];
            $email[$i] = $decode["Email"];
            $message[$i] = $decode["Message"];
            $i = $i + 1;
        }
        $i--;
        $this->data->rows =--$i;
        $this->data->name =$name;
        $this->data->email =$email;
        $this->data->message =$message;
    }
}
```
## display :
 [Link](https://geeksesi.xyz/upload/view2.text )

---
5. in `example.dev/contact/read`'s controller check if `db.text` is `!empty` load `view_table`'s function in `view` else echo `sorry but file is empty`
6. in `example.dev/contact/read`'s view `db.text`'s content convert to array and display in table in `display.html` by `Twig` and this variable : `name | email | message`