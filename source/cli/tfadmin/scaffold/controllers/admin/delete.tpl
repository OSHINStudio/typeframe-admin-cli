&lt;?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$@{model}@ = @{model}@::Get($_REQUEST['@{primarykey}@']);
	if(!$@{model}@->exists()){
		Typeframe::Redirect('Invalid @{model}@ Requested...', TYPEF_WEB_DIR . '/admin/@{applicationname}@');
	}
	$@{model}@->delete();
	
	Typeframe::Redirect('@{modelnick}@ deleted.', TYPEF_WEB_DIR . '/admin/@{applicationname}@');
} else {
	Typeframe::Redirect('Nothing to do.', TYPEF_WEB_DIR . '/admin/@{applicationname}@');
}
