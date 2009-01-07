<?php
class Template extends Shell {
    public function render($filename = null, $data = array()) {
        $file = App::exists("Template", $filename, "phtm");
        if($file):
            if(!is_string($file)):
				return false;
	    	endif;
		    extract($data, EXTR_SKIP);
		    ob_start();
		    include $file;
		    $out = ob_get_clean();
		    return $out;
        else:
            $this->error("missing template file \"{$filename}\"");
            return false;
        endif;
    }
}
?>