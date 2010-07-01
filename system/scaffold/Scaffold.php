<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since 		1.0
 */

/**
 * This class manages our templates and loads them for display
 * @package Aspen_Framework
 */
class Scaffold extends Library {

	/**
	 *
	 * @var <type>
	 */
	private $langs = array();

	/**
	 * Converts text into a proper English field name
	 * @param string $name
	 * @return string
	 * @access private
	 */
	private function fieldName($name){
		return ucwords(str_replace(array("-", "_"), ' ', strtolower($name)));
	}


	/**
	 * Creates the basic list view scaffold
	 * @param string $table
	 * @access public
	 */
	public function view($table = false){

		app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
		app()->template->addView(SYSTEM_PATH.DS.'scaffold'.DS.'templates'.DS . 'generic.tpl.php');
		app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
		app()->template->display( array('html' => $this->view_html($table) ));

	}


	/**
	 * Creates the basic list view scaffold
	 * @param string $table
	 * @access private
	 */
	public function view_html($table = false){

		$html = '';

		if($table){

			$thead = '';
			$tbody = '';

			$model = app()->model->open($table);
			$results 	= $model->results();
			$schema 	= $model->getSchema();
			$key_field 	= $model->getPrimaryKey();

			foreach($schema['schema'] as $field){
				$thead .= sprintf('<th>%s</th>' . "\n", $this->fieldName($field->name));
			}

			// loop the results
			if($results){
				foreach($results as $result){

					$tbody .= '<tr>' . "\n";

					foreach($result as $field => $value){
						$tbody .= sprintf('<td>%s</td>' . "\n", app()->template->link($value, 'edit', array('id' => $result[$key_field])));
					}

					$tbody .= '</tr>' . "\n";

				}
			}


			// begin building the html
			$html .= sprintf('<h2>%s</h2>'."\n", ucwords($table));
			$html .= '<p>'.app()->template->link('Add a new record', 'add') . "</p>\n";

			// create our table
			$html .= '<table>' . "\n";
			$html .= sprintf('<tr>%s</tr>' . "\n", $thead);
			$html .= '<thead>' . "\n";
			$html .= '</thead>' . "\n";
			$html .= '<tbody>' . "\n";
			$html .= $tbody;
			$html .= '</tbody>' . "\n";

			// close table
			$html .= '</table>' . "\n";

		}

		return $html;

	}


	/**
	 * Creates the basic list view scaffold
	 * @param string $table
	 * @access private
	 */
	public function view_php($table = false){

		$html = '';

		if($table){

			$thead = '';
			$tbody = '';

			$model = app()->model->open($table);
			$results 	= $model->results();
			$schema 	= $model->getSchema();
			$key_field 	= $model->getPrimaryKey();

			foreach($schema['schema'] as $field){
				$this->langs['table:th:'.$field->name] = $this->fieldName($field->name);
				$thead .= sprintf('			<th><?php print $this->text(\'table:th:%s\'); ?></th>' . "\n", $field->name);
			}

			// loop the results


$tbody .= "
		<?php
			if($".$table."){
				foreach($".$table." as \$record){
		?>
		<tr>\n";

foreach($schema['schema'] as $field){
	$tbody .= sprintf('		<td>%s</td>' . "\n", "<?php print \$this->link(\$record['".$field->name."'], 'edit', array('id' => \$record['".$key_field."'])) ?>");
}

$tbody .= "		</tr>\n
		<?php
				}
			}
		?>

";


			// begin building the html
			$this->langs['index:title'] = ucwords($table);
			$html .= '<h2><?php print $this->text(\'index:title\'); ?></h2>'."\n\n";
			$html .= '<?php print app()->sml->printMessage(); ?>'."\n\n";
			$this->langs['add-new'] = 'Add new record';
			$html .= "<p><?php print \app()->template->link(\$this->text('add-new'), 'add'); ?></p>\n\n";

			// create our table
			$html .= '<table>' . "\n";
			$html .= '	<thead>' . "\n";
			$html .= '		<tr>' . "\n";
			$html .= $thead;
			$html .= '		</tr>' . "\n";
			$html .= '	</thead>' . "\n";
			$html .= '	<tbody>' . "\n";
			$html .= $tbody;
			$html .= '	</tbody>' . "\n";

			// close table
			$html .= '</table>' . "\n";

		}

		return $html;

	}


