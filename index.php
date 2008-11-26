<?php
/**
 *  Spaghetti's index.php file. Where the magic takes place. In this file,
 *  the only thing you have to define is your application's enviroment, whether
 *  it's a test, development or production one.
 *  
 *  Spaghetti is licensed under the MIT License. By using this software, you agree
 *  with the terms specified below. The license agreement extends to all the files
 *  within this installation.
 *
 *  The MIT License
 *  
 *  Copyright (c) 2008 Julio Greff de Oliveira,
 *                     Rafael Marin Bortolotto.
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 *
 *  @package Spaghetti
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 */

/**
 * Application environment (test|dev|production|...)
 */
    define("APP_ENV", "dev");
    require_once "spaghetti.php";
    new Dispatcher();

?>