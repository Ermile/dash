<?php
namespace addons\includes\cls\form;

class symbol extends \lib\form
{
	function __construct()
	{
		$this->title   = $this->make("text")->name('title');
		$this->slug		= $this->make("text")->name('slug')->maxlength(40);
								 // ->validate()->slugify("'.$prefix.'_title")
		$this->desc 	= $this->make("textarea")->name("desc");
		$this->email 	= $this->make("email")->name("email");
		$this->website = $this->make("text")->name("website");
		$this->type 	= $this->make("text")->name("type");

		$this->tel 	   = $this->make("tel")->name('tel')->type("tel")->label(T_("Tel"))
							->required()->maxlength(17)->pattern(".{9,}");

		$this->mobile 	= $this->make("mobile")->name('mobile')->type("tel")->label(T_("Mobile"))->pl(T_("Mobile"))->pos('hint--top')
							->required()->maxlength(17)->pattern(".{10,}")->autocomplete('off');

		$this->pass =
		$this->password = $this->make("password")->name("pass")->label(T_("Password"))->autocomplete('off')->pos('hint--bottom')
							->maxlength(40)->pattern("^.{5,40}$")->title(T_("between 5-40 character"));

		// $this->password->validate()->password();
	}
}
?>