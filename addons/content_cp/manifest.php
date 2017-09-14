<?php
$addons = array();
$addons['attachments'] = [
	'path'			=> 'addons/attachments',
];

$modules = array();
$modules['home'] = array(
	'title' 		=> T_('Dashboard'),
);

$modules['posts'] = array(
	'desc' 			=> T_('Use posts to share your news in specefic category'),
	'icon'			=> 'file-text-o',
	'permissions'	=> ['view', 'add', 'edit', 'delete', 'admin'],
	'addons'		=> ['attachments']
);

$modules['tags'] = array(
	'permissions'	=> ['view', 'add', 'edit', 'delete'],
	'desc' 			=> T_('Assign keywords to your posts using tags'),
	'parent'		=> 'posts'
);

$modules['categories'] = array(
	'desc' 			=> T_('Use categories to define sections of your site and group related posts'),
	'title' 		=> T_('Categories'),
	'permissions'	=> ['view', 'add', 'edit', 'delete'],
	'parent'		=> 'posts'
);

$modules['pages'] = array(
	'desc' 			=> T_('Use pages to share your static content'),
	'icon'			=> 'files-o',
	'permissions'	=> ['view', 'add', 'edit', 'delete', 'admin'],
	'addons'		=> ['attachments']
);

$modules['books'] = array(
	'disable'		=> true,
	'desc'			=> T_('Use book to define important parts to use in posts'),
	'title' 		=> T_('Books'),
	'icon'			=> 'book',
	'permissions'	=> ['view', 'add', 'edit', 'delete', 'admin']
);

$modules['helps'] = [
	'title' 		=> T_('Help Center'),
	'desc' 			=> T_('Help center is ready to use'),
	'icon'			=> 'question-circle',
	'permissions'	=> ['view', 'add', 'edit', 'delete', 'admin'],
	'addons'		=> ['attachments']
];

$modules['helpcategories'] = [
	'parent' 		=> 'helps',
	'permissions'	=> ['view', 'add', 'edit', 'delete']
];

$modules['bookcategories'] = array(
	'desc' 			=> T_('Use categories to define sections of your site and group related books'),
	'title' 		=> T_('Book Categories'),
	'parent'		=> 'books',
	'permissions'	=> ['view', 'add', 'edit', 'delete']
);

$modules['socialnetworks'] = array(
	'disable'		=> true,
	'desc' 			=> T_('Publish new post in social networks'),
	'icon'			=> 'share-alt',
	'permissions'	=> ['view', 'add', 'edit', 'delete', 'admin']
);

$modules['attachments'] = array(
	'permissions' 	=> ['view', 'add', 'edit', 'delete', 'admin'],
	'desc' 			=> T_('Upload your media'),
	'icon'			=> 'picture-o',
);

$modules['filecategories'] = array(
	'desc' 			=> T_('Use categories to define sections of your site and group related files'),
	'title' 		=> T_('File Categories'),
	'parent'		=> 'files',
	'permissions'	=> ['view', 'add', 'edit', 'delete']
);

$modules['polls'] = array(
	'disable' 		=> true,
	'icon'			=> 'hand-paper-o',
	'permissions'	=> ['view', 'add', 'edit', 'delete', 'admin']
);

$modules['pollcategories'] = array(
	'parent' 		=> 'polls',
	'permissions'	=> ['view', 'add', 'edit', 'delete']
);

$modules['users'] = array(
	'icon'			=> 'user',
	'permissions'	=> ['view', 'add', 'edit', 'delete', 'admin']
);

$modules['permissions'] = array(
	'permissions'	=> ['view', 'add', 'edit', 'delete'],
	'icon' 			=> 'lock'
);

$modules['visitors'] = array(
	'childless' 	=> true,
	'icon'			=> 'line-chart',
	'permissions'	=> ['view']
);

$modules['options'] = array(
	'permissions' 	=> ['view', 'edit'],
	'icon'			=> 'cog',
	'submodules'	=> ['status' => true,'general' => true,'config' => true,'sms' => true,'social' => true,'account' => true]
);

$modules['tools'] = array(
	'permissions'	=> ['view', 'admin'],
	'icon'			=> 'wrench',
);
if(Tld !== 'dev')
	$modules['tools']['parent'] = 'post';

$modules['lock'] = array(
	'parent' => 'profile',
);

$modules['logout'] = array(
	'parent' => 'profile',
);

$modules['profile'] = array('parent' => 'global', 'childless' => true, 'permissions' => ['view']);
return ["modules" => $modules, "addons" => $addons];
?>