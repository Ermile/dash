<?php
namespace lib\mvc;

class view extends \lib\view
{
	use \lib\mvc\viewes\constructor;
	use \lib\mvc\viewes\posts;
	use \lib\mvc\viewes\terms;

	/**
	 * set title for pages depending on condition
	 */
	public function set_title()
	{
		if($page_title = $this->data->page['title'])
		{
			// set title of locations if exist in breadcrumb
			if(isset($this->data->breadcrumb[$page_title]))
			{
				$page_title = $this->data->breadcrumb[$page_title];
			}
			// replace title of page
			if(!$this->data->page['special'])
			{
				$page_title = ucwords(str_replace('-', ' ', $page_title));
			}
			// for child page set the
			if($this->data->child && SubDomain === 'cp')
			{
				if(substr($this->module(), -3) === 'ies')
				{
					$moduleName = substr($this->module(), 0, -3).'y';
				}
				elseif(substr($this->module(), -1) === 's')
				{
					$moduleName = substr($this->module(), 0, -1);
				}
				else
				{
					$moduleName = $this->module();
				}

				$childName = $this->child(true);
				if($childName)
				{
					$page_title = T_($childName).' '.T_($moduleName);
				}
			}

			// set user-friendly title for books
			if($this->module() === 'book')
			{
				$breadcrumb = $this->model()->breadcrumb();
				$page_title = $breadcrumb[0] . ' ';
				array_shift($breadcrumb);

				foreach ($breadcrumb as $value)
				{
					$page_title .= $value . ' - ';
				}
				$page_title = substr($page_title, 0, -3);
				$this->data->parentList = $this->model()->sp_books_nav();
			}

			// translate all title at last step
			$page_title = T_($page_title);
			$this->data->page['title'] = $page_title;
			if($this->data->page['special'])
			{
				$this->global->title = $page_title;
			}
			else
			{
				$this->global->title = $page_title.' | '.T_($this->data->site['title']);
			}
		}
		else
		{
			$this->global->title = T_($this->data->site['title']);
		}

		$this->global->short_title = substr($this->global->title, 0, strrpos(substr($this->global->title, 0, 120), ' ')) . '...';
	}


	/**
	 * overite corridor to call set_title before creating page
	 * @return [type] [description]
	 */
	function corridor()
	{
		// if set title exist
		if(method_exists($this, 'set_title'))
		{
			$this->set_title();
		}
		parent::corridor();
	}
}
?>