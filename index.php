<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8">
        <title>Erro - Spaghetti* Framework</title>
        
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
            
            section#error {
                border-bottom: 1px solid #444140;
                display: block;
                padding: 40px 0;
            }
            
                section#error h1 {
                    color: #FFCC00;
                    font: lighter 30px/28px "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, arial, sans-serif;
                    margin-bottom: 20px;
                }
                
                section#error p {
                    font-size: 15px;
                    line-height: 22px;
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

        <section id="error">
            <h1>O Spaghetti* não conseguiu iniciar!</h1>
            <?php if(!function_exists("apache_get_version")): ?>
            <p>Isso se deve ao servidor que você está usando. O Spaghett* já vem pronto para ser usado com o Apache. Se você usa outro servidor, você precisará configurá-lo para funcionar com as regras de reescrita de URL requeridas pelo Spaghetti*.</p>
            <?php elseif(!in_array("mod_rewrite", apache_get_modules())): ?>
            <p>Para que o Spaghetti* funcione corretamente, você precisa habilitar o módulo de reescrita de URL do Apache, o <strong>mod_rewrite</strong>.</p>
            <?php else: ?>
            <p>Você tem tudo o que precisa para rodar o Spaghetti*, entretanto, seu servidor não permite que as URLs sejam escritas. Você precisa definir a diretiva <strong>AllowOverride</strong> com pelo menos <strong>Options</strong> e <strong>FileInfo</strong> para que o Spaghetti* possa funcionar.</p>
            <?php endif ?>
        </section>

        <footer>
            <p>Obrigado por usar Spaghetti* :)</p>
        </footer>
    </body>
</html>