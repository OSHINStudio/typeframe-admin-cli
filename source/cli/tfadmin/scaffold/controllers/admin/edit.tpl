&lt;?php

$@{model}@ = @{model}@::Get($_REQUEST['@{primarykey}@']);
if(!$@{model}@->exists()){
	Typeframe::Redirect('Invalid @{model}@ Requested...', TYPEF_WEB_DIR . 'admin/@{applicationname}@');
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	<pm:loop name="fields" as="field value"><pm:if expr="value->type != 'hidden'">$@{field}@ = @$_POST['@{field}@'];
	</pm:if></pm:loop>
	$errors = array();
	<pm:loop name="fields" as="field value"><pm:if expr="value->type != 'hidden'"><pm:if expr="value->required">if(!isset($@{field}@) || empty($@{field}@)){
		$errors[] = 'Please enter a @{field}@.';
	}@{"\n"}@</pm:if></pm:if></pm:loop>
	
	if(empty($errors)){
		<pm:loop name="fields" as="field value"><pm:if expr="value->type != 'hidden'"><pm:if expr="value->type =='calendar'">$@{model}@->set('@{field}@', date('Y-m-d H:i:s', strtotime($@{field}@)));</pm:if><pm:else>$@{model}@->set('@{field}@', $@{field}@);</pm:else>
		</pm:if></pm:loop>$@{model}@->save();
		
		Typeframe::Redirect('@{modelnick}@ edited', TYPEF_WEB_DIR . '/admin/@{applicationname}@');
		return;
	} else {
		$pm->setVariable('errors', $errors);
	}
}

<pm:loop name="fields" as="field value">$pm->setVariable('@{field}@', $@{model}@->get('@{field}@'));
</pm:loop>

<pm:loop name="fields" as="field value"><pm:if expr="value->type =='model'">$@{value->model}@s = new @{value->model}@();@{"\n"}@$pm->setVariable('@{value->model}@s', $@{value->model}@s);@{"\n"}@</pm:if></pm:loop>