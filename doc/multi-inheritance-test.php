<?php

class Theme {
    public function hello() {
        error_log(__METHOD__);
        return "Theme says hello";
    }
}

class Basic extends Theme {
    public function hello() {
        error_log(__METHOD__);
        return "Basic says: " . parent::hello();  // Can access Theme's hello
    }
}

class TopNav extends Basic {
    public function hello() {
        error_log(__METHOD__);
        return "TopNav says: " . parent::hello() . "\n"; // Can access Basic's hello
    }
}

$nav = new TopNav();
echo $nav->hello(); 

// Output: "TopNav says: Basic says: Theme says hello"
