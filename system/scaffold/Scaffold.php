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

		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
		$this->APP->template->addView(SYSTEM_PATH.DS.'scaffold'.DS.'templates'.DS . 'generic.tpl.php');
		$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
		$this->APP->template->display( array('html' => $this->view_html($table) ));

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

			$model = $this->APP->model->open($table);
			$results 	= $model->results();
			$schema 	= $model->getSchema();
			$key_field 	= $model->getPrimaryKey();

			foreach($schema['schema'] as $field){
				$thead .= sprintf('<th>%s</th>' . "\n", $this->fieldName($field->name));
			}

			// loop the results
			if($results['RECORDS']){
				foreach($results['RECORDS'] as $result){

					$tbody .= '<tr>' . "\n";

					foreach($result as $field => $value){
						$tbody .= sprintf('<td>%s</td>' . "\n", $this->APP->template->createLink($value, 'edit', array('id' => $result[$key_field])));
					}

					$tbody .= '</tr>' . "\n";

				}
			}


			// begin building the html
			$html .= sprintf('<h2>%s</h2>'."\n", ucwords($table));
			$html .= '<p>'.$this->APP->template->createLink('Add a new record', 'add') . "</p>\n";

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

			$model = $this->APP->model->open($table);
			$results 	= $model->results();
			$schema 	= $model->getSchema();
			$key_field 	= $model->getPrimaryKey();

			foreach($schema['schema'] as $field){
				$thead .= sprintf('			<th>%s</th>' . "\n", $this->fieldName($field->name));
			}

			// loop the results


$tbody .= "
		<?php
			if($".$table."['RECORDS']){
				foreach($".$table."['RECORDS'] as \$record){
		?>
		<tr>\n";

foreach($schema as $field){
	$tbody .= sprintf('		<td>%s</td>' . "\n", "<?php print \$this->createLink(\$record['".$field->name."'], 'edit', array('id' => \$record['".$key_field."'])) ?>");
}

$tbody .= "		</tr>\n
		<?php
				}
			}
		?>

";


			// begin building the html
			$html .= sprintf('<h2>%s</h2>'."\n", ucwords($table));
			$html .= "<p><?php print \$this->APP->template->createLink('Add a new record', 'add'); ?></p>\n";

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

		$html = '		';
		$html .= sprintf('<label for="%s">%s:</label><br />' . "\n", $field->name, $this->fieldName($field->name));

		if($return_html){
			$value = "<?php print \$values['".$field->name."']; ?>";
		}

		if(in_array($field->type, $this->APP->config('mysql_field_group_int'))){
			$html .= sprintf('		<input type="text" name="%s" id="%s" value="%s" class="text" />', $field->name, $field->name, $value);
		}


		if(in_array($field->type, $this->APP->config('mysql_field_group_text'))){
			if($field->max_length > 0 && $field->max_length <= 100){
				$html .= sprintf('		<input type="text" name="%s" id="%s" value="%s" class="text" />', $field->name, $field->name, $value);
			} else {
				$html .= sprintf('		<textarea rows="20" cols="40" name="%s" id="%s">%s</textarea>', $field->name, $field->name, $value);
			}
		}

		if(in_array($field->type, $this->APP->config('mysql_field_group_date'))){
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
		$this->APP->form->load($table, $id);

		// if form has been submitted
		if($this->APP->form->isSubmitted()){

			if($this->APP->form->save($id)){
				$this->APP->router->redirect('view');
			}
		}


		// make sure the template has access to all current values
		$values = $this->APP->form->getCurrentValues();
		$schema = $this->APP->model->getSchema();

		// build the html form
		$html = '';
		$html .= sprintf('<h2>%s</h2>'."\n\n", $type . ' ' . ucwords($table) . ' Record');

		if($type == 'Edit'){
			if($return_html){
				$html .= "<p><?php print \$this->createLink('Delete', 'delete', array('".$this->APP->model->getPrimaryKey()."' => \$values['".$this->APP->model->getPrimaryKey()."'])); ?></p>\n\n";
			} else {
				$html .= $this->APP->template->createLink('Delete', 'delete', array($this->APP->model->getPrimaryKey() => $id)) . "\n";
			}
		}

		if($return_html){
			$html .= '<form action="<?php print $this->createFormAction(); ?>" method="post">'."\n";
		} else {
			$html .= sprintf('<form action="%s" method="post">'."\n", $this->APP->template->createFormAction());
		}

		foreach($schema['schema'] as $field){

			if(!$field->primary_key){
				$html .= '	<p>' . "\n";
				$html .= $this->getFormField($field, $values[$field->name], $return_html)."\n";
				$html .= '	</p>' . "\n";
			}
		}

		$html .= '	<p><input type="submit" name="submit" value="'.($type == 'Edit' ? 'Save Changes' : 'Add').'" /></p>' . "\n";
		$html .= '</form>';

		if($return_html){

			return $html;

		} else {

			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'header.tpl.php');
			$this->APP->template->addView(SYSTEM_PATH.DS.'scaffold'.DS.'templates'.DS . 'generic.tpl.php');
			$this->APP->template->addView($this->APP->template->getTemplateDir().DS . 'footer.tpl.php');
			$this->APP->template->display(array('html' => $html));

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
			if($this->APP->model->delete($table, $id)){
				$this->APP->router->redirect('view');
			}
		}
	}
}
?>