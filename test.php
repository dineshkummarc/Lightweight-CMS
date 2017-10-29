<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function test($name, $test){
    $color = "#DDAA00";
    $result = $test();
    if($result === true){
         $color = "#00CC00";
    }  elseif($result === false) {
        $color = "#CC0000";
    }else{
        $name = '[PENDING]'.$name;
    }
    print('<div style="color: '.$color.';">'.$name.'</div>');
}


//sessions

test("logout should delete user session and fourm session", function (){
    
});

//posting
test("Delete last post of topic should update topic last post info", function (){
    
});

test("Delete first post of topic should update topic first post info", function (){
    
});

test("Delete last post of forum should update forum last post", function (){
    
});

test("Delete last topic of forum should update forum last post", function (){
    
});

test("Delete post should decrease author post count by one", function (){
    
});

test("Delete topic should decrease postcounts of all authors by the number of posts they have in topic", function (){
    
});

//forums
test("Delete forum should decrease postcounts of all authors by the number of posts they have in forum", function (){
    
});



function asdf(){
inverse(1);
}

class Foo {
    public $aMemberVar = 'aMemberVar Member Variable';
    public $aFuncName = 'aMemberFunc';
   
   
    function aMemberFunc() {
        print 'Inside `aMemberFunc()`';
    }
    
    function inverse($x) {
        if (!$x) {
            throw new Exception('Division by zero.');
        }
        return 1/$x;
    }

    function asdf(){
        inverse();
    }

}

$foo = new Foo; 