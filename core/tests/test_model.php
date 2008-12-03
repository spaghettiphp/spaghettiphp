<?php
/**
 *  Put description here.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Tests.Model
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

include_once "setup.php";

class BaseModel extends AppModel {
    public $recursion = 0;
    public $table = false;
}

class Post extends BaseModel {
    public $belongsTo = array("Author");
    public $table = "posts";
}

class Author extends BaseModel {
    public $hasMany = array("Post");
    public $table = "authors";
}

class Profile extends BaseModel {
    public $hasOne = array("Author");
    public $table = "profiles";
}

class TestModel extends UnitTestCase {
    public function setUp() {
        $posts = array(
            array(
                "author_id" => 1,
                "title" => "Spaghetti",
                "text" => "PHP Framework"
            ),
            array(
                "author_id" => 1,
                "title" => "Model",
                "text" => "Testing Model"
            ),
            array(
                "author_id" => 2,
                "title" => "Is this a blog?",
                "text" => "We're only testing model"
            )
        );
        $authors = array(
            array(
                "profile_id" => 1,
                "name" => "Julio Greff",
                "email" => "julio@spaghettiphp.org"
            ),
            array(
                "profile_id" => 2,
                "name" => "Rafael Marin",
                "email" => "rafael@spaghettiphp.org"
            )
        );
        $profiles = array(
            array(
                "url" => "http://juliogreff.net"
            ),
            array(
                "url" => "http://rafaelmarin.net"
            )
        );
        $this->Post = ClassRegistry::init("Post");
        $this->Author = ClassRegistry::init("Author");
        $this->Profile = ClassRegistry::init("Profile");
        $this->Time = date("Y-m-d H:i:s");
        $this->Post->saveAll($posts);
        $this->Author->saveAll($authors);
        $this->Profile->saveAll($profiles);
    }
    public function tearDown() {
        $this->Post->execute($this->Post->sqlQuery("truncate"));
        $this->Author->execute($this->Author->sqlQuery("truncate"));
        $this->Profile->execute($this->Profile->sqlQuery("truncate"));
        $this->Post = null;
        $this->Author = null;
        $this->Profile = null;
    }
    public function testSelect() {
        $results = $this->Author->findAll();
        $expected = array(
            array(
                "id" => 1,
                "profile_id" => 1,
                "name" => "Julio Greff",
                "email" => "julio@spaghettiphp.org",
                "created" => $this->Time
            ),
            array(
                "id" => 2,
                "profile_id" => 2,
                "name" => "Rafael Marin",
                "email" => "rafael@spaghettiphp.org",
                "created" => $this->Time
            )
        );
        $this->assertEqual($expected, $results);

        $results = $this->Author->findAll(array(
            "id >" => 1
        ));
        $expected = array(
            array(
                "id" => 2,
                "profile_id" => 2,
                "name" => "Rafael Marin",
                "email" => "rafael@spaghettiphp.org",
                "created" => $this->Time
            )
        );
        $this->assertEqual($expected, $results);

        $results = $this->Author->findAll(array(
            "name LIKE" => "%ul%"
        ));
        $expected = array(
            array(
                "id" => 1,
                "profile_id" => 1,
                "name" => "Julio Greff",
                "email" => "julio@spaghettiphp.org",
                "created" => $this->Time
            )
        );
        $this->assertEqual($expected, $results);

        $results = $this->Author->findAll(array(
            "id BETWEEN" => array(0, 1)
        ));
        $expected = array(
            array(
                "id" => 1,
                "profile_id" => 1,
                "name" => "Julio Greff",
                "email" => "julio@spaghettiphp.org",
                "created" => $this->Time
            )
        );
        $this->assertEqual($expected, $results);
    }
    public function testGenerateAssociation() {
        $this->Author->createLinks();
        $results = $this->Author->generateAssociation("has_many");
        $expected = array(
            "Post" => array(
                "class_name" => "Post",
                "foreign_key" => "author_id",
                "conditions" => array(),
                "order" => null,
                "limit" => null,
                "dependent" => true
            )
        );
        $this->assertEqual($expected, $results);

        $this->Post->createLinks();
        $results = $this->Post->generateAssociation("belongsTo");
        $expected = array(
            "Author" => array(
                "className" => "Author",
                "foreignKey" => "author_id",
                "conditions" => array(),
            )
        );
        $this->assertEqual($expected, $results);
    }
    public function testSelectRelational() {
        $results = $this->Author->findAll(null, null, null, 1);
        $expected = array(
            array(
                "id" => 1,
                "profile_id" => 1,
                "name" => "Julio Greff",
                "email" => "julio@spaghettiphp.org",
                "created" => $this->Time,
                "post" => array(
                    array(
                        "id" => 1,
                        "author_id" => 1,
                        "title" => "Spaghetti",
                        "text" => "PHP Framework",
                        "created" => $this->Time,
                        "modified" => $this->Time
                    ),
                    array(
                        "id" => 2,
                        "author_id" => 1,
                        "title" => "Model",
                        "text" => "Testing Model",
                        "created" => $this->Time,
                        "modified" => $this->Time
                    )
                )
            ),
            array(
                "id" => 2,
                "profile_id" => 2,
                "name" => "Rafael Marin",
                "email" => "rafael@spaghettiphp.org",
                "created" => $this->Time,
                "post" => array(
                    array(
                        "id" => 3,
                        "author_id" => 2,
                        "title" => "Is this a blog?",
                        "text" => "We're only testing model",
                        "created" => $this->Time,
                        "modified" => $this->Time
                    )
                )
            )
        );
        $this->assertEqual($expected, $results);

        $results = $this->Post->findAll(null, null, null, 1);
        $expected = array(
            array(
                "id" => 1,
                "author_id" => 1,
                "title" => "Spaghetti",
                "text" => "PHP Framework",
                "created" => $this->Time,
                "modified" => $this->Time,
                "author" => array(
                    "id" => 1,
                    "profile_id" => 1,
                    "name" => "Julio Greff",
                    "email" => "julio@spaghettiphp.org",
                    "created" => $this->Time
                )
            ),
            array(
                "id" => 2,
                "author_id" => 1,
                "title" => "Model",
                "text" => "Testing Model",
                "created" => $this->Time,
                "modified" => $this->Time,
                "author" => array(
                    "id" => 1,
                    "profile_id" => 1,
                    "name" => "Julio Greff",
                    "email" => "julio@spaghettiphp.org",
                    "created" => $this->Time
                )
            ),
            array(
                "id" => 3,
                "author_id" => 2,
                "title" => "Is this a blog?",
                "text" => "We're only testing model",
                "created" => $this->Time,
                "modified" => $this->Time,
                "author" => array(
                    "id" => 2,
                    "profile_id" => 2,
                    "name" => "Rafael Marin",
                    "email" => "rafael@spaghettiphp.org",
                    "created" => $this->Time
                )
            )
        );
        $this->assertEqual($expected, $results);

        $results = $this->Profile->findById(1, null, null, 1);
        $expected = array(
            "id" => 1,
            "url" => "http://juliogreff.net",
            "author" => array(
                "id" => 1,
                "profile_id" => 1,
                "name" => "Julio Greff",
                "email" => "julio@spaghettiphp.org",
                "created" => $this->Time
            )
        );
        $this->assertEqual($expected, $results);

        $results = $this->Author->findById(1, array("post" => array("id >" => 1)), null, 2);
        $expected = array(
            "id" => 1,
            "profile_id" => 1,
            "name" => "Julio Greff",
            "email" => "julio@spaghettiphp.org",
            "created" => $this->Time,
            "post" => array(
                array(
                    "id" => 2,
                    "author_id" => 1,
                    "title" => "Model",
                    "text" => "Testing Model",
                    "created" => $this->Time,
                    "modified" => $this->Time,
                    "author" => array(
                        "id" => 1,
                        "profile_id" => 1,
                        "name" => "Julio Greff",
                        "email" => "julio@spaghettiphp.org",
                        "created" => $this->Time
                    )
                )
            )
        );
        $this->assertEqual($expected, $results);
    }
    public function testDelete() {
        $this->Post->delete(3);
        $results = $this->Post->findAll();
        $expected = array(
            array(
                "id" => 1,
                "author_id" => 1,
                "title" => "Spaghetti",
                "text" => "PHP Framework",
                "created" => $this->Time,
                "modified" => $this->Time
            ),
            array(
                "id" => 2,
                "author_id" => 1,
                "title" => "Model",
                "text" => "Testing Model",
                "created" => $this->Time,
                "modified" => $this->Time
            )
        );
        $this->assertEqual($expected, $results);
        
        $this->Author->delete(1, true);
        $results = $this->Post->findAll();
        $expected = array();
        $this->assertEqual($expected, $results);
        
        $this->Profile->delete(2, true);
        $results = $this->Author->findAll();
        $expected = array();
        $this->assertEqual($expected, $results);
    }
    public function testSave() {
        $time = date("Y-m-d H:i:s");
        $this->Profile->save(array(
            "url" => "http://jaderubini.net",
            "author" => array(
                "name" => "Jader Rubini",
                "email" => "jader@spaghettiphp.org"                
            )
        ));
        $results = $this->Profile->findById(3, null, null, 1);
        $expected = array(
            "id" => 3,
            "url" => "http://jaderubini.net",
            "author" => array(
                "id" => 3,
                "profile_id" => 3,
                "name" => "Jader Rubini",
                "email" => "jader@spaghettiphp.org",
                "created" => $time,
            ),
        );
        $this->assertEqual($expected, $results);

        $this->Profile->save(array(
            "id" => 1,
            "url" => "http://juliogreff.blog.br",
            "author" => array(
                "id" => 1,
                "name" => "Julio Greff",
                "email" => "julio@juliogreff.net"                
            )
        ));
        $results = $this->Profile->findById(1, null, null, 1);
        $expected = array(
            "id" => 1,
            "url" => "http://juliogreff.blog.br",
            "author" => array(
                "id" => 1,
                "profile_id" => 1,
                "name" => "Julio Greff",
                "email" => "julio@juliogreff.net",
                "created" => $this->Time,
            ),
        );
        $this->assertEqual($expected, $results);
    }
}

?>