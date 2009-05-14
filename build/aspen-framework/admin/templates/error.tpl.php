<script type="text/javascript">
function showParam(i) {
    currentParam = i;
    document.getElementById('paramHide').style.display = ''
    document.getElementById('paramSpace').style.display = ''
    document.getElementById('param').style.display = ''
    document.getElementById('param').innerHTML = '<pre>' + document.getElementById('param' + i).innerHTML + '</pre>'
}
function hideParam() {
    currentParam = -1;
    document.getElementById('paramHide').style.display = 'none'
    document.getElementById('paramSpace').style.display = 'none'
    document.getElementById('param').style.display = 'none'
}
function showOrHideParam(i) {
    if (currentParam == i) {
        hideParam()
    } else {
        showParam(i)
    }
}
function showFile(id) {
    eval('display = document.getElementById("file' + id + '").style.display')
    eval('if (display == "none") { document.getElementById("file' + id + '").style.display = "" } else { document.getElementById("file' + id + '").style.display = "none" } ');
}
function showDetails(cnt) {
    for (i = 0; i < cnt; ++i) {
        eval('document.getElementById("file' + i + '").style.display = ""')
    }
}
function hideDetails(cnt) {
    for (i = 0; i < cnt; ++i) {
        eval('document.getElementById("file' + i + '").style.display = "none"')
    }
}
var currentParam = -1;
</script>

<pre>
<b>Error type:</b> <?php echo $this->APP->error->getErrorType(); ?>

<b>Line number:</b> <?php echo $this->APP->error->getErrorLine(); ?>

<b>File:</b> <?php echo $this->APP->error->getErrorFile(); ?>

<?php

$c['default'] = '#000000';
$c['keyword'] = '#0000A0';
$c['number']  = '#800080';
$c['string']  = '#404040';
$c['comment'] = '#808080';

if (count($this->APP->error->getErrorInfo())) {
  foreach ($this->APP->error->getErrorInfo() as $k => $v) {
    echo '<b>';
    echo $k;
    echo ':</b> ';
    echo $v;
    echo "\r\n";
  }
} else {
  echo '<b>Message:</b> ';
  echo $this->APP->error->getErrorMessage();
  echo "\r\n";
}

echo "\r\n";

if (count($this->APP->error->getErrorTrace())) {

  echo '<span style="font-family: monospaced; font-size: 11px;">Trace: ' . count($this->APP->error->getErrorTrace()) . "</span> ";
  echo '<span style="font-family: monospaced; font-size: 11px; cursor: pointer;" onclick="showDetails('.count($this->APP->error->getErrorTrace()).')">[show details]</span> ';
  echo '<span style="font-family: monospaced; font-size: 11px; cursor: pointer;" onclick="hideDetails('.count($this->APP->error->getErrorTrace()).')">[hide details]</span>';

  echo "\r\n";
  echo "\r\n";



  echo '<ul>';
  $currentParam = -1;

  foreach ($this->APP->error->getErrorTrace() as $k => $v) {

    $currentParam++;

    echo '<li style="list-style-type: square;">';

    if (isset($v['class'])) {
        echo '<span onmouseover="this.style.color=\'#0000ff\'" onmouseout="this.style.color=\''.$c['keyword'].'\'" style="color: '.$c['keyword'].'; cursor: pointer;" onclick="showFile('.$k.')">';
        echo $v['class'];
        echo ".";
    } else {
        echo '<span onmouseover="this.style.color=\'#0000ff\'" onmouseout="this.style.color=\''.$c['keyword'].'\'" style="color: '.$c['keyword'].'; cursor: pointer;" onclick="showFile('.$k.')">';
    }

    echo $v['function'];
    echo '</span>';
    echo " (";

    $sep = '';
    $v['args'] = (array) @$v['args'];
    foreach ($v['args'] as $arg) {

      $currentParam++;

      echo $sep;
      $sep    = ', ';
      $color = '#404040';

      switch (true) {

        case is_bool($arg):
          $param  = 'TRUE';
          $string = $param;
          break;

        case is_int($arg):
        case is_float($arg):
          $param  = $arg;
          $string = $arg;
          $color = $c['number'];
          break;

        case is_null($arg):
          $param = 'NULL';
          $string = $param;
          break;

        case is_string($arg):
          $param = $arg;
          $string = 'string[' . strlen($arg) . ']';
          break;

        case is_array($arg):
          ob_start();
          print_r($arg);
          $param = ob_get_contents();
          ob_end_clean();
          $string = 'array[' . count($arg) . ']';
          break;

        case is_object($arg):
          ob_start();
          print_r($arg);
          $param = ob_get_contents();
          ob_end_clean();
          $string = 'object: ' . get_class($arg);
          break;

        case is_resource($arg):
          $param = 'resource: ' . get_resource_type($arg);
          $string = 'resource';
          break;

        default:
          $param = 'unknown';
          $string = $param;
          break;

        }

        echo '<span style="cursor: pointer; color: '.$color.';" onclick="showOrHideParam('.$currentParam.')" onmouseout="this.style.color=\''.$color.'\'" onmouseover="this.style.color=\'#dd0000\'">';
        echo $string;
        echo '</span>';
        echo '<span id="param'.$currentParam.'" style="display: none;">' . $param . '</span>';

      }

      echo ")";
      echo "\r\n";

      if (!isset($v['file'])) {
          $v['file'] = 'unknown';
      }
      if (!isset($v['line'])) {
          $v['line'] = 'unknown';
      }

      $v['line'] = @$v['line'];
      echo '<span id="file'.$k.'" style="display: none; color: gray;">';
      if ($v['file'] && $v['line']) {
          echo 'FILE: ' . basename($v['file']);
      }
      echo "\r\n";
      echo 'LINE: ' . $v['line'] . "\r\n";
      echo 'DIR:  ' . dirname($v['file']) . "\r\n";
      echo '</span>';

      echo '</li>';
  }

  echo '</ul>';

} else {
    echo '<b>File:</b> ';
    echo basename($file);
    echo ' (' . $this->APP->error->error->getErrorLine() . ') ';
    echo dirname($file);
}

?>

<?php echo '<span id="paramHide" style="display: none; font-family: monospaced; font-size: 11px; cursor: pointer;" onclick="hideParam()">[hide param]</span>';?>
<span id="paramSpace" style="display: none;">

</span>
</pre>
