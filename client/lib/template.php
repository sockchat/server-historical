<?php

namespace sockchat;

class Template {
    protected $varList = [];

    public function SetVariable($key, $value) {
        $this->varList[$key] = $value;
    }

    public function SetVariables($arr) {
        if(!is_array($arr)) return;
        $this->varList = array_merge($this->varList, $arr);
    }

    public function Serve($url) {
        if(file_exists($url)) {
            $blob = file_get_contents($url);
            foreach($this->varList as $key => $value) {
                $blob = str_ireplace(["{{{ $key }}}", "{|{ $key }|}"                                   , "{[{ $key }]}"],
                                     [$value        , str_replace(["\\","\""], ["\\\\","\\\""], $value), str_replace(["\\","'"], ["\\\\", "\\'"], $value)], $blob);
            }
            echo $blob;
        } else echo "Error in templating engine: Cannot find $url!";
    }
}