	/**
	 * Generates an add/edit form field with name and value
	 * @param string $name
	 * @param string $type
	 * @param string $value
	 * @return string
	 * @access private
	 */
	private function getFormField($field, $value = false, $return_html = false){

		$this->langs['form:'.$field->name] = $this->fieldName($field->name);

		$html = '		';
		$html .= sprintf('<label for="%s"><?php print $this->text(\'form:%1$s\'); ?>:</label><br />' . "\n", $field->name);

		if($return_html){
			$value = "<?php print \$form->cv('".$field->name."'); ?>";
		}

		if(in_array($field->type, app()->config('mysql_field_group_int')) || in_array($field->type, app()->config('mysql_field_group_dec'))){
			$html .= sprintf('		<input type="text" name="%s" id="%s" value="%s" class="text" />', $field->name, $field->name, $value);
		}


		if(in_array($field->type, app()->config('mysql_field_group_text'))){
			if($field->max_length > 0 && $field->max_length <= 100){
				$html .= sprintf('		<input type="text" name="%s" id="%s" value="%s" class="text" />', $field->name, $field->name, $value);
			} else {
				$html .= sprintf('		<textarea rows="20" cols="40" name="%s" id="%s">%s</textarea>', $field->name, $field->name, $value);
			}
		}

		if(in_array($field->type, app()->config('mysql_field_group_date'))){
			$html .= sprintf('		<input type="text" name="%s" id="%s" value="%s" class="text" />', $field->name, $field->name, $value);
		}

		return $html;

	}


	/**
	 * Creates a basic add record form scaffold
	 * @param string $table
	 * @access public
	 */
	public function add($table = false){
		$this->recordForm($table, 'Add');
	}


	/**
	 * Creates a basic edit record form scaffold
	 * @param string $table
	 * @param integer $id
	 * @access public
	 */
	public function edit($table = false, $id = false){
		$this->recordForm($table, 'Edit', $id);
	}


	/**
	 * Generates the add and edit form
	 * @param string $table
	 * @param mixed $id
	 * @access private
	 */
	public function recordForm($table = false, $type = 'Add', $id = false, $return_html = false){

		// loads record - if fails it defaults to loadTable
		$form = new Form($table, $id);

		// if form has been submitted
		if($form->isSubmitted()){

			if($form->save($id)){
				app()->router->redirect('view');
			}
		}


		// make sure the template has access to all current values
		// @todo fix this
		$values = $form->getCurrentValues();

		$model = app()->model->open($table);
		$schema = $model->getSchema();

		// build the html form
		$html = '';
		$html .= '<h2><?php print $this->text(\'form:title-\'.ADD_OR_EDIT); ?></h2>'."\n\n";

		$this->langs['form:title-add'] = 'Add ' . ucwords($table) . ' Record';
		$this->langs['form:title-edit'] = 'Edit ' . ucwords($table) . ' Record';

		if($type == 'Edit'){
			if($return_html){
				$html .= "<?php if(IS_EDIT_PAGE){ ?>\n";
				$html .= "<p><?php print \$this->link('Delete', 'delete', array('".$model->getPrimaryKey()."' => \$form->cv('".$model->getPrimaryKey()."'))); ?></p>\n";
				$html .= "<?php } ?>\n\n";
			} else {
				$html .= app()->template->link('Delete', 'delete', array($model->getPrimaryKey() => $id)) . "\n";
			}
		}

		$html .= '<?php print $form->printErrors(); ?>'."\n";
		$html .= '<?php print app()->sml->printMessage(); ?>'."\n\n";

		if($return_html){
			$html .= '<form action="<?php print $this->action(); ?>" method="post">'."\n";
		} else {
			$html .= sprintf('<form action="%s" method="post">'."\n", app()->template->action());
		}

		foreach($schema['schema'] as $field){

			if(!$field->primary_key){
				$html .= '	<p>' . "\n";
				$html .= $this->getFormField($field, $values->cv($field->name), $return_html)."\n";
				$html .= '	</p>' . "\n";
			}
		}

		$this->langs['form:submit-add'] = 'Add Record';
		$this->langs['form:submit-edit'] = 'Save Changes';

		$html .= '	<p><input type="submit" name="submit" value="<?php print $this->text(\'form:submit-\'.ADD_OR_EDIT); ?>" /></p>' . "\n";
		$html .= '</form>';

		if($return_html){

			return $html;

		} else {

			app()->template->addView(app()->template->getTemplateDir().DS . 'header.tpl.php');
			app()->template->addView(SYSTEM_PATH.DS.'scaffold'.DS.'templates'.DS . 'generic.tpl.php');
			app()->template->addView(app()->template->getTemplateDir().DS . 'footer.tpl.php');
			app()->template->display(array('html' => $html));

		}
	}


	/**
	 * Creates a basic delete record form scaffold
	 * @param string $table
	 * @param integer $id
	 * @access public
	 */
	public function delete($table = false, $id = false){
		if($table && $id){
			if(app()->model->delete($table, $id)){
				app()->router->redirect('view');
			}
		}
	}


	/**
	 *
	 */
	public function languageFile(){

		$content = '<?php'."\n";

		foreach($this->langs as $key => $lang){
			$content .= "\$lang['*']['".$key."'] = '".$lang."';"."\n";
		}

		$content .= '?>';

		return $content;

	}
}
?>