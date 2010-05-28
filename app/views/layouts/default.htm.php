<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->html->charset() ?>
        <title>Spaghetti* Framework</title>
        
        <style type="text/css">
            * {
                margin:0;
                padding:0;
            }
            html {
                background: #37312F;
            }
            body {
                background: #23201E;
                color: #FFFFFF;
                font: 12px "Helvetica", "Arial", sans-serif;
                margin: 40px auto;
                padding: 50px 70px;
                width: 580px;
            }
            a {
                color: #9c0;
            }
            code {
                font: 10px Monaco, Consolas, "Courier New", monospace;
            }
            hr {
                border: none;
                clear: both;
            }
            
            header {
                border-bottom: 1px solid #444140;
                display: block;
                padding-bottom: 40px;
            }

                header #logo {
                    color: #FFFFFF;
                    font-size: 26px;
                    font-weight: bold;
                    letter-spacing: -2px;
                    text-decoration: none;
                }
                    header #logo span {
                        color: #99CC00;
                    }
                    
                header #info {
                    -moz-border-radius: 3px;
                    -webkit-border-radius: 3px;
                    border: 1px solid #2F2C2A;
                    color: #99CC00;
                    display: block;
                    float: right;
                    font-size: 11px;
                    height: 20px;
                    line-height: 20px;
                    outline: none;
                    margin-top: 5px;
                    text-align: center;
                    text-decoration: none;
                    width: 175px;
                }
                
                    header #info:hover {
                        background: -webkit-gradient(linear, left top, left bottom, color-stop(0.0, #23201E), color-stop(1.0, #111111));
                        border-color: #444140;
                    }
                    
                    header #info:active,
                    header #info.on {
                        background: -webkit-gradient(linear, left top, left bottom, color-stop(0.0, #111111), color-stop(1.0, #23201E));
                        color: #FFFFFF;
                    }
            
            section#environment {
                border-bottom: 1px solid #444140;
                display: none;
                padding: 20px 0;
            }
            
                section#environment table {
                    -moz-border-radius: 3px;
                    -webkit-border-radius: 3px;
                    border-collapse: collapse;
                    border: 1px solid #2F2C2A;
                    width: 100%;
                }
                
                section#environment table th {
                    background: #282422;
                    border-bottom: 1px solid #37312F;
                    padding: 5px 8px;
                    text-align: left;
                    width: 180px;
                }
                
                section#environment table td {
                    border-bottom: 1px solid #2F2C2A;
                    padding: 5px 8px;
                    text-align: left;
                }
            
            section#head {
                border-bottom: 1px solid #444140;
                display: block;
                padding: 40px 0;
            }
            
                section#head h1 {
                    color: #99CC00;
                    font: lighter 36px "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, arial, sans-serif;
                    margin-bottom: 20px;
                }
                
                section#head p {
                    font-size: 15px;
                    line-height: 22px;
                }
                
            section#quickstart {
                border-bottom: 1px solid #444140;
                display: block;
                padding: 40px 0;
            }
            
                section#quickstart div {
                    float: left;
                    margin-right: 20px;
                    width: 130px;
                }
            
                    section#quickstart div:nth-child(4) {
                        margin-right: 0;
                    }
                
                section#quickstart div span {
                    color: #99CC00;
                    font: lighter 36px "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, arial, sans-serif;
                    margin-bottom: 20px;
                }
                
                section#quickstart div h3 {
                    font-size: 12px;
                    letter-spacing: -0.5px;
                    margin-bottom: 10px;
                }
                
                section#quickstart div p {
                    color: #6E6766;
                    font-size: 11px;
                }
                
                section#quickstart div:hover p {
                    color: #FFF;
                }
            
            section.features {
                border-bottom: 1px solid #444140;
                clear: both;
                display: block;
                padding: 40px 0;
            }
            
                section.features div {
                    float: left;
                    width: 275px;
                }
                
                    section.features div h2 {
                        color: #99CC00;
                        font-size: 16px;
                        font-weight: normal    ;
                        margin-bottom: 20px;
                    }
                    
                    section.features div p {
                        line-height: 18px;
                    }
                
                    section.features div:first-child {
                        margin-right: 20px;
                    }
            
            footer {
                display: block;
                padding-top: 40px;
            }
            
                footer p {
                    color: #37312F;
                    font: lighter 16px "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, arial, sans-serif;
                    text-align: center;
                }
        </style>
    </head>
    
    <body>
        <header>
            <a href="http://spaghettiphp.org" id="logo">Spaghetti<span>*</span></a>
        </header>

        <?php echo $this->contentForLayout ?>
        
        <footer>
            <p>Obrigado por usar Spaghetti* :)</p>
        </footer>
    </body>
</html>