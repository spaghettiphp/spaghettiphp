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
    public $recursive = 0;
}

class Post extends BaseModel {
    public $belongs_to = array("Author");
    public $table = "posts";
}

class Author extends BaseModel {
    public $has_many = array("Post");
    public $table = "authors";
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
                "name" => "Julio Greff",
                "email" => "julio@spaghettiphp.org"
            ),
            array(
                "name" => "Rafael Marin",
                "email" => "rafael@spaghettiphp.org"
            )
        );
        $this->Post = new Post;
        $this->Author = new Author;
        $this->Post->save_all($posts);
        $this->Author->save_all($authors);
        $this->Time = date("Y-m-d H:i:s");
    }
    public function tearDown() {
        $this->Post->execute($this->Post->sql_query("truncate"));
        $this->Author->execute($this->Author->sql_query("truncate"));
        $this->Post = null;
        $this->Author = null;
    }
    public function testSelect() {
        $results = $this->Author->find_all();
        $expected = array(
            array(
                "id" => 1,
                "name" => "Julio Greff",
                "email" => "julio@spaghettiphp.org",
                "created" => $this->Time
            ),
            array(
                "id" => 2,
                "name" => "Rafael Marin",
                "email" => "rafael@spaghettiphp.org",
                "created" => $this->Time
            )
        );
        $this->assertEqual($expected, $results);

        $results = $this->Author->find_all(array(
            "id >" => 1
        ));
        $expected = array(
            array(
                "id" => 2,
                "name" => "Rafael Marin",
                "email" => "rafael@spaghettiphp.org",
                "created" => $this->Time
            )
        );
        $this->assertEqual($expected, $results);

        $results = $this->Author->find_all(array(
            "name LIKE" => "%ul%"
        ));
        $expected = array(
            array(
                "id" => 1,
                "name" => "Julio Greff",
                "email" => "julio@spaghettiphp.org",
                "created" => $this->Time
            )
        );
        $this->assertEqual($expected, $results);
    }
    public function testSelectRelational() {
        $results = $this->Author->find_all(null, null, null, 1);
        $expected = array(
            array(
                "id" => 1,
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

        $results = $this->Post->find_all(null, null, null, 1);
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
                    "name" => "Rafael Marin",
                    "email" => "rafael@spaghettiphp.org",
                    "created" => $this->Time
                )
            )
        );
        $this->assertEqual($expected, $results);
  }
}

?>